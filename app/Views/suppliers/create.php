<!-- app\Views\suppliers\create.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Supplier</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Create Supplier</h1>
        <form action="/suppliers/create" method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_name" class="form-label">Company Name:</label>
                    <input type="text" name="company_name" id="company_name" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_name" class="form-label">Owner Name:</label>
                    <input type="text" name="contact_name" id="contact_name" class="form-control" required>
                </div>
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label for="tax_type" class="form-label">Tax Type:</label>
                    <select name="tax_type" id="tax_type" class="form-select" required>
                        <option value="1">Registered</option>
                        <option value="2">Unregistered</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="msme_registered" class="form-label">MSME Registered:</label>
                    <div class="form-check">
                        <input type="radio" id="msme_registered1" name="msme_registered" value="1" class="form-check-input">
                        <label for="msme_registered1" class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" id="msme_registered2" name="msme_registered" value="2" class="form-check-input">
                        <label for="msme_registered2" class="form-check-label">No</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="gst_no" class="form-label">GST Number:</label>
                    <input type="text" name="gst_no" id="gst_no" class="form-control">
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="contact_no" class="form-label">Supplier Contact Number:</label>
                    <input type="text" name="contact_no" id="contact_no" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" autocomplete="off" id="email" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="address1" class="form-label">Address Line 1:</label>
                    <textarea name="address1" id="address1" class="form-control" required></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="address2" class="form-label">Address Line 2:</label>
                    <textarea name="address2" id="address2" class="form-control"></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label">City:</label>
                    <input type="text" name="city" id="city" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="zip" class="form-label">Pin Code:</label>
                    <input type="text" name="zip" id="zip" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="country" class="form-label">Country:</label>
                    <select name="country" id="country" class="form-select" required>
                        <option value="">Select Country</option>
                        <option value="India">India</option>
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

            <button type="submit" class="btn btn-primary">Create Supplier</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies (optional, if needed) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#country').change(function() {
                var country = $(this).val();
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
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching states:', error);
                        }
                    });
                } else {
                    $('#state').empty();
                    $('#state').append('<option value="">Select State</option>');
                }
            });
        });
    </script>
</body>

</html>