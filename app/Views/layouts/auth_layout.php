<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= get_setting('app_name', 'RasiDev HR') ?> | Login</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE 4 (using Alpha/Beta/Custom Style for now as 4 is standard BS5 mostly) -->
    <style>
        body {
            background-color: #e9ecef;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .login-box {
            width: 360px;
        }
        .card {
            border: 0;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1.25rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 300;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        /* Gradient Background */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-header a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
