<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= get_setting('app_name', 'RasiDev HR') ?> | <?= $this->renderSection('title') ?></title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.3.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE 4 -->
    <link rel="stylesheet" href="https://unpkg.com/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css" crossorigin="anonymous">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <!-- Custom Sidebar Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar_colors.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/sidebar_colors.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/list-ui.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/list-ui.css') ?>">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs5/dt-1.13.8/datatables.min.css" rel="stylesheet">
    <!-- Flatpickr (Datepicker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .app-header {
            background: #fff;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .main-sidebar {
            background-color: #343a40;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary text-sm <?= service('request')->getUserAgent()->isMobile() ? 'is-mobile-device' : '' ?>">
    <div class="app-wrapper">
        
        <!-- Header -->
        <?= $this->include('layouts/header') ?>

        <!-- Sidebar -->
        <?= $this->include('layouts/sidebar') ?>

        <!-- Main Content -->
        <main class="app-main">
            <!-- Content Header (Page header) -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <?= $this->renderSection('header') ?>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="app-content">
                <div class="container-fluid">
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?= $this->include('layouts/footer') ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/datatables.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }

            // Initialize Select2 on remaining .select2 and .form-select dropdowns after page scripts run
            window.initStyledSelects = function(root) {
                const $scope = root ? $(root) : $(document);
                $scope.find('select.select2, select.form-select:not(.select2):not(.no-select2):not(.flatpickr-monthDropdown-months):not([multiple])').each(function() {
                    const $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) return;
                    if (!$el.hasClass('select2')) {
                        if ($el.closest('.dataTables_length, .dt-length').length) return;
                        const inlineStyle = ($el.attr('style') || '').replace(/\s+/g, '');
                        if (inlineStyle.indexOf('width:50px') !== -1 || inlineStyle.indexOf('width:55px') !== -1) return;
                    }
                    const $modal = $el.closest('.modal');
                    const inGridCol = $el.parent().is('[class*="col-"]');
                    const inCompactHeaderOrGroup = !inGridCol && $el.closest('.card-header, .input-group').length > 0;
                    const hasExplicitWidth = $el.data('width') || ($el.attr('style') && $el.attr('style').indexOf('width') !== -1);
                    const opts = {
                        theme: 'bootstrap-5',
                        width: $el.data('width') ? $el.data('width') : (inCompactHeaderOrGroup || hasExplicitWidth ? 'resolve' : '100%'),
                        dropdownAutoWidth: inCompactHeaderOrGroup,
                        placeholder: $el.data('placeholder'),
                        minimumResultsForSearch: $el.hasClass('select2') ? 0 : 7
                    };
                    if ($el.hasClass('form-select-sm')) {
                        opts.selectionCssClass = 'select2--small';
                        opts.dropdownCssClass = 'select2--small';
                    }
                    if ($modal.length) {
                        opts.dropdownParent = $modal;
                    }
                    $el.select2(opts);
                });
            };
            setTimeout(function() { window.initStyledSelects(); }, 0);

            // Initialize Flatpickr for all date inputs
            flatpickr("input[type=date]", {
                dateFormat: "Y-m-d", // Format for the backend
                altInput: true,      // Create a visually distinct text input
                altFormat: "d-m-Y",  // Display format (dd-mm-yyyy) for the user
                allowInput: true
            });
        });

        // Global Back button behavior: go back to previous screen when navigated within the app
        document.addEventListener("click", function(e) {
            const backBtn = e.target.closest("a.btn");
            if (!backBtn) return;
            if (backBtn.hasAttribute("data-no-history-back") || backBtn.target === "_blank") return;
            const href = backBtn.getAttribute("href") || "";
            if (!href || href === "#" || href.startsWith("javascript:")) return;

            const hasBackIcon = !!backBtn.querySelector(".fa-arrow-left, .fa-chevron-left");
            const txt = (backBtn.textContent || "").trim().toLowerCase();
            const isBackText = txt === "back" || txt.startsWith("back to ");
            if (!hasBackIcon && !isBackText) return;

            const sameOriginRef = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
            if (window.history.length > 1 && sameOriginRef && document.referrer !== window.location.href) {
                e.preventDefault();
                window.history.back();
            }
        });
    </script>
    <?= $this->renderSection('scripts') ?>
    <!-- List UI Enhancements -->
    <script src="<?= base_url('assets/js/list-ui.js') ?>"></script>
</body>
</html>
