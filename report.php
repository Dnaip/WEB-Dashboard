<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - My Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
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
        @media print {
            .sidebar, .navbar, .btn { display: none !important; }
            .main-panel { margin-left: 0 !important; width: 100% !important; background-color: #fff; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            /* ขยายกราฟให้เต็มหน้ากระดาษ */
            canvas { max-height: 400px; }
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
        
        /* โหมดมืด: เงาเวลา Hover ต้องใช้สีดำที่เข้มขึ้นเพื่อให้เห็นมิติ */
        [data-bs-theme="dark"] .card:hover {
            box-shadow: 0 12px 30px rgba(0,0,0,0.4) !important;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Sidebar -->
        <?php 
            $page = 'report'; // ประกาศบอกว่าหน้านี้คือหน้า report
            include 'sidebar.php'; 
        ?>

        <!-- Main Content Area -->
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

    <div class="ms-auto d-flex align-items-center">
    <!-- เพิ่มปุ่ม Toggle Dark Mode ตรงนี้ครับ -->
    <button id="themeToggle" class="btn btn-light rounded-circle me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-moon-stars"></i>
    </button>
    <!-- ========================= -->

    <!-- โค้ดโปรไฟล์เดิม -->
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
                    
                    <!-- ส่วนหัว & ปุ่ม Export -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h2 class="mb-0 fw-bold text-dark">รายงานสรุปผลการลงทุน</h2>
                        <div>
    <!-- ใช้คำสั่ง window.print() ของ JavaScript เพื่อเปิดหน้าต่างพิมพ์ -->
    <button onclick="window.print();" class="btn btn-outline-secondary rounded-pill px-3 me-2">
        <i class="bi bi-printer me-1"></i> พิมพ์ / PDF
    </button>
   
    <a href="export_excel.php" class="btn btn-success rounded-pill px-4 shadow-sm" target="_blank">
    <i class="bi bi-file-earmark-excel me-2"></i> ส่งออก Excel
    </a>
</div>
                    </div>
                    
                    <!-- กล่องสำหรับเลือกตัวกรองข้อมูล (Filter) -->
                    <div class="card border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label text-muted small">เลือกพื้นที่ (Zone)</label>
                                    <select id="filterZone" class="form-select px-3 py-2">
                                        <option value="all">ทั้งหมด (All Zones)</option>
                                        <option value="rayong">ระยอง</option>
                                        <option value="chonburi">ชลบุรี</option>
                                        <option value="chachoengsao">ฉะเชิงเทรา</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small">สถานะโครงการ</label>
                                    <select id="filterStatus" class="form-select px-3 py-2">
                                        <option value="all">ทั้งหมด (All Status)</option>
                                        <option value="approved">ดำเนินการแล้ว</option>
                                        <option value="pending">รออนุมัติ</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">ช่วงเวลา (ไตรมาส)</label>
                                    <select class="form-select px-3 py-2">
                                        <option value="q1">ไตรมาสที่ 1 (ม.ค. - มี.ค.)</option>
                                        <option value="q2">ไตรมาสที่ 2 (เม.ย. - มิ.ย.)</option>
                                        <option value="q3">ไตรมาสที่ 3 (ก.ค. - ก.ย.)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button id="btnFilter" class="btn btn-primary w-100 py-2">กรองข้อมูล</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- กราฟเส้นแสดงแนวโน้ม (Line Chart) -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card border-0 h-100">
                                <div class="card-header bg-white border-0 pt-4 pb-2">
                                    <h5 class="card-title fw-bold mb-0">แนวโน้มมูลค่าการลงทุน (รายไตรมาส)</h5>
                                </div>
                                <div class="card-body position-relative" style="min-height: 350px;">
                <!-- ตัว Spinner วงกลมหมุนๆ (ซ่อนไว้ก่อน) -->
                <div id="chartLoader" class="position-absolute top-50 start-50 translate-middle d-flex justify-content-center align-items-center" style="z-index: 10; display: none !important;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            </div>
                <!-- ตัวกราฟ (ใส่ opacity และ transition เพื่อให้มันค่อยๆ สว่างขึ้น) -->
                <canvas id="trendChart" style="min-height: 350px; opacity: 1; transition: opacity 0.4s ease;"></canvas>
            </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    // สคริปต์จัดการ Sidebar ของเดิม
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // 1. ประกาศตัวแปรกราฟเป็น Global ไว้ด้านนอก เพื่อให้ดึงไปอัปเดตค่าได้
    let trendChart;
    const ctxTrend = document.getElementById('trendChart').getContext('2d');

    // 2. สร้างโครงกราฟเปล่าๆ ขึ้นมาก่อน (ข้อมูลว่างไว้)
    trendChart = new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: [], 
            datasets: [{
                label: 'มูลค่าการลงทุน (ล้านบาท)',
                data: [],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 5,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 3. ฟังก์ชันดึงข้อมูลแบบ AJAX (ใช้ Fetch API)
    function loadChartData() {
        const zone = document.getElementById('filterZone').value;
        const status = document.getElementById('filterStatus').value;
        
        // ประกาศตัวแปรเรียก Loader และ Canvas
        const loader = document.getElementById('chartLoader');
        const canvas = document.getElementById('trendChart');

        // สเต็ป 1: แสดง Spinner และทำกราฟให้จางลง (Fade out)
        loader.style.setProperty('display', 'flex', 'important');
        canvas.style.opacity = '0.3';
        
        // ยิง request ไปขอข้อมูล
        fetch(`get_report_data.php?zone=${zone}&status=${status}`)
            .then(response => response.json())
            .then(data => {
                // อัปเดตข้อมูลกราฟ
                trendChart.data.labels = data.labels;
                trendChart.data.datasets[0].data = data.values;
                trendChart.update();
                
                // สเต็ป 2: หน่วงเวลาหลอกๆ 0.5 วินาทีให้เห็นแอนิเมชัน (เอาออกได้ถ้าอยากให้ไวสุดๆ)
                setTimeout(() => {
                    loader.style.setProperty('display', 'none', 'important'); // ซ่อน Spinner
                    canvas.style.opacity = '1'; // ทำกราฟให้สว่างกลับมา (Fade in)
                }, 500); 
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                loader.style.setProperty('display', 'none', 'important');
                canvas.style.opacity = '1';
            });
    }

    // 4. ผูกฟังก์ชันเข้ากับปุ่ม "กรองข้อมูล"
    document.getElementById('btnFilter').addEventListener('click', loadChartData);

    // 5. สั่งให้โหลดข้อมูลทั้งหมดขึ้นมาแสดงครั้งแรก ตอนเปิดหน้าเว็บ
    loadChartData();
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
</body>
</html>