<?php foreach ($payments as $payment): ?>
    <tr>
        <td class="fw-bold"><?= esc($payment['payment_number']) ?></td>
        <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
        <td><?= esc($payment['agent_name']) ?></td>
        <td><?= esc($payment['phone_number']) ?></td>
        <td><span class="badge text-bg-info"><?= esc($payment['payment_mode']) ?></span></td>
        <td><?= esc($payment['reference_number']) ?: '-' ?></td>
        <td class="text-end fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
        <td class="text-center">
            <div class="btn-group btn-group-sm">
                <a href="<?= site_url('agent-payments/view/' . $payment['id']) ?>" class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>
                <a href="<?= site_url('agent-payments/delete/' . $payment['id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></a>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
