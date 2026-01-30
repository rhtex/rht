<?php foreach ($shipments as $shipment): ?>
    <tr>
        <td class="fw-bold"><?= esc($shipment['reference_no']) ?></td>
        <td><?= esc($shipment['vendor_name']) ?></td>
        <td><?= date('d/m/Y', strtotime($shipment['return_date'])) ?></td>
        <td><?= esc($shipment['transport_name']) ?: '<span class="text-muted">Not Set</span>' ?></td>
        <td>
            <?php if($shipment['waybill_number']): ?>
                <div><?= esc($shipment['waybill_number']) ?></div>
                <small class="text-muted"><?= $shipment['waybill_date'] ? date('d/m/Y', strtotime($shipment['waybill_date'])) : '' ?></small>
                <?php if($shipment['waybill_image']): ?>
                    <br><a href="<?= base_url('uploads/vendor_returns/' . $shipment['waybill_image']) ?>" target="_blank" class="badge bg-light text-primary border"><i class="fas fa-image"></i> View LR</a>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Missing LR</span>
            <?php endif; ?>
        </td>
        <td>
            <?php
            $delStatusColors = [
                'Pending' => 'secondary',
                'In Transit' => 'primary',
                'Completed' => 'success',
                'Cancelled' => 'danger'
            ];
            $delColor = $delStatusColors[$shipment['delivery_status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $delColor ?>"><?= esc($shipment['delivery_status']) ?></span>
        </td>
        <td>
            <div class="btn-group w-100 gap-2">
                <button type="button" class="btn btn-sm btn-primary rounded" onclick="openWaybillModal(<?= htmlspecialchars(json_encode($shipment)) ?>)">
                    <i class="fas fa-truck"></i> Waybill
                </button>
                <button type="button" class="btn btn-sm btn-info rounded" onclick="openDeliveryModal(<?= htmlspecialchars(json_encode($shipment)) ?>)">
                    <i class="fas fa-check-circle"></i> Status
                </button>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
