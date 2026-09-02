<header class="navbar navbar-light sticky-top flex-md-nowrap p-0 glass-header header-custom">
    <button class="navbar-toggler d-md-none ms-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="เปิดเมนู">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 fw-bold text-truncate" href="index.php">
        <i class="bi bi-recycle me-2"></i><span class="d-none d-sm-inline">ระบบคัดแยกขยะชุมชน</span><span class="d-inline d-sm-none">คัดแยกขยะ</span>
    </a>
    
    <div class="navbar-nav ms-auto px-2 px-md-3 d-flex flex-row align-items-center">
        <div class="nav-item text-nowrap me-2 me-md-3 small d-none d-sm-block">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        <div class="nav-item text-nowrap">
            <a class="nav-link px-2 px-md-3 btn btn-sm btn-logout" href="logout.php" title="ออกจากระบบ">
                <i class="bi bi-box-arrow-right"></i><span class="d-none d-md-inline ms-1">ออกจากระบบ</span>
            </a>
        </div>
    </div>
</header>
