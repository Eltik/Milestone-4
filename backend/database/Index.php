<?php
    $servername = "127.0.0.1";
    $username = "postgres";
    $password = "password";
    $dbname = "milestone-4";

    $conn = mysqli_connect($servername, $username, $password);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully.";

    /**
     * Create Database
     */
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (mysqli_query($conn, $sql)) {
        echo "Database ready.";
    } else {
        echo "Error creating database: " . mysqli_error($conn);
    }
    mysqli_select_db($conn, $dbname);

    require_once "impl/Users.php";
    if (mysqli_query($conn, Database\User::CREATE_TABLE)) {
        echo "Table created successfully";
    } else {
        echo "Error creating table: " . mysqli_error($conn);
    }

    mysqli_close($conn);

    echo "Closed connection.";
?>
