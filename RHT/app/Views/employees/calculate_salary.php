<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Calculate Salary</h4>

<!-- Flash Message Section -->
<div id="flashMessage" style="display: none;">
    <div class="alert" role="alert"></div>
</div>

<!-- Form to select month and employee -->
<div class="row mb-3" id="monthYear">
    <div class="col-md-6 mb-3">
        <label for="searchMonth" class="form-label">Month</label>
        <input type="month" class="form-control" id="searchMonth" name="searchMonth">
    </div>
    <div class="col-md-6 mb-3">
        <label for="searchEmployee2" class="form-label">Employee Name</label>
        <select class="form-control" id="searchEmployee2" name="searchEmployee2">
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id']; ?>"><?= esc($employee['first_name'] . ' ' . $employee['last_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<button type="button" id="generateSalary" class="btn btn-primary">Generate</button>

<!-- Salary Result Display -->
<div id="salaryResult" style="display: none; margin-top: 30px;margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 8px; background-color: #f9f9f9;">
    <h5 class="text-center text-success mb-4">Salary Payslip</h5>
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Employee ID: <span id="employee_id"></span></strong></p>
                <p><strong>Employee Name: <span id="employee_name"></span></strong></p>
                <p><strong>Month: <span id="month"></span></strong></p>
            </div>
            <div class="col-md-6 d-flex flex-column align-items-end">
                <p><strong>Employee Salary: <span id="employee_salary" class="text-primary"></span></strong></p>
                <p><strong>Per Day Salary: <span id="perday_salary" class="text-primary"></span></strong></p>
                <p><strong>Total Salary: <span id="salary_amount" class="text-success"></span></strong></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <p><strong>Total Present Days: <span id="total_present_days"class="text-primary"></span></strong></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Total Salary Days: <span id="total_days"class="text-primary"></span></strong></p>    
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <p><strong>Present Days: <span id="present_days"class="text-primary"></span></strong></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Holiday Days: <span id="holiday_days"class="text-primary"></span></strong></p>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <p><strong>Leave Days: <span id="leave_days"class="text-primary"></span></strong></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Half Days: <span id="half_days"class="text-primary"></span></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Get today's date
        var today = new Date();

        // Format the current year and month (YYYY-MM format)
        var year = today.getFullYear();
        var month = today.getMonth(); // Get current month (0-indexed, so add 1)
        if (month < 10) {
            month = '0' + month; // Add leading zero for single digit months
        }

        // Set the current month as the value of the month input field
        $('#searchMonth').val(year + '-' + month);

        // Set the max attribute of the month input field to the current month
        $('#searchMonth').attr('max', year + '-' + month);

        // On clicking the Generate button
        $('#generateSalary').click(function() {
            var month = $('#searchMonth').val();
            var employeeId = $('#searchEmployee2').val();

            // Make the AJAX request to calculate the salary
            $.ajax({
                url: '/salary-history/generate', // Change this to the correct URL for your controller action
                method: 'POST',
                data: {
                    month: month,
                    employee_id: employeeId
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        // Show the result in the view
                        $('#salaryResult').show();
                        $('#employee_id').text(response.employee_id);
                        $('#employee_name').text(response.employee_name);
                        $('#employee_salary').text(response.employee_salary);
                        $('#month').text(response.month);
                        $('#perday_salary').text(response.perday_salary);
                        $('#salary_amount').text(response.salary_amount);
                        $('#total_days').text(response.total_days);
                        $('#total_present_days').text(response.total_present_days);
                        $('#present_days').text(response.present_days);
                        $('#holiday_days').text(response.holiday_days);
                        $('#leave_days').text(response.leave_days);
                        $('#half_days').text(response.half_days);

                        // Show flash message with the response message
                        showFlashMessage(response.message, 'success');
                    } else {
                        // If response is not successful, show error message
                        showFlashMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    showFlashMessage('Error calculating salary.', 'danger');
                }
            });
        });

        // Function to show flash message
        function showFlashMessage(message, type) {
            var flashMessage = $('#flashMessage');
            flashMessage.show();
            flashMessage.find('.alert').removeClass('alert-success alert-danger'); // Clear previous types
            flashMessage.find('.alert').addClass('alert-' + type); // Add appropriate alert type
            flashMessage.find('.alert').text(message); // Set the message text
        }
    });
</script>
