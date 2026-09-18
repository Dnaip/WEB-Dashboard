<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $display_name = trim($_POST['display_name']);
    $new_password = $_POST['new_password'];
    
    // อัปเดตชื่อ
    if (!empty($display_name)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$display_name, $user_id]);
        $_SESSION['user_name'] = $display_name; // อัปเดต Session
    }

    // อัปเดตรหัสผ่าน (ถ้ามีการกรอกมา)
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
    }

    // จัดการอัปโหลดรูปโปรไฟล์
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $newFilename = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $newFilename)) {
            // ดึงชื่อไฟล์เก่ามาลบทิ้ง (เพื่อไม่ให้เปลืองพื้นที่เซิร์ฟเวอร์)
            $stmtOld = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmtOld->execute([$user_id]);
            $oldImage = $stmtOld->fetchColumn();
            if ($oldImage && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }

            // บันทึกชื่อไฟล์ใหม่ลง Database
            $stmtImg = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmtImg->execute([$newFilename, $user_id]);
            $_SESSION['profile_image'] = $newFilename; // เก็บลง Session
        }
    }

    // กลับไปหน้าตั้งค่าพร้อมแจ้งเตือน
    header("Location: settings.php?msg=success");
    exit();
}
?>