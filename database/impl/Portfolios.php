<?php
namespace Database;

class Portfolios {
    const CREATE_TABLE = "
    CREATE TABLE IF NOT EXISTS portfolios (
        id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
        user_id VARCHAR(36) NOT NULL,
        allocation JSON NOT NULL,
        holdings JSON NOT NULL,
        last_updated DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    ";

    public function __construct(
        public string $id,
        public string $userId,
        public array $allocation,
        public array $holdings,
        public ?string $lastUpdated = null
    ) {}

    /**
     * Seeds a random portfolio for a user
     */
   public static function seedRandomPortfolio(\mysqli $conn, string $userId, string $provider = 'Robinhood'): bool {
        // 1. Get existing holdings first so we don't overwrite them
        $existing = self::getPortfolioByUser($conn, $userId);
        $holdings = $existing ? $existing->holdings : [];

        $stockPool = ['AAPL', 'MSFT', 'TSLA', 'NVDA', 'GOOGL', 'AMZN', 'META', 'NFLX', 'DIS', 'BND'];
        shuffle($stockPool);
        
        // Pick 2-3 random stocks to add from THIS specific connector
        $selectedKeys = array_rand($stockPool, rand(2, 3));
        
        foreach ((array)$selectedKeys as $key) {
            $ticker = $stockPool[$key];
            
            // If ticker doesn't exist, initialize it with an empty sources array
            if (!isset($holdings[$ticker])) {
                $holdings[$ticker] = [
                    "qty" => 0,
                    "sources" => [] 
                ];
            }

            $newQty = rand(1, 50);
            $holdings[$ticker]["qty"] += $newQty;
            
            // Add this specific source entry to the list
            $holdings[$ticker]["sources"][] = [
                "name" => $provider,
                "qty" => $newQty
            ];
        }

        $allocation = [
            "stocks" => 70,
            "bonds" => 20,
            "cash" => 10
        ];

        // Use the existing ID if it exists, otherwise generate a new one
        $id = $existing ? $existing->id : self::generateUUID();

        $portfolio = new Portfolios(
            $id,
            $userId,
            $allocation,
            $holdings,
            date("Y-m-d H:i:s")
        );

        // 2. Use the "Save" method (UPSERT) instead of "Insert"
        return $portfolio->save($conn);
    }

    public function save(\mysqli $conn): bool {
        // This SQL handles both new users and existing users (Upsert logic)
        $sql = "INSERT INTO portfolios (id, user_id, allocation, holdings, last_updated) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                holdings = VALUES(holdings), 
                allocation = VALUES(allocation), 
                last_updated = VALUES(last_updated)";

        $stmt = $conn->prepare($sql);
        $allocJson = json_encode($this->allocation);
        $holdJson = json_encode($this->holdings);

        $stmt->bind_param("sssss", 
            $this->id, 
            $this->userId, 
            $allocJson, 
            $holdJson, 
            $this->lastUpdated
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public static function getPortfolioByUser(\mysqli $conn, string $userId): ?Portfolios {
        $stmt = mysqli_prepare($conn, "SELECT * FROM portfolios WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) return null;

        return new Portfolios(
            $row['id'],
            $row['user_id'],
            json_decode($row['allocation'], true) ?? [],
            json_decode($row['holdings'], true) ?? [],
            $row['last_updated']
        );
    }

    private static function generateUUID(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

//     public function save(\mysqli $conn): bool {
//     // We use "ON DUPLICATE KEY UPDATE" so it updates existing portfolios
//     $sql = "INSERT INTO portfolios (id, user_id, allocation, holdings, last_updated) 
//             VALUES (?, ?, ?, ?, NOW())
//             ON DUPLICATE KEY UPDATE 
//             holdings = VALUES(holdings), 
//             allocation = VALUES(allocation), 
//             last_updated = NOW()";

//     $stmt = $conn->prepare($sql);
//     $allocationJson = json_encode($this->allocation);
//     $holdingsJson = json_encode($this->holdings);

//     $stmt->bind_param("ssss", $this->id, $this->userId, $allocationJson, $holdingsJson);
//     return $stmt->execute();
// }

}