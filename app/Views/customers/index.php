<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customers<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-users text-primary me-2"></i>Customer Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= base_url('sample_customer.csv') ?>" class="btn btn-sm btn-outline-secondary" title="Download Sample CSV">
                <i class="fas fa-download me-1"></i> Sample CSV
            </a>
            <a href="<?= site_url('bulk-upload/form') ?>" class="btn btn-sm btn-outline-primary" title="Bulk Upload Customers">
                <i class="fas fa-file-upload me-1"></i> Bulk Upload
            </a>
            <?php if (in_array('customer.create', session('permissions') ?? [])): ?>
                <a href="<?= site_url('customers/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Customer
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
        <a href="<?= site_url('customers') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-users bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $totalCount ?? count($customers) ?></div>
            <div class="bt-stat-label">Total Customers</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('customers?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-user-check bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $activeCount ?? 0 ?></div>
            <div class="bt-stat-label">Active Customers</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($totalCount) && $totalCount > 0) ? round(($activeCount / $totalCount) * 100) : 100 ?>%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('customers?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-user-slash bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $inactiveCount ?? 0 ?></div>
            <div class="bt-stat-label">Inactive Customers</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($totalCount) && $totalCount > 0) ? round(($inactiveCount / $totalCount) * 100) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Customers</h3>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('customers') ?>">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, Phone or City..."
                        value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Agent</label>
                    <select name="agent_id" class="form-select select2">
                        <option value="">-- All Agents --</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id'] ?>" <?= (($filters['agent_id'] ?? '') == $agent['id']) ? 'selected' : '' ?>>
                                <?= esc($agent['agent_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">-- All Status --</option>
                        <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="<?= site_url('customers') ?>" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
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
        <h3 class="card-title fw-bold"><i class="fas fa-list me-1"></i> List of Customers</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <?php
                    $currentSortBy = $filters['sort_by'] ?? 'customers.id';
                    $currentSortOrder = $filters['sort_order'] ?? 'DESC';

                    function getSortLink($field, $currentSortBy, $currentSortOrder)
                    {
                        $newOrder = ($currentSortBy == $field && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
                        $params = $_GET;
                        $params['sort_by'] = $field;
                        $params['sort_order'] = $newOrder;
                        unset($params['page']); // Reset page when sorting
                        return site_url('customers?' . http_build_query($params));
                    }

                    function getSortIcon($field, $currentSortBy, $currentSortOrder)
                    {
                        if ($currentSortBy != $field)
                            return '<i class="fas fa-sort text-muted ms-1"></i>';
                        return $currentSortOrder == 'ASC' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>';
                    }
                    ?>
                    <tr>
                        <th><a href="<?= getSortLink('customers.id', $currentSortBy, $currentSortOrder) ?>"
                                class="text-dark text-decoration-none">ID
                                <?= getSortIcon('customers.id', $currentSortBy, $currentSortOrder) ?></a></th>
                        <th><a href="<?= getSortLink('customers.name', $currentSortBy, $currentSortOrder) ?>"
                                class="text-dark text-decoration-none">Customer Name (City)
                                <?= getSortIcon('customers.name', $currentSortBy, $currentSortOrder) ?></a></th>
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
                <tbody id="customerTableBody">
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold">#<?= $customer['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="bt-avatar"><?= esc(strtoupper(mb_substr($customer['name'], 0, 1))) ?></span>
                                        <div>
                                            <strong><a href="<?= site_url('customers/view/' . $customer['id']) ?>"
                                                    class="text-dark"><?= esc($customer['name']) ?></a></strong>
                                            <?php if ($customer['city']): ?>
                                                <span class="badge bg-light text-dark border ms-1"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($customer['city']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($customer['contact_person'])): ?>
                                                <div class="small text-muted"><i class="far fa-user me-1"></i><?= esc($customer['contact_person']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($customer['phone'])): ?>
                                        <a href="tel:<?= esc($customer['phone']) ?>" class="text-decoration-none fw-semibold"><i class="fas fa-phone-alt text-success me-1"></i><?= esc($customer['phone']) ?></a><br>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                    <?php if (!empty($customer['email'])): ?>
                                        <small class="text-muted"><i class="far fa-envelope me-1"></i><?= esc($customer['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($customer['gstin'])): ?>
                                        <code><?= esc($customer['gstin']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <span class="<?= $customer['pending_balance'] >= 0 ? 'text-danger' : 'text-success' ?>">
                                        ₹<?= number_format(abs($customer['pending_balance']), 2) ?>
                                        <?= $customer['pending_balance'] >= 0 ? 'Dr' : 'Cr' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $customer['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($customer['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('customers/view/' . $customer['id']) ?>" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('customers/delete/' . $customer['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noRecordsRow">
                            <td colspan="7" class="text-center py-4 text-muted">No customers found</td>
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
                            const tbody = document.getElementById('customerTableBody');
                            data.data.forEach(customer => {
                                const pendingBalance = parseFloat(customer.pending_balance);
                                const balanceType = pendingBalance >= 0 ? 'Dr' : 'Cr';
                                const balanceClass = pendingBalance >= 0 ? 'text-danger' : 'text-success';
                                const formattedBalance = '₹' + Math.abs(pendingBalance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + balanceType;

                                const firstLetter = (customer.name || 'C').charAt(0).toUpperCase();
                                const row = `
                                <tr>
                                    <td class="text-center text-muted fw-bold">#${customer.id}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bt-avatar">${firstLetter}</span>
                                            <div>
                                                <strong><a href="<?= site_url('customers/view/') ?>${customer.id}" class="text-dark">${customer.name}</a></strong>
                                                ${customer.city ? `<span class="badge bg-light text-dark border ms-1"><i class="fas fa-map-marker-alt text-danger me-1"></i>${customer.city}</span>` : ''}
                                                ${customer.contact_person ? `<div class="small text-muted"><i class="far fa-user me-1"></i>${customer.contact_person}</div>` : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        ${customer.phone ? `<a href="tel:${customer.phone}" class="text-decoration-none fw-semibold"><i class="fas fa-phone-alt text-success me-1"></i>${customer.phone}</a><br>` : '-<br>'}
                                        ${customer.email ? `<small class="text-muted"><i class="far fa-envelope me-1"></i>${customer.email}</small>` : ''}
                                    </td>
                                    <td>${customer.gstin ? `<code>${customer.gstin}</code>` : '<span class="text-muted small">N/A</span>'}</td>
                                    <td class="text-end fw-bold ${balanceClass}">
                                        ${formattedBalance}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-${customer.status === 'active' ? 'success' : 'danger'}">
                                            ${customer.status.charAt(0).toUpperCase() + customer.status.slice(1)}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('customers/view/') ?>${customer.id}" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('customers/edit/') ?>${customer.id}" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('customers/delete/') ?>${customer.id}" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
                        alert('Failed to load more customers.');
                    });
            });
        }
    });
</script>
<?= $this->endSection() ?>