<?php
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['soi_name']);
        $desc = trim($_POST['description']);
        $stmt = $pdo->prepare("INSERT INTO sois (soi_name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM sois WHERE id = ?");
        $stmt->execute([$id]);
    }
}

$sois = $pdo->query("
    SELECT s.*, COUNT(h.id) as house_count 
    FROM sois s 
    LEFT JOIN houses h ON s.id = h.soi_id 
    GROUP BY s.id
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">จัดการข้อมูลซอย</h1>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addSoiModal">
        <i class="bi bi-plus-circle"></i> เพิ่มซอย
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อซอย</th>
                        <th>รายละเอียด</th>
                        <th>จำนวนบ้าน</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sois as $soi): ?>
                    <tr>
                        <td><?php echo $soi['id']; ?></td>
                        <td><?php echo htmlspecialchars($soi['soi_name']); ?></td>
                        <td><?php echo htmlspecialchars($soi['description']); ?></td>
                        <td><?php echo $soi['house_count']; ?> หลัง</td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ? ข้อมูลบ้านในซอยนี้จะถูกลบไปด้วย');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $soi['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addSoiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มข้อมูลซอยใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">ชื่อซอย</label>
                        <input type="text" class="form-control" name="soi_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary-custom">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
