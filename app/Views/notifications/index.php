<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Notifications Central<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bolder mb-0 text-dark">Notification <span class="text-primary">Center</span></h4>
        <p class="text-muted smaller mb-0">System alerts and action items</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('notifications/markAllRead') ?>"
            class="btn btn-sm btn-soft-primary fw-bold rounded-pill px-3">
            <i class="fas fa-check-double me-1 small"></i> Mark All as Read
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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
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

    .bg-soft-warning {
        background: #fef3c7;
        color: #d97706;
    }

    .bg-soft-info {
        background: #e0f2fe;
        color: #0284c7;
    }

    .bg-soft-success {
        background: #dcfce7;
        color: #16a34a;
    }

    .bg-soft-primary-light {
        background: #f1f4ff;
    }

    .table-sm td {
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .uppercase {
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .nav-pills-custom .nav-link {
        color: #64748b;
        font-weight: 700;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .nav-pills-custom .nav-link.active {
        background: white;
        color: #4f46e5;
        border-color: #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .nav-pills-custom .nav-link:hover:not(.active) {
        background: #f1f5f9;
    }

    .smaller {
        font-size: 0.75rem;
    }

    .btn-xs {
        padding: 2px 8px;
        font-size: 0.7rem;
        line-height: 1.2;
        border-radius: 4px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid pb-5">

    <!-- Tab Navigation -->
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-3" id="notifyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button"
                role="tab">
                <i class="fas fa-layer-group me-1 small"></i> All
                <?php $unreadAll = count(array_filter($notifications, fn($n) => !$n['is_read'])); ?>
                <?php if ($unreadAll > 0): ?><span
                        class="badge bg-danger ms-1 smaller"><?= $unreadAll ?></span><?php endif; ?>
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lr-missing-tab" data-bs-toggle="pill" data-bs-target="#lr-missing"
                type="button" role="tab">
                <i class="fas fa-truck-loading me-1 small"></i> LR Missing
                <?php $unreadLR = count(array_filter($notifications, fn($n) => !$n['is_read'] && $n['type'] == 'lr_update')); ?>
                <?php if ($unreadLR > 0): ?><span
                        class="badge bg-danger ms-1 smaller"><?= $unreadLR ?></span><?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tasks-tab" data-bs-toggle="pill" data-bs-target="#tasks" type="button"
                role="tab">
                <i class="fas fa-calendar-check me-1 small"></i> Tasks
                <?php $unreadTasks = count(array_filter($notifications, fn($n) => !$n['is_read'] && $n['type'] == 'reminder')); ?>
                <?php if ($unreadTasks > 0): ?><span
                        class="badge bg-danger ms-1 smaller"><?= $unreadTasks ?></span><?php endif; ?>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="notifyTabsContent">
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="card border-0 shadow-none bg-transparent">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0"
                            style="border-collapse: separate; border-spacing: 0 4px;">
                            <thead class="bg-light text-muted smaller uppercase fw-bold">
                                <tr>
                                    <th style="width: 40px;" class="ps-3"></th>
                                    <th>Title & Message</th>
                                    <th style="width: 120px;">Date</th>
                                    <th style="width: 150px;" class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($notifications)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No notifications found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notify): ?>
                                        <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <div class="tab-pane fade" id="lr-missing" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0"
                    style="border-collapse: separate; border-spacing: 0 4px;">
                    <tbody>
                        <?php
                        $lrNotifs = array_filter($notifications, fn($n) => $n['type'] == 'lr_update');
                        if (empty($lrNotifs)): ?>
                            <tr>
                                <td class="text-center py-4 text-muted small">No LR missing alerts.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lrNotifs as $notify): ?>
                                <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="tasks" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0"
                    style="border-collapse: separate; border-spacing: 0 4px;">
                    <tbody>
                        <?php
                        $taskNotifs = array_filter($notifications, fn($n) => $n['type'] == 'reminder');
                        if (empty($taskNotifs)): ?>
                            <tr>
                                <td class="text-center py-4 text-muted small">No pending tasks.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($taskNotifs as $notify): ?>
                                <?= view('notifications/partials/card', ['notify' => $notify]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>