<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Quotations<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice text-primary me-2"></i>Quotations / Estimates</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <!-- View Mode Toggle: Split View vs Full List View -->
            <button type="button" id="btnToggleSplit" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm" title="Toggle between Split View and Full View">
                <i class="fas fa-expand me-1" id="splitToggleIcon"></i> <span id="splitToggleText">Full View</span>
            </button>
            <a href="<?= site_url('quotations') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('quotations/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Quotation
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('quotations') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($quotations)) ?></div>
            <div class="bt-stat-label">Total Quotations</div>
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
        <a href="<?= site_url('quotations?status=Accepted') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Accepted' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['accepted_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Accepted Quotes</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['accepted_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('quotations?status=Invoiced') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['status'] ?? '') === 'Invoiced' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['invoiced_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Invoiced Quotes</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['invoiced_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Quotations</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end" id="quotationFilterForm">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Quotation #</label>
                <input type="text" name="quotation_number" class="form-control" placeholder="Search Quotation #" value="<?= esc($filters['quotation_number'] ?? '') ?>">
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
                    <option value="Sent" <?= (isset($filters['status']) && $filters['status'] == 'Sent') ? 'selected' : '' ?>>Sent</option>
                    <option value="Accepted" <?= (isset($filters['status']) && $filters['status'] == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                    <option value="Declined" <?= (isset($filters['status']) && $filters['status'] == 'Declined') ? 'selected' : '' ?>>Declined</option>
                    <option value="Invoiced" <?= (isset($filters['status']) && $filters['status'] == 'Invoiced') ? 'selected' : '' ?>>Invoiced</option>
                    <option value="Expired" <?= (isset($filters['status']) && $filters['status'] == 'Expired') ? 'selected' : '' ?>>Expired</option>
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
                <a href="<?= site_url('quotations') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Split View Workspace Layout -->
<div class="row g-3 align-items-start" id="quotationSplitRow">
    <!-- Center Quotations List Column -->
    <div class="col-12 col-lg-5 col-xl-5 transition-col" id="quotationListCol">
        <div class="card card-outline card-secondary shadow-sm mb-3" id="quotationListCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="quotationListCardHeader">
                <h3 class="card-title fw-bold text-dark m-0"><i class="fas fa-list text-primary me-2"></i>Quotations List</h3>
                <span class="text-muted small">Loaded <strong id="loadedQuotationsCount"><?= count($quotations) ?></strong> quotes</span>
            </div>
            <div class="card-body p-0 position-relative" id="quotationListBody">
                <div id="quotationListScrollContainer" style="overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-hover align-middle mb-0 w-100" id="quotationsMasterTable" data-no-enhance="true" style="table-layout: fixed;">
                        <thead class="table-light sticky-top bg-light" style="z-index: 10;">
                            <tr>
                                <th style="width: 32%;">Quotation #</th>
                                <th style="width: 38%;">Customer</th>
                                <th class="text-end" style="width: 20%;">Total</th>
                                <th class="text-center" style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="quotationsMasterTbody">
                            <?php if (empty($quotations)): ?>
                                <tr id="noQuotationsRow">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block opacity-50"></i>
                                        No quotations found matching criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($quotations as $idx => $q): ?>
                                    <?php
                                    $statusColors = [
                                        'Draft'     => 'secondary',
                                        'Sent'      => 'info',
                                        'Accepted'  => 'success',
                                        'Declined'  => 'danger',
                                        'Invoiced'  => 'primary',
                                        'Expired'   => 'dark'
                                    ];
                                    $color = $statusColors[$q['status']] ?? 'secondary';
                                    ?>
                                    <tr class="quotation-row cursor-pointer <?= $idx === 0 ? 'table-active border-primary' : '' ?>" 
                                        data-id="<?= $q['id'] ?>"
                                        id="quotation-row-<?= $q['id'] ?>"
                                        style="cursor: pointer;">
                                        <td class="text-truncate">
                                            <div class="fw-bold text-primary text-truncate"><?= esc($q['quotation_number']) ?></div>
                                            <small class="text-muted d-block"><?= date('d/m/Y', strtotime($q['quotation_date'])) ?></small>
                                            <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($q['status']) ?></span>
                                            <?php if (!empty($q['converted_to_so_id']) || !empty($q['linked_so_id'])): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.65rem;">
                                                    <i class="fas fa-shopping-cart me-1"></i>SO Converted
                                                </span>
                                            <?php elseif (!empty($q['converted_to_invoice_id']) || !empty($q['linked_invoice_id'])): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                                                    <i class="fas fa-file-invoice me-1"></i>Invoiced
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate">
                                            <div class="fw-semibold text-dark text-truncate" title="<?= esc($q['customer_name']) ?>"><?= esc($q['customer_name']) ?></div>
                                            <small class="text-muted d-block text-truncate">
                                                <?= !empty($q['city']) ? esc($q['city']) : '-' ?>
                                            </small>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <div class="fw-bold text-dark fs-6">₹<?= number_format($q['total_amount'], 2) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= site_url('quotations/view/' . $q['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
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
                        <small class="text-muted fw-semibold">Loading 20 more quotations...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Quotation View Column (hidden on mobile) -->
    <div class="col-lg-7 col-xl-7 transition-col d-none d-lg-block" id="quotationDetailCol">
        <div class="card card-outline card-primary shadow-sm mb-3" id="quotationDetailCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" id="quotationDetailCardHeader">
                <h5 class="card-title fw-bold text-dark m-0" id="detailPaneTitle">
                    <i class="fas fa-eye text-primary me-2"></i>Quotation Details
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnOpenSinglePage" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" target="_blank" title="Open in standalone full page">
                        <i class="fas fa-external-link-alt me-1 text-primary"></i> Single Page View
                    </a>
                </div>
            </div>
            <!-- Quotation Details Container: natural height, no inner scroll -->
            <div class="card-body p-3" id="quotationDetailContainer" style="overflow: visible;">
                <div class="text-center py-5 text-muted" id="detailPlaceholder">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted">Loading quotation details...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quotation-row {
    transition: background-color 0.15s ease-in-out;
}
.quotation-row:hover {
    background-color: #f1f5f9 !important;
}
.quotation-row.table-active {
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
    const listCol = document.getElementById('quotationListCol');
    const detailCol = document.getElementById('quotationDetailCol');
    const splitBtn = document.getElementById('btnToggleSplit');
    const splitText = document.getElementById('splitToggleText');
    const splitIcon = document.getElementById('splitToggleIcon');
    const container = document.getElementById('quotationDetailContainer');
    const fullPageBtn = document.getElementById('btnOpenSinglePage');
    const scrollContainer = document.getElementById('quotationListScrollContainer');
    const tbody = document.getElementById('quotationsMasterTbody');
    const spinner = document.getElementById('infiniteScrollSpinner');
    const countBadge = document.getElementById('loadedQuotationsCount');

    const isMobile = window.innerWidth < 992;
    let isSplitOn = !isMobile;
    let activeQuotationId = null;
    let currentOffset = document.querySelectorAll('.quotation-row').length;
    let isLoadingMore = false;
    let hasMoreRows = true;

    // Load quotation details via AJAX into the right pane
    window.loadQuotationDetail = function(quotationId) {
        if (!quotationId) return;
        activeQuotationId = quotationId;

        document.querySelectorAll('.quotation-row').forEach(r => r.classList.remove('table-active', 'border-primary'));
        const activeRow = document.getElementById('quotation-row-' + quotationId);
        if (activeRow) {
            activeRow.classList.add('table-active');
        }

        if (fullPageBtn) {
            fullPageBtn.href = '<?= site_url('quotations/view/') ?>' + quotationId + '?mode=single';
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
                <div class="text-muted">Loading quotation #${quotationId}...</div>
            </div>
        `;

        fetch('<?= site_url('quotations/view/') ?>' + quotationId + '?ajax=1', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, */*'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status + ': Quotation details could not be loaded.');
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
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="loadQuotationDetail(${quotationId})">
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
        const rows = tbody.querySelectorAll('.quotation-row');
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
            const thead = document.querySelector('#quotationsMasterTable thead');
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

        const detailCard = document.getElementById('quotationDetailCard');
        const detailContainer = document.getElementById('quotationDetailContainer');
        const listHeader = document.getElementById('quotationListCardHeader');
        if (!scrollContainer) return;

        const minHeight = getTwentyRowsHeight();

        let targetScrollHeight = minHeight;
        if (detailCard && detailContainer) {
            const listHeaderHeight = listHeader ? listHeader.offsetHeight : 45;
            const detailCardHeader = document.getElementById('quotationDetailCardHeader');
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
        const detailContainerEl = document.getElementById('quotationDetailContainer');
        if (detailContainerEl) {
            resizeObserver.observe(detailContainerEl);
        }
    }

    window.addEventListener('resize', syncPaneHeights);
    syncPaneHeights();

    // Attach row click listeners
    function bindRowClicks(scope) {
        const rowsToBind = (scope || document).querySelectorAll('.quotation-row:not([data-bound])');
        rowsToBind.forEach(row => {
            row.setAttribute('data-bound', '1');
            row.addEventListener('click', function(e) {
                if (e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                const id = this.getAttribute('data-id');
                if (window.innerWidth < 992 || !isSplitOn) {
                    window.location.href = '<?= site_url('quotations/view/') ?>' + id + '?mode=single';
                } else {
                    loadQuotationDetail(id);
                }
            });
        });
    }

    bindRowClicks(tbody);

    // Auto-load first quotation on page load (desktop only)
    const initialRows = document.querySelectorAll('.quotation-row');
    if (initialRows.length > 0) {
        const firstId = initialRows[0].getAttribute('data-id');
        if (window.innerWidth >= 992 && isSplitOn) {
            loadQuotationDetail(firstId);
        }
    } else {
        container.innerHTML = '<div class="text-center py-5 text-muted">No quotations found.</div>';
    }

    // Infinite scroll handler
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (isLoadingMore || !hasMoreRows) return;
            const distanceToBottom = scrollContainer.scrollHeight - (scrollContainer.scrollTop + scrollContainer.clientHeight);
            if (distanceToBottom <= 100) {
                loadMoreQuotations();
            }
        });
    }

    function loadMoreQuotations() {
        if (isLoadingMore || !hasMoreRows) return;
        isLoadingMore = true;
        if (spinner) spinner.style.display = 'block';

        const filterForm = document.getElementById('quotationFilterForm');
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : []);
        params.set('load_more', '1');
        params.set('offset', currentOffset);
        params.set('limit', '20');

        fetch('<?= site_url('quotations') ?>?' + params.toString(), {
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
                    spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All quotations loaded.</small>';
                    spinner.style.display = 'block';
                }
            } else {
                tbody.insertAdjacentHTML('beforeend', html);
                const newlyAdded = tbody.querySelectorAll('.quotation-row:not([data-bound])');
                currentOffset += newlyAdded.length;
                if (countBadge) {
                    countBadge.textContent = document.querySelectorAll('.quotation-row').length;
                }
                bindRowClicks(tbody);
                syncPaneHeights();

                if (newlyAdded.length < 20) {
                    hasMoreRows = false;
                    if (spinner) {
                        spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All quotations loaded.</small>';
                        spinner.style.display = 'block';
                    }
                } else if (spinner) {
                    spinner.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.error('Error loading more quotations:', err);
            if (spinner) {
                spinner.innerHTML = `<small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Failed to load more (${err.message}). <a href="javascript:void(0)" onclick="loadMoreQuotations()" class="fw-bold">Retry</a></small>`;
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
                if (activeQuotationId) {
                    loadQuotationDetail(activeQuotationId);
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
