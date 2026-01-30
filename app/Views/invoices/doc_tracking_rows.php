<?php foreach ($invoices as $inv): ?>
    <tr>
        <td class="fw-bold"><?= esc($inv['invoice_number']) ?></td>
        <td><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
        <td><?= esc($inv['customer_name'] ?? 'N/A') ?></td>
        <td>
            <?php if ($inv['doc_courier_name']): ?>
                <strong><?= esc($inv['doc_courier_name']) ?></strong><br>
                <small class="text-muted">Tracking: <?= esc($inv['doc_tracking_number']) ?></small><br>
                <small class="text-muted">Date: <?= date('d/m/Y', strtotime($inv['doc_dispatched_date'])) ?></small>
            <?php else: ?>
                <span class="text-muted italic">Not Dispatched</span>
            <?php endif; ?>
        </td>
        <td>
            <?php
            $statusColors = [
                'Pending' => 'secondary',
                'Dispatched' => 'primary',
                'Delivered' => 'success',
                'Returned' => 'danger'
            ];
            $color = $statusColors[$inv['doc_status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $color ?>"><?= esc($inv['doc_status']) ?></span>
            <?php if ($inv['doc_status'] == 'Delivered' && ($inv['doc_received_date'] ?? null)): ?>
                <div class="small mt-1 text-muted">Rec'd: <?= date('d/m/Y', strtotime($inv['doc_received_date'])) ?></div>
            <?php endif; ?>
        </td>
        <td>
            <div class="btn-group w-100 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 rounded" 
                        onclick="openDocDetailsModal(<?= htmlspecialchars(json_encode([
                            'id' => $inv['id'],
                            'invoice_number' => $inv['invoice_number'],
                            'doc_courier_name' => $inv['doc_courier_name'],
                            'doc_tracking_number' => $inv['doc_tracking_number'],
                            'doc_dispatched_date' => $inv['doc_dispatched_date']
                        ])) ?>)">
                    <i class="fas fa-paper-plane"></i><span> Dispatch</span>
                </button>
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 rounded" 
                        onclick="openDocStatusModal(<?= htmlspecialchars(json_encode([
                            'id' => $inv['id'],
                            'invoice_number' => $inv['invoice_number'],
                            'doc_status' => $inv['doc_status']
                        ])) ?>)">
                    <i class="fas fa-check-double"></i><span> Status</span>
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
