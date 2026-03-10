<?php
    namespace Database;

    class Connectors {
        const CREATE_TABLE = "
        CREATE TABLE IF NOT EXISTS connectors (
            id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            user_id VARCHAR(36) NOT NULL,
            authentication_information JSON NOT NULL,
            portfolio JSON NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        ";

        /**
        * @param array $authenticationInformation
        * @param array $portfolio
        */
        public function __construct(
            public string $id,
            public string $userId,
            public array $authenticationInformation,
            public array $portfolio,
            public ?string $createdAt = null,
            public ?string $updatedAt = null
        ) {}

        /**
        * @param array $authenticationInformation
        * @param array $portfolio
        */
        public static function create(
            string $userId,
            array $authenticationInformation,
            array $portfolio = []
        ): Connectors {
            return new Connectors(
                self::generateUUID(),
                $userId,
                $authenticationInformation,
                $portfolio,
                date("Y-m-d H:i:s"),
                date("Y-m-d H:i:s")
            );
        }

        public static function getConnector(\mysqli $conn, string $id): ?Connectors {
            $stmt = mysqli_prepare($conn, "SELECT * FROM connectors WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "s", $id);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$row) {
                return null;
            }

            return new Connectors(
                $row["id"],
                $row["user_id"],
                json_decode($row["authentication_information"], true) ?? [],
                json_decode($row["portfolio"], true) ?? [],
                $row["created_at"],
                $row["updated_at"]
            );
        }

        public static function getConnectorsByUser(\mysqli $conn, string $userId): array {
            $stmt = mysqli_prepare($conn, "SELECT * FROM connectors WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "s", $userId);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $connectors = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $connectors[] = new Connectors(
                    $row["id"],
                    $row["user_id"],
                    json_decode($row["authentication_information"], true) ?? [],
                    json_decode($row["portfolio"], true) ?? [],
                    $row["created_at"],
                    $row["updated_at"]
                );
            }

            mysqli_stmt_close($stmt);

            return $connectors;
        }

        public function insertConnector(\mysqli $conn): bool {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO connectors (id, user_id, authentication_information, portfolio, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)"
            );

            $authJson = json_encode($this->authenticationInformation);
            $portfolioJson = json_encode($this->portfolio);

            mysqli_stmt_bind_param($stmt, "ssssss",
                $this->id,
                $this->userId,
                $authJson,
                $portfolioJson,
                $this->createdAt,
                $this->updatedAt
            );

            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $result;
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
