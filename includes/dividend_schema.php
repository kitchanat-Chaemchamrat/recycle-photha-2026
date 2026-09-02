<?php
function ensureDividendWeeklySchema(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $periodCols = $pdo->query("SHOW COLUMNS FROM dividend_periods")->fetchAll(PDO::FETCH_COLUMN, 0);
    $periodAlters = [];
    if (!in_array('period_type', $periodCols, true)) {
        $periodAlters[] = "ADD COLUMN period_type ENUM('weekly','monthly') NOT NULL DEFAULT 'weekly'";
    }
    if (!in_array('week_start', $periodCols, true)) {
        $periodAlters[] = "ADD COLUMN week_start DATE NULL DEFAULT NULL";
    }
    if (!in_array('week_end', $periodCols, true)) {
        $periodAlters[] = "ADD COLUMN week_end DATE NULL DEFAULT NULL";
    }
    if (!in_array('sale_income', $periodCols, true)) {
        $periodAlters[] = "ADD COLUMN sale_income DECIMAL(12,2) NOT NULL DEFAULT 0.00";
    }
    if ($periodAlters) {
        $pdo->exec("ALTER TABLE dividend_periods " . implode(", ", $periodAlters));
    }

    $detailCols = $pdo->query("SHOW COLUMNS FROM dividend_details")->fetchAll(PDO::FETCH_COLUMN, 0);
    $detailAlters = [];
    if (!in_array('period_type', $detailCols, true)) {
        $detailAlters[] = "ADD COLUMN period_type ENUM('weekly','monthly') NOT NULL DEFAULT 'weekly'";
    }
    if (!in_array('period_start', $detailCols, true)) {
        $detailAlters[] = "ADD COLUMN period_start DATE NULL DEFAULT NULL";
    }
    if (!in_array('period_end', $detailCols, true)) {
        $detailAlters[] = "ADD COLUMN period_end DATE NULL DEFAULT NULL";
    }
    if (!in_array('sale_income', $detailCols, true)) {
        $detailAlters[] = "ADD COLUMN sale_income DECIMAL(12,2) NOT NULL DEFAULT 0.00";
    }
    if ($detailAlters) {
        $pdo->exec("ALTER TABLE dividend_details " . implode(", ", $detailAlters));
    }
}
