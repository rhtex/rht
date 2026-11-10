<?php if (!empty($bills)): ?>
    <?php foreach ($bills as $b): ?>
        <?php
        $statusColors = [
            'Draft'          => 'secondary',
            'Open'           => 'primary',
            'Paid'           => 'success',
            'Partially Paid' => 'info',
            'Overdue'        => 'danger',
            'Void'           => 'dark'
        ];
        $color = $statusColors[$b['status']] ?? 'secondary';
        ?>
        <tr class="bill-row cursor-pointer" 
            data-id="<?= $b['id'] ?>"
            id="bill-row-<?= $b['id'] ?>"
            style="cursor: pointer;">
            <td class="text-truncate">
                <div class="fw-bold text-primary text-truncate"><?= esc($b['bill_number']) ?></div>
                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($b['bill_date'])) ?></small>
                <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($b['status']) ?></span>
            </td>
            <td class="text-truncate">
                <div class="fw-semibold text-dark text-truncate" title="<?= esc($b['vendor_name']) ?>"><?= esc($b['vendor_name']) ?></div>
                <small class="text-muted d-block text-truncate">
                    <?= !empty($b['vendor_city']) ? esc($b['vendor_city']) : '-' ?>
                </small>
            </td>
            <td class="text-end text-nowrap">
                <div class="fw-bold text-dark fs-6">₹<?= number_format($b['total_amount'], 2) ?></div>
                <small class="text-danger d-block">Bal: ₹<?= number_format($b['balance'], 2) ?></small>
            </td>
            <td class="text-center">
                <a href="<?= site_url('bills/view/' . $b['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
                    <i class="fas fa-external-link-alt text-muted"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

