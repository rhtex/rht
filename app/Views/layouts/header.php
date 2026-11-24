<nav class="app-header navbar navbar-expand bg-body-secondary shadow-sm" data-bs-theme="dark">
    <div class="container-fluid">
        <!-- Start Navbar Links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="<?= site_url('dashboard') ?>" class="nav-link"><?= lang('App.home') ?></a>
            </li>
        </ul>
        <!-- End Navbar Links -->

        <!-- Start Navbar Links -->
        <ul class="navbar-nav ms-auto">
            <!-- User Menu Dropdown -->

                <!-- Language Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="fas fa-globe me-1"></i>
                        <span class="d-none d-md-inline"><?= session('lang') == 'ta' ? 'தமிழ்' : 'English' ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
                        <a href="<?= site_url('lang/en') ?>" class="dropdown-item <?= session('lang') == 'en' ? 'active' : '' ?>">English</a>
                        <a href="<?= site_url('lang/ta') ?>" class="dropdown-item <?= session('lang') == 'ta' ? 'active' : '' ?>">தமிழ் (Tamil)</a>
                    </div>
                </li>

                <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('name')) ?>&background=random" class="user-image rounded-circle shadow" alt="User Image">
                    <span class="d-none d-md-inline"><?= session()->get('name') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end user-account-dropdown" data-bs-theme="light">
                    <!-- User image -->
                    <li class="user-header text-white text-center py-3 px-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('name')) ?>&background=1e293b&color=ffffff&bold=true" class="rounded-circle shadow-sm mb-2 user-popup-avatar" alt="User Image">
                        <p class="mb-0 fw-bold fs-6 text-white">
                            <?= esc(session()->get('name')) ?>
                        </p>
                        <span class="badge bg-light text-primary fw-semibold mt-1 px-3 py-1">
                            <?= esc(ucfirst((string) session()->get('role'))) ?>
                        </span>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer d-flex justify-content-between align-items-center gap-2 px-3 py-2">
                        <a href="<?= site_url('profile/change_password') ?>" class="btn btn-sm btn-primary fw-semibold flex-fill text-center">
                            <i class="fas fa-user-cog me-1"></i><?= lang('App.profile') ?>
                        </a>
                        <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-danger fw-semibold flex-fill text-center">
                            <i class="fas fa-sign-out-alt me-1"></i><?= lang('App.sign_out') ?>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- End Navbar Links -->
    </div>
</nav>
