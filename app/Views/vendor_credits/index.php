<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Vendor Credits<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-undo-alt text-primary me-2"></i>Vendor Credits / Debit Notes</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <button type="button" id="btnToggleSplit" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm" title="Toggle between Split View and Full View">
                <i class="fas fa-expand me-1" id="splitToggleIcon"></i> <span id="splitToggleText">Full View</span>
            </button>
            <a href="<?= site_url('vendor_credits/sync-zoho') ?>" class="btn btn-sm btn-outline-info" title="Pull updates from Zoho Books">
                <i class="fas fa-sync-alt me-1"></i> Sync from Zoho
            </a>
            <a href="<?= site_url('vendor_credits') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-redo me-1"></i> Refresh
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <!-- Total Credits -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('vendor_credits') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-receipt bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Credits (<?= number_format($stats['total'] ?? 0) ?> Notes)</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>

    <!-- Available / Open Credit Balance -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('vendor_credits?status=Open') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['status'] ?? '') === 'Open' ? 'active' : '' ?>">
            <i class="fas fa-wallet bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['open_balance'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Available Balance (<?= number_format($stats['open_count'] ?? 0) ?> Open)</div>
            <div class="progress">
                <div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['open_balance'] / $stats['total_amount']) * 100)) : 0 ?>%"></div>
            </div>
        </a>
    </div>

    <!-- Used / Applied Credits -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('vendor_credits?status=Closed') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Closed' ? 'active' : '' ?>">
            <i class="fas fa-check-double bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['used_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Applied / Closed (<?= number_format($stats['closed_count'] ?? 0) ?> Notes)</div>
            <div class="progress">
                <div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['used_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div>
            </div>
        </a>
    </div>

    <!-- Linked Purchase Bills -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bills') ?>" class="bt-stat bt-g-purple text-decoration-none">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><i class="fas fa-arrow-right me-1 small"></i>View Bills</div>
            <div class="bt-stat-label">Go to Purchase Bills</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Vendor Credits</h3>
        <div class="card-tools ms-auto">
            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-label="Toggle Filters">
                <i class="fas fa-plus filter-toggle-icon"></i>
            </button>
        </div>
    </div>
    <div id="filterCollapse" class="collapse">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end" id="vcFilterForm">
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Credit Note / Ref #</label>
                <input type="text" name="reference_no" class="form-control" placeholder="Search Ref #" value="<?= esc($filters['reference_no'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select select2">
                    <option value="">All Vendors</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= $vendor['id'] ?>" <?= (isset($filters['vendor_id']) && $filters['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                            <?= esc($vendor['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Open" <?= (isset($filters['status']) && $filters['status'] == 'Open') ? 'selected' : '' ?>>Open / Available</option>
                    <option value="Closed" <?= (isset($filters['status']) && $filters['status'] == 'Closed') ? 'selected' : '' ?>>Closed / Used</option>
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
            <div class="col-12 text-end mt-2">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-search me-1"></i> Apply Filters</button>
                <a href="<?= site_url('vendor_credits') ?>" class="btn btn-outline-secondary btn-sm px-3 ms-1"><i class="fas fa-undo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
    </div>
</div>

<!-- Split View Workspace Layout -->
<div class="row g-3 align-items-start" id="creditSplitRow">
    <!-- Center List Column -->
    <div class="col-12 col-lg-5 col-xl-5 transition-col" id="creditListCol">
        <div class="card card-outline card-secondary shadow-sm mb-3" id="creditListCard">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold text-dark m-0"><i class="fas fa-list text-primary me-2"></i>Credits List</h3>
                <span class="text-muted small">Loaded <strong id="loadedCreditsCount"><?= count($credits) ?></strong> credits</span>
            </div>
            <div class="card-body p-0 position-relative">
                <div class="erp-mobile-list p-2 bg-light" id="creditMobileList">
                    <?php if (empty($credits)): ?>
                        <div class="text-center py-4 text-muted">No vendor credits found.</div>
                    <?php else: ?>
                        <?php foreach ($credits as $c): ?>
                            <a href="<?= site_url('vendor_credits/view/' . $c['id']) ?>" class="erp-mobile-card">
                                <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35;">
                                    <?= esc($c['vendor_name'] ?? 'N/A') ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                                    <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                        <?= esc($c['reference_no']) ?>
                                        <?php if (!empty($c['credit_note_date'])): ?>
                                            &middot; <?= date('d-m-Y', strtotime($c['credit_note_date'])) ?>
                                        <?php endif; ?>
                                    </span>
                                    <span class="fw-bold small text-dark" style="flex: 0 0 auto; white-space: nowrap;">
                                        &#8377;<?= format_inr((float)($c['amount'] ?? 0)) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php if (count($credits) >= 50): ?>
                            <div class="text-center pt-1 pb-2" id="creditMobileLoadMoreWrap">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" onclick="loadMoreCredits()">
                                    View More <i class="fas fa-chevron-down ms-1"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div id="creditListScrollContainer" class="erp-desktop-wrap" style="overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-hover align-middle mb-0 w-100" id="creditsMasterTable" data-no-enhance="true" style="table-layout: fixed;">
                        <thead class="table-light sticky-top bg-light" style="z-index: 10;">
                            <tr>
                                <th style="width: 38%;">Note #</th>
                                <th style="width: 36%;">Vendor</th>
                                <th class="text-end" style="width: 20%;">Amount</th>
                                <th class="text-center" style="width: 6%;"></th>
                            </tr>
                        </thead>
                        <tbody id="creditsMasterTbody">
                            <?php if (empty($credits)): ?>
                                <tr id="noCreditsRow">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-undo-alt fa-3x text-muted mb-3 d-block opacity-50"></i>
                                        No vendor credits found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?= view('vendor_credits/list_rows', ['credits' => $credits]) ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="loadingMoreSpinner" class="text-center py-3" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                        <small class="text-muted">Loading more credits...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Detail Column -->
    <div class="col-12 col-lg-7 col-xl-7 transition-col" id="creditDetailCol">
        <div id="creditDetailContainer">
            <div class="card card-outline card-secondary shadow-sm text-center py-5 text-muted">
                <i class="fas fa-mouse-pointer fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h5>Select a Vendor Credit</h5>
                <p class="small text-muted mb-0">Click any credit note from the list on the left to preview its details.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeCreditId = null;
    let isSplitOn = true;
    let currentOffset = <?= count($credits) ?>;
    let hasMoreRows = <?= count($credits) >= 50 ? 'true' : 'false' ?>;
    let isLoadingMore = false;

    const listCol = document.getElementById('creditListCol');
    const detailCol = document.getElementById('creditDetailCol');
    const splitBtn = document.getElementById('btnToggleSplit');
    const splitText = document.getElementById('splitToggleText');
    const splitIcon = document.getElementById('splitToggleIcon');
    const scrollContainer = document.getElementById('creditListScrollContainer');
    const tbody = document.getElementById('creditsMasterTbody');
    const countBadge = document.getElementById('loadedCreditsCount');
    const spinner = document.getElementById('loadingMoreSpinner');

    function syncPaneHeights() {
        if (!scrollContainer) return;
        const availableHeight = Math.max(500, window.innerHeight - 240);
        scrollContainer.style.height = availableHeight + 'px';
        scrollContainer.style.maxHeight = availableHeight + 'px';
        const detailContainer = document.getElementById('creditDetailContainer');
        if (detailContainer) {
            detailContainer.style.minHeight = availableHeight + 'px';
        }
    }
    syncPaneHeights();
    window.addEventListener('resize', syncPaneHeights);

    function loadCreditDetail(id) {
        if (!id) return;
        activeCreditId = id;
        document.querySelectorAll('.credit-row').forEach(r => r.classList.remove('table-active', 'border-primary'));
        const activeRow = document.getElementById('credit-row-' + id);
        if (activeRow) activeRow.classList.add('table-active', 'border-primary');

        const container = document.getElementById('creditDetailContainer');
        if (container) {
            container.innerHTML = `
                <div class="card card-outline card-secondary shadow-sm text-center py-5 text-muted">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div>Loading credit details...</div>
                </div>
            `;
        }

        fetch('<?= site_url('vendor_credits/view') ?>/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            if (container) container.innerHTML = html;
        })
        .catch(err => {
            if (container) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load details: ${err.message}</div>`;
            }
        });
    }

    function bindRowClicks(scope) {
        (scope || document).querySelectorAll('.credit-row:not([data-bound])').forEach(row => {
            row.setAttribute('data-bound', '1');
            row.addEventListener('click', function() {
                loadCreditDetail(this.getAttribute('data-id'));
            });
        });
    }
    bindRowClicks();

    // Select first row by default
    const firstRow = document.querySelector('.credit-row');
    if (firstRow) {
        loadCreditDetail(firstRow.getAttribute('data-id'));
    }

    // Infinite scroll
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', function() {
            if (!hasMoreRows || isLoadingMore) return;
            if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 60) {
                loadMoreCredits();
            }
        });
    }

    function loadMoreCredits() {
        if (!hasMoreRows || isLoadingMore) return;
        isLoadingMore = true;
        if (spinner) spinner.style.display = 'block';

        const filterForm = document.getElementById('vcFilterForm');
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : []);
        params.set('load_more', '1');
        params.set('offset', currentOffset);
        params.set('limit', '20');

        fetch('<?= site_url('vendor_credits') ?>?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            if (!html || html.trim() === '') {
                hasMoreRows = false;
                if (spinner) {
                    spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All credits loaded.</small>';
                    spinner.style.display = 'block';
                }
            } else {
                tbody.insertAdjacentHTML('beforeend', html);
                const newlyAdded = tbody.querySelectorAll('.credit-row:not([data-bound])');
                const mobileList = document.getElementById('creditMobileList');
                const mobileLoadWrap = document.getElementById('creditMobileLoadMoreWrap');
                if (mobileList) {
                    const escapeHtml = (s) => String(s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
                    newlyAdded.forEach(tr => {
                        const id = tr.dataset.id;
                        const docNo = tr.querySelector('td:nth-child(1) span.text-primary')?.textContent.trim() || '';
                        const party = tr.querySelector('td:nth-child(2) .fw-bold')?.textContent.trim() || '';
                        const amt = tr.querySelector('td:nth-child(3) .fw-bold')?.textContent.trim() || '';
                        const cardHtml = `
                            <a href="<?= site_url('vendor_credits/view/') ?>${id}" class="erp-mobile-card">
                                <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35;">${escapeHtml(party)}</div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                                    <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">${escapeHtml(docNo)}</span>
                                    <span class="fw-bold small text-dark" style="flex: 0 0 auto; white-space: nowrap;">${escapeHtml(amt)}</span>
                                </div>
                            </a>`;
                        if (mobileLoadWrap) {
                            mobileLoadWrap.insertAdjacentHTML('beforebegin', cardHtml);
                        } else {
                            mobileList.insertAdjacentHTML('beforeend', cardHtml);
                        }
                    });
                }
                currentOffset += newlyAdded.length;
                if (countBadge) {
                    countBadge.textContent = document.querySelectorAll('.credit-row').length;
                }
                bindRowClicks(tbody);
                if (newlyAdded.length < 20) {
                    hasMoreRows = false;
                    if (mobileLoadWrap) mobileLoadWrap.remove();
                    if (spinner) {
                        spinner.innerHTML = '<small class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i>All credits loaded.</small>';
                        spinner.style.display = 'block';
                    }
                } else if (spinner) {
                    spinner.style.display = 'none';
                }
            }
        })
        .catch(err => {
            if (spinner) {
                spinner.innerHTML = `<small class="text-danger">Failed to load more. <a href="javascript:void(0)" onclick="loadMoreCredits()">Retry</a></small>`;
            }
        })
        .finally(() => {
            isLoadingMore = false;
        });
    }

    // Toggle View Mode
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
                if (activeCreditId) loadCreditDetail(activeCreditId);
            } else {
                listCol.className = 'col-12 transition-col';
                detailCol.classList.add('d-none');
                detailCol.style.display = 'none';
                splitText.textContent = 'Split View';
                splitIcon.className = 'fas fa-columns me-1';
                splitBtn.classList.remove('btn-outline-primary');
                splitBtn.classList.add('btn-primary');
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
