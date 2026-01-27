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
                        <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                        <p><?= lang('App.dashboard') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= site_url('notifications') ?>" class="nav-link">
                        <i class="nav-icon fas fa-bell text-warning"></i>
                        <p>
                            Notifications
                            <?php 
                                $notifModel = new \App\Models\NotificationModel();
                                $unreadCount = count($notifModel->getUnread());
                                if($unreadCount > 0): 
                            ?>
                                <span class="badge bg-danger float-end"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </p>
                    </a>
                </li>

                <!-- SALES: Customers, Invoices, Receipts, Agents, Agent Payments -->
                <?php if(in_array('customer.view', session('permissions') ?? []) || in_array('agent.view', session('permissions') ?? []) || in_array('invoice.view', session('permissions') ?? []) || in_array('agent_payments.view', session('permissions') ?? [])): ?>
                <li class="nav-header">SALES</li>
                <?php if(in_array('customer.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('customers') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-friends text-success"></i>
                        <p>Customers</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('quotation.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('quotations') ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-alt text-info"></i>
                        <p>Quotations</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('sales_order.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('sales_orders') ?>" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart text-warning"></i>
                        <p>Sales Orders</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('invoice.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('invoices') ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice text-primary"></i>
                        <p>Invoices</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('invoice_payments') ?>" class="nav-link">
                        <i class="nav-icon fas fa-receipt text-success"></i>
                        <p>Receipts</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('agent.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('agents') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-secret text-warning"></i>
                        <p>Agents</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('agent_payments.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-usd text-success"></i>
                        <p>
                            Agent Payments
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('agent-payments') ?>" class="nav-link">
                                <i class="fas fa-list nav-icon text-info"></i>
                                <p>All Payments</p>
                            </a>
                        </li>
                        <?php if(in_array('agent_payments.create', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('agent-payments/create') ?>" class="nav-link">
                                <i class="fas fa-plus nav-icon text-success"></i>
                                <p>Record Payment</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('agent_payments.reports', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('agent-payments/reports') ?>" class="nav-link">
                                <i class="fas fa-chart-line nav-icon text-warning"></i>
                                <p>Waiting Payment Report</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <!-- PURCHASES: Vendors, Expenses -->
                <?php if(in_array('vendor.view', session('permissions') ?? []) || in_array('expense.view', session('permissions') ?? []) || in_array('bill.view', session('permissions') ?? [])): ?>
                <li class="nav-header">PURCHASES</li>
                <?php if(in_array('vendor.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('vendors') ?>" class="nav-link">
                        <i class="nav-icon fas fa-store text-info"></i>
                        <p>Vendors</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('vendors/returns') ?>" class="nav-link">
                        <i class="nav-icon fas fa-undo-alt text-danger"></i>
                        <p>Pending Returns</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('vendors/returns/create') ?>" class="nav-link">
                        <i class="nav-icon fas fa-shipping-fast text-warning"></i>
                        <p>New Return Shipment</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if(in_array('bill.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('bills') ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice-dollar text-primary"></i>
                        <p>Bills</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('payments') ?>" class="nav-link">
                        <i class="nav-icon fas fa-money-check-alt text-success"></i>
                        <p>Payments</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('expense.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('expenses') ?>" class="nav-link">
                        <i class="nav-icon fas fa-receipt text-danger"></i>
                        <p>Expenses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('expense_categories') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tags text-warning"></i>
                        <p>Expense Categories</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <!-- INVENTORY: Products, Categories -->
                <?php if(in_array('product.view', session('permissions') ?? []) || in_array('product_category.view', session('permissions') ?? [])): ?>
                <li class="nav-header">INVENTORY</li>
                <?php if(in_array('product.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('products') ?>" class="nav-link">
                        <i class="nav-icon fas fa-boxes text-info"></i>
                        <p>Products</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('product.edit', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('products/approvals') ?>" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-check text-warning"></i>
                        <p>Inventory Approvals</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('product_category.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('product_categories') ?>" class="nav-link">
                        <i class="nav-icon fas fa-list-alt text-success"></i>
                        <p>Categories</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <!-- PRODUCTION: Weavers -->
                <?php if(in_array('weaver.view', session('permissions') ?? [])): ?>
                <li class="nav-header">PRODUCTION</li>
                <li class="nav-item">
                    <a href="<?= site_url('production/weavers') ?>" class="nav-link">
                        <i class="nav-icon fas fa-industry text-warning"></i>
                        <p>Weavers</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- OPERATIONS: Transports -->
                <?php if(in_array('transport.view', session('permissions') ?? [])): ?>
                <li class="nav-header">OPERATIONS</li>
                <li class="nav-item">
                    <a href="<?= site_url('transports') ?>" class="nav-link">
                        <i class="nav-icon fas fa-truck text-danger"></i>
                        <p>Transports</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- FINANCE: Bank Accounts -->
                <?php if(in_array('bank_account.view', session('permissions') ?? [])): ?>
                <li class="nav-header">FINANCE</li>
                <li class="nav-item">
                    <a href="<?= site_url('bank_accounts') ?>" class="nav-link">
                        <i class="nav-icon fas fa-university text-primary"></i>
                        <p>Bank Accounts</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- HR MANAGEMENT: Employees, Attendance, Loans, Salaries -->
                <?php if(in_array('employee.view', session('permissions') ?? []) || in_array('attendance.view', session('permissions') ?? []) || in_array('loan.view', session('permissions') ?? []) || in_array('salary.view', session('permissions') ?? [])): ?>
                <li class="nav-header">HR MANAGEMENT</li>
                <?php if(in_array('employee.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('employees') ?>" class="nav-link">
                        <i class="nav-icon fas fa-user-tie text-primary"></i>
                        <p><?= lang('App.employees') ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('attendance.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-calendar-check text-success"></i>
                        <p>
                            <?= lang('App.attendance') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('attendance') ?>" class="nav-link">
                                <i class="fas fa-calendar-day nav-icon text-info"></i>
                                <p><?= lang('App.daily_entry') ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('attendance/report') ?>" class="nav-link">
                                <i class="fas fa-calendar-alt nav-icon text-primary"></i>
                                <p><?= lang('App.monthly_report') ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('attendance/shortfall') ?>" class="nav-link">
                                <i class="fas fa-calendar-minus nav-icon text-danger"></i>
                                <p><?= lang('App.shortfall_report') ?></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if(in_array('loan.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('loans') ?>" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-usd text-warning"></i>
                        <p><?= lang('App.loans') ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(in_array('salary.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="<?= site_url('salaries') ?>" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice-dollar text-success"></i>
                        <p><?= lang('App.salary') ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <!-- ADMINISTRATION: User Management, Settings -->
                <?php if(in_array('user.view', session('permissions') ?? []) || in_array('role.view', session('permissions') ?? []) || in_array('setting.view', session('permissions') ?? []) || in_array('module.view', session('permissions') ?? []) || in_array('permission.view', session('permissions') ?? [])): ?>
                <li class="nav-header"><?= lang('App.admin') ?></li>
                
                <?php if(in_array('user.view', session('permissions') ?? []) || in_array('role.view', session('permissions') ?? []) || in_array('module.view', session('permissions') ?? []) || in_array('permission.view', session('permissions') ?? [])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users-cog text-warning"></i>
                        <p>
                            <?= lang('App.user_management') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('user.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('users') ?>" class="nav-link">
                                <i class="fas fa-users nav-icon text-primary"></i>
                                <p><?= lang('App.users') ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('role.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('roles') ?>" class="nav-link">
                                <i class="fas fa-user-shield nav-icon text-danger"></i>
                                <p><?= lang('App.roles') ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('module.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('modules') ?>" class="nav-link">
                                <i class="fas fa-cubes nav-icon text-success"></i>
                                <p>Modules</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(in_array('permission.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('permissions') ?>" class="nav-link">
                                <i class="fas fa-key nav-icon text-warning"></i>
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
                        <i class="nav-icon fas fa-cogs text-secondary"></i>
                        <p>
                            <?= lang('App.settings') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('setting.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('settings') ?>" class="nav-link">
                                <i class="fas fa-tools nav-icon text-info"></i>
                                <p>App Settings</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('country.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('countries') ?>" class="nav-link">
                                <i class="fas fa-globe nav-icon text-primary"></i>
                                <p>Countries</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('state.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('states') ?>" class="nav-link">
                                <i class="fas fa-map nav-icon text-success"></i>
                                <p>States</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('zoho.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('zoho-settings') ?>" class="nav-link">
                                <i class="fas fa-sync nav-icon text-warning"></i>
                                <p>Zoho Integration</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('tax.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('settings/taxes') ?>" class="nav-link">
                                <i class="fas fa-percent nav-icon text-success"></i>
                                <p>Taxes</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
                
        </nav>
    </div>
</aside>
