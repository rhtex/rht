<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Add Attendance</h4>

<form action="/store_attendance" method="post" id="attendanceForm">
    <div class="mb-3">
        <label for="attendance_date" class="form-label">Date</label>
        <input type="text" class="form-control" id="attendance_date" name="attendance_date"
            value="<?= date('d/m/Y') ?>" required placeholder="dd/mm/yyyy">

        <button type="button" id="fetchAttendance" class="btn btn-secondary mt-2">Fetch</button>
    </div>
    <div id="attendance-entries" style="display: none;">
        <?php foreach ($employees as $index => $employee): ?>
            <div class="row mb-3 attendance-entry">
                <div class="col-md-4">
                    <label style="font-size: 18px;font-weight: bold">
                        <?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?>
                    </label>
                    <input type="hidden" name="attendance[<?= $index ?>][employee_id]"
                        value="<?= $employee['id'] ?>" data-index="<?= $index ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="entry_time_<?= $index ?>" class="form-label">Entry Time</label>
                    <input type="time" class="form-control" id="entry_time_<?= $index ?>" value="00:00"
                        name="attendance[<?= $index ?>][entry_time]" data-index="<?= $index ?>">
                </div>
                <div class="col-md-3">
                    <label for="exit_time_<?= $index ?>" class="form-label">Exit Time</label>
                    <input type="time" class="form-control" id="exit_time_<?= $index ?>" value="00:00"
                        name="attendance[<?= $index ?>][exit_time]" data-index="<?= $index ?>">
                </div>
                <div class="col-md-3">
                    <label for="status_<?= $index ?>" class="form-label">Attendance Status</label>
                    <select class="form-control" id="status_<?= $index ?>" name="attendance[<?= $index ?>][attendance_status]" data-index="<?= $index ?>" required>
                        <option value="1">Present</option>
                        <option value="2">Absent</option>
                        <option value="3">Holiday</option>
                        <option value="4">Half Day</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="salary_<?= $index ?>" class="form-label">Salary / Day</label>
                    <input type="number" class="form-control" id="salary_<?= $index ?>"
                        name="attendance[<?= $index ?>][salary_count]" step="0.50" value="1.00" <?= $admin ? '' : 'readonly'; ?> data-index="<?= $index ?>">
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        flatpickr("#attendance_date", {
            dateFormat: "d/m/Y",
            defaultDate: "today",
            allowInput: true,
            disableMobile: true, // Ensures consistent desktop-style popup on mobile too
            maxDate: "today", // Disable future dates
            minDate: new Date().fp_incr(-60) // Disable anything before 60 days ago
        });

        // Handle the Fetch button click event
        $('#fetchAttendance').click(function() {
            const selectedDate = $('#attendance_date').val();
            if (!selectedDate) {
                alert("Please select a date.");
                return;
            }

            function updateSalary(index) {
                var status = $('#status_' + index).val();
                var salaryInput = $('#salary_' + index);

                // Update salary based on status selection
                switch (status) {
                    case '1': // Present
                        $('#entry_time_' + index).prop('readonly', false);
                        $('#exit_time_' + index).prop('readonly', false);
                        $('#salary_' + index).prop('readonly', false);
                        $('#entry_time_' + index).val('09:00');
                        $('#exit_time_' + index).val('19:00');
                        $('#entry_time_' + index).show();
                        $('#entry_time_' + index).show();
                        salaryInput.val('1.00');
                        break;
                    case '2': // Absent
                        $('#entry_time_' + index).prop('readonly', true);
                        $('#exit_time_' + index).prop('readonly', true);
                        $('#salary_' + index).prop('readonly', true);
                        $('#entry_time_' + index).val('00:00');
                        $('#exit_time_' + index).val('00:00');
                        salaryInput.val('0.00');
                        break;
                    case '3': // Holiday
                        $('#entry_time_' + index).prop('readonly', true);
                        $('#exit_time_' + index).prop('readonly', true);
                        $('#salary_' + index).prop('readonly', true);
                        $('#entry_time_' + index).val('00:00');
                        $('#exit_time_' + index).val('00:00');
                        salaryInput.val('1.00');
                        break;
                    case '4': // Half Day
                        $('#entry_time_' + index).prop('readonly', false);
                        $('#exit_time_' + index).prop('readonly', false);
                        $('#salary_' + index).prop('readonly', false);
                        $('#entry_time_' + index).val('09:00');
                        $('#exit_time_' + index).val('19:00');
                        salaryInput.val('0.50');
                        break;
                    default:
                        $('#entry_time_' + index).prop('readonly', false);
                        $('#exit_time_' + index).prop('readonly', false);
                        $('#salary_' + index).prop('readonly', false);
                        $('#entry_time_' + index).val('09:00');
                        $('#exit_time_' + index).val('19:00');
                        salaryInput.val('1.00'); // Default to 1.00 if no valid status is selected
                }
            }

            // Attach change event listener to all attendance status selects
            $('[id^="status_"]').change(function() {
                var index = $(this).data('index');
                updateSalary(index);
            });

            // Initialize salary input values based on initial status
            $('[id^="status_"]').each(function() {
                var index = $(this).data('index');
                updateSalary(index);
            });

            $.ajax({
                url: '/get_attendance_data', // Update with your actual endpoint for fetching attendance data
                method: 'GET',
                data: {
                    date: selectedDate
                },
                success: function(response) {
                    const attendanceData = response.attendanceData;

                    // Populate fields for each employee
                    $('[name^="attendance"]').each(function() {
                        var index = $(this).data('index');
                        var employeeId = $('[name="attendance[' + index + '][employee_id]"]').val();

                        // Check if there's attendance data for this employee
                        if (attendanceData[employeeId]) {
                            const data = attendanceData[employeeId];
                            $('#entry_time_' + index).val(data.entry_time);
                            $('#exit_time_' + index).val(data.exit_time);
                            $('#status_' + index).val(data.status);
                            $('#salary_' + index).val(data.salary);
                            $("#attendance-entries").show();
                        } else {
                            // Reset values if no attendance data is found
                            $('#entry_time_' + index).val('09:00');
                            $('#exit_time_' + index).val('18:00');
                            $('#status_' + index).val('1'); // Default to Present
                            $('#salary_' + index).val('1.00'); // Default salary
                            $("#attendance-entries").show();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching attendance data:", error);
                    alert("Failed to retrieve attendance data. Please try again.");
                }
            });
        });
        $('#attendanceForm').on('submit', function() {
            let dateInput = $('#attendance_date');
            let parts = dateInput.val().split('/');
            if (parts.length === 3) {
                let formatted = `${parts[2]}-${parts[1]}-${parts[0]}`;
                dateInput.val(formatted);
            }
        });

    });
</script>