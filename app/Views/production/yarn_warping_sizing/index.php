<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Yarn Warping & Sizing<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6"><h1>Yarn Warping & Sizing Job Work</h1></div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-warping-sizing/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> New Warping & Sizing DC</a>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-body">
        <?php if(session()->has('success')): ?><div class="alert alert-success"><?= session('success') ?></div><?php endif; ?>
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>DC Number</th>
                    <th>Date</th>
                    <th>Vendor (Sizer)</th>
                    <th>Design Pattern</th>
                    <th>Total Ends</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dcs as $dc): ?>
                <tr>
                    <td><strong><?= esc($dc['dc_number']) ?></strong></td>
                    <td><?= esc($dc['dc_date']) ?></td>
                    <td><?= esc($dc['vendor_name']) ?></td>
                    <td><?= esc($dc['design_pattern'] ?: '-') ?></td>
                    <td><strong><?= esc($dc['total_ends'] ?: '0') ?></strong></td>
                    <td>
                        <span class="badge bg-<?php 
                            if ($dc['status'] === 'Open') echo 'primary';
                            elseif ($dc['status'] === 'Partially Received') echo 'warning';
                            elseif ($dc['status'] === 'Completed') echo 'success';
                            else echo 'danger';
                        ?>"><?= esc($dc['status']) ?></span>
                    </td>
                    <td>
                        <a href="<?= site_url('production/yarn-warping-sizing/view/'.$dc['id']) ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <?php if($dc['status'] === 'Open'): ?>
                            <a href="<?= site_url('production/yarn-warping-sizing/edit/'.$dc['id']) ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= site_url('production/yarn-warping-sizing/delete/'.$dc['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this DC?')" title="Delete"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>