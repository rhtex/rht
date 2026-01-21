<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Attendance Shortfall<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= lang('App.shortfall_report') ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('attendance') ?>" class="btn btn-secondary float-sm-end">Back to Daily Entry</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title"><?= lang('App.shortfall_report') ?></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?= lang('App.employees') ?></th>
                        <th>Date</th>
                        <th><?= lang('App.target_hours') ?></th>
                        <th><?= lang('App.worked_hours') ?></th>
                        <th><?= lang('App.shortfall') ?></th>
                        <th><?= lang('App.deadline') ?></th>
                        <th><?= lang('App.status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($shortfalls)): ?>
                        <?php foreach ($shortfalls as $row): ?>
                            <?php 
                                $deadline = date('Y-m-d', strtotime($row['attendance_date'] . ' +7 days'));
                                $isOverdue = $deadline < date('Y-m-d');
                            ?>
                            <tr>
                                <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                                <td><?= date('d M Y', strtotime($row['attendance_date'])) ?></td>
                                <td><?= format_hours($row['daily_working_hours']) ?></td>
                                <td><?= format_hours($row['hours_worked']) ?></td>
                                <td class="text-danger fw-bold"><?= format_hours($row['shortfall_hours']) ?></td>
                                <td class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                    <?= date('d M Y', strtotime($deadline)) ?>
                                    <?php if($isOverdue): ?> <small>(Overdue)</small> <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?= lang('App.pending') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center p-4">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <p>No pending shortfalls found!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">Note: Shortfalls must be recovered by working extra hours on subsequent days within 7 days of the shortfall date.</small>
    </div>
</div>
<?= $this->endSection() ?>
