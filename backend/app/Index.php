<?php
    require_once __DIR__ . "/../database/Index.php";

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $method = $_SERVER["REQUEST_METHOD"];

    header("Content-Type: application/json");

    if ($uri === "/" || $uri === "") {
        echo json_encode(["message" => "Welcome to Milestone 4 API"]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Not found"]);
    }
?>
