<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waiting Payment Report - <?= esc($agent['agent_name']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            color: #1a1a1a; 
            margin: 0; 
            padding: 40px;
            line-height: 1.5;
            background-color: #fff;
        }
        
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .no-print-header {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid #dee2e6;
            border-radius: 8px;
        }

        .btn {
            padding: 10px 24px;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #0d6efd; color: white; margin-right: 10px; }
        .btn-print:hover { background: #0b5ed7; }
        .btn-close { background: #6c757d; color: white; }
        .btn-close:hover { background: #5c636a; }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .company-info h1 {
            margin: 0;
            color: #0d6efd;
            font-size: 28px;
            letter-spacing: -0.5px;
        }
        .company-info p { margin: 5px 0; color: #666; font-size: 14px; }

        .report-title {
            text-align: right;
        }
        .report-title h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
            text-transform: uppercase;
        }
        .report-title p {
            margin: 5px 0;
            color: #e67e22;
            font-weight: bold;
            font-size: 14px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .detail-box {
            padding: 20px;
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .detail-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 13px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 1px;
        }
        .detail-box p { margin: 8px 0; font-size: 15px; }
        .detail-box strong { color: #333; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .text-right { text-align: right; }
        
        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 350px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
        }
        .totals-row.grand-total {
            border-top: 2px solid #dee2e6;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: 700;
            font-size: 18px;
            color: #28a745;
        }

        .signatures {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            color: #999;
            font-size: 12px;
            border-top: 1px dashed #eee;
            padding-top: 20px;
        }

        @media print {
            .no-print-header { display: none; }
            body { padding: 0; }
            .detail-box { border: 1px solid #ddd; }
            th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="no-print-header">
            <button onclick="window.print()" class="btn btn-print">Print Liquidation Report</button>
            <button onclick="window.close()" class="btn btn-close">Close Preview</button>
        </div>

        <header>
            <div class="company-info">
                <h1><?= esc(get_setting('app_name', 'RASIDEV HR')) ?></h1>
                <p><?= esc(get_setting('address', 'Business Park Center, Level 4')) ?></p>
                <p>Phone: <?= esc(get_setting('phone', '+91 98765 43210')) ?> | Email: <?= esc(get_setting('email', 'accounts@rasidev.com')) ?></p>
            </div>
            <div class="report-title">
                <h2>Waiting Payment</h2>
                <p>LIQUIDATION PREVIEW #<?= date('YmdHi') ?></p>
                <div style="font-size: 13px; color: #888; margin-top: 5px;">Date: <?= date('d M, Y') ?></div>
            </div>
        </header>

        <div class="details-grid">
            <div class="detail-box">
                <h3>Agent Information</h3>
                <p><strong>Name:</strong> <?= esc($agent['agent_name']) ?></p>
                <p><strong>Phone:</strong> <?= esc($agent['phone_number']) ?></p>
                <p><strong>Commission Rate:</strong> <?= number_format($agent['commission_percentage'], 2) ?>%</p>
            </div>
            <div class="detail-box">
                <h3>Liquidation Summary</h3>
                <p><strong>Total Invoices:</strong> <?= count($invoices) ?></p>
                <p><strong>Generated By:</strong> <?= esc(session('name')) ?></p>
                <p><strong>Status:</strong> <span style="color: #e67e22;">Pending Payment</span></p>
            </div>
        </div>

        <div style="margin-top: 10px; font-size: 13px; color: #666; font-style: italic;">
            * Note: Commission is calculated on the subtotal amount (excluding taxes and other charges).
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Invoice # & Customer</th>
                    <th>Date</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Tax Amt</th>
                    <th class="text-right">Inv. Total</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Comm. Amt</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalInvoices = 0;
                $totalComm = 0;
                foreach ($invoices as $inv): 
                    $totalInvoices += $inv['total_amount'];
                    $totalComm += $inv['agent_commission_amount'];
                ?>
                <tr>
                    <td style="font-weight: 600;">
                        <?= esc($inv['invoice_number']) ?><br>
                        <small style="color: #666; font-weight: normal;"><?= esc($inv['customer_name']) ?></small>
                    </td>
                    <td><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
                    <td class="text-right">₹<?= number_format($inv['subtotal'], 2) ?></td>
                    <td class="text-right">₹<?= number_format($inv['tax_amount'], 2) ?></td>
                    <td class="text-right">₹<?= number_format($inv['total_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format($inv['agent_commission_percent'], 2) ?>%</td>
                    <td class="text-right" style="font-weight: 600;">₹<?= number_format($inv['agent_commission_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row">
                    <span>Total Sales Value:</span>
                    <span>₹<?= number_format($totalInvoices, 2) ?></span>
                </div>
                <div class="totals-row">
                    <span>Number of Items:</span>
                    <span><?= count($invoices) ?> Items</span>
                </div>
                <div class="totals-row grand-total">
                    <span>Total To Be Paid:</span>
                    <span>₹<?= number_format($totalComm, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig-box">
                Agent Signature
            </div>
            <div class="sig-box">
                Authorized Associate
            </div>
        </div>

        <div class="footer">
            <p>This report is a preliminary liquidation document and does not constitute a final receipt.</p>
            <p>&copy; <?= date('Y') ?> <?= esc(get_setting('app_name', 'RASIDEV HR')) ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
