<?php
requireRole(['super_admin', 'admin', 'collector']);
require_once __DIR__ . '/../../includes/approval_schema.php';
ensureEvaluationApprovalSchema($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'evaluate') {
    $house_id = $_POST['house_id'];
    $evaluation_date = $_POST['evaluation_date'];
    $food = (int)$_POST['food_score'];
    $plastic = (int)$_POST['plastic_score'];
    $paper = (int)$_POST['paper_score'];
    $glass = (int)$_POST['glass_score'];
    $metal = (int)$_POST['metal_score'];
    $contamination = isset($_POST['contamination']) ? 1 : 0;
    
    $total = $food + $plastic + $paper + $glass + $metal;
    if ($contamination) {
        $total -= 20;
    }
    
    $evaluator_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO waste_evaluations (house_id, evaluation_date, food_score, plastic_score, paper_score, glass_score, metal_score, contamination, total_score, evaluator_id, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$house_id, $evaluation_date, $food, $plastic, $paper, $glass, $metal, $contamination, $total, $evaluator_id]);
        $eval_id = $pdo->lastInsertId();
        
        // Handle Photo Uploads
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $types = ['before', 'after'];
        foreach($types as $type) {
            if (isset($_FILES["photo_$type"]) && $_FILES["photo_$type"]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES["photo_$type"]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $filename = uniqid() . '_' . $type . '.' . $ext;
                    $target = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES["photo_$type"]['tmp_name'], $target)) {
                        $stmtImg = $pdo->prepare("INSERT INTO photos (evaluation_id, image_path, image_type) VALUES (?, ?, ?)");
                        $stmtImg->execute([$eval_id, $target, $type]);
                    }
                }
            }
        }
        
        $pdo->commit();
        $success = "บันทึกการประเมินสำเร็จ เลขประเมิน #$eval_id ถูกส่งรอแอดมินอนุมัติ";
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

$houses = $pdo->query("SELECT h.id, h.house_no, s.soi_name FROM houses h JOIN sois s ON h.soi_id = s.id")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">ประเมินการคัดแยกขยะ</h1>
    <a class="btn btn-outline-primary" href="index.php?page=qr_scan">
        <i class="bi bi-qr-code-scan me-1"></i> สแกน QR
    </a>
</div>

<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="evaluate">
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">วันที่ประเมิน</label>
                    <input type="date" class="form-control" name="evaluation_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">บ้านเลขที่</label>
                    <select class="form-select" name="house_id" required>
                        <option value="">เลือกบ้าน...</option>
                        <?php foreach($houses as $h): ?>
                            <option value="<?php echo $h['id']; ?>"><?php echo htmlspecialchars($h['house_no'] . ' (' . $h['soi_name'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 text-primary-custom section-divider">ส่วนที่ 1: ให้คะแนนการแยกขยะ (0-20 คะแนนต่อหมวด)</h5>
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">เศษอาหาร</label>
                    <input type="number" class="form-control" name="food_score" min="0" max="20" value="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">พลาสติก</label>
                    <input type="number" class="form-control" name="plastic_score" min="0" max="20" value="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">กระดาษ</label>
                    <input type="number" class="form-control" name="paper_score" min="0" max="20" value="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">แก้ว</label>
                    <input type="number" class="form-control" name="glass_score" min="0" max="20" value="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">โลหะ</label>
                    <input type="number" class="form-control" name="metal_score" min="0" max="20" value="0" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input bg-danger border-danger" type="checkbox" id="contamination" name="contamination" value="1">
                    <label class="form-check-label text-danger fw-bold" for="contamination">
                        พบขยะปนเปื้อน (หัก 20 คะแนน)
                    </label>
                </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 text-primary-custom section-divider">ส่วนที่ 2: แนบรูปภาพหลักฐาน</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ภาพก่อนเก็บขยะ (ถ้ามี)</label>
                    <input type="file" class="form-control" name="photo_before" accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ภาพหลังเก็บขยะ (ถ้ามี)</label>
                    <input type="file" class="form-control" name="photo_after" accept=".jpg,.jpeg,.png">
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-bold">บันทึกผลการประเมิน</button>
        </form>
    </div>
</div>
