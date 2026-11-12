<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Maintenance | Rasi Handloom Textiles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 20px;
        }
        .maintenance-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 580px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
        }
        .icon-pulse {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 24px;
            position: relative;
        }
        .icon-pulse::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #f59e0b;
            animation: pulse 2s infinite ease-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .app-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .message-box {
            background: rgba(15, 23, 42, 0.6);
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px 18px;
            text-align: left;
            margin: 24px 0;
            font-size: 14px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="app-badge">
            <i class="fas fa-shield-alt text-warning me-1"></i> Rasi Handloom Textiles ERP
        </div>

        <div>
            <div class="icon-pulse">
                <i class="fas fa-tools"></i>
            </div>
        </div>

        <h2 class="fw-bold mb-2">System Under Maintenance</h2>
        <p class="text-secondary small mb-3">
            We are currently conducting scheduled database & system maintenance to upgrade application performance and data integrity.
        </p>

        <?php if (!empty($message)): ?>
            <div class="message-box">
                <div class="fw-bold text-warning small text-uppercase mb-1">
                    <i class="fas fa-info-circle me-1"></i> Maintenance Notice
                </div>
                <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <div class="p-3 rounded bg-black bg-opacity-25 border border-white border-opacity-10 d-flex justify-content-around text-center mb-4">
            <div>
                <div class="text-secondary small text-uppercase fw-semibold">Started At</div>
                <div class="fw-bold text-white small"><?= !empty($started_at) ? htmlspecialchars($started_at, ENT_QUOTES, 'UTF-8') : date('h:i A') ?></div>
            </div>
            <div class="border-start border-white border-opacity-10"></div>
            <div>
                <div class="text-secondary small text-uppercase fw-semibold">Estimated Duration</div>
                <div class="fw-bold text-warning small"><?= !empty($duration) ? htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') : 'Few minutes' ?></div>
            </div>
            <div class="border-start border-white border-opacity-10"></div>
            <div>
                <div class="text-secondary small text-uppercase fw-semibold">Status</div>
                <div class="fw-bold text-info small"><i class="fas fa-sync-alt fa-spin me-1"></i> In Progress</div>
            </div>
        </div>

        <p class="text-secondary small mb-4">
            Please check back shortly. Form submissions and transactions are temporarily paused to prevent data conflicts.
        </p>

        <button type="button" class="btn btn-outline-light btn-sm px-4 rounded-pill" onclick="window.location.reload();">
            <i class="fas fa-redo me-1"></i> Refresh Page
        </button>

        <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-muted" style="font-size: 11px;">
            &copy; <?= date('Y') ?> Rasi Handloom Textiles. All rights reserved.
        </div>
    </div>
</body>
</html>
