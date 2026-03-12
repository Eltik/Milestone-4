<?php
    $servername = "p:127.0.0.1";
    $username = "username";
    $password = "";
    $dbname = "milestone4";

    $conn = mysqli_connect($servername, $username, $password);

    if (!function_exists('logger')) {
        function logger(string $message): void {
            file_put_contents("php://stdout", $message . "\n");
        }
    }

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    logger("Connected successfully.");

    /**
     * Create Database
     */
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (mysqli_query($conn, $sql)) {
        logger("Database ready.");
    } else {
        logger("Error creating database: " . mysqli_error($conn));
    }
    mysqli_select_db($conn, $dbname);

    // Initialize Users
    require_once "impl/Users.php";
    if (mysqli_query($conn, Database\User::CREATE_TABLE)) {
        logger("Users table created successfully.");
    } else {
        logger("Error creating users table: " . mysqli_error($conn));
    }

    // Initialize Connectors
    require_once "impl/Connectors.php";
    if (mysqli_query($conn, Database\Connectors::CREATE_TABLE)) {
        logger("Connectors table created successfully.");
    } else {
        logger("Error creating connectors table: " . mysqli_error($conn));
    }

    // Initialize Portfolios
    require_once "impl/Portfolios.php";
    if (mysqli_query($conn, Database\Portfolios::CREATE_TABLE)) {
        logger("Portfolios table created successfully.");
    } else {
        logger("Error creating portfolios table: " . mysqli_error($conn));
    }

    logger("Database initialized.");
?>