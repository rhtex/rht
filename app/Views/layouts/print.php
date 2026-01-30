<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $this->renderSection('title') ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            background-color: white !important;
            font-size: 12px;
            color: #333;
        }
        .statement-container {
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
        }
        .table th {
            background-color: #f8f9fa !important;
            color: #333 !important;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-primary { color: #0d6efd !important; }
        .text-muted { color: #6c757d !important; }
        .text-danger { color: #dc3545 !important; }
        .text-success { color: #198754 !important; }
        .bg-light { background-color: #f8f9fa !important; }
        .fw-bold { font-weight: 700 !important; }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?= $this->renderSection('content') ?>
    </div>
</body>
</html>
