<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-palette text-primary me-2"></i>Color Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/colors/create') ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="fas fa-plus me-1"></i> Add Color</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px;">
    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i>Colors List</h5>
    </div>
    <div class="card-body p-3 bg-white">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0" id="colorsTable">
                <thead>
                    <tr>
                        <th class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom">Color Name</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom">Color Code</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom text-center">Color Palette</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom text-center">Status</th>
                        <th width="15%" class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php if(!empty($colors)): ?>
                        <?php foreach($colors as $color): ?>
                        <tr>
                            <td class="py-2 px-3 fw-bold text-dark"><?= esc($color['name']) ?></td>
                            <td class="py-2 px-2 text-secondary font-monospace"><?= esc($color['code'] ?: '-') ?></td>
                            <td class="py-2 px-2 text-center">
                                <?php if($color['color_palette']): ?>
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded border border-light-subtle shadow-sm" style="width: 24px; height: 24px; background-color: <?= esc($color['color_palette']) ?>;"></span>
                                        <span class="small font-monospace text-muted"><?= esc($color['color_palette']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-2 text-center">
                                <span class="badge rounded-pill bg-<?= $color['status'] === 'Active' ? 'success' : 'danger' ?> px-2 py-1 text-uppercase" style="font-size: 0.65rem; font-weight: 750;">
                                    <?= esc($color['status']) ?>
                                </span>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= site_url('production/colors/edit/'.$color['id']) ?>" class="btn btn-xs btn-warning text-dark rounded-pill px-2" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('production/colors/delete/'.$color['id']) ?>" class="btn btn-xs btn-danger text-white rounded-pill px-2" onclick="return confirm('Delete this color?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-palette fa-2x text-muted mb-2 d-block"></i>
                                No Colors found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined') {
            if ($.fn.DataTable.isDataTable('#colorsTable')) {
                $('#colorsTable').DataTable().destroy();
            }
            $('#colorsTable').DataTable({
                "order": [[ 0, "asc" ]],
                "pageLength": 25,
                "searching": true
            });
        }
    });
</script>
<?= $this->endSection() ?>
