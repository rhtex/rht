<?php foreach ($credits as $c): ?>
    <?php
    $statusColors = [
        'Open'    => 'primary',
        'Unused'  => 'primary',
        'Partial' => 'warning',
        'Closed'  => 'success',
        'Used'    => 'success',
        'Void'    => 'dark',
        'Draft'   => 'secondary',
    ];
    $color = $statusColors[$c['status']] ?? 'secondary';
    ?>
    <tr class="credit-row cursor-pointer" 
        data-id="<?= $c['id'] ?>"
        id="credit-row-<?= $c['id'] ?>"
        style="cursor: pointer;">
        <td class="fw-semibold">
            <span class="text-primary"><?= esc($c['reference_no']) ?></span>
            <div class="small text-muted">
                <?= !empty($c['credit_note_date']) ? date('d-m-Y', strtotime($c['credit_note_date'])) : '' ?>
                <?php if (!empty($c['bill_number'])): ?>
                    <span class="badge bg-light text-dark border ms-1">Bill #<?= esc($c['bill_number']) ?></span>
                <?php endif; ?>
            </div>
        </td>
        <td>
            <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" title="<?= esc($c['vendor_name'] ?? 'N/A') ?>">
                <?= esc($c['vendor_name'] ?? 'N/A') ?>
            </div>
            <span class="badge bg-<?= $color ?> badge-sm"><?= esc($c['status']) ?></span>
        </td>
        <td class="text-end">
            <div class="fw-bold">₹<?= number_format((float) ($c['amount'] ?? 0), 2) ?></div>
            <?php if ((float) ($c['balance'] ?? 0) > 0.01): ?>
                <small class="text-danger">Bal: ₹<?= number_format((float) $c['balance'], 2) ?></small>
            <?php else: ?>
                <small class="text-success"><i class="fas fa-check-circle me-1"></i>Settled</small>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <i class="fas fa-chevron-right text-muted small"></i>
        </td>
    </tr>
<?php endforeach; ?>
