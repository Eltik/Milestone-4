<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../database/Index.php";
    require_once __DIR__ . "/../sources/impl/RobinHood.php";
    require_once __DIR__ . "/../sources/impl/YahooFinance.php";
    require_once __DIR__ . "/../database/impl/Portfolios.php";

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

    // --- API ROUTES ---

    // Welcome
    if ($uri === "/api" || $uri === "/api/") {
        jsonResponse(200, ["message" => "Welcome to Milestone 4 API"]);
    }

    // AUTH: Signup
    elseif ($uri === "/api/auth/signup" && $method === "POST") {
        $body = getRequestBody();
        if (empty($body["email"]) || empty($body["phone"]) || empty($body["username"]) || empty($body["password"])) {
            jsonResponse(400, ["error" => "Missing required fields"]);
            exit;
        }

        if (Database\User::getUserByUsername($conn, $body["username"]) || Database\User::getUserByEmail($conn, $body["email"])) {
            jsonResponse(409, ["error" => "User already exists"]);
            exit;
        }

        $user = Database\User::create($body["email"], $body["phone"], $body["username"], $body["password"]);
        if ($user->insertUser($conn)) {
            $_SESSION["user_id"] = $user->id;
            jsonResponse(201, ["user" => userToArray($user)]);
        } else {
            jsonResponse(500, ["error" => "Failed to create user"]);
        }
    }

    // AUTH: Login
    elseif ($uri === "/api/auth/login" && $method === "POST") {
        $body = getRequestBody();
        $user = Database\User::getUserByUsername($conn, $body["username"] ?? '');
        if (!$user || !$user->verifyPassword($body["password"] ?? '')) {
            jsonResponse(401, ["error" => "Invalid credentials"]);
            exit;
        }
        $_SESSION["user_id"] = $user->id;
        jsonResponse(200, ["user" => userToArray($user)]);
    }

    // AUTH: Logout
    elseif ($uri === "/api/auth/logout" && $method === "POST") {
        session_destroy();
        jsonResponse(200, ["message" => "Logged out"]);
    }

    // USER: Get Current Portfolio
    elseif ($uri === "/api/user/portfolio" && $method === "GET") {
        if (empty($_SESSION["user_id"])) {
            jsonResponse(401, ["error" => "Not authenticated"]);
            exit;
        }
        $portfolio = Database\Portfolios::getPortfolioByUser($conn, $_SESSION["user_id"]);
        if ($portfolio) {
            jsonResponse(200, [
                "holdings" => $portfolio->holdings,
                "allocation" => $portfolio->allocation,
                "last_updated" => $portfolio->lastUpdated
            ]);
        } else {
            jsonResponse(404, ["error" => "No portfolio found"]);
        }
    }

    // USER: Get Current Connectors
    elseif ($uri === "/api/user/connectors" && $method === "GET") {
        if (empty($_SESSION["user_id"])) {
            jsonResponse(401, ["error" => "Not authenticated"]);
            exit;
        }
        $connectors = Database\Connectors::getConnectorsByUser($conn, $_SESSION["user_id"]);
        jsonResponse(200, array_map('connectorToArray', $connectors));
    }

// CONNECT: Simulate Brokerage Connection
elseif ($uri === "/api/sources/connect" && $method === "POST") {
    if (empty($_SESSION["user_id"])) {
        jsonResponse(401, ["error" => "Not authenticated"]);
        exit;
    }
    
    $body = getRequestBody();
    $userId = $_SESSION["user_id"];
    
    // 1. Capture the provider name from the request (e.g., 'robinhood' or 'yahoo')
    $providerName = $body["provider"] ?? 'robinhood';
    
    $authInfo = [
        "simulated" => true, 
        "provider" => $providerName
    ];
    
    $connector = Database\Connectors::create($userId, $authInfo);
    
    if ($connector->insertConnector($conn)) {
        // 2. Pass the provider name to the seeder so it can label the stocks
        // We use ucfirst() to make it look nice, like "Robinhood"
        Database\Portfolios::seedRandomPortfolio($conn, $userId, ucfirst($providerName));
        
        jsonResponse(201, ["status" => "success"]);
    } else {
        jsonResponse(500, ["error" => "Failed to connect"]);
    }
}

    // 404
    else {
        jsonResponse(404, ["error" => "Not found"]);
    }
?>