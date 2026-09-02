<?php
requireRole(['super_admin', 'admin', 'auditor']);

// Fetch Top Soi Ranking data
$top_sois = $pdo->query("
    SELECT s.soi_name, 
           AVG(e.total_score) as avg_score, 
           SUM(w.total_kg) as total_weight,
           (AVG(e.total_score) * SUM(w.total_kg)) as weighted_score
    FROM sois s
    LEFT JOIN houses h ON s.id = h.soi_id
    LEFT JOIN waste_evaluations e ON h.id = e.house_id
    LEFT JOIN waste_weights w ON h.id = w.house_id
    GROUP BY s.id
    ORDER BY weighted_score DESC
")->fetchAll();

// Fetch Top House Ranking
$top_houses = $pdo->query("
    SELECT h.house_no, h.owner_name, s.soi_name,
           AVG(e.total_score) as avg_score, 
           SUM(w.total_kg) as total_weight
    FROM houses h
    LEFT JOIN sois s ON h.soi_id = s.id
    LEFT JOIN waste_evaluations e ON h.id = e.house_id
    LEFT JOIN waste_weights w ON h.id = w.house_id
    GROUP BY h.id
    ORDER BY avg_score DESC, total_weight DESC
    LIMIT 10
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
    <h1 class="h2">รายงานภาพรวม (Ranking)</h1>
    <div>
        <button class="btn btn-sm btn-outline-danger" onclick="printReport()"><i class="bi bi-printer"></i> พิมพ์รายงาน</button>
        <button class="btn btn-sm btn-outline-primary-custom" onclick="exportExcel()"><i class="bi bi-file-excel"></i> Export Excel</button>
    </div>
</div>

<div class="print-header d-none d-print-block mb-4">
    <h2 class="mb-1">รายงานภาพรวม (Ranking)</h2>
    <p class="text-muted mb-0">ระบบบริหารการคัดแยกขยะชุมชน — พิมพ์เมื่อ <?php echo date('d/m/Y H:i'); ?></p>
</div>

<div id="reportContainer">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-primary fw-bold">Top Soi Ranking (จัดอันดับซอยดีเด่น)</div>
                <div class="card-body">
                    <table id="soiTable" class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>อันดับ</th>
                                <th>ชื่อซอย</th>
                                <th>คะแนนเฉลี่ย</th>
                                <th>ปริมาณรีไซเคิล (กก.)</th>
                                <th>Weighted Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach($top_sois as $soi): ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($soi['soi_name']); ?></td>
                                <td><?php echo number_format($soi['avg_score'], 2); ?></td>
                                <td><?php echo number_format($soi['total_weight'], 2); ?></td>
                                <td class="fw-bold"><?php echo number_format($soi['weighted_score'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-accent fw-bold">Top House Ranking (10 อันดับบ้านดีเด่น)</div>
                <div class="card-body">
                    <table id="houseTable" class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>อันดับ</th>
                                <th>บ้านเลขที่</th>
                                <th>เจ้าของบ้าน</th>
                                <th>ซอย</th>
                                <th>คะแนนเฉลี่ย</th>
                                <th>ปริมาณรีไซเคิล (กก.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach($top_houses as $house): ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($house['house_no']); ?></td>
                                <td><?php echo htmlspecialchars($house['owner_name']); ?></td>
                                <td><?php echo htmlspecialchars($house['soi_name']); ?></td>
                                <td><?php echo number_format($house['avg_score'], 2); ?></td>
                                <td><?php echo number_format($house['total_weight'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printReport() {
    window.print();
}

function exportExcel() {
    // Merge both tables into one workbook
    var wb = XLSX.utils.book_new();
    
    var wsSoi = XLSX.utils.table_to_sheet(document.getElementById('soiTable'));
    XLSX.utils.book_append_sheet(wb, wsSoi, "Soi Ranking");
    
    var wsHouse = XLSX.utils.table_to_sheet(document.getElementById('houseTable'));
    XLSX.utils.book_append_sheet(wb, wsHouse, "House Ranking");

    XLSX.writeFile(wb, "Waste_Report.xlsx");
}
</script>
