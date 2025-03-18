<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Add Attendance</h4>

<form action="/store_attendance" method="post" id="attendanceForm">
    <div class="mb-3">
        <label for="attendance_date" class="form-label">Date</label>
        <input type="date" class="form-control" id="attendance_date" name="attendance_date"
            value="<?= date('Y-m-d') ?>" required>
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
                        salaryInput.val('1.00');
                        break;
                    case '2': // Absent
                        salaryInput.val('0.00');
                        break;
                    case '3': // Holiday
                        salaryInput.val('1.00');
                        break;
                    case '4': // Half Day
                        salaryInput.val('0.50');
                        break;
                    default:
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
                            $('#entry_time_' + index).val('00:00');
                            $('#exit_time_' + index).val('00:00');
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
    });
</script>