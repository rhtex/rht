<div class="col-12">
    <div class="card notify-card <?= $notify['is_read'] ? '' : 'unread' ?> shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="icon-box me-4 <?= $notify['type'] == 'low_stock' ? 'bg-soft-warning' : 'bg-soft-info' ?>">
                    <i class="fas fa-<?= $notify['type'] == 'low_stock' ? 'exclamation-triangle' : 'truck-loading' ?>"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-1 text-dark"><?= esc($notify['title']) ?></h6>
                        <span class="text-muted smaller"><?= date('M d, h:i A', strtotime($notify['created_at'])) ?></span>
                    </div>
                    <p class="text-muted small mb-0"><?= esc($notify['message']) ?></p>
                </div>
                <div class="ms-4 d-flex gap-2">
                    <?php if($notify['link']): ?>
                        <a href="<?= site_url($notify['link']) ?>" class="btn btn-sm btn-soft-primary px-3 rounded-pill fw-bold">Resolve</a>
                    <?php endif; ?>
                    <?php if(!$notify['is_read']): ?>
                        <a href="<?= site_url('notifications/markAsRead/'.$notify['id']) ?>" class="btn btn-sm btn-light border px-2 rounded-circle" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
