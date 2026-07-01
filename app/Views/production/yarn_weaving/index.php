<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Yarn Weaving Job Work<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-scroll text-primary me-2"></i>Yarn Weaving Job Work</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-weaving/create') ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="fas fa-plus me-1"></i> New Weaving DC</a>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px;">
    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #1565c0 0%, #1e88e5 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i>Weaving Delivery Challans</h5>
    </div>
    <div class="card-body p-3 bg-white">
        <?php if(session()->has('success')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('success') ?></div><?php endif; ?>
        <?php if(session()->has('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?= session('error') ?></div><?php endif; ?>
        
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead>
                    <tr>
                        <th class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom">DC Number</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom">Date</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom">Vendor (Weaver)</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom text-center">Status</th>
                        <th width="15%" class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach($dcs as $dc): ?>
                    <tr class="table-row-card">
                        <td class="py-2 px-3 fw-bold text-dark">
                            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle me-2" style="width: 28px; height: 28px;">
                                <i class="fas fa-file-invoice" style="font-size: 0.8rem;"></i>
                            </span>
                            <?= esc($dc['dc_number']) ?>
                        </td>
                        <td class="py-2 px-2 text-secondary" style="font-size: 0.85rem;">
                            <i class="far fa-calendar-alt text-muted me-1"></i> <?= date('d-M-Y', strtotime($dc['dc_date'])) ?>
                        </td>
                        <td class="py-2 px-2 text-dark fw-semibold" style="font-size: 0.85rem;">
                            <i class="fas fa-user-tie text-muted me-1"></i> <?= esc($dc['vendor_name']) ?>
                        </td>
                        <td class="py-2 px-2 text-center">
                            <?php 
                            $statusClass = 'secondary';
                            if ($dc['status'] === 'Open') $statusClass = 'info';
                            if ($dc['status'] === 'Partially Received') $statusClass = 'warning text-dark';
                            if ($dc['status'] === 'Completed') $statusClass = 'success';
                            if ($dc['status'] === 'Cancelled') $statusClass = 'danger';
                            ?>
                            <span class="badge rounded-pill bg-<?= $statusClass ?> px-2 py-1 text-uppercase" style="font-size: 0.65rem; font-weight: 750;">
                                <?= esc($dc['status']) ?>
                            </span>
                        </td>
                        <td class="py-2 px-3 text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="<?= site_url('production/yarn-weaving/view/'.$dc['id']) ?>" class="btn btn-xs btn-outline-info rounded-pill px-2" title="View"><i class="fas fa-eye"></i></a>
                                <?php if($dc['status'] === 'Open'): ?>
                                    <a href="<?= site_url('production/yarn-weaving/edit/'.$dc['id']) ?>" class="btn btn-xs btn-outline-warning rounded-pill px-2" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('production/yarn-weaving/delete/'.$dc['id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill px-2" onclick="return confirm('Delete this DC?')" title="Delete"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>