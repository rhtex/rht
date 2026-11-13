<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Sales Orders<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-shopping-cart text-primary me-2"></i>Sales Orders</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <!-- View Mode Toggle: Split View vs Full List View -->
            <button type="button" id="btnToggleSplit" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm" title="Toggle between Split View and Full View">
                <i class="fas fa-expand me-1" id="splitToggleIcon"></i> <span id="splitToggleText">Full View</span>
            </button>
            <a href="<?= site_url('sales_orders') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('sales_order.create')): ?>
            <a href="<?= site_url('sales_orders/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Sales Order
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_orders') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($orders)) ?></div>
            <div class="bt-stat-label">Total Orders</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-chart-line bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Value</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_orders?status=Confirmed') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Confirmed' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['confirmed_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Confirmed Orders</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['confirmed_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_orders') ?>" class="bt-stat bt-g-amber text-decoration-none">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['converted_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Invoiced Orders</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['converted_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Sales Orders</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end" id="orderFilterForm">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Order #</label>
                <input type="text" name="sales_order_number" class="form-control" placeholder="Search Order #" value="<?= esc($filters['sales_order_number'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>" <?= (isset($filters['customer_id']) && $filters['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                            <?= esc($customer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Confirmed" <?= (isset($filters['status']) && $filters['status'] == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Closed" <?= (isset($filters['status']) && $filters['status'] == 'Closed') ? 'selected' : '' ?>>Closed</option>
                    <option value="Void" <?= (isset($filters['status']) && $filters['status'] == 'Void') ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filters"><i class="fas fa-search"></i></button>
                <a href="<?= site_url('sales_orders') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Split View Workspace Layout -->
<div class="row g-3 align-items-start" id="orderSplitRow">
    <!-- Center Orders List Column -->
    <div class="col-12 col-lg-5 col-xl-5 transition-col" id="orderListCol">
        <div class="card card-outline card-secondary shadow-sm mb-3" id="orderListCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="orderListCardHeader">
                <h3 class="card-title fw-bold text-dark m-0"><i class="fas fa-list text-primary me-2"></i>Orders List</h3>
                <span class="text-muted small">Loaded <strong id="loadedOrdersCount"><?= count($orders) ?></strong> orders</span>
            </div>
            <div class="card-body p-0 position-relative" id="orderListBody">
                <div id="orderListScrollContainer" style="overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-hover align-middle mb-0 w-100" id="ordersMasterTable" data-no-enhance="true" style="table-layout: fixed;">
                        <thead class="table-light sticky-top bg-light" style="z-index: 10;">
                            <tr>
                                <th style="width: 32%;">Order #</th>
                                <th style="width: 38%;">Customer</th>
                                <th class="text-end" style="width: 20%;">Total</th>
                                <th class="text-center" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="ordersMasterTbody">
                            <?php if (empty($orders)): ?>
                                <tr id="noOrdersRow">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3 d-block opacity-50"></i>
                                        No sales orders found matching criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $idx => $order): ?>
                                    <?php
                                    $statusColors = [
                                        'Draft'     => 'secondary',
                                        'Confirmed' => 'primary',
                                        'Closed'    => 'success',
                                        'Void'      => 'dark'
                                    ];
                                    $color = $statusColors[$order['status']] ?? 'secondary';
                                    ?>
                                    <tr class="order-row cursor-pointer <?= $idx === 0 ? 'table-active border-primary' : '' ?>" 
                                        data-id="<?= $order['id'] ?>"
                                        id="order-row-<?= $order['id'] ?>"
                                        style="cursor: pointer;">
                                        <td class="text-truncate">
                                            <div class="fw-bold text-primary text-truncate"><?= esc($order['sales_order_number']) ?></div>
                                            <small class="text-muted d-block"><?= date('d/m/Y', strtotime($order['order_date'])) ?></small>
                                            <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($order['status']) ?></span>
                                            <?php if (!empty($order['converted_to_invoice_id'])): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                                                    <i class="fas fa-check-double me-1"></i>Invoiced
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate">
                                            <div class="fw-semibold text-dark text-truncate" title="<?= esc($order['customer_name']) ?>"><?= esc($order['customer_name']) ?></div>
                                            <small class="text-muted d-block text-truncate">
                                                <?= !empty($order['customer_city']) ? esc($order['customer_city']) : '-' ?>
                                            </small>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <div class="fw-bold text-dark fs-6">₹<?= number_format($order['total_amount'], 2) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
                                                <i class="fas fa-external-link-alt text-muted"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Infinite Scroll Spinner -->
                    <div id="infiniteScrollSpinner" class="text-center py-2 bg-light border-top" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <small class="text-muted fw-semibold">Loading 20 more orders...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Order View Column (hidden on mobile) -->
    <div class="col-lg-7 col-xl-7 transition-col d-none d-lg-block" id="orderDetailCol">
        <div class="card card-outline card-primary shadow-sm mb-3" id="orderDetailCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="orderDetailCardHeader">
                <h5 class="card-title fw-bold text-dark m-0" id="detailPaneTitle">
                    <i class="fas fa-eye text-primary me-2"></i>Sales Order Details
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnOpenSinglePage" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" target="_blank" title="Open in standalone full page">
                        <i class="fas fa-external-link-alt me-1 text-primary"></i> Single Page View
                    </a>
                </div>
            </div>
            <!-- Order Details Container: natural height, no inner scroll -->
            <div class="card-body p-3" id="orderDetailContainer" style="overflow: visible;">
                <div class="text-center py-5 text-muted" id="detailPlaceholder">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted">Loading order details...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-row {
    transition: background-color 0.15s ease-in-out;
}
.order-row:hover {
    background-color: #f1f5f9 !important;
}
.order-row.table-active {
    background-color: #e0f2fe !important;
    border-left: 4px solid #0284c7 !important;
}
.transition-col {
    transition: all 0.25s ease-in-out;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listCol = document.getElementById('orderListCol');
    const detailCol = document.getElementById('orderDetailCol');
    const splitBtn = document.getElementById('btnToggleSplit');
    const splitText = document.getElementById('splitToggleText');
    const splitIcon = document.getElementById('splitToggleIcon');
    const container = document.getElementById('orderDetailContainer');
    const fullPageBtn = document.getElementById('btnOpenSinglePage');
    const scrollContainer = document.getElementById('orderListScrollContainer');
    const tbody = document.getElementById('ordersMasterTbody');
    const spinner = document.getElementById('infiniteScrollSpinner');
    const countBadge = document.getElementById('loadedOrdersCount');

    const isMobile = window.innerWidth < 992;
    let isSplitOn = !isMobile;
    let activeOrderId = null;
    let currentOffset = document.querySelectorAll('.order-row').length;
    let isLoadingMore = false;
    let hasMoreRows = true;

    // Load order details via AJAX into the right pane
    window.loadOrderDetail = function(orderId) {
        if (!orderId) return;
        activeOrderId = orderId;

        document.querySelectorAll('.order-row').forEach(r => r.classList.remove('table-active', 'border-primary'));
        const activeRow = document.getElementById('order-row-' + orderId);
        if (activeRow) {
            activeRow.classList.add('table-active');
        }

        if (fullPageBtn) {
            fullPageBtn.href = '<?= site_url('sales_orders/view/') ?>' + orderId + '?mode=single';
        }

        if (scrollContainer) {
            scrollContainer.style.height = '';
            scrollContainer.style.maxHeight = '';
        }

        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Loading order #${orderId}...</div>
            </div>
        `;

        fetch('<?= site_url('sales_orders/view/') ?>' + orderId + '?ajax=1', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, */*'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status + ': Order details could not be loaded.');
            return response.text();
        })
        .then(html => {
            if (html.includes('login-box') || html.includes('Sign in to start your session')) {
                container.innerHTML = `
                    <div class="alert alert-warning m-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Your session has expired. Please <a href="<?= site_url('login') ?>" target="_blank" class="alert-link">log in again</a> and refresh this page.
                    </div>
                `;
                return;
            }
            container.innerHTML = html;
            requestAnimationFrame(() => {
                syncPaneHeights();
            });
        })
        .catch(err => {
            container.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> ${err.message}
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="loadOrderDetail(${orderId})">
                            <i class="fas fa-redo me-1"></i> Retry
                        </button>
                    </div>
                </div>
            `;
            requestAnimationFrame(() => {
                syncPaneHeights();
            });
        });
    };

    // Calculate height of 20 table rows dynamically
    function getTwentyRowsHeight() {
        const rows = tbody.querySelectorAll('.order-row');
        if (rows.length > 0) {
            let total = 0;
            const count = Math.min(20, rows.length);
            for (let i = 0; i < count; i++) {
                total += rows[i].offsetHeight || 48;
            }
            if (rows.length < 20) {
                const avg = total / rows.length;
                total += avg * (20 - rows.length);
            }
            const thead = document.querySelector('#ordersMasterTable thead');
            const theadH = thead ? thead.offsetHeight : 42;
            return Math.ceil(total + theadH + 4);
        }
        return 980;
    }

    // Synchronize heights:
    // Expands when detail is taller, shrinks back to natural height (minimum 20 rows)
    function syncPaneHeights() {
        if (!isSplitOn || window.innerWidth < 992) {
            if (scrollContainer) {
                scrollContainer.style.height = '';
                scrollContainer.style.maxHeight = '';
                scrollContainer.style.overflowY = 'visible';
            }
            return;
        }

        const detailCard = document.getElementById('orderDetailCard');
        const detailContainer = document.getElementById('orderDetailContainer');
        const listHeader = document.getElementById('orderListCardHeader');
        if (!scrollContainer) return;

        const minHeight = getTwentyRowsHeight();

        let targetScrollHeight = minHeight;
        if (detailCard && detailContainer) {
            const listHeaderHeight = listHeader ? listHeader.offsetHeight : 45;
            const detailCardHeader = document.getElementById('orderDetailCardHeader');
            const detailHeaderHeight = detailCardHeader ? detailCardHeader.offsetHeight : 45;
            const detailContentHeight = detailContainer.scrollHeight || detailContainer.offsetHeight || 0;
            const naturalDetailCardHeight = detailHeaderHeight + detailContentHeight;

            targetScrollHeight = Math.max(minHeight, naturalDetailCardHeight - listHeaderHeight);
        }

        scrollContainer.style.height = targetScrollHeight + 'px';
        scrollContainer.style.maxHeight = targetScrollHeight + 'px';
        scrollContainer.style.overflowY = 'auto';
    }

    // Watch for detail content size changes
    if (window.ResizeObserver) {
        let isSyncing = false;
        const resizeObserver = new ResizeObserver(() => {
            if (isSyncing) return;
            isSyncing = true;
            syncPaneHeights();
            setTimeout(() => { isSyncing = false; }, 50);
        });
        const detailContainerEl = document.getElementById('orderDetailContainer');
        if (detailContainerEl) {
            resizeObserver.observe(detailContainerEl);
        }
    }

    window.addEventListener('resize', syncPaneHeights);
    syncPaneHeights();

    // Attach row click listeners
    function bindRowClicks(scope) {
        const rowsToBind = (scope || document).querySelectorAll('.order-row:not([data-bound])');
        rowsToBind.forEach(row => {
            row.setAttribute('data-bound', '1');
            row.addEventListener('click', function(e) {
                if (e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                const id = this.getAttribute('data-id');
                if (window.innerWidth < 992 || !isSplitOn) {
                    window.location.href = '<?= site_url('sales_orders/view/') ?>' + id + '?mode=single';
                } else {
                    loadOrderDetail(id);
                }
            });
        });
    }

    bindRowClicks(tbody);

    // Auto-load first order on page load (desktop only)
    const initialRows = document.querySelectorAll('.order-row');
    if (initialRows.length > 0) {
        const firstId = initialRows[0].getAttribute('data-id');
        if (window.innerWidth >= 992 && isSplitOn) {
            loadOrderDetail(firstId);
        }
    } else {
        container.innerHTML = '<div class="text-center py-5 text-muted">No sales orders found.</div>';
    }

    // Infinite scroll handler
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (isLoadingMore || !hasMoreRows) return;
            const distanceToBottom = scrollContainer.scrollHeight - (scrollContainer.scrollTop + scrollContainer.clientHeight);
            if (distanceToBottom <= 100) {
                loadMoreOrders();
            }
        });
    }

    function loadMoreOrders() {
        if (isLoadingMore || !hasMoreRows) return;
        isLoadingMore = true;
        if (spinner) spinner.style.display = 'block';

        const filterForm = document.getElementById('orderFilterForm');
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : []);
        params.set('load_more', '1');
        params.set('offset', currentOffset);
        params.set('limit', '20');

        fetch('<?= site_url('sales_orders') ?>?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, */*'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error: ' + response.status);
            return response.text();
        })
        .then(html => {
            if (!html || html.trim() === '') {
                hasMoreRows = false;
                if (spinner) {
                    spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All orders loaded.</small>';
                    spinner.style.display = 'block';
                }
            } else {
                tbody.insertAdjacentHTML('beforeend', html);
                const newlyAdded = tbody.querySelectorAll('.order-row:not([data-bound])');
                currentOffset += newlyAdded.length;
                if (countBadge) {
                    countBadge.textContent = document.querySelectorAll('.order-row').length;
                }
                bindRowClicks(tbody);
                syncPaneHeights();

                if (newlyAdded.length < 20) {
                    hasMoreRows = false;
                    if (spinner) {
                        spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All orders loaded.</small>';
                        spinner.style.display = 'block';
                    }
                } else if (spinner) {
                    spinner.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.error('Error loading more orders:', err);
            if (spinner) {
                spinner.innerHTML = `<small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Failed to load more (${err.message}). <a href="javascript:void(0)" onclick="loadMoreOrders()" class="fw-bold">Retry</a></small>`;
                spinner.style.display = 'block';
            }
        })
        .finally(() => {
            isLoadingMore = false;
        });
    }

    // Toggle View Mode: Split View (Default) <-> Full View
    if (splitBtn) {
        splitBtn.addEventListener('click', function() {
            isSplitOn = !isSplitOn;
            if (isSplitOn) {
                listCol.className = 'col-12 col-lg-5 col-xl-5 transition-col';
                detailCol.classList.remove('d-none');
                detailCol.style.display = 'block';
                splitText.textContent = 'Full View';
                splitIcon.className = 'fas fa-expand me-1';
                splitBtn.classList.remove('btn-primary');
                splitBtn.classList.add('btn-outline-primary');
                if (activeOrderId) {
                    loadOrderDetail(activeOrderId);
                } else {
                    syncPaneHeights();
                }
            } else {
                listCol.className = 'col-12 transition-col';
                detailCol.classList.add('d-none');
                detailCol.style.display = 'none';
                splitText.textContent = 'Split View';
                splitIcon.className = 'fas fa-columns me-1';
                splitBtn.classList.remove('btn-outline-primary');
                splitBtn.classList.add('btn-primary');
                syncPaneHeights();
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
