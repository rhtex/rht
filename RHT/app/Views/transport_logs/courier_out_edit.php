<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4>Edit Outgoing Courier</h4>
        <form action="<?= site_url('/update_transport_logs_out/'.$log['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" id="booked_by" value="<?= $log['booked_by'] ?>" name="booked_by">
                    <div class="mb-3" style="display:none;">
                        <label for="sender" class="form-label">Sender:</label>
                        <input type="text" class="form-control" id="sender" name="sender" value="RHT">
                    </div>

                    <div class="mb-3">
                        <label for="receiver" class="form-label">Receiver:</label>
                        <input type="text" class="form-control" id="receiver" name="receiver" value="<?= $log['receiver'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="received_by" class="form-label">Received By:</label>
                        <select class="form-select" id="received_by" name="received_by">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $log['received_by'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= $user['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" class="form-control" id="price" name="price" value="<?= $log['price'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="paid_by" class="form-label">Paid By:</label>
                        <select class="form-select" id="paid_by" name="paid_by">
                            <option value="sender" <?= $log['paid_by'] == 'sender' ? 'selected' : '' ?>>Sender</option>
                            <option value="receiver" <?= $log['paid_by'] == 'receiver' ? 'selected' : '' ?>>Receiver</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transport_no" class="form-label">Transport Number:</label>
                        <input type="text" class="form-control" id="transport_no" name="transport_no" value="<?= $log['transport_no'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="contains" class="form-label">Contains:</label>
                        <select class="form-select" id="contains" name="contains">
                            <option value="bill" <?= $log['contains'] == 'bill' ? 'selected' : '' ?>>Bill</option>
                            <option value="invoice" <?= $log['contains'] == 'invoice' ? 'selected' : '' ?>>Invoice</option>
                            <option value="debit note" <?= $log['contains'] == 'debit note' ? 'selected' : '' ?>>Debit Note</option>
                            <option value="credit note" <?= $log['contains'] == 'credit note' ? 'selected' : '' ?>>Credit Note</option>
                            <option value="materials" <?= $log['contains'] == 'materials' ? 'selected' : '' ?>>Materials</option>
                            <option value="waybill" <?= $log['contains'] == 'waybill' ? 'selected' : '' ?>>Waybill</option>
                            <option value="cheque" <?= $log['contains'] == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                            <option value="others" <?= $log['contains'] == 'others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                    <div class="mb-3" id="othersreasonblockcourierout" style="display: <?= $log['contains'] == 'others' ? 'block' : 'none' ?>;">
                        <label for="others_reason" class="form-label">Others Reason:</label>
                        <textarea class="form-control" id="others_reason" name="others_reason"><?= $log['others_reason'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="transport_id" class="form-label">Transport ID:</label>
                        <select class="form-select" id="transport_id" name="transport_id">
                            <?php foreach ($transports as $transport): ?>
                                <option value="<?= $transport['id'] ?>" <?= $log['transport_id'] == $transport['id'] ? 'selected' : '' ?>>
                                    <?= $transport['transporter_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="sent_date" class="form-label">Sent Date:</label>
                        <input type="date" class="form-control" id="sent_date" name="sent_date" value="<?= $log['sent_date'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="delivered_date" class="form-label">Delivered Date:</label>
                        <input type="date" class="form-control" id="delivered_date" name="delivered_date" value="<?= $log['delivered_date'] ?>">
                    </div>

                    <div class="mb-3" style="display:none;">
                        <label for="delivery_status" class="form-label">Delivery Status:</label>
                        <input type="text" value="NA" class="form-control" id="delivery_status" name="delivery_status" value="<?= $log['delivery_status'] ?>">
                    </div>

                    <div class="mb-3">
                        <label for="parcel_type" class="form-label">Parcel Type:</label>
                        <select class="form-select" id="parcel_type" name="parcel_type">
                            <option value="cover" <?= $log['parcel_type'] == 'cover' ? 'selected' : '' ?>>COVER</option>
                            <option value="parcel" <?= $log['parcel_type'] == 'parcel' ? 'selected' : '' ?>>PARCEL</option>
                        </select>
                    </div>

                    <div class="mb-3" style="display:none">
                        <label for="transport_type" class="form-label">Transport Type:</label>
                        <select class="form-select" id="transport_type" name="transport_type">
                            <option selected value="2">outgoing</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Transport Log</button>
            </div>
        </form>
    </div>
</div>
<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>
