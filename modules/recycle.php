<?php
requireRole(['super_admin', 'admin', 'collector']);

$success = '';
$selected_house_id = isset($_GET['house_id']) ? (int)$_GET['house_id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_weight') {
    $house_id = $_POST['house_id'];
    $record_date = $_POST['record_date'];
    $plastic = (float)$_POST['plastic_kg'];
    $paper = (float)$_POST['paper_kg'];
    $glass = (float)$_POST['glass_kg'];
    $metal = (float)$_POST['metal_kg'];
    
    $total = $plastic + $paper + $glass + $metal;
    
    $stmt = $pdo->prepare("INSERT INTO waste_weights (house_id, record_date, plastic_kg, paper_kg, glass_kg, metal_kg, total_kg) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$house_id, $record_date, $plastic, $paper, $glass, $metal, $total]);
    $success = "บันทึกน้ำหนักสำเร็จ รวม {$total} กก.";
}

$houses = $pdo->query("SELECT h.id, h.house_no, s.soi_name FROM houses h JOIN sois s ON h.soi_id = s.id")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">บันทึกน้ำหนักขยะรีไซเคิล</h1>
</div>

<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="record_weight">
                    
                    <div class="mb-3">
                        <label class="form-label">วันที่</label>
                        <input type="date" class="form-control" name="record_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">บ้านเลขที่</label>
                        <select class="form-select" name="house_id" required>
                            <option value="">เลือกบ้าน...</option>
                            <?php foreach($houses as $h): ?>
                                <option value="<?php echo $h['id']; ?>" <?php echo $selected_house_id === (int)$h['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($h['house_no'] . ' (' . $h['soi_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <h6 class="mb-3">ระบุน้ำหนัก (กิโลกรัม)</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">พลาสติก</label>
                            <input type="number" step="0.01" class="form-control weight-input" name="plastic_kg" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">กระดาษ</label>
                            <input type="number" step="0.01" class="form-control weight-input" name="paper_kg" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">แก้ว</label>
                            <input type="number" step="0.01" class="form-control weight-input" name="glass_kg" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">โลหะ</label>
                            <input type="number" step="0.01" class="form-control weight-input" name="metal_kg" value="0">
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">น้ำหนักรวม: <span id="totalDisplay" class="text-primary-custom fw-bold">0.00</span> กก.</h5>
                        <button type="submit" class="btn btn-primary-custom px-4">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.weight-input').forEach(input => {
    input.addEventListener('input', () => {
        let total = 0;
        document.querySelectorAll('.weight-input').forEach(inp => {
            total += parseFloat(inp.value) || 0;
        });
        document.getElementById('totalDisplay').textContent = total.toFixed(2);
    });
});
</script>
