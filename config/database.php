<?php
// config/database.php
$host = 'sql304.infinityfree.com';
$dbname = 'if0_42810720_remon_waste'; // หรือถ้าใช้ชื่ออื่นใน local ให้แก้ตรงนี้นะครับ เช่น 'remon_waste'
$username = 'if0_42810720';
$password = '1mfw2F21dE'; // ปรับตามจริง

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
