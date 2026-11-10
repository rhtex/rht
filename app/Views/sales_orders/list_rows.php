<?php if (!empty($orders)): ?>
    <?php foreach ($orders as $order): ?>
        <?php
        $statusColors = [
            'Draft'     => 'secondary',
            'Confirmed' => 'primary',
            'Closed'    => 'success',
            'Void'      => 'dark'
        ];
        $color = $statusColors[$order['status']] ?? 'secondary';
        ?>
        <tr class="order-row cursor-pointer" 
            data-id="<?= $order['id'] ?>"
            id="order-row-<?= $order['id'] ?>"
            style="cursor: pointer;">
            <td class="text-truncate">
                <div class="fw-bold text-primary text-truncate"><?= esc($order['sales_order_number']) ?></div>
                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($order['order_date'])) ?></small>
                <span class="badge text-bg-<?= $color ?>" style="font-size:0.68rem;"><?= esc($order['status']) ?></span>
                <?php if (!empty($order['converted_to_invoice_id'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                        <i class="fas fa-check-double me-1"></i>Invoiced
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-truncate">
                <div class="fw-semibold text-dark text-truncate" title="<?= esc($order['customer_name']) ?>"><?= esc($order['customer_name']) ?></div>
                <small class="text-muted d-block text-truncate">
                    <?= !empty($order['customer_city']) ? esc($order['customer_city']) : '-' ?>
                </small>
            </td>
            <td class="text-end text-nowrap">
                <div class="fw-bold text-dark fs-6">₹<?= number_format($order['total_amount'], 2) ?></div>
            </td>
            <td class="text-center">
                <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>?mode=single" class="btn btn-sm btn-light border p-1" title="Open Standalone Page">
                    <i class="fas fa-external-link-alt text-muted"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

