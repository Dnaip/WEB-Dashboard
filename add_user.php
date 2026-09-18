<?php
session_start();
require_once 'db.php';

// ป้องกันคนที่ไม่ใช่ Admin แอบยิงข้อมูลเข้ามา
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $name = trim($_POST['name']);
    $role = $_POST['role'];

    // 1. เช็กก่อนว่า Username นี้มีคนใช้หรือยัง
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtCheck->execute([$username]);
    if ($stmtCheck->rowCount() > 0) {
        // มีผู้ใช้นี้อยู่แล้ว (คุณสามารถประยุกต์ใช้ SweetAlert แจ้งเตือนได้)
        header("Location: users.php?error=exists");
        exit();
    }

    // 2. เข้ารหัสรหัสผ่าน (Hash)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. บันทึกลงฐานข้อมูล
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $name, $role]);

    // บันทึก Log การทำงาน
    $stmtLog = $pdo->prepare("INSERT INTO system_logs (user_id, action_type, description) VALUES (?, ?, ?)");
    $stmtLog->execute([$_SESSION['user_id'], 'CREATE', "เพิ่มผู้ใช้งานใหม่: $username ($role)"]);

    header("Location: users.php?msg=success");
    exit();
}
?>