
<body>
	<!-- [ Pre-loader ] start -->
	<div class="loader-bg">
		<div class="loader-track">
			<div class="loader-fill"></div>
		</div>
	</div>
	<!-- [ Pre-loader ] End -->
	<!-- [ Mobile header ] start -->
	<div class="pc-mob-header pc-header">
		<div class="pcm-logo">
			<img src="/public/assets/images/logo.png" width="100%" alt="" class="logo logo-lg">
		</div>
		<div class="pcm-toolbar">
			<a href="#!" class="pc-head-link" id="mobile-collapse">
				<div class="hamburger hamburger--arrowturn">
					<div class="hamburger-box">
						<div class="hamburger-inner"></div>
					</div>
				</div>
			</a>
			<a href="#!" class="pc-head-link" id="headerdrp-collapse">
				<i data-feather="align-right"></i>
			</a>
			<a href="#!" class="pc-head-link" id="header-collapse">
				<i data-feather="more-vertical"></i>
			</a>
		</div>
	</div>
	<!-- [ Mobile header ] End -->

	<!-- [ navigation menu ] start -->
	<nav class="pc-sidebar">
		<div class="navbar-wrapper">
			<div class="m-header">
				<a href="/dashboard" class="b-brand">
					<!-- ========   change your logo hear   ============ -->
					<img src="/public/assets/images/logo.png" width="100%" alt="" class="logo logo-lg">
					<img src="/public/assets/images/logo.png" width="100%" alt="" class="logo logo-sm">
				</a>
			</div>
			<div class="navbar-content">
				<ul class="pc-navbar">
					<?php include 'menu-list.php'; ?>
				</ul>
			</div>
		</div>
	</nav>
	<!-- [ navigation menu ] end -->
	<!-- [ Header ] start -->
	<header class="pc-header">
		<div class="header-wrapper">
			<?php include 'header-content.php'; ?>
		</div>
	</header>
	<!-- [ Header ] end -->
	<div class="pc-container">
	<div class="pcoded-content">