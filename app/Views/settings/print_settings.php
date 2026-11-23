<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Print Settings - Document Configuration<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-print text-primary me-2"></i>Print Settings & Document Configuration</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('settings') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> General Settings
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .doc-tabs .nav-link { font-weight: 600; }
    .field-row {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 10px;
        margin-bottom: 6px;
        transition: background-color 0.15s;
    }
    .field-row.is-hidden-field { background: #f8f9fa; opacity: 0.78; }
    .field-row.dragging { opacity: 0.45; border-style: dashed; border-color: #0d6efd; }
    .drag-handle { cursor: grab; color: #6c757d; padding: 4px 6px; }
    .drag-handle:active { cursor: grabbing; }
    .preview-pane-sticky { position: sticky; top: 12px; }
    .preview-frame-wrap {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 6px;
        height: 76vh;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 10px;
    }
    #livePreviewFrame {
        border: none;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        width: 210mm;
        height: 297mm;
        transform-origin: top center;
    }
</style>

<!-- Document Selector Tabs & Actions -->
<div class="card card-outline card-primary mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <ul class="nav nav-pills doc-tabs">
                <?php foreach ($docTypes as $dKey => $dMeta): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeDoc === $dKey ? 'active' : '' ?>" href="<?= site_url('settings/print?doc=' . $dKey) ?>">
                            <?= esc($dMeta['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-info btn-sm" id="btnRefreshPreview">
                    <i class="fas fa-sync-alt me-1"></i> Preview
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm" id="btnPdfPreview">
                    <i class="fas fa-file-pdf me-1"></i> Preview PDF
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#copyConfigModal">
                    <i class="fas fa-copy me-1"></i> Copy From...
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnResetDefault">
                    <i class="fas fa-undo me-1"></i> Reset to Default
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveConfig">
                    <i class="fas fa-save me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<div id="printAlertBox" class="alert d-none" role="alert"></div>

<div class="row">
    <!-- Left: Configuration Builder -->
    <div class="col-lg-6 mb-3">
        <!-- Page Setup Card -->
        <div class="card mb-3">
            <div class="card-header bg-light py-2">
                <strong><i class="fas fa-sliders-h me-1"></i> Page & Typography Setup — <?= esc($docTypes[$activeDoc]['label']) ?></strong>
            </div>
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm mb-1">Orientation (A4)</label>
                        <select class="form-select form-select-sm cfg-global" id="cfgOrientation">
                            <option value="portrait" <?= ($config['orientation'] ?? 'portrait') === 'portrait' ? 'selected' : '' ?>>A4 Portrait</option>
                            <option value="landscape" <?= ($config['orientation'] ?? 'portrait') === 'landscape' ? 'selected' : '' ?>>A4 Landscape</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label form-label-sm mb-1">Font Family</label>
                        <select class="form-select form-select-sm cfg-global" id="cfgFontFamily">
                            <?php $ff = $config['font_family'] ?? 'Helvetica, Arial, sans-serif'; ?>
                            <option value="Helvetica, Arial, sans-serif" <?= str_contains($ff, 'Helvetica') ? 'selected' : '' ?>>Helvetica / Arial (Standard PDF)</option>
                            <option value="'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" <?= str_contains($ff, 'Segoe') ? 'selected' : '' ?>>Segoe UI</option>
                            <option value="'Times New Roman', Times, serif" <?= str_contains($ff, 'Times') ? 'selected' : '' ?>>Times New Roman</option>
                            <option value="'Courier New', Courier, monospace" <?= str_contains($ff, 'Courier') ? 'selected' : '' ?>>Courier New</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm mb-1">Base Font Size</label>
                        <select class="form-select form-select-sm cfg-global" id="cfgBaseFontSize">
                            <?php $bfs = $config['base_font_size'] ?? '10.5px'; ?>
                            <?php foreach (['9.5px', '10px', '10.5px', '11px', '12px'] as $sz): ?>
                                <option value="<?= $sz ?>" <?= $bfs === $sz ? 'selected' : '' ?>><?= $sz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections Accordion -->
        <div class="accordion" id="printSectionsAccordion">
            <?php $secIdx = 0; foreach ($sections as $secKey => $secTitle): $secIdx++; ?>
                <?php
                $secFields = \App\Libraries\PrintConfig::getSectionFields($config, $secKey, false);
                if (empty($secFields)) continue;
                $isItemsSec = ($secKey === 'items');
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading_<?= $secKey ?>">
                        <button class="accordion-button <?= $secIdx > 1 ? 'collapsed' : '' ?> py-2 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $secKey ?>">
                            <?= esc($secTitle) ?>
                            <?php if ($isItemsSec): ?>
                                <span class="badge bg-info text-dark ms-2">Drag & Drop Columns</span>
                                <span class="badge bg-secondary ms-2" id="itemsWidthTotalBadge">Width: 100%</span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="collapse_<?= $secKey ?>" class="accordion-collapse collapse <?= $secIdx === 1 ? 'show' : '' ?>" data-bs-parent="#printSectionsAccordion">
                        <div class="accordion-body p-2 section-fields-list" data-section="<?= esc($secKey) ?>">
                            <?php foreach ($secFields as $fKey => $fDef): ?>
                                <div class="field-row <?= empty($fDef['visible']) ? 'is-hidden-field' : '' ?>"
                                     draggable="true"
                                     data-key="<?= esc($fKey) ?>"
                                     data-section="<?= esc($secKey) ?>"
                                     data-mandatory="<?= !empty($fDef['mandatory']) ? '1' : '0' ?>">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>

                                        <div class="form-check mb-0" style="min-width: 175px;">
                                            <input class="form-check-input fld-visible" type="checkbox" id="vis_<?= esc($fKey) ?>" <?= !empty($fDef['visible']) ? 'checked' : '' ?>>
                                            <label class="form-check-label small fw-bold" for="vis_<?= esc($fKey) ?>">
                                                <?= esc($fDef['name']) ?>
                                                <?php if (!empty($fDef['mandatory'])): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;" title="Mandatory legal / tax field">Tax/Legal</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>

                                        <?php if (!empty($fDef['has_label'])): ?>
                                            <div style="width: 130px;">
                                                <input type="text" class="form-control form-control-sm fld-label" value="<?= esc($fDef['label']) ?>" placeholder="Label" title="Custom Label">
                                            </div>
                                        <?php else: ?>
                                            <input type="hidden" class="fld-label" value="<?= esc($fDef['label']) ?>">
                                        <?php endif; ?>

                                        <div class="form-check mb-0" title="Bold">
                                            <input class="form-check-input fld-bold" type="checkbox" id="bold_<?= esc($fKey) ?>" <?= !empty($fDef['bold']) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="bold_<?= esc($fKey) ?>">B</label>
                                        </div>

                                        <?php if (!empty($fDef['has_align'])): ?>
                                            <select class="form-select form-select-sm fld-align" style="width: 82px;" title="Alignment">
                                                <option value="left" <?= ($fDef['align'] ?? 'left') === 'left' ? 'selected' : '' ?>>Left</option>
                                                <option value="center" <?= ($fDef['align'] ?? 'left') === 'center' ? 'selected' : '' ?>>Center</option>
                                                <option value="right" <?= ($fDef['align'] ?? 'left') === 'right' ? 'selected' : '' ?>>Right</option>
                                            </select>
                                        <?php endif; ?>

                                        <select class="form-select form-select-sm fld-fontsize" style="width: 78px;" title="Font Size">
                                            <?php $fs = $fDef['font_size'] ?? '10.5px'; ?>
                                            <?php foreach (['8.5px', '9px', '9.5px', '10px', '10.5px', '11px', '12px', '12.5px', '13px', '14px', '16px', '18px', '20px'] as $opt): ?>
                                                <option value="<?= $opt ?>" <?= $fs === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <?php if (!empty($fDef['has_width'])): ?>
                                            <div class="input-group input-group-sm" style="width: 86px;" title="Column Width %">
                                                <input type="number" class="form-control fld-width" value="<?= esc($fDef['width'] ?? 10) ?>" min="2" max="80" step="1">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="ms-auto btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light border btn-move-up" title="Move Up"><i class="fas fa-chevron-up"></i></button>
                                            <button type="button" class="btn btn-light border btn-move-down" title="Move Down"><i class="fas fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: Live Print Preview -->
    <div class="col-lg-6">
        <div class="card preview-pane-sticky">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-eye me-1"></i> Live Print Preview (Sample Data)</strong>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary" id="previewOrientationBadge"><?= strtoupper($config['orientation'] ?? 'portrait') ?></span>
                    <select class="form-select form-select-sm" id="previewZoom" style="width: 90px;">
                        <option value="0.55">55%</option>
                        <option value="0.68" selected>68%</option>
                        <option value="0.80">80%</option>
                        <option value="1">100%</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="preview-frame-wrap">
                    <iframe id="livePreviewFrame" name="livePreviewFrame"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy From Another Document Modal -->
<div class="modal fade" id="copyConfigModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Copy Configuration</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small">Copy settings into <strong><?= esc($docTypes[$activeDoc]['label']) ?></strong> from:</label>
                <select class="form-select form-select-sm" id="copySourceSelect">
                    <?php foreach ($docTypes as $dKey => $dMeta): ?>
                        <?php if ($dKey === $activeDoc) continue; ?>
                        <option value="<?= esc($dKey) ?>"><?= esc($dMeta['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted d-block mt-2">Only fields applicable to <?= esc($docTypes[$activeDoc]['label']) ?> will be applied.</small>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmCopy">Copy & Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for posting live preview / PDF preview into iframe/new tab -->
<form id="previewPostForm" method="post" action="<?= site_url('settings/print/preview') ?>" target="livePreviewFrame" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="document_type" value="<?= esc($activeDoc) ?>">
    <input type="hidden" name="paper_size" value="A4">
    <input type="hidden" name="orientation" id="pfOrientation">
    <input type="hidden" name="font_family" id="pfFontFamily">
    <input type="hidden" name="base_font_size" id="pfBaseFontSize">
    <input type="hidden" name="fields_json" id="pfFieldsJson">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const activeDoc = <?= json_encode($activeDoc) ?>;
    const csrfName  = '<?= csrf_token() ?>';
    let csrfHash    = '<?= csrf_hash() ?>';
    let previewTimer = null;

    function showAlert(type, msg) {
        const box = $('#printAlertBox');
        box.removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(msg);
        setTimeout(() => box.addClass('d-none'), 4000);
    }

    function collectFields() {
        const fields = {};
        let order = 1;
        $('.field-row').each(function() {
            const row = $(this);
            const key = row.data('key');
            fields[key] = {
                visible:   row.find('.fld-visible').is(':checked'),
                label:     row.find('.fld-label').val() || '',
                order:     order++,
                bold:      row.find('.fld-bold').is(':checked'),
                align:     row.find('.fld-align').val() || 'left',
                font_size: row.find('.fld-fontsize').val() || '10.5px',
                width:     parseFloat(row.find('.fld-width').val()) || 10
            };
        });
        return fields;
    }

    function updateWidthBadge() {
        let sum = 0;
        $('.section-fields-list[data-section="items"] .field-row').each(function() {
            if ($(this).find('.fld-visible').is(':checked')) {
                sum += parseFloat($(this).find('.fld-width').val()) || 0;
            }
        });
        const badge = $('#itemsWidthTotalBadge');
        badge.text('Width: ' + Math.round(sum) + '%');
        badge.removeClass('bg-secondary bg-success bg-warning text-dark')
             .addClass(Math.abs(sum - 100) < 1 ? 'bg-success' : 'bg-warning text-dark');
    }

    function applyPreviewScale() {
        const ori = $('#cfgOrientation').val();
        const zoom = parseFloat($('#previewZoom').val()) || 0.68;
        const frame = $('#livePreviewFrame');
        frame.css({
            width: ori === 'landscape' ? '297mm' : '210mm',
            height: ori === 'landscape' ? '210mm' : '297mm',
            transform: 'scale(' + zoom + ')'
        });
        $('#previewOrientationBadge').text(ori.toUpperCase());
    }

    function refreshPreview() {
        applyPreviewScale();
        $('#pfOrientation').val($('#cfgOrientation').val());
        $('#pfFontFamily').val($('#cfgFontFamily').val());
        $('#pfBaseFontSize').val($('#cfgBaseFontSize').val());
        $('#pfFieldsJson').val(JSON.stringify(collectFields()));
        const form = $('#previewPostForm');
        form.attr('action', '<?= site_url('settings/print/preview') ?>').attr('target', 'livePreviewFrame');
        form[0].submit();
    }

    function schedulePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 250);
    }

    // Mandatory field warning when unchecking legal/tax fields
    $(document).on('change', '.fld-visible', function() {
        const chk = $(this);
        const row = chk.closest('.field-row');
        if (!chk.is(':checked') && row.data('mandatory') == 1) {
            const label = row.find('.form-check-label').text().trim();
            if (!confirm('Warning: "' + label + '" is a mandatory legal / GST tax field. Hiding it may make the printed document non-compliant. Are you sure you want to hide it?')) {
                chk.prop('checked', true);
                return;
            }
        }
        row.toggleClass('is-hidden-field', !chk.is(':checked'));
        updateWidthBadge();
        schedulePreview();
    });

    $(document).on('input change', '.fld-label, .fld-bold, .fld-align, .fld-fontsize, .fld-width, .cfg-global', function() {
        updateWidthBadge();
        schedulePreview();
    });

    $('#previewZoom').on('change', applyPreviewScale);
    $('#btnRefreshPreview').on('click', refreshPreview);

    $('#btnPdfPreview').on('click', function() {
        $('#pfOrientation').val($('#cfgOrientation').val());
        $('#pfFontFamily').val($('#cfgFontFamily').val());
        $('#pfBaseFontSize').val($('#cfgBaseFontSize').val());
        $('#pfFieldsJson').val(JSON.stringify(collectFields()));
        const form = $('#previewPostForm');
        form.attr('action', '<?= site_url('settings/print/pdf-preview') ?>').attr('target', '_blank');
        form[0].submit();
    });

    // Move Up / Down
    $(document).on('click', '.btn-move-up', function() {
        const row = $(this).closest('.field-row');
        const prev = row.prev('.field-row');
        if (prev.length) { row.insertBefore(prev); schedulePreview(); }
    });
    $(document).on('click', '.btn-move-down', function() {
        const row = $(this).closest('.field-row');
        const next = row.next('.field-row');
        if (next.length) { row.insertAfter(next); schedulePreview(); }
    });

    // HTML5 Drag & Drop reordering within same section
    let draggedRow = null;
    $(document).on('dragstart', '.field-row', function(e) {
        draggedRow = this;
        $(this).addClass('dragging');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    });
    $(document).on('dragend', '.field-row', function() {
        $(this).removeClass('dragging');
        draggedRow = null;
        schedulePreview();
    });
    $(document).on('dragover', '.field-row', function(e) {
        if (!draggedRow || draggedRow === this) return;
        if ($(draggedRow).data('section') !== $(this).data('section')) return;
        e.preventDefault();
        const rect = this.getBoundingClientRect();
        const after = (e.originalEvent.clientY - rect.top) > (rect.height / 2);
        if (after) $(this).after(draggedRow); else $(this).before(draggedRow);
    });

    // Save Configuration
    $('#btnSaveConfig').on('click', function() {
        const btn = $(this).prop('disabled', true);
        $.post('<?= site_url('settings/print/save') ?>', {
            [csrfName]:     csrfHash,
            document_type:  activeDoc,
            paper_size:     'A4',
            orientation:    $('#cfgOrientation').val(),
            font_family:    $('#cfgFontFamily').val(),
            base_font_size: $('#cfgBaseFontSize').val(),
            fields_json:    JSON.stringify(collectFields())
        }).done(function(res) {
            showAlert(res.success ? 'success' : 'danger', res.message || 'Saved.');
        }).fail(function(xhr) {
            showAlert('danger', xhr.responseJSON?.message || 'Error saving configuration.');
        }).always(() => btn.prop('disabled', false));
    });

    // Reset to Default
    $('#btnResetDefault').on('click', function() {
        if (!confirm('Reset <?= esc($docTypes[$activeDoc]['label']) ?> print configuration to system defaults?')) return;
        $.post('<?= site_url('settings/print/reset') ?>', {
            [csrfName]:    csrfHash,
            document_type: activeDoc
        }).done(function(res) {
            if (res.success) location.reload();
        }).fail(() => showAlert('danger', 'Failed to reset configuration.'));
    });

    // Copy From Another Document
    $('#btnConfirmCopy').on('click', function() {
        $.post('<?= site_url('settings/print/copy') ?>', {
            [csrfName]:           csrfHash,
            source_document_type: $('#copySourceSelect').val(),
            target_document_type: activeDoc
        }).done(function(res) {
            if (res.success) location.reload();
        }).fail(() => showAlert('danger', 'Failed to copy configuration.'));
    });

    updateWidthBadge();
    refreshPreview();
})();
</script>
<?= $this->endSection() ?>
