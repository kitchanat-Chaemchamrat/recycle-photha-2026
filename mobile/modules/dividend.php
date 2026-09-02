<?php
requireRole(['super_admin', 'admin']);
require_once __DIR__ . '/../../includes/approval_schema.php';
require_once __DIR__ . '/../../includes/dividend_schema.php';
ensureEvaluationApprovalSchema($pdo);
ensureDividendWeeklySchema($pdo);

$success = '';
$error = '';

function weekRangeForDate(string $date): array {
    $dt = new DateTime($date);
    $dow = (int)$dt->format('N');
    $start = (clone $dt)->modify('-' . ($dow - 1) . ' days');
    $end = (clone $start)->modify('+6 days');
    return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'calculate') {
    $week_start = $_POST['week_start'] ?? date('Y-m-d');
    $week_end = $_POST['week_end'] ?? date('Y-m-d');
    $community_fund_pct = (float)($_POST['community_fund'] ?? 10);
    $worker_fund_pct = (float)($_POST['worker_fund'] ?? 10);
    if ($week_start > $week_end) { [$week_start, $week_end] = [$week_end, $week_start]; }

    try {
        $pdo->beginTransaction();
        $saleStmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM recycle_sales WHERE sale_date BETWEEN ? AND ?");
        $saleStmt->execute([$week_start, $week_end]);
        $sale_income = (float)$saleStmt->fetchColumn();

        $community_fund_amt = $sale_income * ($community_fund_pct / 100);
        $worker_fund_amt = $sale_income * ($worker_fund_pct / 100);
        $dividend_pool = $sale_income - $community_fund_amt - $worker_fund_amt;

        $stmt = $pdo->prepare("SELECT id FROM dividend_periods WHERE period_type='weekly' AND week_start=? AND week_end=? LIMIT 1");
        $stmt->execute([$week_start, $week_end]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $period_id = (int)$existing;
            $pdo->prepare("DELETE FROM dividend_details WHERE dividend_period_id = ?")->execute([$period_id]);
            $pdo->prepare("UPDATE dividend_periods SET total_income=?, sale_income=?, community_fund=?, worker_fund=?, dividend_pool=? WHERE id=?")
                ->execute([$sale_income, $sale_income, $community_fund_amt, $worker_fund_amt, $dividend_pool, $period_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO dividend_periods (period_type, week_start, week_end, total_income, sale_income, community_fund, worker_fund, dividend_pool, month, year) VALUES ('weekly', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$week_start, $week_end, $sale_income, $sale_income, $community_fund_amt, $worker_fund_amt, $dividend_pool, (int)date('n', strtotime($week_start)), (int)date('Y', strtotime($week_start))]);
            $period_id = (int)$pdo->lastInsertId();
        }

        $sois = $pdo->query("SELECT id FROM sois")->fetchAll(PDO::FETCH_COLUMN);
        $soi_data = [];
        $total_weighted_score = 0;
        foreach ($sois as $soi_id) {
            $stmtScore = $pdo->prepare("SELECT AVG(e.total_score) FROM waste_evaluations e JOIN houses h ON e.house_id = h.id WHERE h.soi_id = ? AND e.approval_status='approved' AND e.evaluation_date BETWEEN ? AND ?");
            $stmtScore->execute([$soi_id, $week_start, $week_end]);
            $avg_score = $stmtScore->fetchColumn() ?: 0;
            $stmtWeight = $pdo->prepare("SELECT SUM(w.total_kg) FROM waste_weights w JOIN houses h ON w.house_id = h.id WHERE h.soi_id = ? AND w.record_date BETWEEN ? AND ?");
            $stmtWeight->execute([$soi_id, $week_start, $week_end]);
            $total_weight = $stmtWeight->fetchColumn() ?: 0;
            $weighted_score = $avg_score * $total_weight;
            $total_weighted_score += $weighted_score;
            $soi_data[$soi_id] = ['avg_score' => $avg_score, 'total_weight' => $total_weight, 'weighted_score' => $weighted_score];
        }
        $stmtDiv = $pdo->prepare("INSERT INTO dividend_details (dividend_period_id, period_type, period_start, period_end, sale_income, soi_id, avg_score, recycle_weight, weighted_score, ratio_percent, dividend_amount) VALUES (?, 'weekly', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($soi_data as $soi_id => $data) {
            $ratio = $total_weighted_score > 0 ? ($data['weighted_score'] / $total_weighted_score) : 0;
            $stmtDiv->execute([$period_id, $week_start, $week_end, $sale_income, $soi_id, $data['avg_score'], $data['total_weight'], $data['weighted_score'], $ratio * 100, $dividend_pool * $ratio]);
        }
        $pdo->commit();
        $success = 'คำนวณรอบสัปดาห์ ' . $week_start . ' ถึง ' . $week_end . ' สำเร็จแล้ว';
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

$periods = $pdo->query("SELECT * FROM dividend_periods WHERE period_type='weekly' ORDER BY week_end DESC, id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">คำนวณเงินปันผลรายสัปดาห์</h1>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#calcModal"><i class="bi bi-calculator"></i> คำนวณสัปดาห์ใหม่</button>
</div>

<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>ช่วงสัปดาห์</th><th>รายได้ขายขยะ</th><th>กองทุนชุมชน</th><th>ค่าแรงคัดแยก</th><th>ยอดเงินปันผลรวม</th><th>รายละเอียด</th></tr></thead>
                <tbody>
                    <?php foreach ($periods as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['week_start'] . ' ถึง ' . $p['week_end']); ?></td>
                        <td><?php echo number_format($p['sale_income'], 2); ?></td>
                        <td><?php echo number_format($p['community_fund'], 2); ?></td>
                        <td><?php echo number_format($p['worker_fund'], 2); ?></td>
                        <td class="text-primary-custom fw-bold"><?php echo number_format($p['dividend_pool'], 2); ?></td>
                        <td><button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo $p['id']; ?>)"><i class="bi bi-eye"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="calcModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST">
<div class="modal-header"><h5 class="modal-title">คำนวณเงินปันผลรายสัปดาห์</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" name="action" value="calculate">
<?php [$defaultStart, $defaultEnd] = weekRangeForDate(date('Y-m-d')); ?>
<div class="row mb-3"><div class="col-6"><label class="form-label">เริ่มสัปดาห์</label><input type="date" class="form-control" name="week_start" value="<?php echo $defaultStart; ?>" required></div><div class="col-6"><label class="form-label">สิ้นสุดสัปดาห์</label><input type="date" class="form-control" name="week_end" value="<?php echo $defaultEnd; ?>" required></div></div>
<div class="row mb-3"><div class="col-6"><label class="form-label">หักกองทุนชุมชน (%)</label><input type="number" name="community_fund" class="form-control" value="10" required></div><div class="col-6"><label class="form-label">หักค่าแรงคัดแยก (%)</label><input type="number" name="worker_fund" class="form-control" value="10" required></div></div>
<div class="alert alert-info py-2 mb-0">ระบบจะดึงรายได้จาก recycle_sales อัตโนมัติในช่วงสัปดาห์ที่เลือก</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button><button type="submit" class="btn btn-primary-custom">คำนวณและบันทึก</button></div>
</form></div></div></div>

<div class="modal fade" id="periodDetailsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">รายละเอียดรอบสัปดาห์</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="periodDetailsBody"><div class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</div></div></div></div></div>
<script>
async function viewDetails(id) {
    const body = document.getElementById('periodDetailsBody');
    body.innerHTML = '<div class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</div>';
    const modal = new bootstrap.Modal(document.getElementById('periodDetailsModal'));
    modal.show();
    const res = await fetch('?ajax=period_details&id=' + encodeURIComponent(id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    body.innerHTML = await res.text();
}
</script>
