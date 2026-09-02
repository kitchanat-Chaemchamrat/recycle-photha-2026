<?php
requireRole(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sale') {
    $sale_date = $_POST['sale_date'];
    $waste_type = $_POST['waste_type'];
    $weight = (float)$_POST['weight_kg'];
    $price = (float)$_POST['price_per_kg'];
    $total = $weight * $price;
    
    $stmt = $pdo->prepare("INSERT INTO recycle_sales (sale_date, waste_type, weight_kg, price_per_kg, total_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sale_date, $waste_type, $weight, $price, $total]);
}

$sales = $pdo->query("SELECT * FROM recycle_sales ORDER BY sale_date DESC")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">ขายขยะรีไซเคิล</h1>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addSaleModal">
        <i class="bi bi-cart-plus"></i> บันทึกการขาย
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>วันที่ขาย</th>
                        <th>ประเภทขยะ</th>
                        <th>น้ำหนัก (กก.)</th>
                        <th>ราคาต่อ กก.</th>
                        <th>ยอดรวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sum = 0;
                    foreach ($sales as $sale): 
                        $sum += $sale['total_amount'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        <td><?php echo htmlspecialchars($sale['waste_type']); ?></td>
                        <td><?php echo number_format($sale['weight_kg'], 2); ?></td>
                        <td><?php echo number_format($sale['price_per_kg'], 2); ?></td>
                        <td class="fw-bold text-primary-custom"><?php echo number_format($sale['total_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">ยอดขายสะสมรวม:</td>
                        <td class="text-primary-custom fs-5"><?php echo number_format($sum, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">บันทึกการขายขยะ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_sale">
                    <div class="mb-3">
                        <label class="form-label">วันที่ขาย</label>
                        <input type="date" class="form-control" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภทขยะ</label>
                        <select class="form-select" name="waste_type" required>
                            <option value="รวม (พลาสติก)">พลาสติก</option>
                            <option value="รวม (กระดาษ)">กระดาษ</option>
                            <option value="รวม (แก้ว)">แก้ว</option>
                            <option value="รวม (โลหะ)">โลหะ</option>
                            <option value="ขยะรวม">ขยะรวมหลายประเภท</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">น้ำหนักรวม (กก.)</label>
                            <input type="number" step="0.01" class="form-control" name="weight_kg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ราคา/กก. (บาท)</label>
                            <input type="number" step="0.01" class="form-control" name="price_per_kg" required>
                        </div>
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
