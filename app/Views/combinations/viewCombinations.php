<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h4 class="mb-4">Combinations</h4>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <?php foreach ($combinations as $combination): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body" style="border:1px solid grey;">
                            <div class="svg-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="75%" height="100%">
                                    <rect width="100%" height="100%"
                                        fill="<?= htmlspecialchars($combination['body_color']); ?>" />
                                </svg>
                            </div>
                            <!-- Border Color SVG -->
                            <div class="svg-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="75%" height="50px">
                                    <rect width="100%" height="100%"
                                        fill="<?= htmlspecialchars($combination['border_color']); ?>" />
                                </svg>
                            </div>
                            <div>
                                <p>Body: <?= $combination['body_color_name']?></p>
                                <p>Border: <?= $combination['border_color_name']?></p>
                            </div>
                            <div class="btn-group mt-2">
                                <?php if ($combination['approval']): ?>
                                    <a href="/combinations/approve/<?= $combination['id'] ?>"
                                        class="btn btn-success mr-2">Approve</a>
                                    <a href="/combinations/reject/<?= $combination['id'] ?>" class="btn btn-danger">Reject</a>
                                <?php else: ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>