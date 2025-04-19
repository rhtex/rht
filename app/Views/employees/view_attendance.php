<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


<h4> View Attendance</h4>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php elseif (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div id="flashMessage"></div>
<a href="/add_attendance" class="btn btn-success mb-3">Add Attendance</a>
<a href="/salary-history" class="btn btn-primary mb-3">View Salary History</a>
<a href="/calculate_salary" class="btn btn-danger mb-3">Calculate Salary</a>
<!-- Tabs -->
<ul class="tabs" id="attendanceTab">
    <li data-tab="monthYear" class="active">Search by Month & Year</li>
</ul>

<div class="tab-content active" id="monthYear">
    <div class="mb-3 col-md-4">
        <label for="searchMonth" class="form-label">Month</label>
        <input type="month" class="form-control" id="searchMonth" name="searchMonth">
    </div>
    <div class="mb-3 col-md-4">
        <label for="searchEmployee2" class="form-label">Employee Name</label>
        <select class="form-control" id="searchEmployee2" name="searchEmployee2">
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id']; ?>"><?= esc($employee['first_name'] . ' ' . $employee['last_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" id="monthSearch" class="btn btn-primary">Search</button>
    <div id="calendar" style="margin-top: 10px;"></div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        $('#attendanceTab li').click(function() {
            // Remove active class from all tabs and contents
            $('#attendanceTab li').removeClass('active');
            $('.tab-content').removeClass('active');

            // Add active class to the clicked tab and the corresponding content
            $(this).addClass('active');
            $('#' + $(this).data('tab')).addClass('active');
        });

        var today = new Date().toISOString().split('T')[0];
        $('#searchDate').val(today);
        var today1 = new Date();

        var year = today1.getFullYear();
        var month = today1.getMonth() + 1;
        if (month < 10) {
            month = '0' + month; // Add leading zero for single digit months
        }
        $('#searchMonth').val(year + '-' + month);

        // Handle Month & Year Search
        $('#monthSearch').click(function(e) {
            e.preventDefault();

            var searchMonth = $('#searchMonth').val();
            var searchEmployee2 = $('#searchEmployee2').val();

            var formData = {
                searchMonth: searchMonth,
                searchEmployee2: searchEmployee2
            };

            $.ajax({
                url: '<?= base_url('search_by_month_year_ajax') ?>',
                method: 'get',
                data: formData,
                success: function(response) {
                    var attendanceData = response.attendanceData;
                    createCalendar(searchMonth, attendanceData);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Function to create the calendar with detailed data
        function createCalendar(monthYear, attendanceData) {
            var monthYearArray = monthYear.split('-');
            var year = parseInt(monthYearArray[0]);
            var month = parseInt(monthYearArray[1]) - 1; // month is 0-based in JavaScript Date

            var firstDay = new Date(Date.UTC(year, month, 1));
            var lastDay = new Date(Date.UTC(year, month + 1, 0));

            var calendarHtml = '<table class="table table-bordered"><thead><tr>';
            calendarHtml += '<th style="font-size: 18px;">Sun</th><th style="font-size: 18px;">Mon</th><th style="font-size: 18px;">Tue</th><th style="font-size: 18px;">Wed</th><th style="font-size: 18px;">Thu</th><th style="font-size: 18px;">Fri</th><th style="font-size: 18px;">Sat</th>';
            calendarHtml += '</tr></thead><tbody><tr>';

            var dayOfWeek = firstDay.getUTCDay(); // Adjusted to use UTC
            for (var i = 0; i < dayOfWeek; i++) {
                calendarHtml += '<td></td>';
            }

            for (var day = 1; day <= lastDay.getUTCDate(); day++) {
                var currentDate = new Date(Date.UTC(year, month, day));
                var dateString = currentDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD

                var className = '';
                var details = '';

                if (attendanceData[dateString]) {
                    var entryExitText = 'Entry: ' + attendanceData[dateString].entry_time + '<br>Exit: ' + attendanceData[dateString].exit_time;
                    var salaryCountText = 'Salary: ' + attendanceData[dateString].salary_count;

                    // Determine the status class
                    if (attendanceData[dateString].status === '1') {
                        className = 'bg-success'; // Green for present
                        details = 'Present<br>' + entryExitText + '<br>' + salaryCountText;
                    } else if (attendanceData[dateString].status === '2') {
                        className = 'bg-danger'; // Red for absent
                        details = 'Absent<br>' + entryExitText + '<br>' + salaryCountText;
                    } else if (attendanceData[dateString].status === '3') {
                        className = 'bg-info'; // Yellow for holiday
                        details = 'Holiday<br>' + entryExitText + '<br>' + salaryCountText;
                    } else if (attendanceData[dateString].status === '4') {
                        className = 'bg-warning'; // Yellow for holiday
                        details = 'Holiday<br>' + entryExitText + '<br>' + salaryCountText;
                    }
                }

                // Generate HTML for each day in the calendar
                calendarHtml += '<td class="' + className + '" style="padding: 10px; width: 100px; height: 100px;">';
                calendarHtml += '<span style="font-size: 20px; font-weight: bold">' + day + '</span><br>' + details;
                calendarHtml += '</td>';

                // Start a new row if it's the last day of the week (Saturday)
                if ((day + dayOfWeek) % 7 === 0) {
                    calendarHtml += '</tr><tr>';
                }
            }

            // Fill in the empty days after the last day of the month
            var remainingDays = (7 - (lastDay.getUTCDay() + 1)) % 7;
            for (var i = 0; i < remainingDays; i++) {
                calendarHtml += '<td></td>';
            }

            calendarHtml += '</tr></tbody></table>';
            $('#calendar').html(calendarHtml); // Append the calendar to the div
        }

    });
</script>