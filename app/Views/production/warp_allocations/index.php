<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/warp-allocations/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> New Allocation</a>
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
                            <th>Allocation #</th>
                            <th>Date</th>
                            <th>Weaver</th>
                            <th>Loom</th>
                            <th>Warp Beam #</th>
                            <th>Contract Type</th>
                            <th>Ref Doc</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allocations as $allocation): ?>
                            <tr>
                                <td><?= esc($allocation['allocation_number']) ?></td>
                                <td><?= esc($allocation['allocation_date']) ?></td>
                                <td><?= esc($allocation['weaver_name']) ?></td>
                                <td><?= esc($allocation['loom_number']) ?></td>
                                <td><?= $allocation['warp_beam_id'] ? esc($allocation['beam_number']) : '<span class="text-muted">None</span>' ?></td>
                                <td><span class="badge bg-info"><?= esc($allocation['contract_type']) ?></span></td>
                                <td>
                                    <?php if($allocation['reference_document_type'] == 'Sales Invoice'): ?>
                                        <a href="<?= site_url('bills/view/' . $allocation['reference_document_id']) ?>" target="_blank">Sales Invoice #<?= $allocation['reference_document_id'] ?></a>
                                    <?php else: ?>
                                        DC
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $statusClass = 'secondary';
                                        switch($allocation['status']) {
                                            case 'Allocated': $statusClass = 'primary'; break;
                                            case 'In Weaving': $statusClass = 'warning'; break;
                                            case 'Partially Returned': $statusClass = 'info'; break;
                                            case 'Completed': $statusClass = 'success'; break;
                                        }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= esc($allocation['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= site_url('production/warp-allocations/view/' . $allocation['id']) ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if(in_array($allocation['status'], ['Allocated', 'In Weaving', 'Partially Returned'])): ?>
                                        <a href="<?= site_url('production/receipts/create/' . $allocation['id']) ?>" class="btn btn-sm btn-success" title="Receive Goods"><i class="fas fa-box-open"></i></a>
                                    <?php endif; ?>
                                    <?php if($allocation['status'] == 'Allocated' && in_array('weaver.delete', session('permissions') ?? [])): ?>
                                        <a href="<?= site_url('production/warp-allocations/delete/' . $allocation['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this allocation?');"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
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
