<?php
requireRole(['super_admin', 'admin']);
require_once __DIR__ . '/../../includes/approval_schema.php';
ensureEvaluationApprovalSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE waste_evaluations SET approval_status='approved', approved_at=NOW(), approved_by=?, rejected_at=NULL, rejected_by=NULL, approval_note=NULL WHERE id=?");
        $stmt->execute([$_SESSION['user_id'], $id]);
    } elseif ($id > 0 && $_POST['action'] === 'reject') {
        $note = trim($_POST['approval_note'] ?? '');
        $stmt = $pdo->prepare("UPDATE waste_evaluations SET approval_status='rejected', rejected_at=NOW(), rejected_by=?, approved_at=NULL, approved_by=NULL, approval_note=? WHERE id=?");
        $stmt->execute([$_SESSION['user_id'], $note ?: null, $id]);
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'details') {
    header('Content-Type: text/html; charset=utf-8');
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo '<div class="alert alert-danger mb-0">ไม่พบรายการประเมิน</div>';
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT e.*, h.house_no, h.owner_name, s.soi_name, u.username as evaluator_name,
               au.username as approver_name, ru.username as rejector_name
        FROM waste_evaluations e
        JOIN houses h ON e.house_id = h.id
        JOIN sois s ON h.soi_id = s.id
        JOIN users u ON e.evaluator_id = u.id
        LEFT JOIN users au ON e.approved_by = au.id
        LEFT JOIN users ru ON e.rejected_by = ru.id
        WHERE e.id = ?
    ");
    $stmt->execute([$id]);
    $ev = $stmt->fetch();
    if (!$ev) {
        http_response_code(404);
        echo '<div class="alert alert-warning mb-0">ไม่พบรายการประเมินนี้</div>';
        exit;
    }

    $photosStmt = $pdo->prepare("SELECT image_path, image_type FROM photos WHERE evaluation_id = ? ORDER BY image_type");
    $photosStmt->execute([$ev['id']]);
    $photos = $photosStmt->fetchAll();
    ?>
    <div class="mb-3">
        <h5 class="mb-1">เลขการประเมิน #<?php echo (int)$ev['id']; ?></h5>
        <div class="text-muted small">บ้าน <?php echo htmlspecialchars($ev['house_no']); ?> | ซอย <?php echo htmlspecialchars($ev['soi_name']); ?> | วันที่ <?php echo htmlspecialchars($ev['evaluation_date']); ?></div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-6"><strong>เจ้าของ:</strong> <?php echo htmlspecialchars($ev['owner_name']); ?></div>
        <div class="col-6"><strong>ผู้ตรวจ:</strong> <?php echo htmlspecialchars($ev['evaluator_name']); ?></div>
        <div class="col-6"><strong>สถานะ:</strong> <?php echo htmlspecialchars($ev['approval_status']); ?></div>
        <div class="col-6"><strong>คะแนนรวม:</strong> <?php echo (int)$ev['total_score']; ?></div>
    </div>
    <div class="row g-2 mb-3">
        <div class="col-6"><div class="p-2 border rounded"><small>เศษอาหาร</small><div class="fw-bold"><?php echo (int)$ev['food_score']; ?></div></div></div>
        <div class="col-6"><div class="p-2 border rounded"><small>พลาสติก</small><div class="fw-bold"><?php echo (int)$ev['plastic_score']; ?></div></div></div>
        <div class="col-6"><div class="p-2 border rounded"><small>กระดาษ</small><div class="fw-bold"><?php echo (int)$ev['paper_score']; ?></div></div></div>
        <div class="col-6"><div class="p-2 border rounded"><small>แก้ว</small><div class="fw-bold"><?php echo (int)$ev['glass_score']; ?></div></div></div>
        <div class="col-6"><div class="p-2 border rounded"><small>โลหะ</small><div class="fw-bold"><?php echo (int)$ev['metal_score']; ?></div></div></div>
        <div class="col-6"><div class="p-2 border rounded"><small>ปนเปื้อน</small><div class="fw-bold"><?php echo $ev['contamination'] ? 'พบ' : 'ไม่พบ'; ?></div></div></div>
    </div>
    <div class="mb-3">
        <div class="fw-bold mb-2">รูปหลักฐาน</div>
        <?php if ($photos): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($photos as $photo): ?>
                    <a href="<?php echo htmlspecialchars($photo['image_path']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['image_type']); ?>" style="width:110px;height:110px;object-fit:cover;border-radius:10px;border:1px solid #d0d7de;">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-muted">ไม่มีรูปหลักฐาน</div>
        <?php endif; ?>
    </div>
    <?php
    exit;
}

$filters = [
    'status' => $_GET['status'] ?? 'pending',
    'house' => trim($_GET['house'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to' => trim($_GET['date_to'] ?? ''),
    'evaluator' => trim($_GET['evaluator'] ?? ''),
];

$where = [];
$params = [];
if ($filters['status'] !== 'all') { $where[] = 'e.approval_status = ?'; $params[] = $filters['status']; }
if ($filters['house'] !== '') { $where[] = '(h.house_no LIKE ? OR h.owner_name LIKE ? OR s.soi_name LIKE ?)'; $like = '%' . $filters['house'] . '%'; array_push($params, $like, $like, $like); }
if ($filters['date_from'] !== '') { $where[] = 'e.evaluation_date >= ?'; $params[] = $filters['date_from']; }
if ($filters['date_to'] !== '') { $where[] = 'e.evaluation_date <= ?'; $params[] = $filters['date_to']; }
if ($filters['evaluator'] !== '') { $where[] = '(u.username LIKE ?)'; $params[] = '%' . $filters['evaluator'] . '%'; }

$sql = "
    SELECT e.*, h.house_no, h.owner_name, s.soi_name, u.username as evaluator_name,
           au.username as approver_name, ru.username as rejector_name
    FROM waste_evaluations e
    JOIN houses h ON e.house_id = h.id
    JOIN sois s ON h.soi_id = s.id
    JOIN users u ON e.evaluator_id = u.id
    LEFT JOIN users au ON e.approved_by = au.id
    LEFT JOIN users ru ON e.rejected_by = ru.id
";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY e.submitted_at DESC, e.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$evaluations = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
    <h1 class="h2">อนุมัติผลการประเมิน</h1>
</div>

<form class="card mb-4 no-print" method="GET">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-6">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>รออนุมัติ</option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>อนุมัติแล้ว</option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>ไม่อนุมัติ</option>
                    <option value="all" <?php echo $filters['status'] === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">บ้าน/ซอย</label>
                <input type="text" class="form-control" name="house" value="<?php echo htmlspecialchars($filters['house']); ?>">
            </div>
            <div class="col-6">
                <label class="form-label">วันที่เริ่ม</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            <div class="col-6">
                <label class="form-label">วันที่จบ</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">ผู้ตรวจ</label>
                <input type="text" class="form-control" name="evaluator" value="<?php echo htmlspecialchars($filters['evaluator']); ?>">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom">กรองข้อมูล</button>
                <a href="index.php?page=evaluation_approval" class="btn btn-outline-secondary">ล้างตัวกรอง</a>
            </div>
        </div>
    </div>
</form>

<?php if (!$evaluations): ?>
    <div class="alert alert-info">ไม่พบรายการประเมินตามเงื่อนไขที่เลือก</div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($evaluations as $ev): ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>เลขการประเมิน #<?php echo (int)$ev['id']; ?></strong>
                        <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($ev['approval_status']); ?></span>
                    </div>
                    <div class="small text-muted"><?php echo htmlspecialchars($ev['evaluation_date']); ?></div>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6"><strong>บ้าน:</strong> <?php echo htmlspecialchars($ev['house_no']); ?></div>
                        <div class="col-6"><strong>ซอย:</strong> <?php echo htmlspecialchars($ev['soi_name']); ?></div>
                        <div class="col-6"><strong>เจ้าของ:</strong> <?php echo htmlspecialchars($ev['owner_name']); ?></div>
                        <div class="col-6"><strong>ผู้ตรวจ:</strong> <?php echo htmlspecialchars($ev['evaluator_name']); ?></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark">รวม <?php echo (int)$ev['total_score']; ?></span>
                        <span class="badge bg-light text-dark">อนุมัติ: <?php echo htmlspecialchars($ev['approval_status']); ?></span>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="openEvaluationDetails(<?php echo (int)$ev['id']; ?>)">ดูรายละเอียด</button>
                    <?php if ($ev['approval_status'] === 'pending'): ?>
                        <form method="POST" class="d-grid gap-2">
                            <input type="hidden" name="id" value="<?php echo (int)$ev['id']; ?>">
                            <input type="text" name="approval_note" class="form-control" placeholder="หมายเหตุไม่อนุมัติ">
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="approve" class="btn btn-success flex-fill">อนุมัติผล</button>
                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger flex-fill">ไม่อนุมัติ</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="evaluationDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดผลการประเมิน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluationDetailsBody">
                <div class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</div>
            </div>
        </div>
    </div>
</div>

<script>
async function openEvaluationDetails(id) {
    const body = document.getElementById('evaluationDetailsBody');
    body.innerHTML = '<div class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</div>';
    const modal = new bootstrap.Modal(document.getElementById('evaluationDetailsModal'));
    modal.show();
    try {
        const res = await fetch('?page=evaluation_approval&ajax=details&id=' + encodeURIComponent(id), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        body.innerHTML = await res.text();
    } catch (e) {
        body.innerHTML = '<div class="alert alert-danger mb-0">โหลดรายละเอียดไม่สำเร็จ</div>';
    }
}
</script>
