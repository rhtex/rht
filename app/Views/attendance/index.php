<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Attendance Management<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Daily Attendance</h1>
    </div>
    <div class="col-sm-6">
        <form action="<?= site_url('attendance') ?>" method="get" class="float-sm-end">
            <div class="input-group">
                <input type="date" name="date" class="form-control" value="<?= $date ?>" onchange="this.form.submit()">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Mark Attendance for <?= date('d M Y', strtotime($date)) ?></h3>
    </div>
    <form action="<?= site_url('attendance/store') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="attendance_date" value="<?= $date ?>">
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Employee</th>
                            <th>Status Index</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Target</th>
                            <th>Worked</th>
                            <th>Shortfall</th>
                            <th style="width: 100px">Double Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($employees)): ?>
                            <?php foreach($employees as $emp): ?>
                            <?php 
                                $existing = $attendanceMap[$emp['id']] ?? null;
                                $status = $existing['status'] ?? 'Absent';
                                $checkIn = $existing['check_in_time'] ?? '';
                                $checkOut = $existing['check_out_time'] ?? '';
                                $isDoublePay = isset($existing['multiplier']) && $existing['multiplier'] > 1.0;
                            ?>
                            <tr>
                                <td><?= $emp['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></div>
                                    <small class="text-muted"><?= $emp['mobile_number'] ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="attendance[<?= $emp['id'] ?>][status]" id="status_p_<?= $emp['id'] ?>" value="Present" autocomplete="off" <?= $status == 'Present' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success btn-sm" for="status_p_<?= $emp['id'] ?>">P</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= $emp['id'] ?>][status]" id="status_hd_<?= $emp['id'] ?>" value="Half Day" autocomplete="off" <?= $status == 'Half Day' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-info btn-sm" for="status_hd_<?= $emp['id'] ?>">HD</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= $emp['id'] ?>][status]" id="status_a_<?= $emp['id'] ?>" value="Absent" autocomplete="off" <?= $status == 'Absent' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="status_a_<?= $emp['id'] ?>">A</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= $emp['id'] ?>][status]" id="status_h_<?= $emp['id'] ?>" value="Holiday" autocomplete="off" <?= $status == 'Holiday' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning btn-sm" for="status_h_<?= $emp['id'] ?>">H</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" class="form-control form-control-sm" name="attendance[<?= $emp['id'] ?>][check_in_time]" value="<?= $checkIn ?>">
                                </td>
                                <td>
                                    <input type="time" class="form-control form-control-sm" name="attendance[<?= $emp['id'] ?>][check_out_time]" value="<?= $checkOut ?>">
                                </td>
                                <td><span class="badge bg-secondary"><?= format_hours($emp['daily_working_hours']) ?></span></td>
                                <td>
                                    <?php if($existing): ?>
                                        <span class="text-<?= $existing['hours_worked'] >= $emp['daily_working_hours'] ? 'success' : 'primary' ?>">
                                            <?= format_hours($existing['hours_worked']) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($existing && $existing['shortfall_hours'] > 0): ?>
                                        <span class="badge bg-danger">
                                            -<?= format_hours($existing['shortfall_hours']) ?>
                                        </span>
                                    <?php elseif($existing && $existing['is_recovered']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> OK</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input" name="attendance[<?= $emp['id'] ?>][double_pay]" value="1" <?= $isDoublePay ? 'checked' : '' ?>>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No active employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Attendance</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
