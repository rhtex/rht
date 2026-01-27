<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Notifications Central<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="d-flex justify-content-between align-items-center mb-0">
    <div>
        <h3 class="fw-bolder mb-0 text-dark">Notification <span class="text-primary">Center</span></h3>
        <p class="text-muted small mb-0">System alerts and business action items</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('notifications/markAllRead') ?>" class="btn btn-soft-primary fw-bold rounded-pill px-4">
            <i class="fas fa-check-double me-2"></i> Mark All as Read
        </a>
    </div>
</div>

<style>
    .notify-card {
        border-radius: 16px;
        border: 1px solid #eef2f6;
        transition: all 0.2s ease;
        background: white;
    }
    .notify-card.unread {
        border-left: 4px solid #4f46e5;
        background: #f8faff;
    }
    .notify-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .bg-soft-warning { background: #fef3c7; color: #d97706; }
    .bg-soft-info { background: #e0f2fe; color: #0284c7; }
    .bg-soft-success { background: #dcfce7; color: #16a34a; }

    .nav-pills-custom .nav-link {
        color: #64748b;
        font-weight: 700;
        border-radius: 12px;
        padding: 10px 20px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .nav-pills-custom .nav-link.active {
        background: white;
        color: #4f46e5;
        border-color: #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .nav-pills-custom .nav-link:hover:not(.active) {
        background: #f1f5f9;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid pb-5">
    
    <!-- Tab Navigation -->
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4" id="notifyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab">
                <i class="fas fa-layer-group me-2"></i> All Alerts
                <?php $unreadAll = count(array_filter($notifications, fn($n) => !$n['is_read'])); ?>
                <?php if($unreadAll > 0): ?><span class="badge bg-danger ms-2"><?= $unreadAll ?></span><?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="low-stock-tab" data-bs-toggle="pill" data-bs-target="#low-stock" type="button" role="tab">
                <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Low Stock
                <?php $unreadStock = count(array_filter($notifications, fn($n) => !$n['is_read'] && $n['type'] == 'low_stock')); ?>
                <?php if($unreadStock > 0): ?><span class="badge bg-danger ms-2"><?= $unreadStock ?></span><?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lr-missing-tab" data-bs-toggle="pill" data-bs-target="#lr-missing" type="button" role="tab">
                <i class="fas fa-truck-loading me-2 text-info"></i> LR Missing
                <?php $unreadLR = count(array_filter($notifications, fn($n) => !$n['is_read'] && $n['type'] == 'lr_update')); ?>
                <?php if($unreadLR > 0): ?><span class="badge bg-danger ms-2"><?= $unreadLR ?></span><?php endif; ?>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="notifyTabsContent">
        <!-- All Alerts -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="row g-3">
                <?php if(empty($notifications)): ?>
                    <?= $this->include('notifications/partials/empty_state') ?>
                <?php else: ?>
                    <?php foreach($notifications as $notify): ?>
                        <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="tab-pane fade" id="low-stock" role="tabpanel">
            <div class="row g-3">
                <?php 
                $stockNotifs = array_filter($notifications, fn($n) => $n['type'] == 'low_stock');
                if(empty($stockNotifs)): ?>
                    <?= $this->include('notifications/partials/empty_state') ?>
                <?php else: ?>
                    <?php foreach($stockNotifs as $notify): ?>
                        <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- LR Missing -->
        <div class="tab-pane fade" id="lr-missing" role="tabpanel">
            <div class="row g-3">
                <?php 
                $lrNotifs = array_filter($notifications, fn($n) => $n['type'] == 'lr_update');
                if(empty($lrNotifs)): ?>
                    <?= $this->include('notifications/partials/empty_state') ?>
                <?php else: ?>
                    <?php foreach($lrNotifs as $notify): ?>
                        <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

