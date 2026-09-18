<?php
session_start();
require_once 'db.php';
$page = 'logs';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลประวัติการใช้งานและเชื่อมกับชื่อผู้ใช้
$stmt = $pdo->query("
    SELECT l.*, u.name 
    FROM system_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการใช้งาน - EWC Dash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; }
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); transition: all 0.3s; }
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; background-color: #f4f7fb; }
        .card { border-radius: 16px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.04); }
        
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
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel">
            <!-- Navbar (ก๊อปปี้มาจากหน้าอื่นได้เลย) -->
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
                    <h2 class="fw-bold text-dark mb-0">ประวัติการใช้งานระบบ (Activity Log)</h2>
                </div>
                
                <div class="card p-3">
                    <div class="card-body">
                        <table id="myTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>วันเวลา</th>
                                    <th>ผู้ใช้งาน</th>
                                    <th>การกระทำ</th>
                                    <th>รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($logs as $row): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
                                    <td class="fw-medium text-primary"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($row['name']) ?></td>
                                    <td>
                                        <?php 
                                            $badge = 'bg-secondary';
                                            if($row['action_type'] == 'CREATE') $badge = 'bg-success';
                                            else if($row['action_type'] == 'UPDATE') $badge = 'bg-warning text-dark';
                                            else if($row['action_type'] == 'DELETE') $badge = 'bg-danger';
                                            else if($row['action_type'] == 'LOGIN') $badge = 'bg-info text-dark';
                                        ?>
                                        <span class="badge <?= $badge ?> px-2 py-1"><?= $row['action_type'] ?></span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($row['description']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#logTable').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" },
                "order": [[0, "desc"]] // เรียงจากเวลาล่าสุด
            });
        });

        // Dark Mode Script (ก๊อปปี้จากหน้าอื่น)
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        if(currentTheme === 'dark') { themeIcon.classList.replace('bi-moon-stars', 'bi-sun'); themeToggleBtn.classList.replace('btn-light', 'btn-dark'); document.querySelector('.navbar').classList.remove('bg-white'); }

        themeToggleBtn.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            if(newTheme === 'dark') {
                themeIcon.classList.replace('bi-moon-stars', 'bi-sun'); themeToggleBtn.classList.replace('btn-light', 'btn-dark'); document.querySelector('.navbar').classList.remove('bg-white');
            } else {
                themeIcon.classList.replace('bi-sun', 'bi-moon-stars'); themeToggleBtn.classList.replace('btn-dark', 'btn-light'); document.querySelector('.navbar').classList.add('bg-white');
            }
        });
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