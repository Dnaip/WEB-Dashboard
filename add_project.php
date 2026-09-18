<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name']);
    $zone = trim($_POST['zone']);
    $budget = floatval($_POST['budget']);
    $status = trim($_POST['status']);

    // รันหมายเลขอัตโนมัติ
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM projects");
    $count = $stmtCount->fetchColumn() + 1;
    $project_code = '#EEC-2026-' . str_pad($count, 3, '0', STR_PAD_LEFT);

    // --- จัดการเรื่องอัปโหลดไฟล์ ---
    $uploaded_filename = null;
    
    // เช็กว่ามีการแนบไฟล์มาและไม่มี Error
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        // ถ้ายังไม่มีโฟลเดอร์ uploads ให้สร้างใหม่
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // ดึงนามสกุลไฟล์ออกมา (เช่น .pdf)
        $fileExtension = pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION);
        
        // สุ่มชื่อไฟล์ใหม่ด้วยเวลา เพื่อป้องกันไฟล์ชื่อซ้ำกันแล้วทับกัน
        $uploaded_filename = 'doc_' . time() . '_' . rand(1000,9999) . '.' . $fileExtension;
        
        // ย้ายไฟล์จากเครื่องเรา ไปเก็บไว้ในโฟลเดอร์ uploads ของ XAMPP
        move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadDir . $uploaded_filename);
    }
    // ----------------------------

    if (!empty($project_name) && !empty($zone)) {
        // อัปเดตคำสั่ง SQL ให้บันทึกชื่อไฟล์ลงไปด้วย
        $stmt = $pdo->prepare("INSERT INTO projects (project_code, project_name, zone, budget, status, document_file) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_code, $project_name, $zone, $budget, $status, $uploaded_filename]);
        systemLog($pdo, $_SESSION['user_id'], 'CREATE', "เพิ่มโครงการใหม่: $project_code ($project_name)");
    }

    // เด้งกลับพร้อมส่งข้อความแจ้งเตือน success ไปให้ SweetAlert
    header('Location: data.php?msg=success');
    exit();
}
?>