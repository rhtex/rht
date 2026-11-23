<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customer Order <?= esc($order['order_number'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<?php
$ordNo    = $order['order_number'] ?: ('CO-' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT));
$isClosed = ($order['status'] === 'Closed');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <h1 class="m-0 fs-4">
        <i class="fas fa-swatchbook text-primary me-2"></i>
        <?= esc($ordNo) ?>
        <span class="badge <?= $isClosed ? 'bg-secondary' : 'bg-success' ?> fs-6 ms-1"><?= esc($order['status'] ?: 'Active') ?></span>
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= site_url('customer_orders/toggle-status/' . $order['id']) ?>" class="btn btn-sm btn-<?= $isClosed ? 'success' : 'warning' ?>">
            <i class="fas fa-<?= $isClosed ? 'lock-open' : 'lock' ?> me-1"></i> <?= $isClosed ? 'Reopen Order' : 'Close Order' ?>
        </a>
        <a href="<?= site_url('customer_orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i> Edit</a>
        <a href="<?= site_url('customer_orders') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .design-clickable { cursor: pointer; transition: background-color 0.15s; }
    .design-clickable:hover { background-color: #f1f6ff !important; }
    .mobile-design-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
        cursor: pointer;
    }
    .mobile-design-card:active { background-color: #f1f6ff; }
</style>

<div class="card card-outline card-primary mb-3">
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <small class="text-muted d-block">Customer Name</small>
                <strong class="fs-6"><?= esc($order['customer_name'] ?? '-') ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">Order Date</small>
                <strong><?= !empty($order['order_date']) ? date('d-m-Y', strtotime($order['order_date'])) : '-' ?></strong>
            </div>
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">Order Status</small>
                <span class="badge <?= $isClosed ? 'bg-secondary' : 'bg-success' ?>"><?= esc($order['status'] ?: 'Active') ?></span>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block">Total Designs</small>
                <strong><?= count($order['designs'] ?? []) ?></strong>
            </div>
            <?php if (!empty($order['notes'])): ?>
                <div class="col-12">
                    <small class="text-muted d-block">Notes</small>
                    <div><?= nl2br(esc($order['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold">Design Line Items</h6>
        <small class="text-muted"><i class="fas fa-hand-pointer me-1"></i>Tap or click any row to view image</small>
    </div>
    <div class="card-body p-0">
        <!-- Desktop / Tablet Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 100px;">Design No.</th>
                        <th>Product Name</th>
                        <th class="text-center" style="width: 110px;">Image</th>
                        <th class="text-end" style="width: 140px;">Rate (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['designs'])): ?>
                        <?php foreach ($order['designs'] as $idx => $d): ?>
                            <?php $imgUrl = (!empty($d['image']) && file_exists(FCPATH . $d['image'])) ? base_url($d['image']) : ''; ?>
                            <tr class="design-clickable"
                                data-idx="<?= $idx ?>"
                                data-design="<?= (int) $d['design_no'] ?>"
                                data-product="<?= esc($d['product_name']) ?>"
                                data-rate="<?= number_format((float) $d['rate'], 2) ?>"
                                data-image="<?= esc($imgUrl) ?>">
                                <td class="text-center fw-bold"><span class="badge bg-info text-dark fs-6"><?= (int) $d['design_no'] ?></span></td>
                                <td class="fw-bold"><?= esc($d['product_name']) ?></td>
                                <td class="text-center">
                                    <?php if ($imgUrl !== ''): ?>
                                        <img src="<?= esc($imgUrl) ?>" alt="Design <?= (int) $d['design_no'] ?>" style="width:52px;height:52px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">₹<?= number_format((float) $d['rate'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-3 text-muted">No designs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List -->
        <div class="d-md-none p-2">
            <?php if (!empty($order['designs'])): ?>
                <?php foreach ($order['designs'] as $idx => $d): ?>
                    <?php $imgUrl = (!empty($d['image']) && file_exists(FCPATH . $d['image'])) ? base_url($d['image']) : ''; ?>
                    <div class="mobile-design-card design-clickable d-flex align-items-center gap-3"
                         data-idx="<?= $idx ?>"
                         data-design="<?= (int) $d['design_no'] ?>"
                         data-product="<?= esc($d['product_name']) ?>"
                         data-rate="<?= number_format((float) $d['rate'], 2) ?>"
                         data-image="<?= esc($imgUrl) ?>">
                        <?php if ($imgUrl !== ''): ?>
                            <img src="<?= esc($imgUrl) ?>" alt="Design <?= (int) $d['design_no'] ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;flex-shrink:0;">
                        <?php else: ?>
                            <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width:60px;height:60px;border-radius:6px;border:1px solid #dee2e6;flex-shrink:0;font-size:11px;">No Img</div>
                        <?php endif; ?>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-info text-dark">Design #<?= (int) $d['design_no'] ?></span>
                                <strong class="text-primary">₹<?= number_format((float) $d['rate'], 2) ?></strong>
                            </div>
                            <div class="fw-bold text-truncate"><?= esc($d['product_name']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-3 text-muted">No designs found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Design Image Lightbox Modal -->
<div class="modal fade" id="designImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="designModalTitle">Design Image</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light p-2">
                <img id="designModalImg" src="" alt="Design Image" class="img-fluid rounded d-none" style="max-height: 72vh; object-fit: contain;">
                <div id="designModalNoImg" class="py-5 text-muted d-none">
                    <i class="fas fa-image fa-3x mb-2"></i>
                    <div>No image uploaded for this design.</div>
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <div class="fw-bold text-primary" id="designModalRate"></div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" id="btnPrevDesign"><i class="fas fa-chevron-left me-1"></i> Prev</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnNextDesign">Next <i class="fas fa-chevron-right ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    const items = [];
    $('tr.design-clickable').each(function() {
        items.push({
            design:  $(this).data('design'),
            product: $(this).data('product'),
            rate:    $(this).data('rate'),
            image:   $(this).data('image')
        });
    });

    let currentIdx = 0;
    const modalEl = document.getElementById('designImageModal');
    const modal = new bootstrap.Modal(modalEl);

    function showDesignAt(idx) {
        if (!items.length) return;
        currentIdx = (idx + items.length) % items.length;
        const it = items[currentIdx];
        $('#designModalTitle').text(`Design #${it.design} — ${it.product}`);
        $('#designModalRate').text(`Rate: ₹${it.rate}`);
        if (it.image) {
            $('#designModalImg').attr('src', it.image).removeClass('d-none');
            $('#designModalNoImg').addClass('d-none');
        } else {
            $('#designModalImg').addClass('d-none').attr('src', '');
            $('#designModalNoImg').removeClass('d-none');
        }
        $('#btnPrevDesign, #btnNextDesign').prop('disabled', items.length <= 1);
    }

    $(document).on('click', '.design-clickable', function() {
        const idx = parseInt($(this).data('idx'), 10) || 0;
        showDesignAt(idx);
        modal.show();
    });

    $('#btnPrevDesign').on('click', () => showDesignAt(currentIdx - 1));
    $('#btnNextDesign').on('click', () => showDesignAt(currentIdx + 1));
});
</script>
<?= $this->endSection() ?>
