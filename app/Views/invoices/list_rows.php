<?php if (!empty($invoices)): ?>
    <?php foreach ($invoices as $invoice): ?>
        <?php
        $statusColors = [
            'Draft'          => 'secondary',
            'Open'           => 'primary',
            'Paid'           => 'success',
            'Partially Paid' => 'info',
            'Overdue'        => 'danger',
            'Void'           => 'dark'
        ];
        $color = $statusColors[$invoice['status']] ?? 'secondary';
        ?>
        <tr class="invoice-row cursor-pointer" 
            data-id="<?= $invoice['id'] ?>"
            id="inv-row-<?= $invoice['id'] ?>"
            style="cursor: pointer;">
            <td class="text-truncate">
                <div class="fw-bold text-primary text-truncate"><?= esc($invoice['invoice_number']) ?></div>
                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></small>
                <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($invoice['status']) ?></span>
            </td>
            <td class="text-truncate">
                <div class="fw-semibold text-dark text-truncate" title="<?= esc($invoice['customer_name']) ?>">
                    <?= esc($invoice['customer_name']) ?>
                </div>
                <?php if (!empty($invoice['customer_city'])): ?>
                    <small class="text-muted text-truncate d-block"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($invoice['customer_city']) ?></small>
                <?php endif; ?>
                <?php if (!empty($invoice['so_number'])): ?>
                    <span class="badge bg-light text-secondary border small" style="font-size:0.65rem;">SO: <?= esc($invoice['so_number']) ?></span>
                <?php endif; ?>
            </td>
            <td class="text-end text-truncate">
                <div class="fw-bold text-dark text-truncate">₹<?= number_format($invoice['total_amount'], 2) ?></div>
                <?php if ($invoice['balance'] > 0): ?>
                    <small class="text-danger fw-semibold d-block text-truncate">Bal: ₹<?= number_format($invoice['balance'], 2) ?></small>
                <?php else: ?>
                    <small class="text-success d-block"><i class="fas fa-check me-1"></i>Paid</small>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>?mode=single" 
                   class="btn btn-sm btn-outline-secondary p-1" 
                   title="Open Standalone Single Page View"
                   onclick="event.stopPropagation();">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
