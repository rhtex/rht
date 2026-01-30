<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item active">Agent Payments</li>
            </ol>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter me-2"></i>Filters</h3>
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Agent</label>
                    <select name="agent_id" class="form-select select2">
                        <option value="">All Agents</option>
                        <?php foreach($agents as $agent): ?>
                            <option value="<?= $agent['id'] ?>" <?= (strval($filters['agent_id']) === strval($agent['id'])) ? 'selected' : '' ?>><?= esc($agent['agent_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mode</label>
                    <select name="payment_mode" class="form-select">
                        <option value="">All Modes</option>
                        <option value="Cash" <?= ($filters['payment_mode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                        <option value="Bank Transfer" <?= ($filters['payment_mode'] == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="Cheque" <?= ($filters['payment_mode'] == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
                        <option value="UPI" <?= ($filters['payment_mode'] == 'UPI') ? 'selected' : '' ?>>UPI</option>
                        <option value="Other" <?= ($filters['payment_mode'] == 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="<?= site_url('agent-payments') ?>" class="btn btn-secondary w-100">Reset</a>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">Search Payment # / Ref #</label>
                    <input type="text" name="search" class="form-control" value="<?= esc($filters['search']) ?>" placeholder="Search by number or reference...">
                </div>
            </form>
        </div>
    </div>

    <!-- Payments List -->
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Agent Commission Payments (Total: <?= $total_count ?>)</h3>
            <div class="card-tools ms-auto">
                <a href="<?= site_url('agent-payments/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Record Payment
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Agent</th>
                            <th>Phone</th>
                            <th>Payment Mode</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payments-tbody">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payments found!</td>
                            </tr>
                        <?php else: ?>
                            <?= view('agent_payments/rows', ['payments' => $payments]) ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($has_more): ?>
        <div class="card-footer text-center">
            <button id="load-more-btn" class="btn btn-outline-primary" data-offset="<?= $offset + $limit ?>">
                View More <i class="fas fa-chevron-down ms-2"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#load-more-btn').on('click', function() {
        const btn = $(this);
        const offset = btn.data('offset');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

        // Get current filter params
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('offset', offset);

        $.ajax({
            url: '<?= site_url('agent-payments') ?>?' + urlParams.toString(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                if (html.trim()) {
                    $('#payments-tbody').append(html);
                    const newOffset = offset + <?= $limit ?>;
                    btn.data('offset', newOffset);
                    btn.prop('disabled', false).html(originalText);
                    
                    if (newOffset >= <?= $total_count ?>) {
                        btn.parent().hide();
                    }
                } else {
                    btn.parent().hide();
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Error loading more records.');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
