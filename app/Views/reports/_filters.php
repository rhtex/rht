<?php
/**
 * Shared report filter bar.
 * Expects: $action (url), $filters, $periodOptions, $parties, $partyLabel, $statuses, optional $extra (html)
 */
$isCustom = ($filters['period'] ?? '') === 'custom';
?>
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" action="<?= $action ?>" class="row g-2 align-items-end" id="reportFilterForm">
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold mb-1">Period</label>
                <select name="period" id="rptPeriod" class="form-select form-select-sm">
                    <?php foreach ($periodOptions as $k => $label): ?>
                        <option value="<?= esc($k) ?>" <?= ($filters['period'] ?? '') === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm rpt-date" value="<?= esc($filters['date_from'] ?? '') ?>" <?= $isCustom ? '' : 'readonly' ?>>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm rpt-date" value="<?= esc($filters['date_to'] ?? '') ?>" <?= $isCustom ? '' : 'readonly' ?>>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold mb-1"><?= esc($partyLabel) ?></label>
                <select name="party_id" class="form-select form-select-sm">
                    <option value="">All <?= esc($partyLabel) ?>s</option>
                    <?php foreach ($parties as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (int) ($filters['party_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= esc($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= esc($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?= $extra ?? '' ?>
            <div class="col-lg-auto col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Apply</button>
                <a href="<?= $action ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var sel = document.getElementById('rptPeriod');
    if (!sel) return;
    sel.addEventListener('change', function () {
        var custom = sel.value === 'custom';
        document.querySelectorAll('#reportFilterForm .rpt-date').forEach(function (i) { i.readOnly = !custom; });
        if (!custom) { document.getElementById('reportFilterForm').submit(); }
    });
})();
</script>
