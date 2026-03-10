<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../database/Index.php";
    require_once __DIR__ . "/../sources/impl/RobinHood.php";
    require_once __DIR__ . "/../sources/impl/YahooFinance.php";

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $method = $_SERVER["REQUEST_METHOD"];

    header("Content-Type: application/json");

    function getRequestBody(): array {
        $body = file_get_contents("php://input");
        return json_decode($body, true) ?? [];
    }

    function jsonResponse(int $status, array $data): void {
        http_response_code($status);
        echo json_encode($data);
    }

    function userToArray(Database\User $user): array {
        return [
            "id" => $user->id,
            "email" => $user->email,
            "phone" => $user->phone,
            "username" => $user->username,
            "connectorIds" => $user->connectorIds,
            "createdAt" => $user->createdAt,
            "updatedAt" => $user->updatedAt
        ];
    }

    function connectorToArray(Database\Connectors $connector): array {
        return [
            "id" => $connector->id,
            "userId" => $connector->userId,
            "authenticationInformation" => $connector->authenticationInformation,
            "portfolio" => $connector->portfolio,
            "createdAt" => $connector->createdAt,
            "updatedAt" => $connector->updatedAt
        ];
    }

    // Welcome
    if ($uri === "/api" || $uri === "/api/") {
        jsonResponse(200, ["message" => "Welcome to Milestone 4 API"]);
    }

    // POST /api/auth/signup — Register a new user
    elseif ($uri === "/api/auth/signup" && $method === "POST") {
        $body = getRequestBody();

        if (empty($body["email"]) || empty($body["phone"]) || empty($body["username"]) || empty($body["password"])) {
            jsonResponse(400, ["error" => "Missing required fields: email, phone, username, password"]);
            exit;
        }

        // Check if username already exists
        $existing = Database\User::getUserByUsername($conn, $body["username"]);
        if ($existing) {
            jsonResponse(409, ["error" => "Username already taken"]);
            exit;
        }

        // Check if email already exists
        $existing = Database\User::getUserByEmail($conn, $body["email"]);
        if ($existing) {
            jsonResponse(409, ["error" => "Email already registered"]);
            exit;
        }

        $user = Database\User::create(
            $body["email"],
            $body["phone"],
            $body["username"],
            $body["password"]
        );

        if ($user->insertUser($conn)) {
            $_SESSION["user_id"] = $user->id;
            jsonResponse(201, ["user" => userToArray($user)]);
        } else {
            jsonResponse(500, ["error" => "Failed to create user"]);
        }
    }

    // POST /api/auth/login — Log in
    elseif ($uri === "/api/auth/login" && $method === "POST") {
        $body = getRequestBody();

        if (empty($body["username"]) || empty($body["password"])) {
            jsonResponse(400, ["error" => "Missing required fields: username, password"]);
            exit;
        }

        $user = Database\User::getUserByUsername($conn, $body["username"]);

        if (!$user || !$user->verifyPassword($body["password"])) {
            jsonResponse(401, ["error" => "Invalid username or password"]);
            exit;
        }

        $_SESSION["user_id"] = $user->id;
        jsonResponse(200, ["user" => userToArray($user)]);
    }

    // POST /api/auth/logout — Log out
    elseif ($uri === "/api/auth/logout" && $method === "POST") {
        session_destroy();
        jsonResponse(200, ["message" => "Logged out"]);
    }

    // GET /api/auth/me — Get current session user
    elseif ($uri === "/api/auth/me" && $method === "GET") {
        if (empty($_SESSION["user_id"])) {
            jsonResponse(401, ["error" => "Not authenticated"]);
            exit;
        }

        $user = Database\User::getUser($conn, $_SESSION["user_id"]);
        if ($user) {
            jsonResponse(200, ["user" => userToArray($user)]);
        } else {
            session_destroy();
            jsonResponse(401, ["error" => "Not authenticated"]);
        }
    }

    // POST /api/users — Create user
    elseif ($uri === "/api/users" && $method === "POST") {
        $body = getRequestBody();

        if (empty($body["email"]) || empty($body["phone"]) || empty($body["username"]) || empty($body["password"])) {
            jsonResponse(400, ["error" => "Missing required fields: email, phone, username, password"]);
            exit;
        }

        $user = Database\User::create(
            $body["email"],
            $body["phone"],
            $body["username"],
            $body["password"]
        );

        if ($user->insertUser($conn)) {
            jsonResponse(201, userToArray($user));
        } else {
            jsonResponse(500, ["error" => "Failed to create user"]);
        }
    }

    // GET /api/users/{id}
    elseif (preg_match('#^/api/users/([a-f0-9\-]+)$#', $uri, $matches) && $method === "GET") {
        $user = Database\User::getUser($conn, $matches[1]);

        if ($user) {
            jsonResponse(200, userToArray($user));
        } else {
            jsonResponse(404, ["error" => "User not found"]);
        }
    }

    // GET /api/users/{id}/connectors
    elseif (preg_match('#^/api/users/([a-f0-9\-]+)/connectors$#', $uri, $matches) && $method === "GET") {
        $connectors = Database\Connectors::getConnectorsByUser($conn, $matches[1]);
        $result = array_map('connectorToArray', $connectors);
        jsonResponse(200, $result);
    }

    // POST /api/connectors — Create connector
    elseif ($uri === "/api/connectors" && $method === "POST") {
        $body = getRequestBody();

        if (empty($body["user_id"]) || !isset($body["authentication_information"])) {
            jsonResponse(400, ["error" => "Missing required fields: user_id, authentication_information"]);
            exit;
        }

        $connector = Database\Connectors::create(
            $body["user_id"],
            $body["authentication_information"],
            $body["portfolio"] ?? []
        );

        if ($connector->insertConnector($conn)) {
            jsonResponse(201, connectorToArray($connector));
        } else {
            jsonResponse(500, ["error" => "Failed to create connector"]);
        }
    }

    // GET /api/connectors/{id}
    elseif (preg_match('#^/api/connectors/([a-f0-9\-]+)$#', $uri, $matches) && $method === "GET") {
        $connector = Database\Connectors::getConnector($conn, $matches[1]);

        if ($connector) {
            jsonResponse(200, connectorToArray($connector));
        } else {
            jsonResponse(404, ["error" => "Connector not found"]);
        }
    }

    // GET /api/sources/robinhood/portfolio
    elseif ($uri === "/api/sources/robinhood/portfolio" && $method === "GET") {
        $robinhood = new Sources\RobinHood();
        jsonResponse(200, $robinhood->getPortfolio());
    }

    // GET /api/sources/robinhood/stocks
    elseif ($uri === "/api/sources/robinhood/stocks" && $method === "GET") {
        $robinhood = new Sources\RobinHood();
        $symbol = $_GET["symbol"] ?? null;

        if ($symbol) {
            $stock = $robinhood->getStockBySymbol($symbol);
            if ($stock) {
                jsonResponse(200, $stock);
            } else {
                jsonResponse(404, ["error" => "Stock not found: " . strtoupper($symbol)]);
            }
        } else {
            jsonResponse(200, $robinhood->getStocks());
        }
    }

    // GET /api/sources/yahoo/portfolio
    elseif ($uri === "/api/sources/yahoo/portfolio" && $method === "GET") {
        $yahoo = new Sources\YahooFinance();
        jsonResponse(200, $yahoo->getPortfolio());
    }

    // GET /api/sources/yahoo/stocks
    elseif ($uri === "/api/sources/yahoo/stocks" && $method === "GET") {
        $yahoo = new Sources\YahooFinance();
        $symbol = $_GET["symbol"] ?? null;

        if ($symbol) {
            $stock = $yahoo->getStockBySymbol($symbol);
            if ($stock) {
                jsonResponse(200, $stock);
            } else {
                jsonResponse(404, ["error" => "Stock not found: " . strtoupper($symbol)]);
            }
        } else {
            jsonResponse(200, $yahoo->getStocks());
        }
    }

    // 404
    else {
        jsonResponse(404, ["error" => "Not found"]);
    }
?>
