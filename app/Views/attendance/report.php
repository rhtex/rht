<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Attendance Report<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Monthly Attendance Report</h1>
    </div>
    <div class="col-sm-6">
        <form action="<?= site_url('attendance/report') ?>" method="get" class="float-sm-end">
            <div class="input-group">
                <input type="month" name="month" class="form-control" value="<?= $month ?>" onchange="this.form.submit()">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-info">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center" style="font-size: 0.9em;">
                <thead>
                    <tr>
                        <th class="text-start">Employee</th>
                        <?php for($d=1; $d<=$daysInMonth; $d++): ?>
                            <th style="min-width: 30px;"><?= $d ?></th>
                        <?php endfor; ?>
                        <th>P</th>
                        <th>A</th>
                        <th>L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($employees)): ?>
                        <?php foreach($employees as $emp): ?>
                        <tr>
                            <td class="text-start text-nowrap"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></td>
                            <?php 
                                $pCount = 0; $aCount = 0; $lCount = 0;
                            ?>
                            <?php for($d=1; $d<=$daysInMonth; $d++): ?>
                                <?php 
                                    $currentDate = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                    $status = $reportData[$emp['id']][$currentDate] ?? '-';
                                    
                                    if ($status == 'Present') { $badge = 'bg-success'; $text = 'P'; $pCount++; }
                                    elseif ($status == 'Absent') { $badge = 'bg-danger'; $text = 'A'; $aCount++; }
                                    elseif ($status == 'Leave') { $badge = 'bg-warning'; $text = 'L'; $lCount++; }
                                    else { $badge = 'bg-light'; $text = '-'; }
                                ?>
                                <td>
                                    <?php if($status !== '-'): ?>
                                    <span class="badge <?= $badge ?>" title="<?= $status ?>"><?= $text ?></span>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                            <td class="fw-bold text-success"><?= $pCount ?></td>
                            <td class="fw-bold text-danger"><?= $aCount ?></td>
                            <td class="fw-bold text-warning"><?= $lCount ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $daysInMonth + 5 ?>" class="text-center">No active employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
