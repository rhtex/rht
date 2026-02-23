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
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">Mark Attendance for <?= date('d M Y', strtotime($date)) ?></h3>
        
        <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Shift Start</span>
                <input type="time" id="defaultCheckIn" class="form-control" value="09:00">
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Shift End</span>
                <input type="time" id="defaultCheckOut" class="form-control" value="18:00">
            </div>
            <button type="button" class="btn btn-success btn-sm text-nowrap" onclick="markAllPresent()">
                <i class="fas fa-check-double"></i> Mark All Present
            </button>
        </div>
    </div>
    <form action="<?= site_url('attendance/store') ?>" method="post" id="attendanceForm">
        <?= csrf_field() ?>
        <input type="hidden" name="attendance_date" value="<?= $date ?>">

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th style="min-width: 200px">Employee</th>
                            <th style="min-width: 200px">Status</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th class="text-center">Target</th>
                            <th class="text-center">Worked</th>
                            <th class="text-center">Balance</th>
                            <th style="width: 100px" class="text-center">2x Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $emp): ?>
                                <?php
                                $existing = $attendanceMap[$emp['id']] ?? null;
                                $status = $existing['status'] ?? 'Absent'; // Default to Absent if no record
                                $checkIn = $existing['check_in_time'] ?? '';
                                $checkOut = $existing['check_out_time'] ?? '';
                                $isDoublePay = isset($existing['multiplier']) && $existing['multiplier'] > 1.0;

                                // Row class based on status
                                $rowClass = '';
                                if ($status == 'Present')
                                    $rowClass = 'table-success';
                                elseif ($status == 'Absent')
                                    $rowClass = 'table-danger';
                                elseif ($status == 'Half Day')
                                    $rowClass = 'table-info';
                                elseif ($status == 'Holiday')
                                    $rowClass = 'table-warning';
                                ?>
                                <tr id="row_<?= $emp['id'] ?>" class="<?= $rowClass ?>">
                                    <td><?= $emp['id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= esc($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                                        <small class="text-muted"><?= esc($emp['mobile_number']) ?></small>
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
                                    <input type="time" class="form-control form-control-sm time-input" id="check_in_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_in_time]" value="<?= $checkIn ?>" data-emp="<?= $emp['id'] ?>">
                                </td>
                                <td>
                                    <input type="time" class="form-control form-control-sm time-input" id="check_out_<?= $emp['id'] ?>" name="attendance[<?= $emp['id'] ?>][check_out_time]" value="<?= $checkOut ?>" data-emp="<?= $emp['id'] ?>">
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary" id="target_<?= $emp['id'] ?>" data-hours="<?= $emp['daily_working_hours'] ?>"><?= format_hours($emp['daily_working_hours']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" id="worked_<?= $emp['id'] ?>">
                                        <?= $existing ? format_hours($existing['hours_worked']) : '-' ?>
                                    </span>
                                </td>
                                <td class="text-center">
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
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input" name="attendance[<?= $emp['id'] ?>][double_pay]" value="1" <?= $isDoublePay ? 'checked' : '' ?>>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No active employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Daily Attendance</button>
        </div>
    </form>
</div>

<script>
    // Handle Status Change
    $('.status-radio').on('change', function() {
        const empId = $(this).data('emp');
        const status = $(this).val();
        const row = $('#row_' + empId);
        const checkIn = $('#check_in_' + empId);
        const checkOut = $('#check_out_' + empId);

        // 1. Visual Styling
        row.removeClass('table-success table-danger table-info table-warning');
        if (status === 'Present') row.addClass('table-success');
        else if (status === 'Absent') row.addClass('table-danger');
        else if (status === 'Half Day') row.addClass('table-info');
        else if (status === 'Holiday') row.addClass('table-warning');

        // 2. Logic: If Absent/Holiday, clear inputs (BUT DO NOT DISABLE to allow easy correction)
        if (status === 'Absent' || status === 'Holiday') {
            checkIn.val('');
            checkOut.val('');
            updateWorkedHours(empId); // Reset calculations
        } else {
            // Auto-fill defaults if empty and switching to Present
            if (status === 'Present' && !checkIn.val()) {
                const defIn = $('#defaultCheckIn').val();
                const defOut = $('#defaultCheckOut').val();
                checkIn.val(defIn);
                checkOut.val(defOut);
                updateWorkedHours(empId);
            }
        }
    });

    // Handle Time Input (Focus/Change)
    $('.time-input').on('focus change input', function() {
        const empId = $(this).data('emp');
        const statusRadio = $('input[name="attendance[' + empId + '][status]"]:checked');
        
        // If user interacts with time inputs, auto-select 'Present' if currently Absent/Holiday
        const currentStatus = statusRadio.val();
        if (currentStatus === 'Absent' || currentStatus === 'Holiday') {
            $('#status_p_' + empId).prop('checked', true).trigger('change');
            // Note: trigger change might auto-fill default time if the input was empty.
            // But since the user is presumably typing, we might want to respect their input?
            // However, trigger('change') on radio runs the logic above.
            // If the user *just* focused and it was empty, it will fill defaults. This is good behavior.
            // If the user had a value (?), no, Absent clears value. So it's fine.
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
            // Assume Next Day shift? Or just error. For now, show 0.
            workedSpan.text('Invalid');
            return;
        }

        const diffMs = end - start;
        const diffHrs = diffMs / (1000 * 60 * 60);

        // Convert to HH:mm format
        const hours = Math.floor(diffHrs);
        const minutes = Math.round((diffHrs - hours) * 60);
        const formatted = hours + "h " + minutes + "m";

        workedSpan.text(formatted);

        // Comparison for color
        const targetHours = parseFloat($('#target_' + empId).data('hours')) || 0;
        if (diffHrs >= targetHours) {
            workedSpan.addClass('text-success').removeClass('text-danger');
        } else {
            workedSpan.addClass('text-danger').removeClass('text-success');
        }
    }

    // Mark All Present Action
    function markAllPresent() {
        // Validation for default time
        if (!$('#defaultCheckIn').val() || !$('#defaultCheckOut').val()) {
            alert('Please check Shift Start and Shift End times first.');
            return;
        }

        $('.status-radio[value="Present"]').each(function() {
            if (!$(this).prop('checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });
        
        // Also ensure times are filled for those already checked but empty
        $('.status-radio[value="Present"]:checked').each(function() {
             const empId = $(this).data('emp');
             const checkIn = $('#check_in_' + empId);
             
             // Check if empty OR if we want to overwrite? Let's assume we fill empty. 
             // If user really wants to overwrite all, they can uncheck and check again?
             // Or we just update all presents? For safety, let's only update empty ones so we don't destroy manual edits.
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