<?php if (!empty($quotations)): ?>
    <?php foreach ($quotations as $q): ?>
        <?php
        $statusColors = [
            'Draft'     => 'secondary',
            'Sent'      => 'info',
            'Accepted'  => 'success',
            'Declined'  => 'danger',
            'Invoiced'  => 'primary',
            'Expired'   => 'dark'
        ];
        $color = $statusColors[$q['status']] ?? 'secondary';
        ?>
        <tr class="quotation-row cursor-pointer" 
            data-id="<?= $q['id'] ?>"
            id="quotation-row-<?= $q['id'] ?>"
            style="cursor: pointer;">
            <td class="text-truncate">
                <div class="fw-bold text-primary text-truncate"><?= esc($q['quotation_number']) ?></div>
                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($q['quotation_date'])) ?></small>
                <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($q['status']) ?></span>
                <?php if (!empty($q['converted_to_so_id']) || !empty($q['linked_so_id'])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.65rem;">
                        <i class="fas fa-shopping-cart me-1"></i>SO Converted
                    </span>
                <?php elseif (!empty($q['converted_to_invoice_id']) || !empty($q['linked_invoice_id'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                        <i class="fas fa-file-invoice me-1"></i>Invoiced
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-truncate">
                <div class="fw-semibold text-dark text-truncate" title="<?= esc($q['customer_name']) ?>"><?= esc($q['customer_name']) ?></div>
                <small class="text-muted d-block text-truncate">
                    <?= !empty($q['city']) ? esc($q['city']) : '-' ?>
                </small>
            </td>
            <td class="text-end text-nowrap">
                <div class="fw-bold text-dark fs-6">₹<?= number_format($q['total_amount'], 2) ?></div>
            </td>
            <td class="text-center">
                <a href="<?= site_url('quotations/view/' . $q['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
                    <i class="fas fa-external-link-alt text-muted"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

