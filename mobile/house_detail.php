<?php
require 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die("ไม่พบข้อมูลบ้าน");
}

$stmt = $pdo->prepare("
    SELECT h.*, s.soi_name 
    FROM houses h 
    JOIN sois s ON h.soi_id = s.id 
    WHERE h.id = ?
");
$stmt->execute([$id]);
$house = $stmt->fetch();

if (!$house) {
    die("ไม่พบข้อมูลบ้าน");
}

$members = $pdo->prepare("SELECT * FROM members WHERE house_id = ?");
$members->execute([$id]);
$members_list = $members->fetchAll();

$evaluations = $pdo->prepare("SELECT * FROM waste_evaluations WHERE house_id = ? ORDER BY evaluation_date DESC LIMIT 5");
$evaluations->execute([$id]);
$eval_list = $evaluations->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลบ้านเลขที่ <?php echo htmlspecialchars($house['house_no']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card glass-card mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-house-check text-primary-custom icon-xl"></i>
                        <h2 class="mt-3">บ้านเลขที่ <?php echo htmlspecialchars($house['house_no']); ?></h2>
                        <h5 class="text-muted"><?php echo htmlspecialchars($house['soi_name']); ?></h5>
                    </div>
                </div>

                <div class="card glass-card mb-4">
                    <div class="card-header card-header-primary fw-bold">ข้อมูลเจ้าของบ้านและสมาชิก</div>
                    <div class="card-body">
                        <p><strong>เจ้าของบ้าน:</strong> <?php echo htmlspecialchars($house['owner_name']); ?></p>
                        <p><strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($house['phone'] ?? '-'); ?></p>
                        <hr>
                        <h6>สมาชิกในบ้าน:</h6>
                        <?php if(count($members_list) > 0): ?>
                        <ul>
                            <?php foreach($members_list as $m): ?>
                            <li><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-muted">ไม่มีข้อมูลสมาชิกเพิ่มเติม</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card glass-card">
                    <div class="card-header card-header-accent fw-bold">ประวัติการประเมิน 5 ครั้งล่าสุด</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>คะแนนรวม</th>
                                        <th>ปนเปื้อน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($eval_list) > 0): ?>
                                        <?php foreach($eval_list as $ev): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ev['evaluation_date']); ?></td>
                                            <td class="fw-bold <?php echo $ev['total_score'] >= 80 ? 'text-success' : 'text-danger'; ?>"><?php echo $ev['total_score']; ?></td>
                                            <td><?php echo $ev['contamination'] ? '<span class="badge bg-danger">พบ</span>' : '<span class="badge bg-success">ไม่พบ</span>'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-muted py-3">ยังไม่มีประวัติการประเมิน</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="login.php" class="btn btn-outline-primary-custom">เข้าสู่ระบบเจ้าหน้าที่</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
