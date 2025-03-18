<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4>Transport List</h4>
        <a href="/create_transport" class="btn btn-primary mb-3">Add New Transport</a>
        <table id="transportTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>GST Number</th>
                    <th>Transporter Name</th>
                    <th>Branch Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transports as $transport): ?>
                    <tr>
                        <td><?= $transport['id'] ?></td>
                        <td><?= $transport['gst_number'] ?></td>
                        <td><?= $transport['transporter_name'] ?></td>
                        <td><?= $transport['branch_name'] ?></td>
                        <td>
                            <a href="/view_transport/<?= $transport['id'] ?>" class="btn btn-info btn-sm">View</a>
                            <a href="/edit_transport/<?= $transport['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="/delete_transport/<?= $transport['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transport?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>
