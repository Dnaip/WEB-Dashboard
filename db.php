<?php
$host = 'localhost';
$dbname = 'eec_dashboard';
$username = 'root'; // ค่าเริ่มต้นของ XAMPP คือ root
$password = '';     // ค่าเริ่มต้นของ XAMPP คือ รหัสผ่านว่าง

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // ตั้งค่าให้แสดง Error หากเขียน SQL ผิด
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage();
    exit();
}
function systemLog($pdo, $user_id, $action_type, $description) {
    $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action_type, description) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action_type, $description]);
}
?>