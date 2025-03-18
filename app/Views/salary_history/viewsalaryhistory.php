<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4> View Salary History</h4>

<div class="tab-content active" id="monthYear">
    <div class="mb-3">
        <label for="searchEmployee2" class="form-label">Employee Name</label>
        <select class="form-control" id="searchEmployee2" name="searchEmployee2">
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id']; ?>"><?= esc($employee['first_name'] . ' ' . $employee['last_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="button" id="historySearch" class="btn btn-primary">Search</button>
</div>

<!-- Table to display salary history -->
<table id="salary-history-table" class="table table-bordered table-striped" style="width:100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Salary Amount</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dynamic content will be inserted here via JavaScript -->
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        var table = $('#salary-history-table').DataTable();

        // When the search button is clicked
        $('#historySearch').on('click', function() {
            var employeeId = $('#searchEmployee2').val(); // Get selected employee ID
            
            // Clear the existing table data
            table.clear().draw();

            // Make the AJAX request to fetch salary history for the selected employee
            $.ajax({
                url: '/salary-history/getSalaryHistory', // Your controller method URL
                type: 'POST',
                data: { employee_id: employeeId },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Populate the DataTable with the fetched data
                        data.forEach(function(row) {
                            table.row.add([
                                row.id,
                                row.employee_name,
                                row.salary_amount,
                                row.start_date,
                                row.end_date,
                                row.status,
                                row.actions
                            ]).draw();
                        });
                    }
                },
                error: function() {
                    alert('Error fetching salary history.');
                }
            });
        });
    });
</script>
