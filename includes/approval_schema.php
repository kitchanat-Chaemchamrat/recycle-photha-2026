<?php
function ensureEvaluationApprovalSchema(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $columns = $pdo->query("SHOW COLUMNS FROM waste_evaluations")->fetchAll(PDO::FETCH_COLUMN, 0);
    $alters = [];

    if (!in_array('approval_status', $columns, true)) {
        $alters[] = "ADD COLUMN approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'";
    }
    if (!in_array('submitted_at', $columns, true)) {
        $alters[] = "ADD COLUMN submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    }
    if (!in_array('approved_at', $columns, true)) {
        $alters[] = "ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL";
    }
    if (!in_array('approved_by', $columns, true)) {
        $alters[] = "ADD COLUMN approved_by INT(11) NULL DEFAULT NULL";
    }
    if (!in_array('rejected_at', $columns, true)) {
        $alters[] = "ADD COLUMN rejected_at TIMESTAMP NULL DEFAULT NULL";
    }
    if (!in_array('rejected_by', $columns, true)) {
        $alters[] = "ADD COLUMN rejected_by INT(11) NULL DEFAULT NULL";
    }
    if (!in_array('approval_note', $columns, true)) {
        $alters[] = "ADD COLUMN approval_note VARCHAR(255) NULL DEFAULT NULL";
    }

    if ($alters) {
        $pdo->exec("ALTER TABLE waste_evaluations " . implode(", ", $alters));
    }

    $indexes = $pdo->query("SHOW INDEX FROM waste_evaluations")->fetchAll();
    $indexNames = array_map(fn($row) => $row['Key_name'], $indexes);
    if (!in_array('approval_status', $indexNames, true)) {
        $pdo->exec("CREATE INDEX approval_status ON waste_evaluations (approval_status)");
    }
}
