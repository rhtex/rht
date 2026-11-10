<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Invoices<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Invoices</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <!-- View Mode Toggle: Split View vs Full List View -->
            <button type="button" id="btnToggleSplit" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm" title="Toggle between Split View and Full View">
                <i class="fas fa-expand me-1" id="splitToggleIcon"></i> <span id="splitToggleText">Full View</span>
            </button>
            <a href="<?= site_url('invoices') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('invoices/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Invoice
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($invoices)) ?></div>
            <div class="bt-stat-label">Total Invoices</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Invoiced</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices?status=Paid') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Paid' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['paid_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Paid (<?= $stats['paid_count'] ?? 0 ?> Invoices)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['paid_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices?status=Open') ?>" class="bt-stat bt-g-amber text-decoration-none <?= in_array($filters['status'] ?? '', ['Open', 'Overdue', 'Partially Paid']) ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['balance'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Outstanding Balance</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['balance'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Invoices</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end" id="invoiceFilterForm">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Invoice #</label>
                <input type="text" name="invoice_number" id="filter_invoice_number" class="form-control" placeholder="Search Invoice #" value="<?= esc($filters['invoice_number'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Customer</label>
                <select name="customer_id" id="filter_customer_id" class="form-select select2">
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
                <select name="status" id="filter_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Open" <?= (isset($filters['status']) && $filters['status'] == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Paid" <?= (isset($filters['status']) && $filters['status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Partially Paid" <?= (isset($filters['status']) && $filters['status'] == 'Partially Paid') ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="Overdue" <?= (isset($filters['status']) && $filters['status'] == 'Overdue') ? 'selected' : '' ?>>Overdue</option>
                    <option value="Void" <?= (isset($filters['status']) && $filters['status'] == 'Void') ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" id="filter_date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" id="filter_date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filters"><i class="fas fa-search"></i></button>
                <a href="<?= site_url('invoices') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Split View Workspace Layout -->
<div class="row g-3 align-items-start" id="invoiceSplitRow">
    <!-- Center Invoices List Column -->
    <div class="col-lg-5 col-xl-5 transition-col" id="invoiceListCol">
        <div class="card card-outline card-secondary shadow-sm mb-3" id="invoiceListCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="invoiceListCardHeader">
                <h3 class="card-title fw-bold text-dark m-0"><i class="fas fa-list text-primary me-2"></i>Invoice List</h3>
                <span class="text-muted small">Loaded <strong id="loadedInvoicesCount"><?= count($invoices) ?></strong> invoices</span>
            </div>
            <div class="card-body p-0 position-relative" id="invoiceListBody">
                <div id="invoiceListScrollContainer" style="overflow-y: auto; overflow-x: hidden;">
                <table class="table table-hover align-middle mb-0 w-100" id="invoicesMasterTable" data-no-enhance="true" style="table-layout: fixed;">
                    <thead class="table-light sticky-top bg-light" style="z-index: 10;">
                        <tr>
                            <th style="width: 30%;">Invoice</th>
                            <th style="width: 40%;">Customer</th>
                            <th class="text-end" style="width: 20%;">Amount</th>
                            <th class="text-center" style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody id="invoicesMasterTbody">
                        <?php if (empty($invoices)): ?>
                            <tr id="noInvoicesRow">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block opacity-50"></i>
                                    No invoices found matching criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $idx => $invoice): ?>
                                <?php
                                $statusColors = [
                                    'Draft'          => 'secondary',
                                    'Open'           => 'primary',
                                    'Paid'           => 'success',
                                    'Partially Paid' => 'info',
                                    'Overdue'        => 'danger',
                                    'Void'           => 'dark'
                                ];
                                $color = $statusColors[$invoice['status']] ?? 'secondary';
                                ?>
                                <tr class="invoice-row cursor-pointer <?= $idx === 0 ? 'table-active border-primary' : '' ?>" 
                                    data-id="<?= $invoice['id'] ?>"
                                    id="inv-row-<?= $invoice['id'] ?>"
                                    style="cursor: pointer;">
                                    <td class="text-truncate">
                                        <div class="fw-bold text-primary text-truncate"><?= esc($invoice['invoice_number']) ?></div>
                                        <small class="text-muted d-block"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></small>
                                        <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($invoice['status']) ?></span>
                                    </td>
                                    <td class="text-truncate">
                                        <div class="fw-semibold text-dark text-truncate" title="<?= esc($invoice['customer_name']) ?>">
                                            <?= esc($invoice['customer_name']) ?>
                                        </div>
                                        <?php if (!empty($invoice['customer_city'])): ?>
                                            <small class="text-muted text-truncate d-block"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($invoice['customer_city']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($invoice['so_number'])): ?>
                                            <span class="badge bg-light text-secondary border small" style="font-size:0.65rem;">SO: <?= esc($invoice['so_number']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-truncate">
                                        <div class="fw-bold text-dark text-truncate">₹<?= number_format($invoice['total_amount'], 2) ?></div>
                                        <?php if ($invoice['balance'] > 0): ?>
                                            <small class="text-danger fw-semibold d-block text-truncate">Bal: ₹<?= number_format($invoice['balance'], 2) ?></small>
                                        <?php else: ?>
                                            <small class="text-success d-block"><i class="fas fa-check me-1"></i>Paid</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>?mode=single" 
                                           class="btn btn-sm btn-outline-secondary p-1" 
                                           title="Open Standalone Single Page View"
                                           onclick="event.stopPropagation();">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Infinite Scroll Loading Indicator -->
                <div id="infiniteScrollSpinner" class="text-center py-2 bg-light border-top" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <small class="text-muted fw-semibold">Loading 20 more invoices...</small>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Invoice View Column (No scrollbar, full content flows naturally) -->
    <div class="col-lg-7 col-xl-7 transition-col" id="invoiceDetailCol">
        <div class="card card-outline card-primary shadow-sm mb-3" id="invoiceDetailCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="invoiceDetailCardHeader">
                <h5 class="card-title fw-bold text-dark m-0" id="detailPaneTitle">
                    <i class="fas fa-eye text-primary me-2"></i>Invoice Details
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnOpenSinglePage" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" target="_blank" title="Open this invoice in standalone full page">
                        <i class="fas fa-external-link-alt me-1 text-primary"></i> Single Page View
                    </a>
                </div>
            </div>
            <!-- Invoice Details: No inner scrollbar, expands cleanly to full content height -->
            <div class="card-body p-3" id="invoiceDetailContainer" style="overflow: visible;">
                <div class="text-center py-5 text-muted" id="detailPlaceholder">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted">Loading invoice details...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.invoice-row {
    transition: background-color 0.15s ease-in-out;
}
.invoice-row:hover {
    background-color: #f1f5f9 !important;
}
.invoice-row.table-active {
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
    const listCol = document.getElementById('invoiceListCol');
    const detailCol = document.getElementById('invoiceDetailCol');
    const splitBtn = document.getElementById('btnToggleSplit');
    const splitText = document.getElementById('splitToggleText');
    const splitIcon = document.getElementById('splitToggleIcon');
    const container = document.getElementById('invoiceDetailContainer');
    const fullPageBtn = document.getElementById('btnOpenSinglePage');
    const scrollContainer = document.getElementById('invoiceListScrollContainer');
    const tbody = document.getElementById('invoicesMasterTbody');
    const spinner = document.getElementById('infiniteScrollSpinner');
    const countBadge = document.getElementById('loadedInvoicesCount');

    // Default is split view: ON
    let isSplitOn = true;
    let activeInvoiceId = null;

    // Infinite scroll state: initial loaded count is 100 (or initial rows count)
    let currentOffset = document.querySelectorAll('.invoice-row').length;
    let isLoadingMore = false;
    let hasMoreRows = true;

    // Load invoice detail via AJAX into the right pane
    function loadInvoiceDetail(invoiceId) {
        if (!invoiceId) return;
        activeInvoiceId = invoiceId;

        // Update active row highlighting
        document.querySelectorAll('.invoice-row').forEach(r => r.classList.remove('table-active', 'border-primary'));
        const activeRow = document.getElementById('inv-row-' + invoiceId);
        if (activeRow) {
            activeRow.classList.add('table-active');
        }

        // Update standalone full page button link
        if (fullPageBtn) {
            fullPageBtn.href = '<?= site_url('invoices/view/') ?>' + invoiceId + '?mode=single';
        }

        // Reset list container height prior to loading new details so natural height can shrink
        if (scrollContainer) {
            scrollContainer.style.height = '';
            scrollContainer.style.maxHeight = '';
        }

        container.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Loading invoice #${invoiceId}...</div>
            </div>
        `;

        fetch('<?= site_url('invoices/view/') ?>' + invoiceId + '?ajax=1', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, */*'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status + ': Invoice details could not be loaded.');
            return response.text();
        })
        .then(html => {
            // Check if returned content is login redirect or empty
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
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="loadInvoiceDetail(${invoiceId})">
                            <i class="fas fa-redo me-1"></i> Retry
                        </button>
                    </div>
                </div>
            `;
            requestAnimationFrame(() => {
                syncPaneHeights();
            });
        });
    }

    // Function to calculate exact height of 20 table rows dynamically
    function getTwentyRowsHeight() {
        const rows = tbody.querySelectorAll('.invoice-row');
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
            // Include the table header height
            const thead = document.querySelector('#invoicesMasterTable thead');
            const theadH = thead ? thead.offsetHeight : 42;
            return Math.ceil(total + theadH + 4);
        }
        return 980; // reliable default fallback
    }

    // Function to synchronize heights:
    // 1. Invoice details does not scroll internally and renders at its natural height.
    // 2. Invoice list minimum height is 20 rows.
    // 3. When an invoice with more items is selected, list height expands to match details.
    // 4. When an invoice with fewer items is selected, list height decreases back to match details or minimum 20 rows.
    function syncPaneHeights() {
        if (!isSplitOn) {
            if (scrollContainer) {
                scrollContainer.style.height = '';
                scrollContainer.style.maxHeight = '';
                scrollContainer.style.overflowY = 'visible';
            }
            return;
        }

        const detailCard = document.getElementById('invoiceDetailCard');
        const detailContainer = document.getElementById('invoiceDetailContainer');
        const listHeader = document.getElementById('invoiceListCardHeader');
        if (!scrollContainer) return;

        // Base 20-row height
        const minHeight = getTwentyRowsHeight();

        // Calculate natural height of invoice detail card
        let targetScrollHeight = minHeight;
        if (detailCard && detailContainer) {
            const listHeaderHeight = listHeader ? listHeader.offsetHeight : 45;
            const detailCardHeader = document.getElementById('invoiceDetailCardHeader');
            const detailHeaderHeight = detailCardHeader ? detailCardHeader.offsetHeight : 45;
            const detailContentHeight = detailContainer.scrollHeight || detailContainer.offsetHeight || 0;
            const naturalDetailCardHeight = detailHeaderHeight + detailContentHeight;

            targetScrollHeight = Math.max(minHeight, naturalDetailCardHeight - listHeaderHeight);
        }

        scrollContainer.style.height = targetScrollHeight + 'px';
        scrollContainer.style.maxHeight = targetScrollHeight + 'px';
        scrollContainer.style.overflowY = 'auto';
    }

    // Watch for detail card size changes without recursive loops
    if (window.ResizeObserver) {
        let isSyncing = false;
        const resizeObserver = new ResizeObserver(() => {
            if (isSyncing) return;
            isSyncing = true;
            syncPaneHeights();
            setTimeout(() => { isSyncing = false; }, 50);
        });
        const detailContainerEl = document.getElementById('invoiceDetailContainer');
        if (detailContainerEl) {
            resizeObserver.observe(detailContainerEl);
        }
    }

    window.addEventListener('resize', syncPaneHeights);
    syncPaneHeights();

    // Attach row click listeners
    function bindRowClicks(scope) {
        const rowsToBind = (scope || document).querySelectorAll('.invoice-row:not([data-bound])');
        rowsToBind.forEach(row => {
            row.setAttribute('data-bound', '1');
            row.addEventListener('click', function(e) {
                const id = this.getAttribute('data-id');
                if (isSplitOn) {
                    loadInvoiceDetail(id);
                } else {
                    window.location.href = '<?= site_url('invoices/view/') ?>' + id + '?mode=single';
                }
            });
        });
    }

    bindRowClicks(tbody);

    // Auto-load first invoice on page load by default
    const initialRows = document.querySelectorAll('.invoice-row');
    if (initialRows.length > 0) {
        const firstId = initialRows[0].getAttribute('data-id');
        loadInvoiceDetail(firstId);
    } else {
        container.innerHTML = '<div class="text-center py-5 text-muted">No invoices found.</div>';
    }

    // Infinite Scroll Handler:
    // Triggers when user scrolls down near the bottom of invoiceListScrollContainer
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (isLoadingMore || !hasMoreRows) return;

            // Trigger when within 100px of the bottom
            const distanceToBottom = scrollContainer.scrollHeight - (scrollContainer.scrollTop + scrollContainer.clientHeight);
            if (distanceToBottom <= 100) {
                loadMoreInvoices();
            }
        });
    }

    function loadMoreInvoices() {
        if (isLoadingMore || !hasMoreRows) return;
        isLoadingMore = true;
        if (spinner) spinner.style.display = 'block';

        const filterForm = document.getElementById('invoiceFilterForm');
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : []);
        params.set('load_more', '1');
        params.set('offset', currentOffset);
        params.set('limit', '20');

        fetch('<?= site_url('invoices') ?>?' + params.toString(), {
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
                    spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All invoices loaded.</small>';
                    spinner.style.display = 'block';
                }
            } else {
                tbody.insertAdjacentHTML('beforeend', html);
                const newlyAdded = tbody.querySelectorAll('.invoice-row:not([data-bound])');
                currentOffset += newlyAdded.length;
                if (countBadge) {
                    countBadge.textContent = document.querySelectorAll('.invoice-row').length;
                }
                bindRowClicks(tbody);
                syncPaneHeights();

                if (newlyAdded.length < 20) {
                    hasMoreRows = false;
                    if (spinner) {
                        spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All invoices loaded.</small>';
                        spinner.style.display = 'block';
                    }
                } else if (spinner) {
                    spinner.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.error('Error loading more invoices:', err);
            if (spinner) {
                spinner.innerHTML = `<small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Failed to load more (${err.message}). <a href="javascript:void(0)" onclick="loadMoreInvoices()" class="fw-bold">Retry</a></small>`;
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
                // Switch to Split View
                listCol.className = 'col-lg-5 col-xl-5 transition-col d-flex flex-column';
                detailCol.style.display = 'flex';
                splitText.textContent = 'Full View';
                splitIcon.className = 'fas fa-expand me-1';
                splitBtn.classList.remove('btn-primary');
                splitBtn.classList.add('btn-outline-primary');
                if (activeInvoiceId) {
                    loadInvoiceDetail(activeInvoiceId);
                } else {
                    syncPaneHeights();
                }
            } else {
                // Switch to Full View (list takes full width 12 cols, detail pane hidden)
                listCol.className = 'col-12 transition-col';
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
