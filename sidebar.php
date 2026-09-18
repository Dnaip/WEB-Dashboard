<div class="sidebar d-flex flex-column p-3 text-white" style="width: 260px; min-height: 100vh;">
    <!-- ส่วนหัว โลโก้ -->
    <a href="index.php" class="d-flex align-items-center mb-4 mt-2 text-white text-decoration-none px-2">
        <i class="bi bi-layers-fill fs-3 me-2" style="color: #38bdf8;"></i>
        <span class="fs-4 fw-bold brand-logo-text">EWC</span>
    </a>
    
    <!-- เส้นคั่น -->
    <hr class="mb-4" style="border-color: rgba(255,255,255,0.1);">
    
    <!-- รายการเมนู (ul ต้องมี list-unstyled เพื่อซ่อนจุดวงกลม) -->
    <ul class="nav nav-pills flex-column mb-auto list-unstyled w-100">
        
        <!-- เมนูสำหรับผู้ใช้งานทุกคน -->
        <li class="nav-item mb-1">
            <a href="index.php" class="nav-link text-white <?= (isset($page) && $page == 'home') ? 'active' : '' ?>">
                <i class="bi bi-house-door me-3 fs-5"></i> ภาพรวม (Home)
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="report.php" class="nav-link text-white <?= (isset($page) && $page == 'report') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line me-3 fs-5"></i> รายงาน (Report)
            </a>
        </li>

        <!-- เส้นคั่นบางๆ ก่อนเข้าเมนู Admin (ซ่อนถ้าไม่ใช่ Admin) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li class="mt-4 mb-2 px-3 text-uppercase text-muted small fw-bold" style="letter-spacing: 1px;">Admin Tools</li>
        
        <li class="nav-item mb-1">
            <a href="data.php" class="nav-link text-white <?= (isset($page) && $page == 'data') ? 'active' : '' ?>">
                <i class="bi bi-database me-3 fs-5"></i> จัดการข้อมูล (Data)
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="users.php" class="nav-link text-white <?= (isset($page) && $page == 'users') ? 'active' : '' ?>">
                <i class="bi bi-people me-3 fs-5"></i> จัดการผู้ใช้งาน (Users)
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="logs.php" class="nav-link text-white <?= (isset($page) && $page == 'logs') ? 'active' : '' ?>">
                <i class="bi bi-journal-text me-3 fs-5"></i> ประวัติระบบ (Logs)
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="settings.php" class="nav-link text-white <?= (isset($page) && $page == 'settings') ? 'active' : '' ?>">
                <i class="bi bi-gear me-3 fs-5"></i> ตั้งค่า (Settings)
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>