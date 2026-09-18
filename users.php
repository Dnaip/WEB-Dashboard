<?php
session_start();
require_once 'db.php';
$page = 'users';

// ป้องกันคนที่ไม่ใช่ Admin เข้าหน้านี้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลผู้ใช้ทั้งหมดมาแสดง
$stmt = $pdo->query("SELECT id, username, name, role FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้งาน - EWC Dash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        
        :root {
            --eec-brand: #0284c7;
            --eec-accent: #38bdf8;
        }
        
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        
        /* โครงสร้าง Layout */
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        
        /* ==========================================
           ระบบ Dark Mode (ฉบับสมบูรณ์)
        ========================================== */
        /* 1. พื้นหลังหลัก */
        [data-bs-theme="dark"] body, 
        [data-bs-theme="dark"] html,
        [data-bs-theme="dark"] .main-panel { 
            background-color: #0f172a !important; 
        }
        
        /* 2. จัดการการ์ดและแถบขาวๆ (Card & Headers) */
        [data-bs-theme="dark"] .navbar, 
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-footer { 
            background-color: #1e293b !important; 
            border-color: #334155 !important; 
        }
        
        /* 3. จัดการสีตัวหนังสือทั้งหมดให้อ่านง่าย */
        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] h1, [data-bs-theme="dark"] h2, 
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] h4, 
        [data-bs-theme="dark"] h5, [data-bs-theme="dark"] h6 { 
            color: #f8fafc !important; /* ขาวสว่าง */
        }
        [data-bs-theme="dark"] .text-muted { 
            color: #94a3b8 !important; /* เทาอ่อน */
        }
        
        /* 4. จัดการตาราง (Table & DataTables) */
        [data-bs-theme="dark"] .table { color: #e2e8f0 !important; }
        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] thead th { 
            background-color: #334155 !important; /* สีเทาเข้มสำหรับหัวตาราง */
            color: #f8fafc !important; 
            border-bottom-color: #475569 !important;
        }
        [data-bs-theme="dark"] tbody td { 
            background-color: transparent !important; 
            border-bottom-color: #334155 !important; 
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .table-hover tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.05) !important; /* ไฮไลท์ตอนเอาเมาส์ชี้ */
        }
        
        /* 5. จัดการกล่องค้นหาและ Dropdown */
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .dataTables_wrapper select,
        [data-bs-theme="dark"] .dataTables_wrapper input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .dataTables_wrapper {
            color: #e2e8f0 !important;
        }
        /* --- Sidebar Styles --- */
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            transition: all 0.3s;
            width: 260px !important;
            min-width: 260px;
        }
        
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            white-space: nowrap;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: #cbd5e1 !important;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff !important;
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
            border-left: 4px solid #ffffff;
        }
        
        .brand-logo-text {
            background: linear-gradient(45deg, #38bdf8, #e0f2fe);
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        
        /* --- Card & Buttons --- */
        .card { border-radius: 16px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.04); }
        .btn-primary { background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important; border: none; }
        /* แก้ไขกรอบไอคอน (แว่นขยาย) ให้เป็นสีมืด */
        [data-bs-theme="dark"] .input-group-text,
        [data-bs-theme="dark"] .bg-light {
            background-color: #0f172a !important; 
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        
        /* ปรับสีช่องค้นหาเวลาคลิกพิมพ์ (Focus) ไม่ให้เด้งกลับเป็นสีขาว */
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #38bdf8 !important; 
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }
    </style>
</head>
<body>
    <div class="wrapper d-flex" style="height: 100vh;">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel flex-grow-1" style="overflow-y: auto; background-color: #f4f7fb;">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark mb-0">จัดการผู้ใช้งานระบบ</h2>
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งาน
                    </button>
                </div>
                
                
                <div class="card p-3 shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <table id="myTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>รหัส</th>
                                    <th>ชื่อเข้าใช้ (Username)</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ระดับสิทธิ์</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td class="fw-medium"><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td>
                                        <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= strtoupper($u['role']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal เพิ่มผู้ใช้งาน -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_user.php" method="POST">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">สร้างบัญชีผู้ใช้ใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">ชื่อผู้ใช้งาน (Username สำหรับล็อกอิน)</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">รหัสผ่าน (Password)</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">ชื่อ-นามสกุล (แสดงในระบบ)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">ระดับสิทธิ์ (Role)</label>
                            <select name="role" class="form-select">
                                <option value="user">User (ดูข้อมูลได้อย่างเดียว)</option>
                                <option value="admin">Admin (จัดการระบบได้)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกผู้ใช้ใหม่</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
<!-- 1. เรียกใช้งานไลบรารี SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 2. สคริปต์ดักจับ URL และแสดง Alert -->
    <script>
        // เช็กว่า URL มีคำว่า ?msg= หรือ ?error= ตามมาด้วยไหม
        const urlParams = new URLSearchParams(window.location.search);
        
        // กรณีเพิ่มผู้ใช้งานสำเร็จ (?msg=success)
        if (urlParams.has('msg') && urlParams.get('msg') === 'success') {
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'บันทึกข้อมูลผู้ใช้งานระบบเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonColor: '#0284c7', // สีฟ้าเข้ากับธีม
                background: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#fff' : '#000'
            }).then(() => {
                // เคลียร์ URL ให้สะอาด ป้องกันการกดรีเฟรชแล้วเด้งซ้ำ
                window.history.replaceState(null, null, window.location.pathname);
            });
        } 
        // กรณีชื่อ Username ซ้ำ (?error=exists)
        else if (urlParams.has('error') && urlParams.get('error') === 'exists') {
            Swal.fire({
                title: 'ข้อผิดพลาด!',
                text: 'ชื่อ Username นี้มีคนใช้แล้ว กรุณาตั้งชื่อใหม่ครับ',
                icon: 'error',
                confirmButtonColor: '#ef4444', // สีแดงเตือน
                background: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#fff' : '#000'
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname);
            });
        }
    </script>
    <!-- 1. ต้องเรียกใช้ jQuery ก่อน (เพราะ DataTables ทำงานบน jQuery) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- 2. เรียกใช้ DataTables และไฟล์เชื่อมกับ Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

   <!-- 3. สคริปต์เสกตารางให้เป็น DataTables พร้อมแปลภาษาไทย -->
    <script>
        // แก้ไขจุดนี้: ต้องเป็น $(document).ready(...) ครับ
        $(document).ready(function() {
            $('#myTable').DataTable({
                "language": {
                    "sProcessing": "กำลังดำเนินการ...",
                    "sLengthMenu": "แสดง _MENU_ แถว",
                    "sZeroRecords": "ไม่พบข้อมูล",
                    "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
                    "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 แถว",
                    "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกแถว)",
                    "sSearch": "ค้นหา:",
                    "oPaginate": {
                        "sFirst": "หน้าแรก",
                        "sPrevious": "ก่อนหน้า",
                        "sNext": "ถัดไป",
                        "sLast": "หน้าสุดท้าย"
                    }
                }
            });
        });
    </script>
</body>
</html>