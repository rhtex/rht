

    <?php include 'layouts/head.php'; ?>
 
    <div class="auth-wrapper">
        <div class="auth-content">
            <div class="card">
                <div class="row align-items-center text-center">
                    <div class="col-md-12">
                        <div class="card-body"><img src="/public/assets/images/logo-dark.svg" alt="" class="img-fluid mb-4">
                            <h4 class="mb-3 f-w-400">Signin</h4>

                            <?php if (session()->getFlashdata('msg')): ?>
                                <div class="alert alert-warning">
                                    <?= session()->getFlashdata('msg') ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?php echo base_url(); ?>SigninController/loginAuth" method="post">

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i data-feather="mail"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email address"
                                        value="<?= set_value('email') ?>">
                                </div>

                                <div class="input-group mb-4">
                                    <span class="input-group-text"><i data-feather="lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Password">
                                </div>
                                <button type="submit" class="btn btn-block btn-primary mb-4">Signin</button>
                            </form>
                        </div>

                    </div>
                </div>
                

    <?php include 'layouts/footer.php'; ?>