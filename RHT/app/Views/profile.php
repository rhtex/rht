<?php include 'layouts/head.php'; ?>
<?php include 'layouts/layout-vertical.php'; ?>

    
        <h4>
            <?php $session = session();
            echo $session->get('name'); ?>
        </h4>
    </div>
</div>
<?php include 'layouts/footer.php'; ?>