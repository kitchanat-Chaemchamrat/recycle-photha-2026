<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block glass-sidebar sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <?php if (hasRole(['super_admin', 'admin', 'auditor'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasRole(['super_admin', 'admin'])): ?>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                <span>จัดการข้อมูลหลัก</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'soi' ? 'active' : ''; ?>" href="index.php?page=soi">
                    <i class="bi bi-map me-2"></i> จัดการซอย
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'house' ? 'active' : ''; ?>" href="index.php?page=house">
                    <i class="bi bi-house-door me-2"></i> จัดการบ้าน / QR Code
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'house_card' ? 'active' : ''; ?>" href="index.php?page=house_card">
                    <i class="bi bi-credit-card me-2"></i> สร้างบัตรประจำบ้าน
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'member' ? 'active' : ''; ?>" href="index.php?page=member">
                    <i class="bi bi-people me-2"></i> จัดการสมาชิก
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin', 'collector'])): ?>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                <span>ระบบปฏิบัติการ</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'evaluation' ? 'active' : ''; ?>" href="index.php?page=evaluation">
                    <i class="bi bi-clipboard-check me-2"></i> ประเมินการแยกขยะ
                </a>
            </li>
            <?php if (hasRole(['super_admin', 'admin'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'evaluation_approval' ? 'active' : ''; ?>" href="index.php?page=evaluation_approval">
                    <i class="bi bi-patch-check me-2"></i> อนุมัติผลประเมิน
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'recycle' ? 'active' : ''; ?>" href="index.php?page=recycle">
                    <i class="bi bi-inboxes me-2"></i> บันทึกน้ำหนักขยะ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'qr_scan' ? 'active' : ''; ?>" href="index.php?page=qr_scan">
                    <i class="bi bi-qr-code-scan me-2"></i> สแกน QR ด้วยกล้อง
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasRole(['super_admin', 'admin'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'sales' ? 'active' : ''; ?>" href="index.php?page=sales">
                    <i class="bi bi-currency-dollar me-2"></i> ขายขยะรีไซเคิล
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'dividend' ? 'active' : ''; ?>" href="index.php?page=dividend">
                    <i class="bi bi-cash-coin me-2"></i> คำนวณเงินปันผล
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin', 'auditor'])): ?>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                <span>รายงาน</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'report' ? 'active' : ''; ?>" href="index.php?page=report">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> รายงานภาพรวม
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
