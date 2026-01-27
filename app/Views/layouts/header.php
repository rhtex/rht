<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Start Navbar Links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="<?= site_url('dashboard') ?>" class="nav-link">Home</a>
            </li>
        </ul>
        <!-- End Navbar Links -->

        <!-- Start Navbar Links -->
        <ul class="navbar-nav ms-auto">
            <!-- User Menu Dropdown -->
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="<?= site_url('notifications') ?>">
                        <i class="fas fa-bell"></i>
                        <?php 
                            $notifModel = new \App\Models\NotificationModel();
                            $unreadCount = count($notifModel->getUnread());
                            if($unreadCount > 0): 
                        ?>
                            <span class="badge bg-danger rounded-pill" style="font-size: 10px; position: absolute; top: 5px; right: 5px;"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Language Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="fas fa-globe"></i>
                        <span class="d-none d-md-inline"><?= session('lang') == 'ta' ? 'தமிழ்' : 'English' ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="<?= site_url('lang/en') ?>" class="dropdown-item <?= session('lang') == 'en' ? 'active' : '' ?>">English</a>
                        <a href="<?= site_url('lang/ta') ?>" class="dropdown-item <?= session('lang') == 'ta' ? 'active' : '' ?>">தமிழ் (Tamil)</a>
                    </div>
                </li>

                <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('name')) ?>&background=random" class="user-image rounded-circle shadow" alt="User Image">
                    <span class="d-none d-md-inline"><?= session()->get('name') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!-- User image -->
                    <li class="user-header text-bg-primary">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('name')) ?>&background=random" class="rounded-circle shadow" alt="User Image">
                        <p>
                            <?= session()->get('name') ?>
                            <small><?= session()->get('role') ?></small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <a href="<?= site_url('profile/change_password') ?>" class="btn btn-default btn-flat"><?= lang('App.profile') ?></a>
                        <a href="<?= site_url('logout') ?>" class="btn btn-default btn-flat float-end"><?= lang('App.sign_out') ?></a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- End Navbar Links -->
    </div>
</nav>
