<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Agents<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-ninja text-primary me-2"></i>Sales Agents & Brokers</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('agents') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('agents/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Agent
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-6 col-sm-6">
        <a href="<?= site_url('agents') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-users-cog bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($agents)) ?></div>
            <div class="bt-stat-label">Total Commission Agents</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-6 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-percentage bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['avg_commission'] ?? 0, 2) ?>%</div>
            <div class="bt-stat-label">Average Commission Rate</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Agents</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('agents') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-10 col-sm-9">
                <label for="search" class="form-label">Search Agent</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by agent name, phone, or city..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('agents') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Agents Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="min-width: 200px;">Agent Name</th>
                        <th style="min-width: 140px;">Phone</th>
                        <th style="min-width: 180px;">City & State</th>
                        <th class="text-center" style="min-width: 130px;">Commission Rate</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($agents)): ?>
                        <?php foreach($agents as $index => $agent): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;min-width:36px;">
                                        <?= strtoupper(substr($agent['agent_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <a href="<?= site_url('agents/view/'.$agent['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= esc($agent['agent_name']) ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <?php if(!empty($agent['phone_number'])): ?>
                                    <a href="tel:<?= esc($agent['phone_number']) ?>" class="text-decoration-none text-dark fw-semibold">
                                        <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($agent['phone_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($agent['city']) || !empty($agent['state_name'])): ?>
                                    <span><?= esc($agent['city'] ?? '') ?><?= (!empty($agent['city']) && !empty($agent['state_name'])) ? ', ' : '' ?><?= esc($agent['state_name'] ?? '') ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-primary px-3 py-1 fw-bold fs-6">
                                    <?= number_format($agent['commission_percentage'], 2) ?>%
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('agents/view/'.$agent['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('agents/edit/'.$agent['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('agents/delete/'.$agent['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this agent?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-user-ninja fa-2x text-muted mb-2 d-block"></i>
                                No agents found matching the search criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
