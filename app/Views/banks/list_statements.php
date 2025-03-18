<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h1 class="mt-4">List of Transactions</h1>
<a href="/banks/add-transaction" class="btn btn-primary mb-3">Add New Transaction</a>

<form method="get" action="<?= base_url('/banks/list-statements') ?>" class="row mb-4">
    <div class="col-md-3">
        <label for="date_range" class="form-label">Select Date Range</label>
        <select id="date_range" class="form-select">
            <option value="">Select Range</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="this_week">This Week</option>
            <option value="last_week">Last Week</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
            <option value="last_year">Last Year</option>
            <option value="this_financial_year">This Financial Year</option>
            <option value="last_financial_year">Last Financial Year</option>
            <option value="all">All</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="start_date" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
    </div>
    <div class="col-md-3">
        <label for="end_date" class="form-label">End Date</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
    </div>
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

<table id="statementsTable" class="table table-striped">
    <thead>
        <tr>
            <th>Transaction Date</th>
            <th>Bank Name</th>
            <th>Transaction Type</th>
            <th>Amount</th>
            <th>Depositor / Receiver Name</th>
            <th>Type of Payment</th>
            <th>Mode of Payment</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($statements as $statement): ?>
            <tr>
                <td><?= $statement['transaction_date'] ?></td>
                <td><?= $statement['bank_name'] ?></td>
                <td style="text-transform: capitalize;"><?= $statement['transaction_type'] ?></td>
                <td><?= $statement['amount'] ?></td>
                <td>
                    <?php if ($statement['transaction_type'] === 'credit'): ?>
                        <?= $statement['depositor_name'] ?>
                    <?php elseif ($statement['transaction_type'] === 'debit'): ?>
                        <?= $statement['receiver_name'] ?>
                    <?php endif; ?>
                </td>
                <td><?= $statement['type_of_payment'] ?></td>
                <td><?= $statement['mode_of_payment'] ?></td>
                <td>
                    <a href="<?= base_url('/banks/view-statement/' . $statement['id']) ?>" class="btn btn-info btn-sm">View</a>
                    <a href="<?= base_url('/banks/edit-statement/' . $statement['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>  

<script>
    $('#date_range').on('change', function () {
        const dateRange = $(this).val();
        const startDateInput = $('#start_date');
        const endDateInput = $('#end_date');
        
        const today = new Date();
        let startDate, endDate;
        
        const currentYear = today.getUTCFullYear();
        const financialYearStartMonth = 3;  // April (0-based index for months)

        switch (dateRange) {
            case 'today':
                startDate = endDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), today.getUTCDate()));
                break;
            case 'yesterday':
                startDate = endDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), today.getUTCDate() - 1));
                break;
            case 'this_week':
                startDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), today.getUTCDate() - today.getUTCDay()));
                endDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), startDate.getUTCDate() + 6));
                break;
            case 'last_week':
                startDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), today.getUTCDate() - today.getUTCDay() - 7));
                endDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), startDate.getUTCDate() + 6));
                break;
            case 'this_month':
                startDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), 1));
                endDate = new Date(Date.UTC(currentYear, today.getUTCMonth() + 1, 0));
                break;
            case 'last_month':
                startDate = new Date(Date.UTC(currentYear, today.getUTCMonth() - 1, 1));
                endDate = new Date(Date.UTC(currentYear, today.getUTCMonth(), 0));
                break;
            case 'this_year':
                startDate = new Date(Date.UTC(currentYear, 0, 1));
                endDate = new Date(Date.UTC(currentYear, 11, 31));
                break;
            case 'last_year':
                startDate = new Date(Date.UTC(currentYear - 1, 0, 1));
                endDate = new Date(Date.UTC(currentYear - 1, 11, 31));
                break;
            case 'this_financial_year':
                if (today.getUTCMonth() >= financialYearStartMonth) { 
                    // Current financial year starts in this year
                    startDate = new Date(Date.UTC(currentYear, financialYearStartMonth, 1));
                    endDate = new Date(Date.UTC(currentYear + 1, financialYearStartMonth - 1, 31)); // March 31 of next year
                } else {
                    // Current financial year started last year
                    startDate = new Date(Date.UTC(currentYear - 1, financialYearStartMonth, 1));
                    endDate = new Date(Date.UTC(currentYear, financialYearStartMonth - 1, 31)); // March 31 of this year
                }
                break;
            case 'last_financial_year':
                if (today.getUTCMonth() >= financialYearStartMonth) { 
                    // Last financial year was from last year to current year
                    startDate = new Date(Date.UTC(currentYear - 1, financialYearStartMonth, 1));
                    endDate = new Date(Date.UTC(currentYear, financialYearStartMonth - 1, 31)); // March 31 of this year
                } else {
                    // Last financial year was two years ago to last year
                    startDate = new Date(Date.UTC(currentYear - 2, financialYearStartMonth, 1));
                    endDate = new Date(Date.UTC(currentYear - 1, financialYearStartMonth - 1, 31)); // March 31 of last year
                }
                break;
            case 'all':
                startDate = endDate = null;
                break;
        }

        if (startDate && endDate) {
            startDateInput.val(startDate.toISOString().split('T')[0]);
            endDateInput.val(endDate.toISOString().split('T')[0]);
        } else {
            startDateInput.val('');
            endDateInput.val('');
        }
    });
</script>
