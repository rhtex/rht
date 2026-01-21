<?= $this->extend('layouts/auth_layout') ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <a href="#" class="h1"><b><?= get_setting('app_name', 'RasiDev HR') ?></b></a>
    </div>
    <div class="card-body">
        <p class="login-box-msg text-muted text-center mb-3">Sign in to start your session</p>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('attemptLogin') ?>" method="post">
            <?= csrf_field() ?>
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" value="<?= old('email') ?>" required>
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            </div>
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <div class="row">
                <div class="col-8">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember Me
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block w-100">Sign In</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
