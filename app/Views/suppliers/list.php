<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="mt-2">
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h4 style="color: white;">Supplier List</h4>
        </div>
        <div class="card-body">
            <a href="/suppliers/create" class="btn btn-primary mb-3">Add Supplier</a>
            <div class="table-responsive">
                <table id="suppliersTable" class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>GST No</th>
                            <th>Contact No</th>
                            <th>City</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serialNo = 1; ?>
                        <?php foreach ($suppliers as $supplier) : ?>
                            <tr>
                                <td><?= $serialNo++; ?></td>
                                <td><?= $supplier['company_name']; ?></td>
                                <td><?= $supplier['gst_no']; ?></td>
                                <td><?= $supplier['contact_no']; ?></td>
                                <td><?= $supplier['city']; ?></td>
                                <td><?= $supplier['balance']; ?></td>
                                <td>
                                    <a href="/suppliers/view/<?= $supplier['supplier_id'] ?>" class="btn btn-primary btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="/suppliers/edit/<?= $supplier['supplier_id'] ?>" class="btn btn-info btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <a href="/suppliers/delete/<?= $supplier['supplier_id'] ?>" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash3"></i></a>
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
        $('#suppliersTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Suppliers...",
            },
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>' +
                '<"row"<"col-md-12"tr>>' +
                '<"row"<"col-md-5"i><"col-md-7"p>>',
        });
    });
</script>
