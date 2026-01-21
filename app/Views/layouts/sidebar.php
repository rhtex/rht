<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="<?= site_url('dashboard') ?>" class="brand-link">
            <!-- <img src="/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"> -->
            <span class="brand-text fw-light"><?= get_setting('app_name', 'RasiDev HR') ?></span>
        </a>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p><?= lang('App.dashboard') ?></p>
                    </a>
                </li>

                <?php if(in_array('user.view', session('permissions') ?? []) || in_array('role.view', session('permissions') ?? []) || in_array('setting.view', session('permissions') ?? []) || in_array('module.view', session('permissions') ?? []) || in_array('permission.view', session('permissions') ?? [])): ?>
                <li class="nav-header"><?= lang('App.admin') ?></li>
                
                <?php if(in_array('user.view', session('permissions') ?? []) || in_array('role.view', session('permissions') ?? []) || in_array('module.view', session('permissions') ?? []) || in_array('permission.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            <?= lang('App.user_management') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('user.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('users') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang('App.users') ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('role.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('roles') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang('App.roles') ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('module.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('modules') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Modules</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(in_array('permission.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('permissions') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Permissions</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if(in_array('setting.view', session('permissions') ?? []) || in_array('country.view', session('permissions') ?? []) || in_array('state.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            <?= lang('App.settings') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('setting.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('settings') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>App Settings</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('country.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('countries') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Countries</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('state.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('states') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>States</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('zoho.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('zoho-settings') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Zoho Integration</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <?php if(in_array('employee.view', session('permissions') ?? []) || in_array('attendance.view', session('permissions') ?? [])): ?>
                <li class="nav-header"><?= lang('App.hr') ?></li>
                <?php endif; ?>

                <?php if(in_array('employee.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('employees') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p><?= lang('App.employees') ?></p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('attendance.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>
                            <?= lang('App.attendance') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('attendance') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang('App.daily_entry') ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('attendance/report') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang('App.monthly_report') ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('attendance/shortfall') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang('App.shortfall_report') ?></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if(in_array('agent.view', session('permissions') ?? []) || in_array('transport.view', session('permissions') ?? [])): ?>
                <li class="nav-header">LOGISTICS</li>
                <?php endif; ?>
                
                <?php if(in_array('agent.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('agents') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-secret"></i>
                        <p>Agents</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if(in_array('transport.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('transports') ?>" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Transports</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('customer.view', session('permissions') ?? [])): ?>
                <li class="nav-header">SALES</li>
                <li class="nav-item">
                    <a href="<?= site_url('customers') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>Customers</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('vendor.view', session('permissions') ?? [])): ?>
                <li class="nav-header">PURCHASE</li>
                <li class="nav-item">
                    <a href="<?= site_url('vendors') ?>" class="nav-link">
                        <i class="nav-icon fas fa-store"></i>
                        <p>Vendors</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('loan.view', session('permissions') ?? []) || in_array('salary.view', session('permissions') ?? [])): ?>
                <li class="nav-header"><?= lang('App.payroll') ?></li>
                <?php endif; ?>

                <?php if(in_array('loan.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('loans') ?>" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p><?= lang('App.loans') ?></p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('salary.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('salaries') ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p><?= lang('App.salary') ?></p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('bank_account.view', session('permissions') ?? []) || in_array('expense.view', session('permissions') ?? [])): ?>
                <li class="nav-header">FINANCE</li>
                <?php endif; ?>

                <?php if(in_array('bank_account.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('bank_accounts') ?>" class="nav-link">
                        <i class="nav-icon fas fa-university"></i>
                        <p>Bank Accounts</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array('expense.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('expenses') ?>" class="nav-link">
                        <i class="nav-icon fas fa-receipt"></i>
                        <p>Expenses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('expense_categories') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Expense Categories</p>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
                
        </nav>
    </div>
</aside>
