<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customers<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Customer Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if (in_array('customer.create', session('permissions') ?? [])): ?>
            <a href="<?= site_url('customers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New
                Customer</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Filters -->
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form method="get" action="<?= site_url('customers') ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, Phone or City..."
                            value="<?= esc($filters['search'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Agent</label>
                        <select name="agent_id" class="form-select">
                            <option value="">-- All Agents --</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= (($filters['agent_id'] ?? '') == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- All Status --</option>
                            <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active
                            </option>
                            <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
                                Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search"></i>
                                Filter</button>
                            <a href="<?= site_url('customers') ?>" class="btn btn-outline-secondary"
                                title="Clear Filters"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Preserve sorting in filter form -->
            <input type="hidden" name="sort_by" value="<?= esc($filters['sort_by'] ?? '') ?>">
            <input type="hidden" name="sort_order" value="<?= esc($filters['sort_order'] ?? '') ?>">
        </form>
    </div>
</div>

<div class="mb-3">
    <a href="<?= base_url('sample_customer.csv') ?>" class="btn btn-link"><i class="bi bi-download"></i> Download Sample CSV</a>
</div>
<div class="mb-3">
    <a href="<?= site_url('bulk-upload/form') ?>" class="btn btn-primary">
        <i class="fas fa-upload"></i> Bulk Upload Customers
    </a>
</div>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Customers</h3>
    </div>
    <div class="card-body">
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
                                <td><?= $customer['id'] ?></td>
                                <td>
                                    <strong><a href="<?= site_url('customers/view/' . $customer['id']) ?>"
                                            class="text-dark"><?= esc($customer['name']) ?></a></strong>
                                    <?php if ($customer['city']): ?>
                                        <small class="text-muted">(<?= esc($customer['city']) ?>)</small>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted"><?= esc($customer['contact_person']) ?></small>
                                </td>
                                <td>
                                    <?= esc($customer['phone'] ?: '-') ?><br>
                                    <small><?= esc($customer['email']) ?></small>
                                </td>
                                <td><code><?= esc($customer['gstin'] ?: 'N/A') ?></code></td>
                                <td
                                    class="text-end fw-bold <?= $customer['pending_balance'] >= 0 ? 'text-danger' : 'text-success' ?>">
                                    ₹<?= number_format(abs($customer['pending_balance']), 2) ?>
                                    <?= $customer['pending_balance'] >= 0 ? 'Dr' : 'Cr' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $customer['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($customer['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-warning"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('customers/view/' . $customer['id']) ?>"
                                            class="btn btn-info text-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('customers/delete/' . $customer['id']) ?>" class="btn btn-danger"
                                            onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noRecordsRow">
                            <td colspan="7" class="text-center">No customers found</td>
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

                                const row = `
                                <tr>
                                    <td>${customer.id}</td>
                                    <td>
                                        <strong><a href="<?= site_url('customers/view/') ?>${customer.id}" class="text-dark">${customer.name}</a></strong>
                                        ${customer.city ? `<small class="text-muted">(${customer.city})</small>` : ''}
                                        <br>
                                        <small class="text-muted">${customer.contact_person || ''}</small>
                                    </td>
                                    <td>
                                        ${customer.phone || '-'}<br>
                                        <small>${customer.email || ''}</small>
                                    </td>
                                    <td><code>${customer.gstin || 'N/A'}</code></td>
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
                                            <a href="<?= site_url('customers/edit/') ?>${customer.id}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('customers/view/') ?>${customer.id}" class="btn btn-info text-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('customers/delete/') ?>${customer.id}" class="btn btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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