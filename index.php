<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';
$page = 'home';

// 1. นับจำนวนโครงการทั้งหมด
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();

// 2. คำนวณงบประมาณรวมทั้งหมด
$totalBudget = $pdo->query("SELECT SUM(budget) FROM projects")->fetchColumn();

// 3. ดึงสัดส่วนงบประมาณแยกตามพื้นที่ (เพื่อนำไปใส่วาดกราฟ Doughnut)
$stmtZone = $pdo->query("SELECT zone, SUM(budget) as total_zone_budget FROM projects GROUP BY zone");
$zoneData = $stmtZone->fetchAll(PDO::FETCH_ASSOC);

$zoneLabels = [];
$zoneBudgets = [];
foreach($zoneData as $z) {
    $zoneLabels[] = $z['zone'];
    $zoneBudgets[] = $z['total_zone_budget'];
}

// 4. ดึงรายการโครงการ 5 อันดับล่าสุดมาแสดงในตารางหน้าแรก
$recentProjects = $pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="[https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css](https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css)" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* 1. นำเข้าฟอนต์ Kanit จาก Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        
        /* 2. เปลี่ยนฟอนต์พื้นฐานและสีพื้นหลังเว็บให้ดูสบายตา */
        body, html { height: 100%; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f4f7fb; }
        .wrapper { display: flex; height: 100vh; overflow: hidden; }
        
        /* 3. ตกแต่ง Sidebar ให้เป็นแบบไล่สี (Gradient) */
        .sidebar { 
            width: 250px; 
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); 
            transition: all 0.3s; 
        }
        .nav-link { 
            color: rgba(255, 255, 255, .6); 
            border-radius: 8px; /* ทำปุ่มเมนูให้มน */
            margin: 0 12px 8px 12px;
            padding: 10px 16px;
            transition: all 0.2s;
        }
        .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, .1); }
        .nav-link.active { 
            color: #fff; 
            background-color: #3b82f6; /* สีฟ้าสว่าง */
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); /* เงาสีฟ้าเรืองแสง */
        }
        
        /* 4. ตกแต่ง Card ทุกใบให้โค้งมนและมีเงาละมุนขึ้น */
        .card { 
            border-radius: 16px; 
            box-shadow: 0 4px 24px rgba(0,0,0,0.04) !important; 
            transition: transform 0.2s ease-in-out; 
        }
        .card:hover { transform: translateY(-2px); /* ลอยขึ้นนิดๆ ตอนเอาเมาส์ชี้ */ }
        
        .main-panel { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }

        @media (max-width: 767.98px) {
            .sidebar { 
                margin-left: -250px; 
                position: fixed; z-index: 1050; height: 100%;
            }
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
        /* --- Custom Branding (EWC Theme) --- */
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
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #f4f7fb;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        [data-bs-theme="dark"] #page-loader { background-color: #0f172a; }
        
        #page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .spinner-glow {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            border: 4px solid var(--eec-accent);
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
        }
        
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div id="page-loader">
        <div class="spinner-glow"></div>
    </div>
    <div class="wrapper">
        <!-- 1. Sidebar -->
        <?php 
            $page = 'home'; // ประกาศบอกว่าหน้านี้คือหน้า home
            include 'sidebar.php'; 
        ?>
        <!-- 2. Main Content Area -->
        <div class="main-panel">
            <!-- ค้นหาส่วนนี้ใน Navbar แล้วแก้ให้เป็นแบบนี้ -->
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
    
    <!-- เพิ่มปุ่ม Toggle Dark Mode ตรงนี้ครับ -->
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

            <!-- พื้นที่สำหรับใส่ Card และ Chart -->
            <main class="content p-4">
                <div class="container-fluid">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark mb-0">Dashboard Overview</h2>
    
    <!-- เพิ่ม Dropdown สำหรับเลือกปีตรงนี้ -->
    <select id="yearFilter" class="form-select shadow-sm" style="width: 150px; cursor: pointer;">
        <option value="all">ทุกปีทั้งหมด</option>
        <option value="2026">ปี 2026</option>
        <option value="2025">ปี 2025</option>
        <option value="2024">ปี 2024</option>
    </select>
</div>
<!-- เริ่มต้น: ส่วนการ์ดสรุปตัวเลข (KPIs) -->
<!-- ========================================== -->
<div class="row g-4 mb-4">
    
    <!-- Card 1: จำนวนผู้ใช้งาน -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" data-aos="fade-up">
            <div class="card-body" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-6">จำนวนผู้ใช้งาน</p>
                        <h3 class="fw-bold mb-0">12,540</h3>
                    </div>
                    <!-- ไอคอนพร้อมพื้นหลังสีฟ้าจางๆ -->
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success fw-bold"><i class="bi bi-arrow-up-short"></i> 5.2%</span>
                    <span class="text-muted" style="font-size: 0.85rem;"> จากเดือนที่แล้ว</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: รายได้รวม -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" data-aos="fade-up">
            <div class="card-body" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-6">รายได้รวม (ล้านบาท)</p>
                        <h3 id="kpiProjects" class="fw-bold mb-0 text-dark">0</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cash-stack text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success fw-bold"><i class="bi bi-arrow-up-short"></i> 12.5%</span>
                    <span class="text-muted" style="font-size: 0.85rem;"> จากเดือนที่แล้ว</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: โครงการลงทุน -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" data-aos="fade-up">
            <div class="card-body" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-6">โครงการลงทุน (EWC)</p>
                        <h3 id="kpiBudget" class="fw-bold mb-0 text-dark">0.00</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-building text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-danger fw-bold"><i class="bi bi-arrow-down-short"></i> 1.2%</span>
                    <span class="text-muted" style="font-size: 0.85rem;"> จากเดือนที่แล้ว</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: อัตราการเข้าชม -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100" data-aos="fade-up">
            <div class="card-body" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-6">การเข้าชมระบบ</p>
                        <h3 class="fw-bold mb-0">84,302</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="bi bi-graph-up text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success fw-bold"><i class="bi bi-arrow-up-short"></i> 8.4%</span>
                    <span class="text-muted" style="font-size: 0.85rem;"> จากเดือนที่แล้ว</span>
                </div>
            </div>
        </div>
    </div>

</div>
                    <!-- ย้าย Card ทั้ง 2 ใบ เข้ามาอยู่ใน Row นี้แล้วครับ -->
                    <div class="row" id="dashboard-content">
                        
                        <div class="card p-3 shadow-sm border-0 rounded-4 mt-4" data-aos="fade-up">
                         <div class="card-header bg-transparent border-0">
                        <h5 class="fw-bold mb-0">แนวโน้มมูลค่าการลงทุน (รายเดือน)</h5>
                        </div>
                        <div class="card-body">
                        <!-- จุดที่จะให้กราฟมาแสดง -->
                        <div id="investmentChart" style="min-height: 350px;"></div>
                        </div>
                        </div>
                        
<!-- เริ่มต้น: ส่วนตารางข้อมูล (Data Table) -->
<!-- ========================================== -->
<div class="row mt-2">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            
            <!-- ส่วนหัวของตาราง -->
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">โครงการลงทุนล่าสุด (Recent Projects)</h5>
                <a href="#" class="btn btn-sm btn-primary px-3 rounded-pill">ดูทั้งหมด</a>
            </div>

            <!-- ส่วนตัวตาราง -->
            <div class="card-body p-0">
                <!-- table-responsive ช่วยให้เลื่อนซ้ายขวาได้บนจอมือถือ -->
                <div class="table-responsive">
                    <table id="myProjectTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col" class="ps-4">รหัสโครงการ</th>
                                <th scope="col">ชื่อโครงการ / บริษัท</th>
                                <th scope="col">พื้นที่ (Zone)</th>
                                <th scope="col">งบประมาณ</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col" class="text-end pe-4">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- แถวที่ 1 -->
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">#EWC-2026-001</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            EV
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">โรงงานผลิตแบตเตอรี่ EV</h6>
                                            <small class="text-muted">บจก. พลังงานสะอาด ไทยแลนด์</small>
                                        </div>
                                    </div>
                                </td>
                                <td>ระยอง</td>
                                <td class="fw-semibold">1,200 ลบ.</td>
                                <td><span class="badge bg-success rounded-pill px-3">ดำเนินการแล้ว</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            
                            <!-- แถวที่ 2 -->
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">#EWC-2026-002</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            5G
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">ศูนย์ข้อมูลอัจฉริยะ (Data Center)</h6>
                                            <small class="text-muted">บมจ. คลาวด์ เทคโนโลยี</small>
                                        </div>
                                    </div>
                                </td>
                                <td>ชลบุรี</td>
                                <td class="fw-semibold">850 ลบ.</td>
                                <td><span class="badge bg-warning text-dark rounded-pill px-3">รออนุมัติ</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            <!-- แถวที่ 3 -->
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">#EWC-2026-003</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            Log
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">ระบบโลจิสติกส์ท่าเรือ</h6>
                                            <small class="text-muted">บจก. มารีน ทรานสปอร์ต</small>
                                        </div>
                                    </div>
                                </td>
                                <td>ฉะเชิงเทรา</td>
                                <td class="fw-semibold">430 ลบ.</td>
                                <td><span class="badge bg-secondary rounded-pill px-3">ระงับชั่วคราว</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div> <!-- /table-responsive -->
            </div>
        </div>
    </div>
</div>
<!-- ========================================== -->
<!-- สิ้นสุด: ส่วนตารางข้อมูล (Data Table) -->
<!-- ========================================== -->
                        <div class="card p-3 shadow-sm border-0 rounded-4" data-aos="fade-up">
                        <div class="card-header bg-transparent border-0" data-aos="fade-up">
                        <h5 class="fw-bold mb-0">สัดส่วนผู้ใช้งาน (Doughnut)</h5>
                        </div>
                        <div class="card-body text-center" data-aos="fade-up">
                        <!-- ต้องมีแท็กนี้อยู่เพื่อวาดกราฟ -->
                        <canvas id="myDoughnutChart" style="max-height: 250px;"></canvas>
                        </div>
                    </div>

<!-- กล่องแผนที่ EEC -->
<div class="card p-3 shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-transparent border-0">
        <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i> แผนที่แสดงที่ตั้งโครงการลงทุน (EWC Map)</h5>
    </div>
    <div class="card-body p-0 rounded-3 overflow-hidden border">
        <!-- กล่องสำหรับแสดงแผนที่ (ต้องกำหนด id และความสูงเสมอ) -->
        <div id="eecMap" style="height: 400px; width: 100%; z-index: 1;"></div>
    </div>
</div>
                </div>
            </main>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxDoughnut = document.getElementById('myDoughnutChart').getContext('2d');
    let myDoughnutChart = new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['ผู้ดูแลระบบ', 'ผู้ใช้งานทั่วไป', 'ผู้บริหาร'],
            datasets: [{
                data: [15, 60, 25], // ใส่ข้อมูลจำลองสัดส่วนผู้ใช้งานไปก่อน
                backgroundColor: ['#0284c7', '#38bdf8', '#bae6fd'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // ==========================================
    // 3. ฟังก์ชันอัปเดต Dashboard ด้วย AJAX
    // ==========================================
    function updateDashboard() {
        const year = document.getElementById('yearFilter').value;
        
        //fetch(`get_dashboard_data.php?year=${year}`)
            //.then(response => response.json())
            //.then(data => {
                // อัปเดตตัวเลข KPI
                //document.getElementById('kpiProjects').innerText = data.total_projects;
                //.getElementById('kpiBudget').innerText = data.total_budget;
                
                // อัปเดตกราฟแท่ง
                //myBarChart.data.labels = data.chart_labels;
                //myBarChart.data.datasets[0].data = data.chart_values;
               // myBarChart.update();

                // อัปเดตกราฟโดนัท (เพิ่มส่วนนี้เข้าไป)
               // if (typeof myDoughnutChart !== 'undefined') {
                    //myDoughnutChart.data.labels = data.doughnut_labels;
                    //myDoughnutChart.data.datasets[0].data = data.doughnut_values;
                    
                    // ปรับสีให้เข้ากับสถานะ (เขียว=ดำเนินการแล้ว, เหลือง=รออนุมัติ, แดง=ระงับ)
                    //const bgColors = data.doughnut_labels.map(label => {
                        //if (label === 'ดำเนินการแล้ว') return '#10b981'; // สีเขียว
                        //if (label === 'รออนุมัติ') return '#f59e0b'; // สีเหลือง
                        //if (label === 'ระงับชั่วคราว') return '#ef4444'; // สีแดง
                        //return '#0284c7'; // สีฟ้าเริ่มต้น
                    //});
                    //.data.datasets[0].backgroundColor = bgColors;
                    //myDoughnutChart.update();
                //}
            //})
            //.catch(error => console.error('Error:', error));
    //}

    // ผูก Event เวลามีการเปลี่ยน Dropdown
    document.getElementById('yearFilter').addEventListener('change', updateDashboard);

    // สั่งโหลดข้อมูลครั้งแรกตอนเปิดหน้าเว็บ
    updateDashboard();
</script>   
    
    <!-- Script หลักของหน้าเว็บ (จัดกลุ่มไว้ด้วยกัน) -->
    <script>
        // ฟังก์ชันสำหรับเปิด/ปิด Sidebar ในโหมดมือถือ
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // วาดกราฟที่ 1: Bar Chart
        const ctxBar = document.getElementById('myBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['ปี 2022', 'ปี 2023', 'ปี 2024', 'ปี 2025', 'ปี 2026'],
                datasets: [{
                    label: 'มูลค่าการลงทุน (พันล้านบาท)',
                    data: [350, 420, 510, 680, 750],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
    <!-- jQuery (DataTables ต้องการ jQuery ในการทำงาน) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables JS Core -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <!-- DataTables Bootstrap 5 Integration -->
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // เมื่อหน้าเว็บโหลดเสร็จ ให้เรียกใช้งาน DataTables
        $(document).ready(function() {
            $('#myProjectTable').DataTable({
                "pageLength": 5, // กำหนดให้แสดงหน้าละ 5 แถว (ค่าเริ่มต้นคือ 10)
                "language": {
                    // เปลี่ยนข้อความเป็นภาษาไทย (ถ้าต้องการ)
                    "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                    "zeroRecords": "ไม่พบข้อมูลที่ค้นหา",
                    "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "search": "ค้นหา:",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                }
            });
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<!-- ต้องโหลด JS ของ Leaflet ก่อน Chart.js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ... (โค้ดกราฟแท่งและกราฟโดนัทเดิม ปล่อยไว้ที่เดิมครับ) ...

    // --- เตรียมแผนที่ Leaflet ---
    // เซ็ตพิกัดศูนย์กลางไปที่ภาคตะวันออก (ซูมระดับ 9)
    let map = L.map('eecMap').setView([13.15, 101.15], 9); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let markersLayer = L.layerGroup().addTo(map); // เลเยอร์สำหรับจัดการหมุด

    // --- อัปเดตฟังก์ชันดึงข้อมูล ---
    function updateDashboard() {
        const year = document.getElementById('yearFilter').value;
        
        fetch(`get_dashboard_data.php?year=${year}`)
            .then(response => response.json())
            .then(data => {
                // ... (โค้ดอัปเดต KPI, กราฟแท่ง, กราฟโดนัทเดิม) ...

                // --- อัปเดตแผนที่ ---
                markersLayer.clearLayers(); // ล้างหมุดเก่าออกก่อน
                
                data.map_data.forEach(item => {
                    // กำหนดสีหมุดตามสถานะ (ทำเป็นวงกลมแบบมีรัศมีจะดูพรีเมียมกว่าหมุดปกติ)
                    let markerColor = '#0284c7';
                    if (item.status === 'ดำเนินการแล้ว') markerColor = '#10b981';
                    else if (item.status === 'รออนุมัติ') markerColor = '#f59e0b';
                    else if (item.status === 'ระงับชั่วคราว') markerColor = '#ef4444';

                    // สร้างหมุดวงกลม
                    let circleMarker = L.circleMarker([item.lat, item.lng], {
                        color: markerColor,
                        fillColor: markerColor,
                        fillOpacity: 0.7,
                        radius: 8
                    });

                    // ใส่ Pop-up โชว์ข้อมูลเวลากดคลิกที่หมุด
                    let popupContent = `
                        <div class="p-1" style="font-family: 'Kanit', sans-serif;">
                            <strong class="d-block mb-1">${item.project_name}</strong>
                            <span class="text-muted small">รหัส: ${item.project_code}</span><br>
                            <span class="text-muted small">งบประมาณ: ${parseFloat(item.budget).toLocaleString()} ลบ.</span><br>
                            <span class="badge mt-2" style="background-color: ${markerColor};">${item.status}</span>
                        </div>
                    `;
                    circleMarker.bindPopup(popupContent).addTo(markersLayer);
                });
            })
            .catch(error => console.error('Error:', error));
    }
    
    // ... (ส่วน EventListener และการเรียกใช้ครั้งแรก ปล่อยไว้เหมือนเดิม) ...
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
        // สั่งให้ Loader ค่อยๆ จางหายไปเมื่อเว็บโหลดส่วนประกอบครบ
        window.addEventListener('load', function() {
            document.getElementById('page-loader').classList.add('hidden');
        });
    </script>
    <script>
        // ตั้งค่ากราฟ ApexCharts
        var chartOptions = {
            series: [{
                name: 'มูลค่าการลงทุน (ล้านบาท)',
                data: [1200, 1500, 850, 2200, 3100, 2800, 4500] // ใส่ข้อมูลจำลองไปก่อน
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            colors: ['#38bdf8'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.'],
                tooltip: { enabled: false }
            },
            // เช็กโหมดมืดอัตโนมัติ
            theme: {
                mode: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'
            }
        };

        var investmentChart = new ApexCharts(document.querySelector("#investmentChart"), chartOptions);
        investmentChart.render();
    </script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init({
        duration: 800, // ความเร็วของแอนิเมชัน (มิลลิวินาที)
        once: true, // ให้แสดงแอนิเมชันแค่รอบเดียวตอนโหลดขึ้นมา
        offset: 100 // ระยะเลื่อนหน้าจอก่อนที่แอนิเมชันจะทำงาน
    });
</script>
<script>
    // สั่งให้ทำงานหลังจากโหลดหน้าเว็บเสร็จ
    window.addEventListener('load', function() {
        // หน่วงเวลา 1.5 วินาที เพื่อโชว์แอนิเมชัน Skeleton ให้ผู้ใช้เห็น (ถ้าดึงฐานข้อมูลจริงหนักๆ จะพอดีเลย)
        setTimeout(() => {
            // ซ่อน Skeleton
            document.getElementById('skeleton-stats-1').classList.add('d-none');
            
            // แสดงข้อมูลจริง
            let realContent = document.getElementById('real-stats-1');
            realContent.classList.remove('d-none');
            
            // เพิ่มลูกเล่นให้ข้อมูลจริงค่อยๆ ชัดขึ้นมา (Fade-in แบบเบาๆ)
            realContent.style.opacity = 0;
            setTimeout(() => {
                realContent.style.transition = "opacity 0.5s ease-in";
                realContent.style.opacity = 1;
            }, 50);
            
        }, 1500); 
    });
</script>
<script src="[https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js](https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js)"></script>
<script>
    // 1. สร้างแผนที่และตั้งพิกัดศูนย์กลาง (ละติจูด, ลองจิจูด) โซมระดับ 9
    var map = L.map('eecMap').setView([13.2000, 101.2000], 9);

    // 2. ดึงภาพแผนที่ของ OpenStreetMap มาแสดง
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 3. ข้อมูลพิกัดโครงการจำลอง (อนาคตสามารถใช้ PHP ดึงจาก API มาวนลูปตรงนี้ได้)
    var projects = [
        { name: "ศูนย์ข้อมูลอัจฉริยะ (Data Center)", zone: "ชลบุรี", lat: 13.3611, lng: 100.9846, budget: "850" },
        { name: "โรงงานผลิตแบตเตอรี่ EV", zone: "ระยอง", lat: 12.6814, lng: 101.2816, budget: "1,200" },
        { name: "ระบบโลจิสติกส์ท่าเรือ", zone: "ฉะเชิงเทรา", lat: 13.6904, lng: 101.0719, budget: "430" }
    ];

    // 4. วนลูปปักหมุดและสร้าง Pop-up
    projects.forEach(function(project) {
        var marker = L.marker([project.lat, project.lng]).addTo(map);
        
        var popupContent = `
            <div style="font-family: 'Prompt', sans-serif;">
                <h6 style="margin: 0 0 5px 0; font-weight: bold; color: #0284c7;">${project.name}</h6>
                <span style="font-size: 13px;"><b>พื้นที่:</b> ${project.zone}</span><br>
                <span style="font-size: 13px;"><b>งบประมาณ:</b> ${project.budget} ล้านบาท</span>
            </div>
        `;
        marker.bindPopup(popupContent);
    });
</script>
</body>
</html>