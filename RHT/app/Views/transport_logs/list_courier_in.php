<!-- Include head.php -->

<?php include __DIR__ . '/../layouts/head.php'; ?>



<!-- Include layout-vertical.php -->

<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>





    

        <h4>Incoming Courier List</h4>

        <a href="<?= site_url('/add_new_courier_in') ?>" class="btn btn-primary mb-3">Add New Incoming Courier</a>



        <div class="">

            <table id="transportLogsTable" class="table table-striped table-bordered">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Sender</th>

                        <th>Receiver</th>

                        <th>Price</th>

                        <th>Paid By</th>

                        <th>Transport Number</th>

                        <th>Contains</th>

                        <th>Transport ID</th>

                        <th>Sent Date</th>

                        <th>Delivered Date</th>

                        <th>Delivery Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($transportLogs as $transportLog): ?>

                        <tr>

                            <td>

                                <?= $transportLog['id'] ?>

                            </td>

                            <td>

                                <?= $transportLog['sender'] ?>

                            </td>

                            <td>

                                <?= $transportLog['receiver'] ?>

                            </td>

                            <td>

                                <?= $transportLog['price'] ?>

                            </td>

                            <td>

                                <?= ucfirst($transportLog['paid_by']) ?>

                            </td>

                            <td>

                                <?= $transportLog['transport_no'] ?>

                            </td>

                            <td>

                                <?= $transportLog['contains'] ?>

                            </td>

                            <td>

                                <?= $transportLog['transport_id'] ?>

                            </td>

                            <td>

                                <?= $transportLog['sent_date'] ?>

                            </td>

                            <td>

                                <?= $transportLog['delivered_date'] ?>

                            </td>

                            <td>

                                <?= $transportLog['delivery_status'] ?>

                            </td>

                            <td>

                                <a href="<?= site_url('/courier_in_edit/' . $transportLog['id']) ?>"

                                    class="btn btn-sm btn-primary">Edit</a>

                                <a href="<?= site_url('/transport_logs/delete/' . $transportLog['id']) ?>"

                                    class="btn btn-sm btn-danger"

                                    onclick="return confirm('Are you sure you want to delete this transport log?')">Delete</a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- Include footer.php -->

<?php include __DIR__ . '/../layouts/footer.php'; ?>