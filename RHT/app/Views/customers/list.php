<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="mt-2">

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 style="color: white;">Customer List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="customerTable" class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Contact No</th>
                            <th>GST No</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serialNo = 1; ?>
                        <?php foreach ($customers as $customer) : ?>
                            <tr>
                                <td><?= $serialNo++; ?></td>
                                <td><?= $customer['company_name']; ?></td>
                                <td><?= $customer['contact_no']; ?></td>
                                <td><?= $customer['gst_no']; ?></td>
                                <td><?= $customer['balance']; ?></td>
                                <td>
                                    <a href="/customers/view/<?= $customer['customer_id'] ?>" class="btn btn-primary btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="/customers/edit/<?= $customer['customer_id'] ?>" class="btn btn-info btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <a href="/customers/delete/<?= $customer['customer_id'] ?>" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash3"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Initialize DataTable with Bootstrap styling
        $('#customerTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Customers...",
            },
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>' +
                '<"row"<"col-md-12"tr>>' +
                '<"row"<"col-md-5"i><"col-md-7"p>>',
        });
    });
</script>