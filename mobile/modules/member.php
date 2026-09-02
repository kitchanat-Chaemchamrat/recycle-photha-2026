<?php
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $house_id = $_POST['house_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);
        
        $stmt = $pdo->prepare("INSERT INTO members (house_id, first_name, last_name, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$house_id, $first_name, $last_name, $phone]);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
    }
}

$houses = $pdo->query("SELECT * FROM houses")->fetchAll();
$members = $pdo->query("
    SELECT m.*, h.house_no 
    FROM members m 
    JOIN houses h ON m.house_id = h.id
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">จัดการข้อมูลสมาชิกในบ้าน</h1>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addMemberModal">
        <i class="bi bi-person-plus"></i> เพิ่มสมาชิก
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>บ้านเลขที่</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['house_no']); ?></td>
                        <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบสมาชิกรายนี้?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
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
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสมาชิก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">สังกัดบ้านเลขที่</label>
                        <select class="form-select" name="house_id" required>
                            <option value="">เลือกบ้าน...</option>
                            <?php foreach($houses as $h): ?>
                                <option value="<?php echo $h['id']; ?>"><?php echo htmlspecialchars($h['house_no']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" name="phone">
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
