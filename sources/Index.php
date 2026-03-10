<?php
    namespace Sources;

    abstract class Source {
        abstract public function getPortfolio(): array;
        abstract public function getStocks(): array;
        abstract public function getStockBySymbol(string $symbol): ?array;
    }
?>
