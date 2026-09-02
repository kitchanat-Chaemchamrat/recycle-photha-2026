<?php
require 'config/database.php';
require 'includes/auth.php';

requireLogin();

$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = ['dashboard', 'soi', 'house', 'house_card', 'member', 'evaluation', 'evaluation_approval', 'recycle', 'sales', 'dividend', 'report', 'qr_scan'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการขยะชุมชน</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SheetJS for Excel Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="app-layout bg-light">

    <?php include 'includes/header.php'; ?>

    <div class="app-body container-fluid">
        <div class="row flex-grow-1">
            <?php include 'includes/sidebar.php'; ?>

            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 d-flex flex-column">
                <main class="main-content flex-grow-1">
                    <?php 
                    $file = "modules/{$page}.php";
                    if (file_exists($file)) {
                        include $file;
                    } else {
                        echo "<div class='alert alert-danger mt-4'>ไม่พบหน้าต่างที่ต้องการ</div>";
                    }
                    ?>
                </main>

                <?php include 'includes/footer.php'; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>
