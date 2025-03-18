<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="container mt-4">
    <h2 class="mt-3 mb-4">View Customer</h2>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Customer Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Company Name:</strong> <?= esc($customer['company_name']) ?></p>
                    <p><strong>Owner Name:</strong> <?= esc($customer['contact_name']) ?></p>
                    <p><strong>Contact Number:</strong> <?= esc($customer['contact_no']) ?></p>
                    <p><strong>Email:</strong> <?= esc($customer['email']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Credit Limit:</strong> <?= esc($customer['credit_limit']) ?></p>
                    <p><strong>Tax Type:</strong> <?= esc($customer['tax_type']) ?></p>
                    <p><strong>GST No:</strong> <?= esc($customer['gst_no']) ?></p>
                    <p><strong>Preferred Transport:</strong> <?= esc($customer['preferred_transport']) ?></p>
                    <p><strong>Payment Terms:</strong> <?= esc($customer['payment_terms']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Billing Address -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Billing Address</h4>
                </div>
                <div class="card-body">
                    <p><strong>Address Line 1:</strong> <?= esc($billingAddress['address1']) ?></p>
                    <p><strong>Address Line 2:</strong> <?= esc($billingAddress['address2']) ?></p>
                    <p><strong>City:</strong> <?= esc($billingAddress['city']) ?></p>
                    <p><strong>State:</strong> <?= esc($billingAddress['state']) ?></p>
                    <p><strong>Country:</strong> <?= esc($billingAddress['country']) ?></p>
                    <p><strong>Zip:</strong> <?= esc($billingAddress['zip']) ?></p>
                </div>
            </div>
        </div>
        <!-- Shipping Address -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Shipping Address</h4>
                </div>
                <div class="card-body">
                    <p><strong>Address Line 1:</strong> <?= esc($shippingAddress['address1']) ?></p>
                    <p><strong>Address Line 2:</strong> <?= esc($shippingAddress['address2']) ?></p>
                    <p><strong>City:</strong> <?= esc($shippingAddress['city']) ?></p>
                    <p><strong>State:</strong> <?= esc($shippingAddress['state']) ?></p>
                    <p><strong>Country:</strong> <?= esc($shippingAddress['country']) ?></p>
                    <p><strong>Zip:</strong> <?= esc($shippingAddress['zip']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="/customers/edit/<?= $customer['customer_id'] ?>" class="btn btn-primary me-2">Edit</a>
        <a href="/customers/delete/<?= $customer['customer_id'] ?>" class="btn btn-danger me-2" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
        <a href="/customers" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
