<?php
    $servername = "localhost";
    $username = "username";
    $password = "password";
    $dbname = "mydb";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully.";

    /**
     * Create Database
     */
    require_once "impl/Users.php";
    if (mysqli_query($conn, Database\User::CREATE_TABLE)) {
        echo "Table created successfully";
    } else {
        echo "Error creating table: " . mysqli_error($conn);
    }

    mysqli_close($conn);

    echo "Closed connection.";
?>
