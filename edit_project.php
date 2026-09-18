<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';
$page = 'data'; // ให้ Sidebar ยังคงไฮไลท์ที่เมนูจัดการข้อมูล

// 1. ตรวจสอบและดึงข้อมูลเดิมมาแสดง
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ถ้าไม่พบข้อมูลให้เด้งกลับ
    if (!$project) {
        header('Location: data.php');
        exit();
    }
}

// 2. รับข้อมูลที่แก้ไขแล้วบันทึกลงฐานข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $project_name = trim($_POST['project_name']);
    $zone = trim($_POST['zone']);
    $budget = floatval($_POST['budget']);
    $status = trim($_POST['status']);

    $stmt = $pdo->prepare("UPDATE projects SET project_name = ?, zone = ?, budget = ?, status = ? WHERE id = ?");
    $stmt->execute([$project_name, $zone, $budget, $status, $id]);

    header('Location: data.php?msg=updated');
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลโครงการ - My Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); transition: all 0.3s; }
        .nav-link { color: rgba(255, 255, 255, .6); border-radius: 8px; margin: 0 12px 8px 12px; padding: 10px 16px; }
        .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, .1); }
        .nav-link.active { color: #fff; background-color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .card { border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.04) !important; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel">
            <main class="content p-4">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 fw-bold text-dark">แก้ไขข้อมูลโครงการ: <?= htmlspecialchars($project['project_code']) ?></h2>
                        <a href="data.php" class="btn btn-outline-secondary rounded-pill px-4">กลับไปหน้าตาราง</a>
                    </div>
                    
                    <div class="card border-0 p-4">
                        <div class="card-body">
                            <form action="edit_project.php" method="POST">
                                <!-- ซ่อน ID ไว้ส่งกลับไปตอนบันทึก -->
                                <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small">ชื่อโครงการ</label>
                                    <input type="text" name="project_name" class="form-control px-3 py-2" value="<?= htmlspecialchars($project['project_name']) ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">พื้นที่ (Zone)</label>
                                        <select name="zone" class="form-select px-3 py-2" required>
                                            <option value="ระยอง" <?= $project['zone'] == 'ระยอง' ? 'selected' : '' ?>>ระยอง</option>
                                            <option value="ชลบุรี" <?= $project['zone'] == 'ชลบุรี' ? 'selected' : '' ?>>ชลบุรี</option>
                                            <option value="ฉะเชิงเทรา" <?= $project['zone'] == 'ฉะเชิงเทรา' ? 'selected' : '' ?>>ฉะเชิงเทรา</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">งบประมาณ (ล้านบาท)</label>
                                        <input type="number" step="0.01" name="budget" class="form-control px-3 py-2" value="<?= $project['budget'] ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-muted small">สถานะโครงการ</label>
                                    <select name="status" class="form-select px-3 py-2" required>
                                        <option value="รออนุมัติ" <?= $project['status'] == 'รออนุมัติ' ? 'selected' : '' ?>>รออนุมัติ</option>
                                        <option value="ดำเนินการแล้ว" <?= $project['status'] == 'ดำเนินการแล้ว' ? 'selected' : '' ?>>ดำเนินการแล้ว</option>
                                        <option value="ระงับชั่วคราว" <?= $project['status'] == 'ระงับชั่วคราว' ? 'selected' : '' ?>>ระงับชั่วคราว</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">อัปเดตข้อมูล</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>