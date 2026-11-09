<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Vendors<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-truck text-primary me-2"></i>Vendor Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= base_url('sample_customer.csv') ?>" class="btn btn-sm btn-outline-secondary" title="Download Sample CSV">
                <i class="fas fa-download me-1"></i> Sample CSV
            </a>
            <a href="<?= site_url('bulk-upload/form') ?>" class="btn btn-sm btn-outline-primary" title="Bulk Upload Vendors">
                <i class="fas fa-file-upload me-1"></i> Bulk Upload
            </a>
            <?php if (in_array('vendor.create', session('permissions') ?? [])): ?>
                <a href="<?= site_url('vendors/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Vendor
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('vendors') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-truck bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $totalCount ?? count($vendors) ?></div>
            <div class="bt-stat-label">Total Vendors</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('vendors?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $activeCount ?? 0 ?></div>
            <div class="bt-stat-label">Active Vendors</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($totalCount) && $totalCount > 0) ? round(($activeCount / $totalCount) * 100) : 100 ?>%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('vendors?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-ban bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $inactiveCount ?? 0 ?></div>
            <div class="bt-stat-label">Inactive Vendors</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($totalCount) && $totalCount > 0) ? round(($inactiveCount / $totalCount) * 100) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Vendors</h3>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('vendors') ?>">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, Phone or City..."
                        value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">-- All Status --</option>
                        <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="<?= site_url('vendors') ?>" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                </div>
            </div>
            <!-- Preserve sorting in filter form -->
            <input type="hidden" name="sort_by" value="<?= esc($filters['sort_by'] ?? '') ?>">
            <input type="hidden" name="sort_order" value="<?= esc($filters['sort_order'] ?? '') ?>">
        </form>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-list me-1"></i> List of Vendors</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <?php
                    $currentSortBy = $filters['sort_by'] ?? 'vendors.id';
                    $currentSortOrder = $filters['sort_order'] ?? 'DESC';

                    function getSortLink($field, $currentSortBy, $currentSortOrder)
                    {
                        $newOrder = ($currentSortBy == $field && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
                        $params = $_GET;
                        $params['sort_by'] = $field;
                        $params['sort_order'] = $newOrder;
                        unset($params['page']); // Reset page when sorting
                        return site_url('vendors?' . http_build_query($params));
                    }

                    function getSortIcon($field, $currentSortBy, $currentSortOrder)
                    {
                        if ($currentSortBy != $field)
                            return '<i class="fas fa-sort text-muted ms-1"></i>';
                        return $currentSortOrder == 'ASC' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>';
                    }
                    ?>
                    <tr>
                        <th><a href="<?= getSortLink('vendors.id', $currentSortBy, $currentSortOrder) ?>"
                                class="text-dark text-decoration-none">ID
                                <?= getSortIcon('vendors.id', $currentSortBy, $currentSortOrder) ?></a></th>
                        <th><a href="<?= getSortLink('vendors.name', $currentSortBy, $currentSortOrder) ?>"
                                class="text-dark text-decoration-none">Vendor Name (City)
                                <?= getSortIcon('vendors.name', $currentSortBy, $currentSortOrder) ?></a></th>
                        <th>Contact</th>
                        <th>GSTIN</th>
                        <th class="text-end"><a
                                href="<?= getSortLink('pending_balance', $currentSortBy, $currentSortOrder) ?>"
                                class="text-dark text-decoration-none">Pending Balance
                                <?= getSortIcon('pending_balance', $currentSortBy, $currentSortOrder) ?></a></th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorTableBody">
                    <?php if (!empty($vendors)): ?>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold">#<?= $vendor['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="bt-avatar"><?= esc(strtoupper(mb_substr($vendor['name'], 0, 1))) ?></span>
                                        <div>
                                            <strong><a href="<?= site_url('vendors/view/' . $vendor['id']) ?>"
                                                    class="text-dark"><?= esc($vendor['name']) ?></a></strong>
                                            <?php if ($vendor['city']): ?>
                                                <span class="badge bg-light text-dark border ms-1"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($vendor['city']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($vendor['contact_person'])): ?>
                                                <div class="small text-muted"><i class="far fa-user me-1"></i><?= esc($vendor['contact_person']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['phone'])): ?>
                                        <a href="tel:<?= esc($vendor['phone']) ?>" class="text-decoration-none fw-semibold"><i class="fas fa-phone-alt text-success me-1"></i><?= esc($vendor['phone']) ?></a><br>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                    <?php if (!empty($vendor['email'])): ?>
                                        <small class="text-muted"><i class="far fa-envelope me-1"></i><?= esc($vendor['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['gstin'])): ?>
                                        <code><?= esc($vendor['gstin']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <span class="<?= $vendor['pending_balance'] >= 0 ? 'text-danger' : 'text-success' ?>">
                                        ₹<?= number_format(abs($vendor['pending_balance']), 2) ?>
                                        <?= $vendor['pending_balance'] >= 0 ? 'Payable' : 'Advance' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $vendor['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($vendor['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('vendors/view/' . $vendor['id']) ?>" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('vendors/edit/' . $vendor['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('vendors/delete/' . $vendor['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noRecordsRow">
                            <td colspan="7" class="text-center py-4 text-muted">No vendors found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($has_more)): ?>
            <div class="text-center p-3" id="loadMoreContainer">
                <button type="button" class="btn btn-primary" id="btnLoadMore" data-page="2">
                    View More <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnLoadMore = document.getElementById('btnLoadMore');
        if (btnLoadMore) {
            btnLoadMore.addEventListener('click', function () {
                const page = this.getAttribute('data-page');
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const tbody = document.getElementById('vendorTableBody');
                            data.data.forEach(vendor => {
                                const pendingBalance = parseFloat(vendor.pending_balance);
                                const balanceType = pendingBalance >= 0 ? 'Payable' : 'Advance';
                                const balanceClass = pendingBalance >= 0 ? 'text-danger' : 'text-success';
                                const formattedBalance = '₹' + Math.abs(pendingBalance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + balanceType;

                                const firstLetter = (vendor.name || 'V').charAt(0).toUpperCase();
                                const row = `
                                <tr>
                                    <td class="text-center text-muted fw-bold">#${vendor.id}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bt-avatar">${firstLetter}</span>
                                            <div>
                                                <strong><a href="<?= site_url('vendors/view/') ?>${vendor.id}" class="text-dark">${vendor.name}</a></strong>
                                                ${vendor.city ? `<span class="badge bg-light text-dark border ms-1"><i class="fas fa-map-marker-alt text-danger me-1"></i>${vendor.city}</span>` : ''}
                                                ${vendor.contact_person ? `<div class="small text-muted"><i class="far fa-user me-1"></i>${vendor.contact_person}</div>` : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        ${vendor.phone ? `<a href="tel:${vendor.phone}" class="text-decoration-none fw-semibold"><i class="fas fa-phone-alt text-success me-1"></i>${vendor.phone}</a><br>` : '-<br>'}
                                        ${vendor.email ? `<small class="text-muted"><i class="far fa-envelope me-1"></i>${vendor.email}</small>` : ''}
                                    </td>
                                    <td>${vendor.gstin ? `<code>${vendor.gstin}</code>` : '<span class="text-muted small">N/A</span>'}</td>
                                    <td class="text-end fw-bold ${balanceClass}">
                                        ${formattedBalance}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-${vendor.status === 'active' ? 'success' : 'danger'}">
                                            ${vendor.status.charAt(0).toUpperCase() + vendor.status.slice(1)}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('vendors/view/') ?>${vendor.id}" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('vendors/edit/') ?>${vendor.id}" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('vendors/delete/') ?>${vendor.id}" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                                tbody.insertAdjacentHTML('beforeend', row);
                            });

                            if (data.has_more) {
                                this.setAttribute('data-page', parseInt(page) + 1);
                                this.disabled = false;
                                this.innerHTML = 'View More <i class="fas fa-chevron-down"></i>';
                            } else {
                                this.closest('#loadMoreContainer').remove();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.disabled = false;
                        this.innerHTML = 'View More <i class="fas fa-chevron-down"></i>';
                        alert('Failed to load more vendors.');
                    });
            });
        }
    });
</script>
<?= $this->endSection() ?>