<?php foreach ($invoices as $inv): ?>
    <tr>
        <td class="fw-bold"><?= esc($inv['invoice_number']) ?></td>
        <td><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
        <td><?= esc($inv['customer_name'] ?? 'N/A') ?></td>
        <td><?= esc($inv['transport_name']) ?: '<span class="text-muted">Not Selected</span>' ?></td>
        <td>
            <?php if (!$inv['waybill_number']): ?>
                <span class="badge bg-warning text-dark">No Number</span>
            <?php endif; ?>
            <?php if (!$inv['waybill_date']): ?>
                <span class="badge bg-warning text-dark">No Date</span>
            <?php endif; ?>
            <?php if (!$inv['waybill_image']): ?>
                <span class="badge bg-warning text-dark">No Image</span>
            <?php endif; ?>
            <?php if ($inv['waybill_number'] && $inv['waybill_date'] && $inv['waybill_image']): ?>
                <span class="badge bg-success">Complete</span>
            <?php endif; ?>
        </td>
        <td>
            <?php
            $delStatusColors = [
                'Pending' => 'secondary',
                'In Transit' => 'primary',
                'Delivered' => 'success',
                'Cancelled' => 'danger'
            ];
            $delColor = $delStatusColors[$inv['delivery_status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $delColor ?>"><?= esc($inv['delivery_status']) ?></span>
            <?php if ($inv['delivery_status'] == 'Delivered' && ($inv['delivered_date'] ?? null)): ?>
                <div class="small mt-1 text-muted"><?= date('d/m/Y', strtotime($inv['delivered_date'])) ?></div>
            <?php endif; ?>
        </td>
        <td>
            <div class="btn-group w-100 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 rounded" 
                        onclick="openWaybillModal(<?= htmlspecialchars(json_encode([
                            'id' => $inv['id'],
                            'invoice_number' => $inv['invoice_number'],
                            'waybill_number' => $inv['waybill_number'],
                            'waybill_date' => $inv['waybill_date'],
                            'transport_amount' => $inv['transport_amount'] ?? 0,
                            'transport_pay_type' => $inv['transport_pay_type'] ?? 'To Pay',
                            'waybill_shipping_charge' => $inv['waybill_shipping_charge'] ?? 0
                        ])) ?>)">
                    <i class="fas fa-truck"></i><span> Waybill</span>
                </button>
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 rounded" 
                        onclick="openDeliveryModal(<?= htmlspecialchars(json_encode([
                            'id' => $inv['id'],
                            'invoice_number' => $inv['invoice_number'],
                            'delivery_status' => $inv['delivery_status']
                        ])) ?>)">
                    <i class="fas fa-check-circle"></i><span> Delivery</span>
                </button>
                <button type="button" class="btn btn-info btn-sm d-inline-flex align-items-center gap-2 rounded text-white" 
                        onclick="openHistoryModal(<?= htmlspecialchars(json_encode([
                            'id' => $inv['id'],
                            'invoice_number' => $inv['invoice_number']
                        ])) ?>)">
                    <i class="fas fa-history"></i><span> History</span>
                </button>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
