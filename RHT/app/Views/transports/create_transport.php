<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4>Add New Transport</h4>
        <form action="/store_transport" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="gst_number" class="form-label">GST Number:</label>
                <input type="text" name="gst_number" id="gst_number" class="form-control">
            </div>
            <div class="mb-3">
                <label for="transporter_name" class="form-label">Transporter Name:</label>
                <input type="text" name="transporter_name" id="transporter_name" class="form-control">
            </div>
            <div class="mb-3">
                <label for="transport_address" class="form-label">Transport Address:</label>
                <textarea name="transport_address" id="transport_address" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="branch_name" class="form-label">Branch Name:</label>
                <input type="text" name="branch_name" id="branch_name" class="form-control">
            </div>
            <div class="mb-3">
                <label for="branch_phone" class="form-label">Branch Phone:</label>
                <input type="text" name="branch_phone" id="branch_phone" class="form-control">
            </div>
            <div class="mb-3">
                <label for="branch_mobile" class="form-label">Branch Mobile:</label>
                <input type="text" name="branch_mobile" id="branch_mobile" class="form-control">
            </div>
            <div class="mb-3">
                <label for="branch_email" class="form-label">Branch Email:</label>
                <input type="email" name="branch_email" id="branch_email" class="form-control">
            </div>
            <div class="mb-3">
                <label for="customercare_email" class="form-label">Customer Care Email:</label>
                <input type="email" name="customercare_email" id="customercare_email" class="form-control">
            </div>
            <div class="mb-3">
                <label for="customer_care_phone" class="form-label">Customer Care Phone:</label>
                <input type="text" name="customer_care_phone" id="customer_care_phone" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Add Transport</button>
            <a href="/list_transport"  class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>
