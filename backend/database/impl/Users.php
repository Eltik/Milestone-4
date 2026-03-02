<?php
    namespace Database;

    class User {
        const CREATE_TABLE = "
        CREATE TABLE IF NOT EXISTS users (
            id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            email VARCHAR(20),
            phone VARCHAR(20) NOT NULL,
            username VARCHAR(20) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            connector_ids JSON NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        );
        ";

        /**
        * @param array $connectorIds
        */
        public function __construct(
            public string $id,
            public string $email,
            public string $phone,
            public string $username,
            private string $passwordHash,
            public ?string $createdAt = null,
            public ?string $updatedAt = null,
            public array $connectorIds = []
        ) {}

        /**
        * @param array $connectorIds
        */
        public static function create(
            string $email,
            string $phone,
            string $username,
            string $plainPassword,
            array $connectorIds = []
        ): User {
            return new User(
                self::generateUUID(),
                $email,
                $phone,
                $username,
                password_hash($plainPassword, PASSWORD_DEFAULT),
                date("Y-m-d H:i:s"), // created_at
                date("Y-m-d H:i:s"), // updated_at
                $connectorIds
            );
        }

        public static function getUser(\mysqli $conn, string $id): ?User {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "s", $id);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$row) {
                return null;
            }

            return new User(
                $row["id"],
                $row["email"],
                $row["phone"],
                $row["username"],
                $row["password_hash"],
                $row["created_at"],
                $row["updated_at"],
                json_decode($row["connector_ids"], true) ?? []
            );
        }

        public function insertUser(\mysqli $conn): bool {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (id, email, phone, username, password_hash, created_at, updated_at, connector_ids) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $connectorJson = json_encode($this->connectorIds);

            mysqli_stmt_bind_param($stmt, "ssssssss",
                $this->id,
                $this->email,
                $this->phone,
                $this->username,
                $this->passwordHash,
                $this->createdAt,
                $this->updatedAt,
                $connectorJson
            );

            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $result;
        }

        public function verifyPassword(string $password): bool {
            return password_verify($password, $this->password_hash);
        }

        private static function generateUUID(): string {
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0, 0xffff), random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0x0fff) | 0x4000,  // version 4
                random_int(0, 0x3fff) | 0x8000,  // variant
                random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
            );
        }
    }
?>
