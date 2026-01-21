<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= $salary['id'] ?> | <?= get_setting('app_name', 'RasiDev HR') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .payslip-container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .company-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .table-borderless td { padding: 5px 10px; }
        .section-title { background: #f8f9fa; padding: 10px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>

<div class="payslip-container">
    <div class="company-header">
        <h2><?= get_setting('org_name', 'RasiDev HR System') ?></h2>
        <p><?= get_setting('org_address', '123 Corporate Blvd, Business City') ?></p>
        <h4>Payslip for <?= date('F Y', strtotime($salary['salary_month'])) ?></h4>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Employee ID:</td>
                    <td><?= $salary['employee_id'] ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Name:</td>
                    <td><?= $salary['first_name'] . ' ' . $salary['last_name'] ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Mobile:</td>
                    <td><?= $salary['mobile_number'] ?></td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Payslip #:</td>
                    <td><?= $salary['id'] ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Date:</td>
                    <td><?= date('d M Y') ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Pay Period:</td>
                    <td><?= date('d M Y', strtotime($salary['salary_month'])) ?> - <?= date('d M Y', strtotime(date('Y-m-t', strtotime($salary['salary_month'])))) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="section-title">Attendance Summary</div>
    <div class="row mb-3">
        <div class="col-3 text-center">
            <div class="border p-3">
                <h5 class="text-success"><?= $presentDays ?></h5>
                <small>Present Days</small>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="border p-3">
                <h5 class="text-danger"><?= $absentDays ?></h5>
                <small>Absent Days</small>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="border p-3">
                <h5 class="text-warning"><?= $leaveDays ?></h5>
                <small>Leave Days</small>
            </div>
        </div>
        <div class="col-3 text-center">
            <div class="border p-3 bg-light">
                <h5 class="text-primary"><?= $doublePayDays ?></h5>
                <small>Double Pay Days</small>
            </div>
        </div>
    </div>
    <div class="text-muted small mb-3">
        Total Days in Month: <?= $daysInMonth ?> | Per Day Rate: ₹<?= number_format($perDayRate, 2) ?>
    </div>

    <!-- Earnings and Deductions -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Earnings</th>
                <th class="text-end">Amount (₹)</th>
                <th>Deductions</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="text-end"><?= number_format($salary['basic_salary'], 2) ?></td>
                <td>Absent Deduction (<?= $absentDays ?> days)</td>
                <td class="text-end"><?= number_format($absentDeduction, 2) ?></td>
            </tr>
            <tr>
                <td>Double Pay Allowance (<?= $doublePayDays ?> days)</td>
                <td class="text-end"><?= number_format($extraPay, 2) ?></td>
                <td>Loan Deduction</td>
                <td class="text-end"><?= number_format($loanDeduction, 2) ?></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            
            <tr class="fw-bold table-secondary">
                <td>Total Earnings</td>
                <td class="text-end"><?= number_format($salary['basic_salary'] + $extraPay, 2) ?></td>
                <td>Total Deductions</td>
                <td class="text-end"><?= number_format($salary['deductions'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Loan Details (if any) -->
    <?php if (!empty($loans) && $loanDeduction > 0): ?>
    <div class="section-title">Loan Deduction Details</div>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Loan ID</th>
                <th>Loan Amount</th>
                <th>Monthly Deduction</th>
                <th>Remaining</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan): ?>
            <tr>
                <td><?= $loan['id'] ?></td>
                <td>₹<?= number_format($loan['loan_amount'], 2) ?></td>
                <td>₹<?= number_format($loan['monthly_deduction'], 2) ?></td>
                <td>₹<?= number_format($loan['remaining_amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="alert alert-success d-inline-block px-5">
                <h3>Net Salary: ₹<?= number_format($salary['net_salary'], 2) ?></h3>
                <small>Basic (₹<?= number_format($salary['basic_salary'], 2) ?>) + Allowances (₹<?= number_format($extraPay, 2) ?>) - Deductions (₹<?= number_format($salary['deductions'], 2) ?>)</small>
            </div>
        </div>
    </div>

    <div class="mt-5 text-center text-muted">
        <p>This is a computer-generated document. No signature is required.</p>
        <button onclick="window.print()" class="btn btn-secondary d-print-none">Print Payslip</button>
        <a href="<?= site_url('salaries') ?>" class="btn btn-outline-secondary d-print-none">Back to Salaries</a>
    </div>
</div>

</body>
</html>
