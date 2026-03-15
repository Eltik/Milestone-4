<?php
    namespace Database;

    class User {
        const CREATE_TABLE = "
        CREATE TABLE IF NOT EXISTS users (
            id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            email VARCHAR(50),
            phone VARCHAR(20) NOT NULL,
            username VARCHAR(20) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
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
            public array $connectorIds = [],
            public string $role = 'user'
        ) {}

        public function isAdmin(): bool {
            return $this->role === 'admin';
        }

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
                json_decode($row["connector_ids"], true) ?? [],
                $row["role"] ?? 'user'
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

        public static function getUserByUsername(\mysqli $conn, string $username): ?User {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
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
                json_decode($row["connector_ids"], true) ?? [],
                $row["role"] ?? 'user'
            );
        }

        public static function getUserByEmail(\mysqli $conn, string $email): ?User {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
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
                json_decode($row["connector_ids"], true) ?? [],
                $row["role"] ?? 'user'
            );
        }

        /**
         * Search users by query string across username, email, and phone.
         * returning { results, total, lastPage }.
         *
         * @return array{results: User[], total: int, lastPage: int}
         */
        public static function searchUsers(
            \mysqli $conn,
            string $query = '',
            int $page = 1,
            int $perPage = 10,
            string $sort = 'created_at',
            string $sortDirection = 'DESC'
        ): array {
            // Whitelist allowed sort columns
            $allowedSorts = ['username', 'email', 'role', 'created_at', 'updated_at'];
            if (!in_array($sort, $allowedSorts)) {
                $sort = 'created_at';
            }
            $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

            $offset = $page > 0 ? $perPage * ($page - 1) : 0;

            // Build WHERE clause: LIKE match across multiple columns
            $where = "";
            $likeParam = "";
            if (!empty($query)) {
                $where = "WHERE (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $likeParam = "%" . $query . "%";
            }

            // Count query for total results
            $countSql = "SELECT COUNT(*) as count FROM users $where";
            // Data query with sorting and pagination
            $dataSql = "SELECT * FROM users $where ORDER BY $sort $sortDirection LIMIT ? OFFSET ?";

            // Execute count query
            $countStmt = mysqli_prepare($conn, $countSql);
            if (!empty($query)) {
                mysqli_stmt_bind_param($countStmt, "sss", $likeParam, $likeParam, $likeParam);
            }
            mysqli_stmt_execute($countStmt);
            $countResult = mysqli_stmt_get_result($countStmt);
            $total = (int)(mysqli_fetch_assoc($countResult)["count"] ?? 0);
            mysqli_stmt_close($countStmt);

            // Execute data query
            $dataStmt = mysqli_prepare($conn, $dataSql);
            if (!empty($query)) {
                mysqli_stmt_bind_param($dataStmt, "sssii", $likeParam, $likeParam, $likeParam, $perPage, $offset);
            } else {
                mysqli_stmt_bind_param($dataStmt, "ii", $perPage, $offset);
            }
            mysqli_stmt_execute($dataStmt);
            $dataResult = mysqli_stmt_get_result($dataStmt);

            $results = [];
            while ($row = mysqli_fetch_assoc($dataResult)) {
                $results[] = new User(
                    $row["id"],
                    $row["email"],
                    $row["phone"],
                    $row["username"],
                    $row["password_hash"],
                    $row["created_at"],
                    $row["updated_at"],
                    json_decode($row["connector_ids"], true) ?? [],
                    $row["role"] ?? 'user'
                );
            }
            mysqli_stmt_close($dataStmt);

            $lastPage = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

            return [
                "results" => $results,
                "total" => $total,
                "lastPage" => $lastPage
            ];
        }

        public function verifyPassword(string $password): bool {
            return password_verify($password, $this->passwordHash);
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
