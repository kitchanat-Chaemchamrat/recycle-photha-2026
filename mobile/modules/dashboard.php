<?php
requireRole(['super_admin', 'admin', 'auditor']);

// KPI Queries
$soi_count = $pdo->query("SELECT COUNT(*) FROM sois")->fetchColumn();
$house_count = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();
$member_count = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$total_recycle = $pdo->query("SELECT SUM(total_kg) FROM waste_weights")->fetchColumn() ?: 0;
$total_income = $pdo->query("SELECT SUM(total_amount) FROM recycle_sales")->fetchColumn() ?: 0;
$total_dividend = $pdo->query("SELECT SUM(dividend_pool) FROM dividend_periods")->fetchColumn() ?: 0;

$avg_score = $pdo->query("SELECT AVG(total_score) FROM waste_evaluations")->fetchColumn() ?: 0;

// Chart Data: Monthly Recycle Weight
$monthly_weights = $pdo->query("
    SELECT DATE_FORMAT(record_date, '%Y-%m') as month, SUM(total_kg) as weight 
    FROM waste_weights 
    GROUP BY month 
    ORDER BY month DESC LIMIT 6
")->fetchAll();

// Chart Data: Top Sois
$top_sois = $pdo->query("
    SELECT s.soi_name, AVG(e.total_score) as avg_score
    FROM sois s
    LEFT JOIN houses h ON s.id = h.soi_id
    LEFT JOIN waste_evaluations e ON h.id = e.house_id
    GROUP BY s.id
    ORDER BY avg_score DESC
    LIMIT 5
")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom page-header">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card kpi-card kpi-blue h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-map"></i> จำนวนซอย</h6>
                <h3 class="mb-0"><?php echo number_format($soi_count); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-yellow h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-house"></i> จำนวนบ้าน</h6>
                <h3 class="mb-0"><?php echo number_format($house_count); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-blue h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-people"></i> จำนวนสมาชิก</h6>
                <h3 class="mb-0"><?php echo number_format($member_count); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-yellow h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-star"></i> คะแนนเฉลี่ยรวม</h6>
                <h3 class="mb-0"><?php echo number_format($avg_score, 2); ?> / 100</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card kpi-blue h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-inboxes"></i> น้ำหนักรีไซเคิลรวม (กก.)</h6>
                <h3 class="mb-0"><?php echo number_format($total_recycle, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card kpi-yellow h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-cash"></i> รายได้ขายขยะ (บาท)</h6>
                <h3 class="mb-0"><?php echo number_format($total_income, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card kpi-blue h-100">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-cash-coin"></i> เงินปันผลรวม (บาท)</h6>
                <h3 class="mb-0"><?php echo number_format($total_dividend, 2); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">ปริมาณขยะรีไซเคิล 6 เดือนล่าสุด</div>
            <div class="card-body">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">คะแนนเฉลี่ยซอย (Top 5)</div>
            <div class="card-body">
                <canvas id="soiChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Chart
    const monthlyData = <?php echo json_encode(array_reverse($monthly_weights)); ?>;
    const mLabels = monthlyData.map(d => d.month);
    const mData = monthlyData.map(d => d.weight);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: mLabels,
            datasets: [{
                label: 'น้ำหนัก (กก.)',
                data: mData,
                borderColor: '#0A2540',
                tension: 0.1,
                fill: true,
                backgroundColor: 'rgba(10, 37, 64, 0.1)'
            }]
        }
    });

    // Soi Chart
    const soiData = <?php echo json_encode($top_sois); ?>;
    const sLabels = soiData.map(d => d.soi_name);
    const sData = soiData.map(d => d.avg_score);

    new Chart(document.getElementById('soiChart'), {
        type: 'bar',
        data: {
            labels: sLabels,
            datasets: [{
                label: 'คะแนนเฉลี่ย',
                data: sData,
                backgroundColor: '#B8860B'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
});
</script>
