<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Attendance Management<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-clock text-primary me-2"></i>Daily Employee Attendance</h1>
    </div>
    <div class="col-sm-6 text-end">
        <form action="<?= site_url('attendance') ?>" method="get" class="d-inline-flex gap-1 align-items-center float-sm-end">
            <a href="<?= site_url('attendance?date=' . esc($prevDate)) ?>" class="btn btn-sm btn-outline-secondary" title="Previous Day"><i class="fas fa-chevron-left"></i></a>
            <input type="date" name="date" class="form-control form-control-sm" style="width: auto;" value="<?= esc($date) ?>" onchange="this.form.submit()">
            <a href="<?= site_url('attendance?date=' . esc($nextDate)) ?>" class="btn btn-sm btn-outline-secondary" title="Next Day"><i class="fas fa-chevron-right"></i></a>
            <?php if ($date !== date('Y-m-d')): ?>
                <a href="<?= site_url('attendance?date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-outline-primary">Today</a>
            <?php endif; ?>
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
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-users bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalStaff) ?></div>
            <div class="bt-stat-label">Total Active Staff</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-user-check bt-stat-icon"></i>
            <div class="bt-stat-num" id="kpiPresent"><?= number_format($presentCount) ?></div>
            <div class="bt-stat-label">Present Today</div>
            <div class="progress"><div class="progress-bar" id="kpiPresentBar" style="width:<?= ($totalStaff > 0) ? min(100, round(($presentCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-adjust bt-stat-icon"></i>
            <div class="bt-stat-num" id="kpiHalf"><?= number_format($halfDayCount) ?></div>
            <div class="bt-stat-label">Half Day</div>
            <div class="progress"><div class="progress-bar" id="kpiHalfBar" style="width:<?= ($totalStaff > 0) ? min(100, round(($halfDayCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-red" style="cursor:default;">
            <i class="fas fa-user-times bt-stat-icon"></i>
            <div class="bt-stat-num" id="kpiAbsent"><?= number_format($absentCount) ?></div>
            <div class="bt-stat-label">Absent / Off</div>
            <div class="progress"><div class="progress-bar" id="kpiAbsentBar" style="width:<?= ($totalStaff > 0) ? min(100, round(($absentCount / $totalStaff) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 bg-white">
        <div class="d-flex align-items-center gap-2">
            <h3 class="card-title fw-bold mb-0">Roster for <?= date('l, d F Y', strtotime($date)) ?></h3>
            <span id="unsavedBadge" class="badge text-bg-warning d-none">Unsaved changes</span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="search" id="empSearch" class="form-control form-control-sm" style="width: 170px;" placeholder="Search employee...">
            <div class="input-group input-group-sm" style="width: 145px;">
                <span class="input-group-text bg-light text-muted small">In</span>
                <input type="time" id="defaultCheckIn" class="form-control" value="09:00">
            </div>
            <div class="input-group input-group-sm" style="width: 145px;">
                <span class="input-group-text bg-light text-muted small">Out</span>
                <input type="time" id="defaultCheckOut" class="form-control" value="18:00">
            </div>
            <button type="button" class="btn btn-success btn-sm text-nowrap" onclick="markAllStatus('Present')" title="Mark all visible employees Present with shift times">
                <i class="fas fa-check-double me-1"></i> All Present
            </button>
            <button type="button" class="btn btn-outline-warning text-dark btn-sm text-nowrap" onclick="markAllStatus('Holiday')" title="Mark all visible employees Holiday">
                <i class="fas fa-umbrella-beach me-1"></i> All Holiday
            </button>
            <?php if (!empty($yesterdayMap)): ?>
            <button type="button" class="btn btn-outline-info btn-sm text-nowrap" onclick="copyYesterday()" title="Copy attendance from <?= esc($prevDate) ?>">
                <i class="fas fa-copy me-1"></i> Copy Yesterday
            </button>
            <?php endif; ?>
            <button type="submit" form="attendanceForm" class="btn btn-primary btn-sm text-nowrap shadow-sm">
                <i class="fas fa-save me-1"></i> Save
            </button>
        </div>
    </div>
    <form action="<?= site_url('attendance/store') ?>" method="post" id="attendanceForm">
        <?= csrf_field() ?>
        <input type="hidden" name="attendance_date" value="<?= esc($date) ?>">

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th class="text-center">Target</th>
                            <th class="text-center">Worked</th>
                            <th class="text-center">Balance</th>
                            <th class="text-center">2x Pay</th>
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
                                        <div class="input-group input-group-sm">
                                            <input type="time" class="form-control time-input" id="check_in_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_in_time]" value="<?= esc($checkIn) ?>" data-emp="<?= $emp['id'] ?>">
                                            <button type="button" class="btn btn-outline-secondary px-1" onclick="setNowTime('check_in_<?= $emp['id'] ?>', <?= $emp['id'] ?>)" title="Set Check-In to Now"><i class="fas fa-clock"></i></button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="time" class="form-control time-input" id="check_out_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_out_time]" value="<?= esc($checkOut) ?>" data-emp="<?= $emp['id'] ?>">
                                            <button type="button" class="btn btn-outline-secondary px-1" onclick="setNowTime('check_out_<?= $emp['id'] ?>', <?= $emp['id'] ?>)" title="Set Check-Out to Now"><i class="fas fa-clock"></i></button>
                                        </div>
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
    const totalStaff = <?= (int) $totalStaff ?>;
    const yesterdayMap = <?= json_encode($yesterdayMap ?? []) ?>;

    function markUnsaved() {
        $('#unsavedBadge').removeClass('d-none');
    }

    function refreshLiveKpis() {
        let p = 0, h = 0, a = 0;
        $('.status-radio:checked').each(function() {
            const v = $(this).val();
            if (v === 'Present') p++;
            else if (v === 'Half Day') h++;
            else if (v === 'Absent') a++;
        });
        $('#kpiPresent').text(p);
        $('#kpiHalf').text(h);
        $('#kpiAbsent').text(a);
        if (totalStaff > 0) {
            $('#kpiPresentBar').css('width', Math.min(100, Math.round((p / totalStaff) * 100)) + '%');
            $('#kpiHalfBar').css('width', Math.min(100, Math.round((h / totalStaff) * 100)) + '%');
            $('#kpiAbsentBar').css('width', Math.min(100, Math.round((a / totalStaff) * 100)) + '%');
        }
    }

    function addHoursToTime(hhmm, hrs) {
        const parts = (hhmm || '09:00').split(':');
        const totalMin = Math.min(23 * 60 + 59, (parseInt(parts[0], 10) || 9) * 60 + (parseInt(parts[1], 10) || 0) + Math.round(hrs * 60));
        return String(Math.floor(totalMin / 60)).padStart(2, '0') + ':' + String(totalMin % 60).padStart(2, '0');
    }

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
        } else if (status === 'Half Day' && !checkIn.val()) {
            const defIn = $('#defaultCheckIn').val() || '09:00';
            const targetHrs = parseFloat($('#target_' + empId).data('hours')) || 8;
            checkIn.val(defIn);
            checkOut.val(addHoursToTime(defIn, targetHrs / 2));
            updateWorkedHours(empId);
        } else if (status === 'Present' && !checkIn.val()) {
            checkIn.val($('#defaultCheckIn').val());
            checkOut.val($('#defaultCheckOut').val());
            updateWorkedHours(empId);
        }

        refreshLiveKpis();
        markUnsaved();
    });

    $('.time-input').on('change input', function() {
        const empId = $(this).data('emp');
        const currentStatus = $('input[name="attendance[' + empId + '][status]"]:checked').val();
        if (currentStatus === 'Absent' || currentStatus === 'Holiday') {
            $('#status_p_' + empId).prop('checked', true).trigger('change');
        }
        updateWorkedHours(empId);
        markUnsaved();
    });

    $('input[name$="[double_pay]"]').on('change', markUnsaved);

    function setNowTime(inputId, empId) {
        const now = new Date();
        const hhmm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        $('#' + inputId).val(hhmm).trigger('change');
    }

    function updateWorkedHours(empId) {
        const checkInVal = $('#check_in_' + empId).val();
        const checkOutVal = $('#check_out_' + empId).val();
        const workedSpan = $('#worked_' + empId);

        if (!checkInVal || !checkOutVal) {
            workedSpan.text('-').removeClass('text-success text-danger');
            return;
        }

        const start = new Date("2000-01-01 " + checkInVal);
        const end = new Date("2000-01-01 " + checkOutVal);

        if (end < start) {
            workedSpan.text('Invalid').addClass('text-danger').removeClass('text-success');
            return;
        }

        const diffHrs = (end - start) / (1000 * 60 * 60);
        const hours = Math.floor(diffHrs);
        const minutes = Math.round((diffHrs - hours) * 60);
        workedSpan.text(hours + "h " + minutes + "m");

        const targetHours = parseFloat($('#target_' + empId).data('hours')) || 0;
        workedSpan.toggleClass('text-success', diffHrs >= targetHours).toggleClass('text-danger', diffHrs < targetHours);
    }

    function markAllStatus(status) {
        if (status === 'Present' && (!$('#defaultCheckIn').val() || !$('#defaultCheckOut').val())) {
            alert('Please set Shift In and Out times first.');
            return;
        }
        $('tbody tr[id^="row_"]:visible').each(function() {
            const empId = this.id.replace('row_', '');
            const radio = $(`.status-radio[data-emp="${empId}"][value="${status}"]`);
            if (radio.length && !radio.prop('checked')) {
                radio.prop('checked', true).trigger('change');
            } else if (status === 'Present' && !$('#check_in_' + empId).val()) {
                $('#check_in_' + empId).val($('#defaultCheckIn').val());
                $('#check_out_' + empId).val($('#defaultCheckOut').val());
                updateWorkedHours(empId);
                markUnsaved();
            }
        });
    }

    function copyYesterday() {
        Object.keys(yesterdayMap).forEach(function(empId) {
            const rec = yesterdayMap[empId];
            const radio = $(`.status-radio[data-emp="${empId}"][value="${rec.status}"]`);
            if (!radio.length) return;
            radio.prop('checked', true).trigger('change');
            $('#check_in_' + empId).val(rec.check_in || '');
            $('#check_out_' + empId).val(rec.check_out || '');
            $(`input[name="attendance[${empId}][double_pay]"]`).prop('checked', !!rec.double_pay);
            updateWorkedHours(empId);
        });
        refreshLiveKpis();
        markUnsaved();
    }

    $('#empSearch').on('input', function() {
        const q = $(this).val().trim().toLowerCase();
        $('tbody tr[id^="row_"]').each(function() {
            $(this).toggle(!q || $(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });
</script>
<?= $this->endSection() ?>
