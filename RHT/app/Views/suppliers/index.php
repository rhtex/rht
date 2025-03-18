<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Suppliers</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">List of Suppliers</h1>
        
        <table id="suppliersTable" class="display table table-striped">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>GST No</th>
                    <th>Contact No</th>
                    <th>City</th>
                    <th>Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated by DataTables via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#suppliersTable').DataTable({
                "ajax": {
                    "url": "/suppliers/getSuppliers",
                    "type": "GET",
                    "dataSrc": ""
                },
                "columns": [
                    { "data": "company_name" },
                    { "data": "gst_no" },
                    { "data": "contact_no" },
                    { "data": "city" },
                    { "data": "balance" },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<a href="/suppliers/edit/' + row.supplier_id + '" class="btn btn-primary btn-sm">Edit</a> ' +
                                   '<a href="/suppliers/delete/' + row.supplier_id + '" class="btn btn-danger btn-sm">Delete</a>';
                        }
                    }
                ]
            });

            $('#supplierNameFilter').on('keyup', function() {
                table.columns(1).search(this.value).draw();
            });

            $('#cityFilter').on('keyup', function() {
                table.columns(9).search(this.value).draw();
            });

            $('#balanceFilter').on('keyup', function() {
                table.columns(10).search(this.value).draw();
            });
        });
    </script>
</body>
</html>
