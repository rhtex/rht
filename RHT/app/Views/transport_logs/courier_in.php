<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4>Add New Incoming Courier</h4>
        <form action="<?= site_url('/create_transport_logs') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" id="booked_by" value="" name="booked_by">
                    <div class="mb-3">
                        <label for="sender" class="form-label">Sender:</label>
                        <input type="text" class="form-control" id="sender" name="sender">
                    </div>

                    <div class="mb-3" style="display:none;">
                        <label for="receiver" class="form-label">Receiver:</label>
                        <input type="text" class="form-control" value="RHT" id="receiver" name="receiver">
                    </div>
                    <div class="mb-3">
                        <label for="received_by" class="form-label">Received By:</label>
                        <select class="form-select" id="received_by" name="received_by">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= $user['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" class="form-control" id="price" name="price">
                    </div>

                    <div class="mb-3">
                        <label for="paid_by" class="form-label">Paid By:</label>
                        <select class="form-select" id="paid_by" name="paid_by">
                            <option value="">--SELECT WHO PAID--</option>
                            <option value="sender">Sender</option>
                            <option value="receiver">Receiver</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transport_no" class="form-label">Transport Number:</label>
                        <input type="text" class="form-control" id="transport_no" name="transport_no">
                    </div>

                    <div class="mb-3">
                        <label for="contains" class="form-label">Contains:</label>
                        <select class="form-select" id="contains" name="contains">
                            <option value="">--SELECT WHAT IT CONTAINS--</option>
                            <option value="bill">Bill</option>
                            <option value="invoice">Invoice</option>
                            <option value="debit note">Debit Note</option>
                            <option value="credit note">Credit Note</option>
                            <option value="materials">Materials</option>
                            <option value="waybill">Waybill</option>
                            <option value="cheque">Cheque</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="mb-3" id="othersreasonblockcourierin" style="display:none">
                        <label for="others_reason" class="form-label">Others Reason:</label>
                        <textarea class="form-control" id="others_reason" name="others_reason"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="transport_id" class="form-label">Transport ID:</label>
                        <select class="form-select" id="transport_id" name="transport_id">
                            <option value="">--SELECT TRANSPORT--</option>
                            <?php foreach ($transports as $transport): ?>
                                <option value="<?= $transport['id'] ?>">
                                    <?= $transport['transporter_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="sent_date" class="form-label">Sent Date:</label>
                        <input type="date" class="form-control" id="sent_date" name="sent_date">
                    </div>

                    <div class="mb-3">
                        <label for="delivered_date" class="form-label">Delivered Date:</label>
                        <input type="date" class="form-control" id="delivered_date" name="delivered_date">
                    </div>

                    <div class="mb-3" style="display:none;">
                        <label for="delivery_status" class="form-label">Delivery Status:</label>
                        <input type="text" value="NA" class="form-control" id="delivery_status" name="delivery_status">
                    </div>

                    <div class="mb-3">
                        <label for="parcel_type" class="form-label">Parcel Type:</label>
                        <select class="form-select" id="parcel_type" name="parcel_type">
                            <option value="">--SELECT PARCEL TYPE--</option>
                            <option value="cover">COVER</option>
                            <option value="parcel">PARCEL</option>
                        </select>
                    </div>

                    <div class="mb-3" style="display:none">
                        <label for="transport_type" class="form-label">Transport Type:</label>
                        <select class="form-select" id="transport_type" name="transport_type">
                            <option selected value="1">incoming</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Add Transport Log</button>
        </form>
    </div>
</div>
<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>