<?php
session_start();
require_once 'db.php';

// ถ้าล็อกอินอยู่แล้ว ให้ข้ามไปหน้าภาพรวมเลย
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ค้นหาผู้ใช้จาก username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบรหัสผ่านที่กรอกมา เทียบกับที่เข้ารหัสไว้ในฐานข้อมูล
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        
        // --- ส่วนที่เพิ่มเข้ามา: เก็บสิทธิ์การใช้งาน (Role) ลง Session ---
        $_SESSION['role'] = $user['role']; 
        // --------------------------------------------------------
        
        header("Location: index.php");
        exit();
    } else {
        $error = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - EEC Dash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        .login-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
        .btn-primary { background: linear-gradient(135deg, #0284c7, #38bdf8) !important; border: none; }
        .text-brand {
            background: -webkit-linear-gradient(45deg, #0284c7, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container" style="max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-brand">EWC Dashboard</h3>
            <p class="text-muted">ระบบบริหารจัดการข้อมูลโครงการลงทุน</p>
        </div>
        <div class="card login-card p-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4 text-center">เข้าสู่ระบบ</h5>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 text-center fs-6"><?= $error ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small">ชื่อผู้ใช้งาน</label>
                        <input type="text" name="username" class="form-control px-3 py-2" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small">รหัสผ่าน</label>
                        <input type="password" name="password" class="form-control px-3 py-2" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">เข้าสู่ระบบ</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>