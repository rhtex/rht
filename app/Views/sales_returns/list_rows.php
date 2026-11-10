<?php if (!empty($returns)): ?>
    <?php foreach ($returns as $r): ?>
        <tr class="return-row cursor-pointer" 
            data-id="<?= $r['id'] ?>"
            id="return-row-<?= $r['id'] ?>"
            style="cursor: pointer;">
            <td class="text-truncate">
                <div class="fw-bold text-primary text-truncate"><?= esc($r['return_number']) ?></div>
                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($r['return_date'])) ?></small>
                <span class="badge text-bg-<?= ($r['status'] == 'Closed') ? 'success' : 'warning' ?>" style="font-size:0.68rem;"><?= esc($r['status']) ?></span>
                <?php if (!empty($r['invoice_number'])): ?>
                    <span class="badge bg-light text-muted border" style="font-size:0.65rem;">
                        INV: <?= esc($r['invoice_number']) ?>
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-truncate">
                <div class="fw-semibold text-dark text-truncate" title="<?= esc($r['customer_name']) ?>"><?= esc($r['customer_name']) ?></div>
                <small class="text-muted d-block text-truncate">
                    <?= !empty($r['reason']) ? esc($r['reason']) : '-' ?>
                </small>
            </td>
            <td class="text-end text-nowrap">
                <div class="fw-bold text-dark fs-6">₹<?= number_format($r['total_amount'], 2) ?></div>
            </td>
            <td class="text-center">
                <a href="<?= site_url('sales_returns/view/' . $r['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
                    <i class="fas fa-external-link-alt text-muted"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

