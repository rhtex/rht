<tr class="<?= $notify['is_read'] ? '' : 'bg-soft-primary-light' ?>"
    style="<?= !$notify['is_read'] ? 'border-left: 3px solid #4f46e5;' : '' ?>">
    <td class="ps-3">
        <div class="icon-box <?= $notify['type'] == 'lr_update' ? 'bg-soft-info' : 'bg-soft-success' ?>"
            style="width: 28px; height: 28px; border-radius: 6px; font-size: 0.8rem;">
            <i class="fas fa-<?= $notify['type'] == 'lr_update' ? 'truck-loading' : 'bell' ?>"></i>
        </div>
    </td>
    <td>
        <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;"><?= esc($notify['title']) ?></div>
        <div class="text-muted smaller"><?= esc($notify['message']) ?></div>
    </td>
    <td class="text-muted smaller">
        <?= date('d M, h:i A', strtotime($notify['created_at'])) ?>
    </td>
    <td class="text-end pe-3">
        <div class="d-flex justify-content-end gap-1">
            <?php if ($notify['link']): ?>
                <a href="<?= site_url($notify['link']) ?>" class="btn btn-xs btn-soft-primary fw-bold"
                    title="Resolve">Resolve</a>
            <?php endif; ?>
            <?php if (!$notify['is_read']): ?>
                <a href="<?= site_url('notifications/markAsRead/' . $notify['id']) ?>"
                    class="btn btn-xs btn-outline-secondary" title="Mark as Read">
                    <i class="fas fa-check"></i>
                </a>
            <?php endif; ?>
        </div>
    </td>
</tr>