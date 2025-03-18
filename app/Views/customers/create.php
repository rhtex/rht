<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="container mt-2">
    <h1 class="mb-4">Create Customer</h1>
    <div class="row">
        <!-- Edit Customer Card -->
        <div class="col-md-12 mb-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Customer Details
                </div>
                <div class="card-body">
                    <form action="/customers/create/" method="post">
                        <input type="hidden" value="" name="zoho_contact_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name:</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_name" class="form-label">Owner Name:</label>
                                <input type="text" name="contact_name" id="contact_name" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="credit_limit" class="form-label">Credit Limit:</label>
                                <input type="text" name="credit_limit" id="credit_limit" value="" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tax_type" class="form-label">Tax Type:</label>
                                <select name="tax_type" id="tax_type" class="form-select" required>
                                    <option value="1">Registered</option>
                                    <option value="2">Unregistered</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gst_no" class="form-label">GST Number:</label>
                                <input type="text" name="gst_no" id="gst_no" value="" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferred_transport" class="form-label">Preferred Transport:</label>
                                <input type="text" name="preferred_transport" id="preferred_transport" value="" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_no" class="form-label">Customer Contact Number:</label>
                                <input type="text" name="contact_no" id="contact_no" class="form-control" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" autocomplete="off" id="email" class="form-control" value="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="agent" class="form-label">Agent:</label>
                                <input type="text" name="agent" id="agent" class="form-control" value="">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label">Payment Terms:</label>
                                <select name="payment_terms" id="payment_terms" class="form-select">

                                    <option value="0">Due on receipt</option>
                                    <option value="10">10 Days</option>
                                    <option value="20">20 Days</option>
                                    <option value="30">30 Days</option>
                                    <option value="45">45 Days</option>
                                    <option value="60">60 Days</option>
                                    <option value="90">90 Days</option>
                                </select>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-2">
            <div class="card">
                <div class="card-body">
                    <input class="form-check-input" type="checkbox" id="copy_address" name="copy_address" onchange="copyBillingAddress()">
                    <label class="form-check-label" for="copy_address">Same as Billing Address</label>
                </div>
            </div>
        </div>
        <!-- Billing Address Card -->
        <div class="col-md-6 mb-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Billing Address
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="billing_address1" class="form-label">Address Line 1:</label>
                        <input type="text" name="billing_address1" id="billing_address1" class="form-control" value="" required>
                    </div>
                    <div class="mb-3">
                        <label for="billing_address2" class="form-label">Address Line 2:</label>
                        <input type="text" name="billing_address2" id="billing_address2" class="form-control" value="">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="billing_city" class="form-label">City:</label>
                            <input type="text" name="billing_city" id="billing_city" class="form-control" value="" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="billing_zip" class="form-label">Zip:</label>
                            <input type="text" name="billing_zip" id="billing_zip" class="form-control" value="" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="billing_state" class="form-label">State:</label>
                            <select name="billing_state" id="billing_state" class="form-select" required>
                                <option value="">Select State</option>
                                <!-- Populate dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="billing_country" class="form-label">Country:</label>
                            <select name="billing_country" id="billing_country" class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="India">India</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shipping Address Card -->
        <div class="col-md-6 mb-2">
            <div class="card">
                <div class="card-header bg-info text-white">
                    Shipping Address
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="shipping_address1" class="form-label">Address Line 1:</label>
                        <input type="text" name="shipping_address1" id="shipping_address1" class="form-control" value="">
                    </div>
                    <div class="mb-3">
                        <label for="shipping_address2" class="form-label">Address Line 2:</label>
                        <input type="text" name="shipping_address2" id="shipping_address2" class="form-control" value="">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_city" class="form-label">City:</label>
                            <input type="text" name="shipping_city" id="shipping_city" class="form-control" value="">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shipping_zip" class="form-label">Zip:</label>
                            <input type="text" name="shipping_zip" id="shipping_zip" class="form-control" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_state" class="form-label">State:</label>
                            <select name="shipping_state" id="shipping_state" class="form-select">
                                <option value="">Select State</option>
                                <!-- Populate dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shipping_country" class="form-label">Country:</label>
                            <select name="shipping_country" id="shipping_country" class="form-select">
                                <option value="">Select Country</option>
                                <option value="India">India</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><button type="submit" class="btn btn-primary">Update</button>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        $('#billing_country').change(function() {
            var country = $(this).val();
            if (country == 'India') {
                $.ajax({
                    url: '/getStates',
                    type: 'POST',
                    data: {
                        country: country
                    },
                    success: function(response) {
                        var states = response;
                        $('#billing_state').empty();
                        $('#billing_state').append('<option value="">Select State</option>');
                        $.each(states, function(index, state) {
                            $('#billing_state').append('<option value="' + state.state_code + '">' + state.state_name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching states:', error);
                    }
                });
            } else {
                $('#billing_state').empty();
                $('#billing_state').append('<option value="">Select State</option>');
            }
        });
        $('#shipping_country').change(function() {
            var country = $(this).val();
            if (country == 'India') {
                $.ajax({
                    url: '/getStates',
                    type: 'POST',
                    data: {
                        country: country
                    },
                    success: function(response) {
                        var states = response;
                        $('#shipping_state').empty();
                        $('#shipping_state').append('<option value="">Select State</option>');
                        $.each(states, function(index, state) {
                            $('#shipping_state').append('<option value="' + state.state_code + '">' + state.state_name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching states:', error);
                    }
                });
            } else {
                $('#shipping_state').empty();
                $('#shipping_state').append('<option value="">Select State</option>');
            }
        });
    });
</script>
<script>
    function copyBillingAddress() {
        if (document.getElementById('copy_address').checked) {
            document.getElementById('shipping_address1').value = document.getElementById('billing_address1').value;
            document.getElementById('shipping_address2').value = document.getElementById('billing_address2').value;
            document.getElementById('shipping_city').value = document.getElementById('billing_city').value;
            document.getElementById('shipping_state').value = document.getElementById('billing_state').value;
            document.getElementById('shipping_country').value = document.getElementById('billing_country').value;
            document.getElementById('shipping_zip').value = document.getElementById('billing_zip').value;
        } else {
            document.getElementById('shipping_address1').value = '';
            document.getElementById('shipping_address2').value = '';
            document.getElementById('shipping_city').value = '';
            document.getElementById('shipping_state').value = '';
            document.getElementById('shipping_country').value = '';
            document.getElementById('shipping_zip').value = '';
        }
    }
</script>