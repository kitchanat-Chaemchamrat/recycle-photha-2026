<?php
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $house_no = trim($_POST['house_no']);
        $owner_name = trim($_POST['owner_name']);
        $phone = trim($_POST['phone']);
        $soi_name = trim($_POST['soi_name']);
        $member_count = $_POST['member_count'];

        $stmt = $pdo->prepare("SELECT id FROM sois WHERE soi_name = ?");
        $stmt->execute([$soi_name]);
        $soi_id = $stmt->fetchColumn();
        if (!$soi_id) {
            $stmt = $pdo->prepare("INSERT INTO sois (soi_name) VALUES (?)");
            $stmt->execute([$soi_name]);
            $soi_id = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare("INSERT INTO houses (house_no, owner_name, phone, soi_id, member_count) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$house_no, $owner_name, $phone, $soi_id, $member_count]);
        $house_id = $pdo->lastInsertId();

        // Generate a QR code that points to the public house detail page.
        $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $qr_data = "http://" . $_SERVER['HTTP_HOST'] . $base_path . "/house_detail.php?id=" . $house_id;
        $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);
        
        $stmt = $pdo->prepare("UPDATE houses SET qr_code = ? WHERE id = ?");
        $stmt->execute([$qr_code_url, $house_id]);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM houses WHERE id = ?");
        $stmt->execute([$id]);
    }
}

$sois = $pdo->query("SELECT * FROM sois")->fetchAll();
$houses = $pdo->query("
    SELECT h.*, s.soi_name 
    FROM houses h 
    JOIN sois s ON h.soi_id = s.id
")->fetchAll();
?>

<?php
$base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">จัดการข้อมูลบ้าน</h1>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addHouseModal">
        <i class="bi bi-plus-circle"></i> เพิ่มบ้าน
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>เลขที่บ้าน</th>
                        <th>เจ้าของบ้าน</th>
                        <th>ซอย</th>
                        <th>สมาชิก (คน)</th>
                        <th>QR Code</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($houses as $house): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($house['house_no']); ?></td>
                        <td><?php echo htmlspecialchars($house['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($house['soi_name']); ?></td>
                        <td><?php echo $house['member_count']; ?></td>
                        <td>
                            <?php
                                $detail_url = "http://" . $_SERVER['HTTP_HOST'] . $base_path . "/house_detail.php?id=" . $house['id'];
                                $qr_code_url = $house['qr_code'];
                                if (!$qr_code_url || strpos($qr_code_url, 'http') !== 0) {
                                    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($detail_url);
                                }
                            ?>
                            <?php if($qr_code_url): ?>
                                <a href="<?php echo htmlspecialchars($detail_url); ?>" target="_blank" title="เปิดหน้ารายละเอียดบ้าน">
                                    <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code" width="50" class="img-thumbnail">
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $house['id']; ?>">
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
<div class="modal fade" id="addHouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มข้อมูลบ้าน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">เลขที่บ้าน</label>
                        <input type="text" class="form-control" name="house_no" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อเจ้าของบ้าน</label>
                        <input type="text" class="form-control" name="owner_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนสมาชิก</label>
                        <input type="number" class="form-control" name="member_count" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อซอย</label>
                        <input type="text" class="form-control" name="soi_name" list="soiList" placeholder="พิมพ์ชื่อซอย เช่น ซอย 1" required>
                        <datalist id="soiList">
                            <?php foreach($sois as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['soi_name']); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="form-text">ถ้ายังไม่มีซอยนี้ในระบบ ระบบจะเพิ่มซอยใหม่ให้เอง</div>
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
