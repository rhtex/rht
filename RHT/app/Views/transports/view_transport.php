<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4>Transport Details</h4>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Transport Information</h5>
                <p><strong>ID:</strong> <?= $transport['id'] ?></p>
                <p><strong>GST Number:</strong> <?= $transport['gst_number'] ?></p>
                <p><strong>Transporter Name:</strong> <?= $transport['transporter_name'] ?></p>
                <p><strong>Transport Address:</strong> <?= $transport['transport_address'] ?></p>
                <p><strong>Branch Name:</strong> <?= $transport['branch_name'] ?></p>
                <p><strong>Branch Phone:</strong> <?= $transport['branch_phone'] ?></p>
                <p><strong>Branch Mobile:</strong> <?= $transport['branch_mobile'] ?></p>
                <p><strong>Branch Email:</strong> <?= $transport['branch_email'] ?></p>
                <p><strong>Customer Care Email:</strong> <?= $transport['customercare_email'] ?></p>
                <p><strong>Customer Care Phone:</strong> <?= $transport['customer_care_phone'] ?></p>
            </div>
            <div class="card-footer">
                <a href="/edit_transport/<?= $transport['id'] ?>" class="btn btn-warning">Edit</a>
                <a href="/delete_transport/<?= $transport['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this transport?')">Delete</a>
                <a href="/list_transport" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>
