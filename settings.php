<?php
session_start();
require_once 'db.php';
$page = 'settings';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผู้ใช้ปัจจุบันมาแสดง
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่าโปรไฟล์ - EEC Dash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- ใส่ CSS และ Dark Mode ของคุณที่นี่ (ก๊อปปี้จากไฟล์ index.php มาวางได้เลย) -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        
        :root {
            --eec-brand: #0284c7;
            --eec-accent: #38bdf8;
        }
        
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        
        /* โครงสร้าง Layout */
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; background-color: #f4f7fb; }
        
        /* --- Sidebar Styles --- */
        .sidebar { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important; width: 260px !important; min-width: 260px; transition: all 0.3s; }
        .sidebar .nav-link { display: flex; align-items: center; white-space: nowrap; padding: 10px 16px; border-radius: 8px; transition: all 0.2s ease; color: #cbd5e1 !important; }
        .sidebar .nav-link:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
        .sidebar .nav-link.active { background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important; color: #ffffff !important; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important; border-left: 4px solid #ffffff; }
        
        /* --- Card & Buttons --- */
        .card { border-radius: 16px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.04); }
        .btn-primary { background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important; border: none; }
        
        /* สไตล์ปุ่มอัปโหลดรูป */
        .profile-upload-wrapper { position: relative; width: 120px; height: 120px; margin: 0 auto; }
        .profile-upload-wrapper img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .profile-upload-wrapper input[type="file"] { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; top: 0; left: 0; }
        .upload-badge { position: absolute; bottom: 0; right: 0; background: #0284c7; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; pointer-events: none; }

        /* ==========================================
           ระบบ Dark Mode (ฉบับสมบูรณ์สำหรับหน้า Settings)
        ========================================== */
        /* บังคับพื้นหลังให้ดำสนิท */
        [data-bs-theme="dark"] body, 
        [data-bs-theme="dark"] html,
        [data-bs-theme="dark"] .main-panel { background-color: #0f172a !important; }
        
        /* บังคับ Navbar และการ์ดให้เป็นสีเทาเข้ม */
        [data-bs-theme="dark"] .navbar, 
        [data-bs-theme="dark"] .card { background-color: #1e293b !important; border-color: #334155 !important; }
        
        /* ปรับสีตัวหนังสือให้อ่านง่าย */
        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] h2, [data-bs-theme="dark"] h6 { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        
        /* ปรับกล่อง Input ใส่รหัสผ่านให้เข้ากับโหมดมืด */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .bg-light {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        
        /* ปรับขอบรูปโปรไฟล์ในโหมดมืด */
        [data-bs-theme="dark"] .profile-upload-wrapper img { border-color: #1e293b !important; }
        [data-bs-theme="dark"] .upload-badge { border-color: #1e293b !important; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel">
            <!-- Navbar พร้อมปุ่ม Dark Mode -->
        <!-- คัดลอก Navbar ชุดนี้ไปทับ Navbar เดิมในหน้า users.php, logs.php, settings.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-3 shadow-sm">
    <div class="container-fluid">
        <button type="button" id="sidebarCollapse" class="btn btn-light d-md-none border me-3"><i class="bi bi-list"></i></button>
        
        <!-- เพิ่มช่อง Search กลับเข้ามา เพื่อให้ Layout เท่ากับหน้า Index -->
        <form class="d-none d-md-flex ms-4" style="width: 300px;">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 bg-light" placeholder="ค้นหาข้อมูล..." aria-label="Search">
            </div>
        </form>

        <div class="ms-auto d-flex align-items-center">
            <!-- ปุ่มเปลี่ยนโหมด -->
            <button id="themeToggle" class="btn btn-light rounded-circle me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-moon-stars"></i>
            </button>
            
            <?php 
                $navAvatar = (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) 
                    ? "uploads/profiles/" . $_SESSION['profile_image'] 
                    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['user_name']) . "&background=3b82f6&color=fff"; 
            ?>
            <img src="<?= $navAvatar ?>" class="rounded-circle shadow-sm" width="36" height="36" style="object-fit: cover;" alt="Profile">
            <span class="ms-2 fw-medium d-none d-md-block"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3 rounded-pill px-3">ออกจากระบบ</a>
        </div>
    </div>
</nav>

            <main class="content p-4">
                <div class="container-fluid" style="max-width: 800px;">
                    <h2 class="mb-4 fw-bold text-dark">ตั้งค่าบัญชีผู้ใช้</h2>
                    
                    <div class="card p-4">
                        <div class="card-body">
                            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                                <!-- ส่วนอัปโหลดรูปภาพ -->
                                <div class="text-center mb-5">
                                    <div class="profile-upload-wrapper">
                                        <?php 
                                            $avatarUrl = !empty($user['profile_image']) 
                                                ? "uploads/profiles/" . $user['profile_image'] 
                                                : "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=3b82f6&color=fff";
                                        ?>
                                        <img src="<?= $avatarUrl ?>" id="previewImage" alt="Profile">
                                        <div class="upload-badge"><i class="bi bi-camera-fill"></i></div>
                                        <input type="file" name="profile_image" accept="image/png, image/jpeg" onchange="previewFile(this)">
                                    </div>
                                    <small class="text-muted d-block mt-2">คลิกที่รูปเพื่อเปลี่ยน (JPEG, PNG ไม่เกิน 2MB)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">ชื่อผู้ใช้งาน (Username - เปลี่ยนไม่ได้)</label>
                                    <input type="text" class="form-control px-3 py-2 bg-light" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small">ชื่อ-นามสกุลที่แสดง</label>
                                    <input type="text" name="display_name" class="form-control px-3 py-2" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                
                                <hr class="my-4">
                                <h6 class="fw-bold mb-3">เปลี่ยนรหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small">รหัสผ่านใหม่</label>
                                    <input type="password" name="new_password" class="form-control px-3 py-2" placeholder="กรอกรหัสผ่านใหม่">
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">บันทึกการตั้งค่า</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ฟังก์ชันพรีวิวรูปภาพทันทีที่เลือก
        function previewFile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <!-- สคริปต์ควบคุม Dark Mode -->
<script>
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        const themeIcon = themeToggleBtn.querySelector('i');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        // สั่งเปลี่ยนโหมดทันทีตอนโหลดหน้าเว็บ
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        updateThemeUI(currentTheme);

        // เมื่อกดปุ่มสลับโหมด
        themeToggleBtn.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        });

        function updateThemeUI(theme) {
            const nav = document.querySelector('.navbar');
            if(theme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
                themeToggleBtn.classList.replace('btn-light', 'btn-dark');
                if(nav) nav.classList.remove('bg-white');
            } else {
                themeIcon.classList.replace('bi-sun', 'bi-moon-stars');
                themeToggleBtn.classList.replace('btn-dark', 'btn-light');
                if(nav) nav.classList.add('bg-white');
            }
        }
    }
</script>
</body>
</html>