<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Agent Commission Payments<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-hand-holding-usd text-primary me-2"></i>Agent Commission Payments</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('agent-payments') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('agent-payments/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Record Agent Payment
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-6 col-sm-6">
        <a href="<?= site_url('agent-payments') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($total_count) ?></div>
            <div class="bt-stat-label">Total Commission Disbursements</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-6 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($total_amount ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Commission Paid Out</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Agent Payments</h3>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Agent</label>
                <select name="agent_id" class="form-select select2">
                    <option value="">All Agents</option>
                    <?php foreach($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>" <?= (strval($filters['agent_id'] ?? '') === strval($agent['id'])) ? 'selected' : '' ?>><?= esc($agent['agent_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option value="">All Modes</option>
                    <option value="Cash" <?= (($filters['payment_mode'] ?? '') == 'Cash') ? 'selected' : '' ?>>Cash</option>
                    <option value="Bank Transfer" <?= (($filters['payment_mode'] ?? '') == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="Cheque" <?= (($filters['payment_mode'] ?? '') == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
                    <option value="UPI" <?= (($filters['payment_mode'] ?? '') == 'UPI') ? 'selected' : '' ?>>UPI</option>
                    <option value="Other" <?= (($filters['payment_mode'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('agent-payments') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
            <div class="col-12 mt-2">
                <input type="text" name="search" class="form-control" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Search by payment number or reference...">
            </div>
        </form>
    </div>
</div>

<!-- Payments List -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Payment #</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 180px;">Agent</th>
                        <th style="min-width: 130px;">Phone</th>
                        <th style="min-width: 120px;">Payment Mode</th>
                        <th style="min-width: 130px;">Reference</th>
                        <th class="text-end" style="min-width: 120px;">Amount</th>
                        <th class="text-end" style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="payments-tbody">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-hand-holding-usd fa-2x text-muted mb-2 d-block"></i>
                                No commission payments found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= view('agent_payments/rows', ['payments' => $payments]) ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($has_more): ?>
    <div class="card-footer text-center bg-white border-top">
        <button id="load-more-btn" class="btn btn-outline-primary" data-offset="<?= $offset + $limit ?>">
            Load More Payments <i class="fas fa-chevron-down ms-1"></i>
        </button>
    </div>
    <?php endif; ?>
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
                alert('Could not load more records.');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
