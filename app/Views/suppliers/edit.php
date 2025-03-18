<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Edit Supplier</h1>
        <form action="/suppliers/update/<?= $supplier['supplier_id'] ?>" method="post">
        <input type="hidden" value="<?= $supplier['zoho_contact_id'] ?>" name="zoho_contact_id">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_name" class="form-label">Company Name:</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="<?= $supplier['company_name'] ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_name" class="form-label">Owner Name:</label>
                    <input type="text" name="contact_name" id="contact_name" class="form-control" value="<?= $supplier['contact_name'] ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tax_type" class="form-label">Tax Type:</label>
                    <select name="tax_type" id="tax_type" class="form-select" required>
                        <option value="1" <?= $supplier['tax_type'] == 1 ? 'selected' : '' ?>>Registered</option>
                        <option value="2" <?= $supplier['tax_type'] == 2 ? 'selected' : '' ?>>Unregistered</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="msme_registered" class="form-label">MSME Registered:</label>
                    <div class="form-check">
                        <input type="radio" id="msme_registered1" name="msme_registered" value="1" class="form-check-input" <?= $supplier['msme_registered'] == 1 ? 'checked' : '' ?>>
                        <label for="msme_registered1" class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="msme_registered2" name="msme_registered" value="2" class="form-check-input" <?= $supplier['msme_registered'] == 2 ? 'checked' : '' ?>>
                        <label for="msme_registered2" class="form-check-label">No</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="gst_no" class="form-label">GST Number:</label>
                    <input type="text" name="gst_no" id="gst_no" class="form-control" value="<?= $supplier['gst_no'] ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="payment_terms" class="form-label">Payment Terms:</label>
                    <select name="payment_terms" id="payment_terms" class="form-select">
                        <option value="0" <?= $supplier['payment_terms'] == 0 ? 'selected' : '' ?>>Due on receipt</option>
                        <option value="10" <?= $supplier['payment_terms'] == 10 ? 'selected' : '' ?>>10 Days</option>
                        <option value="20" <?= $supplier['payment_terms'] == 20 ? 'selected' : '' ?>>20 Days</option>
                        <option value="30" <?= $supplier['payment_terms'] == 30 ? 'selected' : '' ?>>30 Days</option>
                        <option value="45" <?= $supplier['payment_terms'] == 45 ? 'selected' : '' ?>>45 Days</option>
                        <option value="60" <?= $supplier['payment_terms'] == 60 ? 'selected' : '' ?>>60 Days</option>
                        <option value="90" <?= $supplier['payment_terms'] == 90 ? 'selected' : '' ?>>90 Days</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="contact_no" class="form-label">Supplier Contact Number:</label>
                    <input type="text" name="contact_no" id="contact_no" class="form-control" value="<?= $supplier['contact_no'] ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" autocomplete="off" id="email" class="form-control" value="<?= $supplier['email'] ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="address1" class="form-label">Address Line 1:</label>
                    <textarea name="address1" id="address1" class="form-control" required><?= $supplier['address1'] ?></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="address2" class="form-label">Address Line 2:</label>
                    <textarea name="address2" id="address2" class="form-control"><?= $supplier['address2'] ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label">City:</label>
                    <input type="text" name="city" id="city" class="form-control" value="<?= $supplier['city'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="zip" class="form-label">PinCode:</label>
                    <input type="text" name="zip" id="zip" class="form-control" value="<?= $supplier['zip'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="country" class="form-label">Country:</label>
                    <select name="country" id="country" class="form-select" required>
                        <option value="">Select Country</option>
                        <option value="India" <?= $supplier['country'] == 'India' ? 'selected' : '' ?>>India</option>
                        <!-- Add other countries as needed -->
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="state" class="form-label">State:</label>
                    <select name="state" id="state" class="form-select" required>
                        <option value="">Select State</option>
                        <!-- States will be populated dynamically -->
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Supplier</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies (optional, if needed) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Function to fetch and populate states
            function populateStates(country) {
                if (country == 'India') {
                    $.ajax({
                        url: '/getStates',
                        type: 'POST',
                        data: {
                            country: country
                        },
                        success: function(response) {
                            $('#state').empty();
                            $('#state').append('<option value="">Select State</option>');
                            $.each(response, function(index, state) {
                                $('#state').append('<option value="' + state.state_code + '">' + state.state_name + '</option>');
                            });
                            // Set the current state
                            $('#state').val('<?= $supplier['state'] ?>');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching states:', error);
                        }
                    });
                } else {
                    $('#state').empty();
                    $('#state').append('<option value="">Select State</option>');
                }
            }

            // Populate states on page load if country is already selected
            var selectedCountry = $('#country').val();
            if (selectedCountry) {
                populateStates(selectedCountry);
            }

            // Populate states when country changes
            $('#country').change(function() {
                populateStates($(this).val());
            });
        });
    </script>
</body>

</html>
