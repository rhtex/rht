<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Attendance Management<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-clock text-primary me-2"></i>Daily Employee Attendance</h1>
    </div>
    <div class="col-sm-6 text-end">
        <form action="<?= site_url('attendance') ?>" method="get" class="d-inline-flex gap-2 align-items-center float-sm-end">
            <span class="small fw-bold text-muted">Attendance Date:</span>
            <input type="date" name="date" class="form-control form-control-sm" style="width: auto;" value="<?= esc($date) ?>" onchange="this.form.submit()">
            <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-calendar-check me-1"></i> Go</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalStaff = count($employees ?? []);
$presentCount = 0;
$halfDayCount = 0;
$absentCount = 0;
$holidayCount = 0;

if (!empty($employees)) {
    foreach ($employees as $emp) {
        $existing = $attendanceMap[$emp['id']] ?? null;
        $st = $existing['status'] ?? 'Absent';
        if ($st === 'Present') $presentCount++;
        elseif ($st === 'Half Day') $halfDayCount++;
        elseif ($st === 'Holiday') $holidayCount++;
        else $absentCount++;
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-users bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalStaff) ?></div>
            <div class="bt-stat-label">Total Active Staff</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-user-check bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($presentCount) ?></div>
            <div class="bt-stat-label">Present Today</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalStaff > 0) ? min(100, round(($presentCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-adjust bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($halfDayCount) ?></div>
            <div class="bt-stat-label">Half Day</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalStaff > 0) ? min(100, round(($halfDayCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-red" style="cursor:default;">
            <i class="fas fa-user-times bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($absentCount) ?></div>
            <div class="bt-stat-label">Absent / Off</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalStaff > 0) ? min(100, round(($absentCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 bg-white">
        <h3 class="card-title fw-bold mb-0">Roster for <?= date('l, d F Y', strtotime($date)) ?></h3>
        
        <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 170px;">
                <span class="input-group-text bg-light text-muted small">Shift In</span>
                <input type="time" id="defaultCheckIn" class="form-control" value="09:00">
            </div>
            <div class="input-group input-group-sm" style="width: 170px;">
                <span class="input-group-text bg-light text-muted small">Shift Out</span>
                <input type="time" id="defaultCheckOut" class="form-control" value="18:00">
            </div>
            <button type="button" class="btn btn-success btn-sm text-nowrap rounded-pill px-3 shadow-sm" onclick="markAllPresent()">
                <i class="fas fa-check-double me-1"></i> Mark All Present
            </button>
        </div>
    </div>
    <form action="<?= site_url('attendance/store') ?>" method="post" id="attendanceForm">
        <?= csrf_field() ?>
        <input type="hidden" name="attendance_date" value="<?= esc($date) ?>">

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th style="min-width: 200px">Employee</th>
                            <th style="min-width: 230px">Status</th>
                            <th style="min-width: 120px">Check-In</th>
                            <th style="min-width: 120px">Check-Out</th>
                            <th class="text-center" style="min-width: 90px">Target</th>
                            <th class="text-center" style="min-width: 90px">Worked</th>
                            <th class="text-center" style="min-width: 100px">Balance</th>
                            <th class="text-center" style="min-width: 90px">2x Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $emp): ?>
                                <?php
                                $existing = $attendanceMap[$emp['id']] ?? null;
                                $status = $existing['status'] ?? 'Absent';
                                $checkIn = $existing['check_in_time'] ?? '';
                                $checkOut = $existing['check_out_time'] ?? '';
                                $isDoublePay = isset($existing['multiplier']) && $existing['multiplier'] > 1.0;

                                $rowClass = '';
                                if ($status == 'Present') $rowClass = 'table-success';
                                elseif ($status == 'Absent') $rowClass = 'table-danger';
                                elseif ($status == 'Half Day') $rowClass = 'table-info';
                                elseif ($status == 'Holiday') $rowClass = 'table-warning';
                                ?>
                                <tr id="row_<?= $emp['id'] ?>" class="<?= $rowClass ?>">
                                    <td class="text-muted fw-semibold">#<?= $emp['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                                <?= strtoupper(substr($emp['first_name'] ?? 'E', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= esc($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                                                <small class="text-muted"><?= esc($emp['mobile_number']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check status-radio"
                                                name="attendance[<?= $emp['id'] ?>][status]" id="status_p_<?= $emp['id'] ?>"
                                                value="Present" data-emp="<?= $emp['id'] ?>" <?= $status == 'Present' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success btn-sm"
                                                for="status_p_<?= $emp['id'] ?>">Present</label>

                                            <input type="radio" class="btn-check status-radio"
                                                name="attendance[<?= $emp['id'] ?>][status]" id="status_hd_<?= $emp['id'] ?>"
                                                value="Half Day" data-emp="<?= $emp['id'] ?>" <?= $status == 'Half Day' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-info btn-sm"
                                                for="status_hd_<?= $emp['id'] ?>">Half</label>

                                            <input type="radio" class="btn-check status-radio"
                                                name="attendance[<?= $emp['id'] ?>][status]" id="status_a_<?= $emp['id'] ?>"
                                                value="Absent" data-emp="<?= $emp['id'] ?>" <?= $status == 'Absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger btn-sm"
                                                for="status_a_<?= $emp['id'] ?>">Absent</label>

                                            <input type="radio" class="btn-check status-radio"
                                                name="attendance[<?= $emp['id'] ?>][status]" id="status_h_<?= $emp['id'] ?>"
                                                value="Holiday" data-emp="<?= $emp['id'] ?>" <?= $status == 'Holiday' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning btn-sm"
                                                for="status_h_<?= $emp['id'] ?>">Holiday</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm time-input" id="check_in_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_in_time]" value="<?= esc($checkIn) ?>" data-emp="<?= $emp['id'] ?>">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm time-input" id="check_out_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_out_time]" value="<?= esc($checkOut) ?>" data-emp="<?= $emp['id'] ?>">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-secondary" id="target_<?= $emp['id'] ?>" data-hours="<?= $emp['daily_working_hours'] ?>"><?= format_hours($emp['daily_working_hours']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold" id="worked_<?= $emp['id'] ?>">
                                            <?= $existing ? format_hours($existing['hours_worked']) : '-' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($existing && $existing['shortfall_hours'] > 0): ?>
                                            <span class="badge text-bg-danger">
                                                -<?= format_hours($existing['shortfall_hours']) ?>
                                            </span>
                                        <?php elseif($existing && $existing['is_recovered']): ?>
                                            <span class="badge text-bg-success"><i class="fas fa-check"></i> OK</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox" class="form-check-input" name="attendance[<?= $emp['id'] ?>][double_pay]" value="1" <?= $isDoublePay ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-user-clock fa-2x text-muted mb-2 d-block"></i>
                                    No active employees found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end bg-white py-3">
            <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-1"></i> Save Daily Attendance</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('.status-radio').on('change', function() {
        const empId = $(this).data('emp');
        const status = $(this).val();
        const row = $('#row_' + empId);
        const checkIn = $('#check_in_' + empId);
        const checkOut = $('#check_out_' + empId);

        row.removeClass('table-success table-danger table-info table-warning');
        if (status === 'Present') row.addClass('table-success');
        else if (status === 'Absent') row.addClass('table-danger');
        else if (status === 'Half Day') row.addClass('table-info');
        else if (status === 'Holiday') row.addClass('table-warning');

        if (status === 'Absent' || status === 'Holiday') {
            checkIn.val('');
            checkOut.val('');
            updateWorkedHours(empId);
        } else {
            if (status === 'Present' && !checkIn.val()) {
                const defIn = $('#defaultCheckIn').val();
                const defOut = $('#defaultCheckOut').val();
                checkIn.val(defIn);
                checkOut.val(defOut);
                updateWorkedHours(empId);
            }
        }
    });

    $('.time-input').on('focus change input', function() {
        const empId = $(this).data('emp');
        const statusRadio = $('input[name="attendance[' + empId + '][status]"]:checked');
        
        const currentStatus = statusRadio.val();
        if (currentStatus === 'Absent' || currentStatus === 'Holiday') {
            $('#status_p_' + empId).prop('checked', true).trigger('change');
        }
        
        updateWorkedHours(empId);
    });

    function updateWorkedHours(empId) {
        const checkInVal = $('#check_in_' + empId).val();
        const checkOutVal = $('#check_out_' + empId).val();
        const workedSpan = $('#worked_' + empId);

        if (!checkInVal || !checkOutVal) {
            workedSpan.text('-');
            workedSpan.removeClass('text-success text-danger');
            return;
        }

        const start = new Date("2000-01-01 " + checkInVal);
        const end = new Date("2000-01-01 " + checkOutVal);

        if (end < start) {
            workedSpan.text('Invalid');
            return;
        }

        const diffMs = end - start;
        const diffHrs = diffMs / (1000 * 60 * 60);

        const hours = Math.floor(diffHrs);
        const minutes = Math.round((diffHrs - hours) * 60);
        const formatted = hours + "h " + minutes + "m";

        workedSpan.text(formatted);

        const targetHours = parseFloat($('#target_' + empId).data('hours')) || 0;
        if (diffHrs >= targetHours) {
            workedSpan.addClass('text-success').removeClass('text-danger');
        } else {
            workedSpan.addClass('text-danger').removeClass('text-success');
        }
    }

    function markAllPresent() {
        if (!$('#defaultCheckIn').val() || !$('#defaultCheckOut').val()) {
            alert('Please check Shift Start and Shift End times first.');
            return;
        }

        $('.status-radio[value="Present"]').each(function() {
            if (!$(this).prop('checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });
        
        $('.status-radio[value="Present"]:checked').each(function() {
             const empId = $(this).data('emp');
             const checkIn = $('#check_in_' + empId);
             if(!checkIn.val()) {
                   const defIn = $('#defaultCheckIn').val();
                   const defOut = $('#defaultCheckOut').val();
                   checkIn.val(defIn);
                   $('#check_out_' + empId).val(defOut);
                   updateWorkedHours(empId);
             }
        });
    }
</script>
<?= $this->endSection() ?>
