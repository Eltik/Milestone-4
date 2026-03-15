<?php
    // Usage: php set-role.php <username> <role>
    // Example: php set-role.php john admin
    //          php set-role.php john user

    if (php_sapi_name() !== 'cli') {
        die("This script can only be run from the command line.");
    }

    if ($argc < 3) {
        echo "Usage: php set-role.php <username> <role>\n";
        echo "Roles: admin, user\n";
        exit(1);
    }

    $targetUsername = $argv[1];
    $targetRole = $argv[2];

    if (!in_array($targetRole, ['admin', 'user'])) {
        echo "Error: Role must be 'admin' or 'user'.\n";
        exit(1);
    }

    // Connect to database
    $conn = mysqli_connect("p:127.0.0.1", "root", "");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error() . "\n");
    }
    mysqli_select_db($conn, "milestone4");

    // Check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id, username, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $targetUsername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo "Error: User '$targetUsername' not found.\n";
        exit(1);
    }

    if ($row["role"] === $targetRole) {
        echo "User '$targetUsername' already has role '$targetRole'.\n";
        exit(0);
    }

    // Update role
    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $targetRole, $targetUsername);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        echo "Updated '$targetUsername' role: {$row['role']} -> $targetRole\n";
    } else {
        echo "Error: Failed to update role.\n";
        exit(1);
    }

    mysqli_close($conn);
?>
