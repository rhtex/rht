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
            <?php
                $uri = service('uri');
                $segment1 = $uri->getSegment(1);
                $segment2 = $uri->getSegment(2);
                
                $isSales = in_array($segment1, ['customers', 'quotations', 'sales_orders', 'invoices', 'sales_returns', 'invoice_payments', 'agents', 'agent-payments']);
                $isPurchases = in_array($segment1, ['vendors', 'bills', 'payments', 'expenses', 'expense_categories']);
                $isInventory = in_array($segment1, ['products', 'product_categories']);
                $isProduction = in_array($segment1, ['production', 'weavers']) || ($segment1 === 'production' && $segment2 === 'colors');
                $isOperations = in_array($segment1, ['transports']);
                $isFinance = in_array($segment1, ['bank_accounts', 'reconciliation']);
                $isHR = in_array($segment1, ['employees', 'attendance', 'loans', 'salaries']);
                $isCalendar = in_array($segment1, ['calendar']);
                $isAdmin = in_array($segment1, ['users', 'roles', 'modules', 'permissions', 'settings', 'countries', 'states']);
            ?>
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                        <p><?= lang('App.dashboard') ?></p>
                    </a>
                </li>


                <li class="nav-item">
                    <a href="<?= site_url('calendar') ?>" class="nav-link <?= $segment1 == 'calendar' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt text-success"></i>
                        <p><?= lang("App.calendar_tasks") ?></p>
                    </a>
                </li>

                <!-- SALES -->
                <?php if(in_array('customer.view', session('permissions') ?? []) || in_array('agent.view', session('permissions') ?? []) || in_array('invoice.view', session('permissions') ?? []) || in_array('agent_payments.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isSales ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isSales ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shopping-basket text-primary"></i>
                        <p>
                            <?= lang("App.sales") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('customer.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('customers') ?>" class="nav-link">
                                <i class="nav-icon fas fa-user-friends text-success"></i>
                                <p><?= lang("App.customers") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('quotation.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('quotations') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-alt text-info"></i>
                                <p><?= lang("App.quotations") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('sales_order.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('sales_orders') ?>" class="nav-link">
                                <i class="nav-icon fas fa-shopping-cart text-warning"></i>
                                <p><?= lang("App.sales_orders") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('invoice.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('invoices') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice text-primary"></i>
                                <p><?= lang("App.invoices") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('invoices/tracking') ?>" class="nav-link">
                                <i class="nav-icon fas fa-truck-loading text-info"></i>
                                <p><?= lang("App.invoice_tracking") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('invoices/doc-tracking') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-contract text-warning"></i>
                                <p><?= lang("App.invoice_doc_tracking") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('sales_returns') ?>" class="nav-link">
                                <i class="nav-icon fas fa-undo text-danger"></i>
                                <p><?= lang("App.sales_returns") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('invoice_payments') ?>" class="nav-link">
                                <i class="nav-icon fas fa-receipt text-success"></i>
                                <p><?= lang("App.receipts") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('agent.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('agents') ?>" class="nav-link">
                                <i class="nav-icon fas fa-user-secret text-warning"></i>
                                <p><?= lang("App.agents") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('agent_payments.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-hand-holding-usd text-success"></i>
                                <p>
                                    <?= lang("App.agent_payments") ?>
                                    <i class="nav-arrow fas fa-angle-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= site_url('agent-payments') ?>" class="nav-link">
                                        <i class="fas fa-list nav-icon text-info"></i>
                                        <p><?= lang("App.all_payments") ?></p>
                                    </a>
                                </li>
                                <?php if(in_array('agent_payments.create', session('permissions') ?? [])): ?>
                                <li class="nav-item">
                                    <a href="<?= site_url('agent-payments/create') ?>" class="nav-link">
                                        <i class="fas fa-plus nav-icon text-success"></i>
                                        <p><?= lang("App.record_payment") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array('agent_payments.reports', session('permissions') ?? [])): ?>
                                <li class="nav-item">
                                    <a href="<?= site_url('agent-payments/reports') ?>" class="nav-link">
                                        <i class="fas fa-chart-line nav-icon text-warning"></i>
                                        <p><?= lang("App.waiting_payment_report") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- PURCHASES -->
                <?php if(in_array('vendor.view', session('permissions') ?? []) || in_array('expense.view', session('permissions') ?? []) || in_array('bill.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isPurchases ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isPurchases ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shopping-bag text-warning"></i>
                        <p>
                            <?= lang("App.purchases") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('vendor.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('vendors') ?>" class="nav-link">
                                <i class="nav-icon fas fa-store text-info"></i>
                                <p><?= lang("App.vendors") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('vendors/returns') ?>" class="nav-link">
                                <i class="nav-icon fas fa-undo-alt text-danger"></i>
                                <p><?= lang("App.pending_returns") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('vendors/returns/create') ?>" class="nav-link">
                                <i class="nav-icon fas fa-shipping-fast text-warning"></i>
                                <p><?= lang("App.new_return_shipment") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('vendors/returns/shipments') ?>" class="nav-link">
                                <i class="nav-icon fas fa-truck-loading text-success"></i>
                                <p><?= lang("App.return_tracking") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if(in_array('bill.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('bills') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice-dollar text-primary"></i>
                                <p><?= lang("App.bills") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('payments') ?>" class="nav-link">
                                <i class="nav-icon fas fa-money-check-alt text-success"></i>
                                <p><?= lang("App.payments") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('expense.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('expenses') ?>" class="nav-link">
                                <i class="nav-icon fas fa-receipt text-danger"></i>
                                <p><?= lang("App.expenses") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('expense_categories') ?>" class="nav-link">
                                <i class="nav-icon fas fa-tags text-warning"></i>
                                <p><?= lang("App.expense_categories") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- INVENTORY -->
                <?php if(in_array('product.view', session('permissions') ?? []) || in_array('product_category.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isInventory ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isInventory ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-boxes text-info"></i>
                        <p>
                            <?= lang("App.inventory") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(in_array('product.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('products') ?>" class="nav-link">
                                <i class="nav-icon fas fa-boxes text-info"></i>
                                <p><?= lang("App.products") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('product.edit', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('products/approvals') ?>" class="nav-link">
                                <i class="nav-icon fas fa-clipboard-check text-warning"></i>
                                <p><?= lang("App.inventory_approvals") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array('product_category.view', session('permissions') ?? [])): ?>
                        <li class="nav-item">
                            <a href="<?= site_url('product_categories') ?>" class="nav-link">
                                <i class="nav-icon fas fa-list-alt text-success"></i>
                                <p><?= lang("App.categories") ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- PRODUCTION -->
                <?php if(in_array('weaver.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isProduction ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isProduction ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-industry text-warning"></i>
                        <p>
                            <?= lang("App.production") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('production/weavers') ?>" class="nav-link">
                                <i class="nav-icon fas fa-industry text-warning"></i>
                                <p><?= lang("App.weavers") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/vendors') ?>" class="nav-link">
                                <i class="nav-icon fas fa-truck-loading text-success"></i>
                                <p><?= lang("App.job_work_vendors") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/agreements') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-signature text-warning"></i>
                                <p><?= lang("App.agreements") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/colors') ?>" class="nav-link <?= $segment2 === 'colors' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-palette text-primary"></i>
                                <p><?= lang("App.colors") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/yarn-purchases') ?>" class="nav-link">
                                <i class="nav-icon fas fa-shopping-cart text-info"></i>
                                <p><?= lang("App.yarn_purchases") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/yarn-inventory') ?>" class="nav-link">
                                <i class="nav-icon fas fa-warehouse text-info"></i>
                                <p><?= lang("App.yarn_inventory") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/yarn-beams') ?>" class="nav-link <?= strpos(current_url(), 'production/yarn-beams') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-ring text-info"></i>
                                <p><?= lang("App.beam_tracker") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/warp-allocations') ?>" class="nav-link <?= strpos(uri_string(), 'production/warp-allocations') !== false ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= lang("App.warp_weft_allocations") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/receipts') ?>" class="nav-link <?= strpos(current_url(), 'production/receipts') !== false ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-boxes text-success"></i>
                                <p><?= lang("App.production_receipts") ?></p>
                            </a>
                        </li>
                        <?php
                            $isYarnJobWorkActive = in_array($segment2, ['yarn-job-work', 'yarn-dyeing', 'yarn-warping-sizing', 'yarn-twisting', 'yarn-weaving']);
                        ?>
                        <li class="nav-item <?= $isYarnJobWorkActive ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $isYarnJobWorkActive ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tasks text-info"></i>
                                <p>
                                    <?= lang("App.yarn_job_work") ?>
                                    <i class="nav-arrow fas fa-angle-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="padding-left: 15px;">
                                <li class="nav-item">
                                    <a href="<?= site_url('production/yarn-dyeing') ?>" class="nav-link <?= $segment2 === 'yarn-dyeing' ? 'active' : '' ?>">
                                        <i class="far fa-circle text-primary nav-icon"></i>
                                        <p><?= lang("App.dyeing_challans") ?></p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= site_url('production/yarn-warping-sizing') ?>" class="nav-link <?= $segment2 === 'yarn-warping-sizing' ? 'active' : '' ?>">
                                        <i class="far fa-circle text-info nav-icon"></i>
                                        <p><?= lang("App.warping_sizing") ?></p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= site_url('production/yarn-twisting') ?>" class="nav-link <?= $segment2 === 'yarn-twisting' ? 'active' : '' ?>">
                                        <i class="far fa-circle text-warning nav-icon"></i>
                                        <p><?= lang("App.twisting_challans") ?></p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= site_url('production/yarn-weaving') ?>" class="nav-link <?= $segment2 === 'yarn-weaving' ? 'active' : '' ?>">
                                        <i class="far fa-circle text-danger nav-icon"></i>
                                        <p><?= lang("App.weaving_challans") ?></p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('production/yarn-reports') ?>" class="nav-link">
                                <i class="nav-icon fas fa-chart-line text-info"></i>
                                <p><?= lang("App.yarn_reports") ?></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- OPERATIONS -->
                <?php if(in_array('transport.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isOperations ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isOperations ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shipping-fast text-danger"></i>
                        <p>
                            <?= lang("App.operations") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('transports') ?>" class="nav-link">
                                <i class="nav-icon fas fa-truck text-danger"></i>
                                <p><?= lang("App.transports") ?></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- FINANCE -->
                <?php if(in_array('bank_account.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isFinance ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isFinance ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-university text-primary"></i>
                        <p>
                            <?= lang("App.finance") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('bank_accounts') ?>" class="nav-link">
                                <i class="nav-icon fas fa-university text-primary"></i>
                                <p><?= lang("App.bank_accounts") ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('reconciliation') ?>" class="nav-link">
                                <i class="nav-icon fas fa-check-double text-success"></i>
                                <p><?= lang("App.reconciliation") ?></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- HR MANAGEMENT -->
                <?php if(in_array('employee.view', session('permissions') ?? []) || in_array('attendance.view', session('permissions') ?? []) || in_array('loan.view', session('permissions') ?? []) || in_array('salary.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isHR ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isHR ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users text-info"></i>
                        <p>
                            <?= lang("App.hr_management") ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
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
                    </ul>
                </li>
                <?php endif; ?>

                <!-- ADMINISTRATION -->
                <?php if(in_array('user.view', session('permissions') ?? []) || in_array('role.view', session('permissions') ?? []) || in_array('setting.view', session('permissions') ?? []) || in_array('module.view', session('permissions') ?? []) || in_array('permission.view', session('permissions') ?? [])): ?>
                <li class="nav-item <?= $isAdmin ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $isAdmin ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shield-alt text-danger"></i>
                        <p>
                            <?= lang('App.admin') ?>
                            <i class="nav-arrow fas fa-angle-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
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
                                        <p><?= lang("App.app_settings") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array('country.view', session('permissions') ?? [])): ?>
                                <li class="nav-item">
                                    <a href="<?= site_url('countries') ?>" class="nav-link">
                                        <i class="fas fa-globe nav-icon text-primary"></i>
                                        <p><?= lang("App.countries") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if(in_array('state.view', session('permissions') ?? [])): ?>
                                <li class="nav-item">
                                    <a href="<?= site_url('states') ?>" class="nav-link">
                                        <i class="fas fa-map nav-icon text-success"></i>
                                        <p><?= lang("App.states") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                

                                
                                <?php if(in_array('tax.view', session('permissions') ?? [])): ?>
                                <li class="nav-item">
                                    <a href="<?= site_url('settings/taxes') ?>" class="nav-link">
                                        <i class="fas fa-percent nav-icon text-success"></i>
                                        <p><?= lang("App.taxes") ?></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
                
        </nav>
    </div>
</aside>
