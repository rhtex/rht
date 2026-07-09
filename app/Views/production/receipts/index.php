<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Weaver</th>
                            <th>Allocation #</th>
                            <th>Status</th>
                            <th>Ref Doc</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($receipts as $receipt): ?>
                            <tr>
                                <td><?= esc($receipt['receipt_number']) ?></td>
                                <td><?= esc($receipt['receipt_date']) ?></td>
                                <td><?= esc($receipt['weaver_name']) ?></td>
                                <td><?= esc($receipt['allocation_number']) ?></td>
                                <td>
                                    <?php
                                        $statusClass = 'secondary';
                                        switch($receipt['status']) {
                                            case 'Pending QC': $statusClass = 'warning'; break;
                                            case 'Approved': $statusClass = 'success'; break;
                                            case 'Cancelled': $statusClass = 'danger'; break;
                                        }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= esc($receipt['status']) ?></span>
                                </td>
                                <td>
                                    <?php if($receipt['reference_document_type'] == 'Purchase Entry'): ?>
                                        <a href="<?= site_url('bills/view/' . $receipt['reference_document_id']) ?>" target="_blank">Purchase Bill</a>
                                    <?php else: ?>
                                        Settlement #<?= $receipt['reference_document_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('production/receipts/view/' . $receipt['id']) ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            "order": [[ 0, "desc" ]],
            "responsive": true
        });
    });
</script>
<?= $this->endSection() ?>
