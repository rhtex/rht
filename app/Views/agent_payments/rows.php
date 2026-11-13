<?php foreach ($payments as $payment): ?>
    <tr>
        <td>
            <a href="<?= site_url('agent-payments/view/' . $payment['id']) ?>" class="fw-bold text-primary text-decoration-none">
                <?= esc($payment['payment_number']) ?>
            </a>
        </td>
        <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
        <td>
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                    <?= strtoupper(substr($payment['agent_name'] ?? 'A', 0, 1)) ?>
                </div>
                <span class="fw-semibold text-dark"><?= esc($payment['agent_name']) ?></span>
            </div>
        </td>
        <td>
            <?php if (!empty($payment['phone_number'])): ?>
                <a href="tel:<?= esc($payment['phone_number']) ?>" class="text-decoration-none text-dark fw-semibold">
                    <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($payment['phone_number']) ?>
                </a>
            <?php else: ?>
                <span class="text-muted">-</span>
            <?php endif; ?>
        </td>
        <td>
            <span class="badge text-bg-info px-2 py-1"><?= esc($payment['payment_mode']) ?></span>
        </td>
        <td>
            <?= !empty($payment['reference_number']) ? '<code>'.esc($payment['reference_number']).'</code>' : '<span class="text-muted">-</span>' ?>
        </td>
        <td class="text-end fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></td>
        <td class="text-end">
            <div class="btn-group btn-group-sm">
                <a href="<?= site_url('agent-payments/view/' . $payment['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                <?php if (can('agent_payments.delete')): ?>
                <a href="<?= site_url('agent-payments/delete/' . $payment['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this payment record?')" title="Delete"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
