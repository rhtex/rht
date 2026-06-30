<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($selectedType) ?> Delivery Challans<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><?= esc($selectedType) ?> Delivery Challans</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('production/yarn-job-work/create?type='.esc($selectedType)) ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Create <?= esc($selectedType) ?> DC</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of <?= esc($selectedType) ?> Delivery Challans (DC)</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dcsTable">
                <thead>
                    <tr>
                        <th>DC Number</th>
                        <th>DC Date</th>
                        <th>Vendor Name</th>
                        <th>Job Work Type</th>
                        <th>Expected Return</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($dcs)): ?>
                        <?php foreach($dcs as $dc): ?>
                        <tr>
                            <td><strong><?= esc($dc['dc_number']) ?></strong></td>
                            <td><?= esc($dc['dc_date']) ?></td>
                            <td><?= esc($dc['vendor_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= esc($dc['job_work_type']) ?></span></td>
                            <td><?= esc($dc['expected_return_date'] ?: '-') ?></td>
                            <td>
                                <?php 
                                    $badge = 'secondary';
                                    if ($dc['status'] === 'Open') $badge = 'primary';
                                    elseif ($dc['status'] === 'Partially Received') $badge = 'warning';
                                    elseif ($dc['status'] === 'Completed') $badge = 'success';
                                    elseif ($dc['status'] === 'Cancelled') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>">
                                    <?= esc($dc['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('production/yarn-job-work/view/'.$dc['id']) ?>" class="btn btn-sm btn-info" title="View & Print">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if((in_array('weaver.edit', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])) && $dc['status'] === 'Open'): ?>
                                <a href="<?= site_url('production/yarn-job-work/edit/'.$dc['id']) ?>" class="btn btn-sm btn-warning" title="Edit DC">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= site_url('production/yarn-job-work/delete/'.$dc['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this Delivery Challan? This will restore the issued stock.')" title="Delete DC">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('weaver.create', session('permissions') ?? []) && ($dc['status'] === 'Open' || $dc['status'] === 'Partially Received')): ?>
                                <a href="<?= site_url('production/yarn-job-work/receipt/'.$dc['id']) ?>" class="btn btn-sm btn-success" title="Receive Stock">
                                    <i class="fas fa-arrow-down"></i> Receive Yarn
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#dcsTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>
<?= $this->endSection() ?>
