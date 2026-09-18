<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Data - My Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); transition: all 0.3s; }
        .nav-link { color: rgba(255, 255, 255, .6); border-radius: 8px; margin: 0 12px 8px 12px; padding: 10px 16px; transition: all 0.2s; }
        .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, .1); }
        .nav-link.active { color: #fff; background-color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
        .card { border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.04) !important; }
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        @media (max-width: 767.98px) {
            .sidebar { margin-left: -250px; position: fixed; z-index: 1050; height: 100%; }
            .sidebar.active { margin-left: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.1); }
        }
        /* --- ระบบ Dark Mode --- */
        [data-bs-theme="dark"] body, 
        [data-bs-theme="dark"] html { background-color: #0f172a !important; }
        
        [data-bs-theme="dark"] .main-panel { background-color: #111827; }
        
        /* เปลี่ยนสี Navbar และ Card ให้กลืนกับโหมดมืด */
        [data-bs-theme="dark"] .navbar, 
        [data-bs-theme="dark"] .card { 
            background-color: #1e293b !important; 
            border-color: #334155 !important; 
        }
        
        /* ปรับสีตัวหนังสือให้สว่างขึ้น */
        [data-bs-theme="dark"] .text-dark { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .table-light { background-color: #334155; color: #f8fafc; }
        [data-bs-theme="dark"] tbody td { color: #e2e8f0; border-bottom-color: #334155; }
        
        /* ปรับช่อง Input ต่างๆ */
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }
        /* --- Custom Branding (EEC Theme) --- */
        :root {
            --eec-brand: #0284c7; /* สีฟ้าน้ำทะเลลึก ดูน่าเชื่อถือ */
            --eec-accent: #38bdf8; /* สีฟ้าสว่าง เพิ่มความทันสมัย */
        }
        
        /* ปรับแต่งปุ่มหลัก (Primary) ให้เป็นแบบไล่สีและมีเงา */
        .btn-primary {
            background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3) !important;
            transition: all 0.3s ease !important;
        }
        .btn-primary:hover {
            transform: translateY(-2px); /* ลอยขึ้นเมื่อเอาเมาส์ชี้ */
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.4) !important;
        }
        
        /* ปรับแต่งปุ่มเมนูใน Sidebar ที่กำลังใช้งาน (Active) */
        .nav-link.active {
            background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
            border-left: 4px solid #fff; /* เพิ่มแถบสีขาวด้านซ้ายให้ดูมีลูกเล่น */
        }
        
        .brand-logo-text {
            background: linear-gradient(45deg, #38bdf8, #e0f2fe); /* มาตรฐาน */
            background: -webkit-linear-gradient(45deg, #38bdf8, #e0f2fe); /* รองรับรุ่นเก่า */
            background-clip: text; /* มาตรฐาน */
            -webkit-background-clip: text; /* รองรับรุ่นเก่า */
            color: transparent; /* มาตรฐาน */
            -webkit-text-fill-color: transparent; /* รองรับรุ่นเก่า */
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        /* --- Smooth Animations --- */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }
        
        /* โหมดสว่าง: ลอยขึ้นและเงาเข้มขึ้นนิดนึง */
        .card:hover {
            transform: translateY(-5px); /* ลอยขึ้น 5px */
            box-shadow: 0 12px 30px rgba(0,0,0,0.08) !important;
        }
        
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
        [data-bs-theme="dark"] .input-group-text {
            background-color: #0f172a !important; /* สีเดียวกับพื้นหลัง */
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        
        /* ปรับสีช่องค้นหาเวลาคลิกพิมพ์ (Focus) ไม่ให้เด้งกลับเป็นสีขาว */
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #38bdf8 !important; /* ขอบสีฟ้าตอนคลิก */
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }
        /* --- Sidebar Styles --- */
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            transition: all 0.3s;
            width: 260px !important; /* ล็อกความกว้างไว้กันเพี้ยน */
            min-width: 260px;
        }
        
        /* สไตล์ของปุ่มเมนู */
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            white-space: nowrap; /* สำคัญมาก: ห้ามข้อความตกบรรทัดเด็ดขาด */
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: #cbd5e1 !important; /* สีเทาอ่อนๆ เวลาไม่ได้กด */
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff !important;
        }

        /* สไตล์ตอนที่เมนูถูกเลือก (Active) */
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--eec-brand), var(--eec-accent)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
            border-left: 4px solid #ffffff;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Sidebar (ย้าย active มาที่ Data) -->
        <?php 
            $page = 'data';
            include 'sidebar.php'; 
        ?>
        <!-- Main Content Area -->
        <div class="main-panel">
<!-- Navbar (แถบด้านบน) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-3 shadow-sm">
    <div class="container-fluid">
        <!-- ปุ่มเปิด/ปิด Sidebar สำหรับมือถือ -->
        <button type="button" id="sidebarCollapse" class="btn btn-light d-md-none border me-3">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- ช่อง Search (ถ้ามี) -->
        <form class="d-flex ms-auto w-50 max-w-400">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input class="form-control border-start-0 bg-light" type="search" placeholder="Search..." aria-label="Search">
            </div>
        </form>

        <!-- มุมโปรไฟล์ขวาบน และปุ่ม Logout -->
        <div class="ms-auto d-flex align-items-center">
        <button id="themeToggle" class="btn btn-light rounded-circle me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-moon-stars"></i>
        </button>
    
        <?php 
        $navAvatar = (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) 
        ? "uploads/profiles/" . $_SESSION['profile_image'] 
        : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['user_name']) . "&background=3b82f6&color=fff";
?>
<img src="<?= $navAvatar ?>" class="rounded-circle shadow-sm" width="36" height="36" style="object-fit: cover;" alt="Profile">
        <a href="logout.php" class="btn btn-sm btn-outline-danger ms-3 rounded-pill px-3">ออกจากระบบ</a>
    </div>
    </div>
    </nav>

            <main class="content p-4">
                <div class="container-fluid">
                    
                    <!-- ส่วนหัว: ชื่อหน้าเว็บ และ ปุ่มเปิด Modal -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 fw-bold text-dark">ฐานข้อมูลโครงการลงทุน</h2>
                        
                        <!-- สเต็ป 3: ปุ่มสำหรับเปิด Modal ฟอร์ม -->
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                            <i class="bi bi-plus-lg me-2"></i> เพิ่มโครงการใหม่
                        </button>
                    </div>
                    
                    <!-- ส่วนตาราง DataTables เต็มจอ -->
                    <div class="card border-0">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="myTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>รหัสโครงการ</th>
                                            <th>ชื่อโครงการ</th>
                                            <th>พื้นที่ (Zone)</th>
                                            <th>งบประมาณ (ลบ.)</th>
                                            <th>สถานะ</th>
                                            <th class="text-end">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    <?php foreach($projects as $row): ?>
    <tr>
        <!-- 1 --> <td><?= htmlspecialchars($row['project_code']) ?></td>
        <td class="fw-medium">
    <?= htmlspecialchars($row['project_name']) ?>
    
    <?php if (!empty($row['document_file'])): ?>
        <br>
        <a href="uploads/<?= $row['document_file'] ?>" target="_blank" class="badge border border-primary text-primary text-decoration-none mt-1 p-2">
            <i class="bi bi-paperclip"></i> ดูเอกสาร
        </a>
    <?php endif; ?>
</td>
        <!-- 3 --> <td><?= htmlspecialchars($row['zone']) ?></td>
        <!-- 4 --> <td><?= number_format($row['budget']) ?></td>
        <!-- 5 --> <td>
            <?php 
                $badgeClass = 'bg-secondary';
                if($row['status'] == 'ดำเนินการแล้ว') $badgeClass = 'bg-success';
                else if($row['status'] == 'รออนุมัติ') $badgeClass = 'bg-warning text-dark';
                else if($row['status'] == 'ระงับชั่วคราว') $badgeClass = 'bg-danger';
            ?>
            <span class="badge <?= $badgeClass ?> rounded-pill px-3"><?= $row['status'] ?></span>
        </td>
        <!-- 6 --> <td class="text-end">
            <!-- ปุ่มแก้ไข -->
            <a href="edit_project.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-primary">
                <i class="bi bi-pencil"></i>
            </a>
                                        <!-- ปุ่มลบ (SweetAlert) -->
                                        <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['project_code']) ?>')" class="btn btn-sm btn-light text-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    </td>
                                    </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- สเต็ป 3: โค้ด Modal (หน้าต่างป็อปอัปสำหรับเพิ่มข้อมูล) -->
    <!-- ============================================== -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <!-- Header ของ Modal -->
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="addProjectModalLabel">เพิ่มโครงการลงทุนใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Body: ส่วนฟอร์มกรอกข้อมูล (รอเชื่อมต่อ PHP/SQL) -->
                <div class="modal-body">
                    <!-- ค้นหาแท็ก <form id="addProjectForm"> แล้วแก้ไขเป็นแบบนี้ -->
                    <form id="addProjectForm" action="add_project.php" method="POST" enctype="multipart/form-data">
                         <div class="mb-3">
                         <label class="form-label text-muted small">ชื่อโครงการ</label>
                        <input type="text" name="project_name" class="form-control px-3 py-2" placeholder="เช่น ศูนย์วิจัยนวัตกรรม" required>
                    </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">พื้นที่ (Zone)</label>
                    <select name="zone" class="form-select px-3 py-2" required>
                    <option value="" selected disabled>เลือกพื้นที่...</option>
                    <option value="ระยอง">ระยอง</option>
                    <option value="ชลบุรี">ชลบุรี</option>
                    <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small">งบประมาณ (ล้านบาท)</label>
                    <input type="number" step="0.01" name="budget" class="form-control px-3 py-2" placeholder="เช่น 500" required>
                </div>
        </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">สถานะโครงการ</label>
                    <select name="status" class="form-select px-3 py-2" required>
                    <option value="รออนุมัติ">รออนุมัติ</option>
                    <option value="ดำเนินการแล้ว">ดำเนินการแล้ว</option>
                    <option value="ระงับชั่วคราว">ระงับชั่วคราว</option>
                 </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">สถานะโครงการ</label>
                    <select name="status" class="form-select px-3 py-2" required>
                    <option value="รออนุมัติ">รออนุมัติ</option>
                    <option value="ดำเนินการแล้ว">ดำเนินการแล้ว</option>
                    <option value="ระงับชั่วคราว">ระงับชั่วคราว</option>
                </select>
                </div>
                        <!-- เพิ่มช่องอัปโหลดเอกสารตรงนี้ -->
                    <div class="mb-3">
                        <label class="form-label text-muted small">เอกสารแนบโครงการ (PDF, DOCX)</label>
                        <input type="file" name="document_file" class="form-control px-3 py-2" accept=".pdf,.doc,.docx">
                        <small class="text-muted">ขนาดไฟล์ไม่เกิน 5MB</small>
                </div>
                </form>
                </div> 
                <!-- Footer: ปุ่มกดยกเลิก/บันทึก -->
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <!-- ปุ่มนี้เตรียมไว้ผูกกับ PHP/AJAX ตอนทำ Backend -->
                    <button type="submit" form="addProjectForm" class="btn btn-primary rounded-pill px-4">บันทึกข้อมูล</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================================== -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // เปิด-ปิด Sidebar มือถือ
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // เปิดใช้งาน DataTables
        $(document).ready(function() {
            $('#myProjectTable').DataTable({
                "pageLength": 10,
                "language": {
                    "lengthMenu": "แสดง _MENU_ แถว",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "หน้า _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "search": "ค้นหา:",
                    "paginate": { "next": "ถัดไป", "previous": "ก่อนหน้า" }
                }
            });
        });
    </script>
    <script>
    // ฟังก์ชันสำหรับแจ้งเตือนยืนยันก่อนลบข้อมูล
    function confirmDelete(id, code) {
        Swal.fire({
            title: 'ยืนยันการลบข้อมูล?',
            text: "คุณต้องการลบโครงการรหัส " + code + " ใช่หรือไม่ ข้อมูลที่ลบจะไม่สามารถกู้คืนได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // สีแดง
            cancelButtonColor: '#6c757d', // สีเทา
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ถ้ากดยืนยัน ให้เปลี่ยนหน้าไปที่ไฟล์ลบข้อมูล
                window.location.href = 'delete_project.php?id=' + id;
            }
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ... (โค้ด confirmDelete ที่เราเพิ่งเขียน) ...

    // ดักจับสถานะจาก URL เพื่อแสดงแจ้งเตือน
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg') && urlParams.get('msg') === 'updated') {
        Swal.fire({
            title: 'สำเร็จ!',
            text: 'อัปเดตข้อมูลโครงการเรียบร้อยแล้ว',
            icon: 'success',
            timer: 2000, // แจ้งเตือนจะปิดเองใน 2 วินาที
            showConfirmButton: false 
        }).then(() => {
            // เคลียร์ URL ให้สะอาด (เอา ?msg=updated ออก)
            window.history.replaceState(null, null, window.location.pathname);
        });
    }
</script>
<script>
    const themeToggleBtn = document.getElementById('themeToggle');
    const themeIcon = themeToggleBtn.querySelector('i');
    
    // เช็กว่าเคยเลือกโหมดมืดไว้ไหม ถ้าไม่เคยค่าเริ่มต้นคือ light
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // สั่งเปลี่ยนโหมดตามค่าที่จำไว้ทันที
    document.documentElement.setAttribute('data-bs-theme', currentTheme);
    updateThemeUI(currentTheme);

    // เมื่อกดปุ่มสลับโหมด
    themeToggleBtn.addEventListener('click', () => {
        const newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
        
        // ถ้าระบบมี Chart.js ให้รีเฟรชสีกราฟด้วย
        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = newTheme === 'dark' ? '#cbd5e1' : '#666';
            Chart.defaults.borderColor = newTheme === 'dark' ? '#334155' : '#e5e7eb';
            // อัปเดตกราฟที่มีอยู่ในหน้า (ถ้ามีตัวแปร trendChart)
            if (typeof trendChart !== 'undefined') trendChart.update();
        }
    });

    // ฟังก์ชันสำหรับเปลี่ยนไอคอนพระอาทิตย์/พระจันทร์
    function updateThemeUI(theme) {
        if(theme === 'dark') {
            themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
            themeToggleBtn.classList.replace('btn-light', 'btn-dark');
            // เอาคลาส bg-white เดิมออก เพื่อให้ CSS โหมดมืดทำงานได้เต็มที่
            document.querySelector('.navbar').classList.remove('bg-white');
        } else {
            themeIcon.classList.replace('bi-sun', 'bi-moon-stars');
            themeToggleBtn.classList.replace('btn-dark', 'btn-light');
            document.querySelector('.navbar').classList.add('bg-white');
        }
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