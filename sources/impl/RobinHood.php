<?php
    namespace Sources;

    require_once __DIR__ . "/../Index.php";

    class RobinHood extends Source {
        private array $portfolioData;
        private array $stocksData;

        public function __construct() {
            $this->portfolioData = json_decode(
                file_get_contents(__DIR__ . "/../mock/robinhood_portfolio.json"),
                true
            );
            $this->stocksData = json_decode(
                file_get_contents(__DIR__ . "/../mock/robinhood_stocks.json"),
                true
            );
        }

        public function getPortfolio(): array {
            return $this->portfolioData;
        }

        public function getStocks(): array {
            return $this->stocksData;
        }

        public function getStockBySymbol(string $symbol): ?array {
            $symbol = strtoupper($symbol);
            foreach ($this->stocksData as $stock) {
                if ($stock["symbol"] === $symbol) {
                    return $stock;
                }
            }
            return null;
        }
    }
?>
