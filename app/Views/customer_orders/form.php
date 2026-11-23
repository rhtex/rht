<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $order ? 'Edit Customer Order' : 'New Customer Order' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-swatchbook text-primary me-2"></i><?= $order ? 'Edit Customer Order' : 'New Customer Order' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('customer_orders') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form action="<?= site_url($order ? 'customer_orders/update/' . $order['id'] : 'customer_orders/store') ?>" method="post" enctype="multipart/form-data" id="customerOrderForm">
    <?= csrf_field() ?>

    <!-- Master Order Card -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header bg-light py-2">
            <strong>Customer Order Details</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Order Number <span class="text-danger">*</span></label>
                    <input type="text" name="order_number" class="form-control fw-bold" value="<?= esc(old('order_number', $nextOrderNumber)) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select select2" required>
                        <option value="">-- Select Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ((int) old('customer_id', $order['customer_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>>
                                <?= esc($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Order Date <span class="text-danger">*</span></label>
                    <input type="date" name="order_date" class="form-control" value="<?= esc(old('order_date', $order['order_date'] ?? date('Y-m-d'))) ?>" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Order Status</label>
                    <select name="status" class="form-select fw-bold">
                        <?php $curStatus = old('status', $order['status'] ?? 'Active'); ?>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= esc($st) ?>" <?= $curStatus === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Order Notes (Optional)</label>
                    <input type="text" name="notes" class="form-control" value="<?= esc(old('notes', $order['notes'] ?? '')) ?>" placeholder="Any general notes for this customer order...">
                </div>
            </div>
        </div>
    </div>

    <!-- Design Line Items Card -->
    <div class="card card-outline card-info">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <strong>Design Line Items</strong>
            <small class="text-muted">Selecting a product automatically adds the next design row</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="designsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 90px;">Design No.</th>
                            <th style="width: 38%;">Product Name</th>
                            <th style="width: 26%;">Image</th>
                            <th class="text-end" style="width: 140px;">Rate (₹)</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="designsBody">
                        <?php
                        $rows = !empty($designs) ? $designs : [];
                        // Always ensure one empty trailing row at the end for instant entry
                        $rows[] = ['product_id' => '', 'product_name' => '', 'rate' => '0.00', 'image' => ''];
                        ?>
                        <?php foreach ($rows as $idx => $d): ?>
                            <?php $hasSelected = !empty($d['product_id']) || !empty($d['product_name']); ?>
                            <tr class="design-row">
                                <td class="text-center">
                                    <span class="badge bg-info text-dark fs-6 design-no-badge"><?= $idx + 1 ?></span>
                                    <input type="hidden" name="designs[<?= $idx ?>][design_no]" class="design-no-val" value="<?= $idx + 1 ?>">
                                </td>
                                <td>
                                    <div class="co-product-select-wrap <?= $hasSelected ? 'd-none' : '' ?>">
                                        <select name="designs[<?= $idx ?>][product_id]" class="form-select form-select-sm co-product-select select2-row">
                                            <option value="">-- Select Product --</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= $p['id'] ?>"
                                                        data-name="<?= esc($p['product_name']) ?>"
                                                        data-rate="<?= esc($p['selling_price']) ?>"
                                                        <?= ((int) ($d['product_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>>
                                                    <?= esc($p['product_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="input-group input-group-sm co-product-display-wrap <?= $hasSelected ? '' : 'd-none' ?>">
                                        <input type="text" name="designs[<?= $idx ?>][product_name]" class="form-control fw-bold bg-light co-product-name" value="<?= esc($d['product_name'] ?? '') ?>" placeholder="Product name..." readonly>
                                        <button type="button" class="btn btn-danger btn-clear-product" title="Remove Product &amp; Reselect"><i class="fas fa-times"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="img-thumb-wrap">
                                            <?php if (!empty($d['image']) && file_exists(FCPATH . $d['image'])): ?>
                                                <img src="<?= base_url($d['image']) ?>" alt="Design" style="width:42px;height:42px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">No Img</span>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" name="designs[<?= $idx ?>][existing_image]" class="co-existing-image" value="<?= esc($d['image'] ?? '') ?>">
                                        <input type="file" name="designs[<?= $idx ?>][image_file]" class="form-control form-control-sm co-image-input" accept="image/*">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="designs[<?= $idx ?>][rate]" class="form-control form-control-sm text-end co-rate" step="0.01" min="0" value="<?= esc(number_format((float) ($d['rate'] ?? 0), 2, '.', '')) ?>">
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-clone-design" title="Clone Design"><i class="fas fa-copy"></i></button>
                                        <button type="button" class="btn btn-outline-primary btn-edit-design" title="Edit Design"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-outline-danger btn-delete-design" title="Delete Design"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= site_url('customer_orders') ?>" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> <?= $order ? 'Update Customer Order' : 'Save Customer Order' ?></button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.select2').select2({ theme: 'bootstrap-5' });
    $('.select2-row').select2({ theme: 'bootstrap-5', width: '100%' });

    const baseUrl = '<?= base_url() ?>';
    const productOptionsHtml = `
        <option value="">-- Select Product --</option>
        <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>" data-name="<?= esc($p['product_name']) ?>" data-rate="<?= esc($p['selling_price']) ?>"><?= esc($p['product_name']) ?></option>
        <?php endforeach; ?>
    `;

    let nextIdx = <?= count($rows) ?>;

    function resequenceDesigns() {
        $('#designsBody .design-row').each(function(i) {
            const num = i + 1;
            $(this).find('.design-no-badge').text(num);
            $(this).find('.design-no-val').val(num);
        });
    }

    function buildDesignRow(preset = {}) {
        const idx = nextIdx++;
        const hasSelected = !!(preset.product_id || preset.product_name);
        const imgHtml = preset.image
            ? `<img src="${baseUrl}${preset.image}" alt="Design" style="width:42px;height:42px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">`
            : `<span class="badge bg-light text-muted border">No Img</span>`;

        const tr = $(`
            <tr class="design-row">
                <td class="text-center">
                    <span class="badge bg-info text-dark fs-6 design-no-badge">1</span>
                    <input type="hidden" name="designs[${idx}][design_no]" class="design-no-val" value="1">
                </td>
                <td>
                    <div class="co-product-select-wrap ${hasSelected ? 'd-none' : ''}">
                        <select name="designs[${idx}][product_id]" class="form-select form-select-sm co-product-select select2-row">
                            ${productOptionsHtml}
                        </select>
                    </div>
                    <div class="input-group input-group-sm co-product-display-wrap ${hasSelected ? '' : 'd-none'}">
                        <input type="text" name="designs[${idx}][product_name]" class="form-control fw-bold bg-light co-product-name" value="${$('<div>').text(preset.product_name || '').html()}" placeholder="Product name..." readonly>
                        <button type="button" class="btn btn-danger btn-clear-product" title="Remove Product &amp; Reselect"><i class="fas fa-times"></i></button>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="img-thumb-wrap">${imgHtml}</div>
                        <input type="hidden" name="designs[${idx}][existing_image]" class="co-existing-image" value="${$('<div>').text(preset.image || '').html()}">
                        <input type="file" name="designs[${idx}][image_file]" class="form-control form-control-sm co-image-input" accept="image/*">
                    </div>
                </td>
                <td>
                    <input type="number" name="designs[${idx}][rate]" class="form-control form-control-sm text-end co-rate" step="0.01" min="0" value="${parseFloat(preset.rate || 0).toFixed(2)}">
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary btn-clone-design" title="Clone Design"><i class="fas fa-copy"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-edit-design" title="Edit Design"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-delete-design" title="Delete Design"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `);

        return tr;
    }

    function appendDesignRow(preset = {}, beforeLastEmpty = false) {
        const tr = buildDesignRow(preset);
        const $last = $('#designsBody .design-row').last();
        const lastIsEmpty = $last.length && !$last.find('.co-product-select').val() && !$last.find('.co-product-name').val().trim();
        if (beforeLastEmpty && lastIsEmpty) {
            tr.insertBefore($last);
        } else {
            $('#designsBody').append(tr);
        }
        const $sel = tr.find('.co-product-select');
        if (preset.product_id) {
            $sel.val(preset.product_id);
        }
        $sel.select2({ theme: 'bootstrap-5', width: '100%' });
        resequenceDesigns();
    }

    function ensureTrailingEmptyRow() {
        const $last = $('#designsBody .design-row').last();
        if (!$last.length || $last.find('.co-product-select').val() || $last.find('.co-product-name').val().trim() !== '') {
            appendDesignRow();
        }
    }

    // Selecting a product hides dropdown, keeps product name + red X, fetches rate, and auto-adds next row
    $('#designsBody').on('change', '.co-product-select', function() {
        const opt = $(this).find('option:selected');
        const row = $(this).closest('.design-row');
        if (opt.val()) {
            row.find('.co-product-name').val(opt.data('name'));
            row.find('.co-rate').val(parseFloat(opt.data('rate') || 0).toFixed(2));
            row.find('.co-product-select-wrap').addClass('d-none');
            row.find('.co-product-display-wrap').removeClass('d-none');
            ensureTrailingEmptyRow();
        }
    });

    // Red X button: clear product name & rate, show dropdown again
    $('#designsBody').on('click', '.btn-clear-product', function() {
        const row = $(this).closest('.design-row');
        row.find('.co-product-name').val('');
        row.find('.co-rate').val('0.00');
        row.find('.co-product-select').val('').trigger('change.select2');
        row.find('.co-product-display-wrap').addClass('d-none');
        row.find('.co-product-select-wrap').removeClass('d-none');
    });

    // Live thumbnail preview on image choose
    $('#designsBody').on('change', '.co-image-input', function() {
        const file = this.files && this.files[0];
        if (!file) return;
        const wrap = $(this).closest('.design-row').find('.img-thumb-wrap');
        const reader = new FileReader();
        reader.onload = e => wrap.html(`<img src="${e.target.result}" style="width:42px;height:42px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">`);
        reader.readAsDataURL(file);
    });

    // Clone Design (insert before trailing empty row so empty row stays at bottom)
    $('#designsBody').on('click', '.btn-clone-design', function() {
        const row = $(this).closest('.design-row');
        if (!row.find('.co-product-select').val() && !row.find('.co-product-name').val().trim()) return;
        appendDesignRow({
            product_id:   row.find('.co-product-select').val(),
            product_name: row.find('.co-product-name').val(),
            rate:         row.find('.co-rate').val(),
            image:        row.find('.co-existing-image').val(),
        }, true);
    });

    // Edit Design
    $('#designsBody').on('click', '.btn-edit-design', function() {
        const row = $(this).closest('.design-row');
        if (!row.find('.co-product-display-wrap').hasClass('d-none')) {
            row.find('.co-product-name').focus().select();
        } else {
            row.find('.co-product-select').select2('open');
        }
    });

    // Delete Design
    $('#designsBody').on('click', '.btn-delete-design', function() {
        if ($('#designsBody .design-row').length <= 1) {
            $(this).closest('.design-row').find('.btn-clear-product').click();
            return;
        }
        $(this).closest('.design-row').remove();
        ensureTrailingEmptyRow();
        resequenceDesigns();
    });

    // Validate at least one design is filled before submit
    $('#customerOrderForm').on('submit', function(e) {
        let filled = 0;
        $('#designsBody .design-row').each(function() {
            if ($(this).find('.co-product-select').val() || $(this).find('.co-product-name').val().trim() !== '') {
                filled++;
            }
        });
        if (filled === 0) {
            e.preventDefault();
            alert('Please add at least one design product.');
        }
    });
});
</script>
<?= $this->endSection() ?>
