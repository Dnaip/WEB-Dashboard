<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmtCheck = $pdo->prepare("SELECT project_code FROM projects WHERE id = ?");
    $stmtCheck->execute([$id]);
    $deletedProject = $stmtCheck->fetchColumn();

    // ... คำสั่งลบ (DELETE FROM projects WHERE id = ?) ...
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);

    // เพิ่มบรรทัดนี้ลงไป เพื่อบันทึกประวัติ
    if($deletedProject) {
        systemLog($pdo, $_SESSION['user_id'], 'DELETE', "ลบข้อมูลโครงการ: $deletedProject");
    }
}
    
// ลบเสร็จให้เด้งกลับไปหน้าตาราง
header('Location: data.php');
exit();
?>