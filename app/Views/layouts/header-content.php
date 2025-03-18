<div class="mr-auto pc-mob-drp">
	<ul class="list-unstyled">
		<li class="dropdown pc-h-item">
			<a class="pc-head-link active dropdown-toggle arrow-none mr-0" data-toggle="dropdown" href="#" role="button"
				aria-haspopup="false" aria-expanded="false">
				Level
			</a>
			<div class="dropdown-menu pc-h-dropdown">
				<a href="#!" class="dropdown-item">
					<i class="material-icons-two-tone">account_circle</i>
					<span>My Account</span>
				</a>
				<div class="pc-level-menu">
					<a href="#!" class="dropdown-item">
						<i class="material-icons-two-tone">list_alt</i>
						<span class="float-right"><i data-feather="chevron-right" class="mr-0"></i></span>
						<span>Level2.1</span>
					</a>
					<div class="dropdown-menu pc-h-dropdown">
						<a href="#!" class="dropdown-item">
							<i class="fas fa-circle"></i>
							<span>My Account</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="fas fa-circle"></i>
							<span>Settings</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="fas fa-circle"></i>
							<span>Support</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="fas fa-circle"></i>
							<span>Lock Screen</span>
						</a>
						<a href="/logout" class="dropdown-item">
							<i class="fas fa-circle"></i>
							<span>Logout</span>
						</a>
					</div>
				</div>
				<a href="#!" class="dropdown-item">
					<i class="material-icons-two-tone">settings</i>
					<span>Settings</span>
				</a>
				<a href="#!" class="dropdown-item">
					<i class="material-icons-two-tone">support</i>
					<span>Support</span>
				</a>
				<a href="#!" class="dropdown-item">
					<i class="material-icons-two-tone">https</i>
					<span>Lock Screen</span>
				</a>
				<a href="/logout" class="dropdown-item">
					<i class="material-icons-two-tone">chrome_reader_mode</i>
					<span>Logout</span>
				</a>
			</div>
		</li>
	</ul>
</div>
<div class="ml-auto">
	<ul class="list-unstyled">
		<li class="dropdown pc-h-item">
			<a class="pc-head-link dropdown-toggle arrow-none mr-0" data-toggle="dropdown" href="#" role="button"
				aria-haspopup="false" aria-expanded="false">
				<i class="material-icons-two-tone">search</i>
			</a>
			<div class="dropdown-menu dropdown-menu-right pc-h-dropdown drp-search">
				<form class="px-3">
					<div class="form-group mb-0 d-flex align-items-center">
						<i data-feather="search"></i>
						<input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . .">
					</div>
				</form>
			</div>
		</li>
		<li class="dropdown pc-h-item">
			<a class="pc-head-link dropdown-toggle arrow-none mr-0" data-toggle="dropdown" href="#" role="button"
				aria-haspopup="false" aria-expanded="false">
				<img src="<?= base_url('') ?><?php $session = session();
												echo $session->get('photo') ?>" alt="user-image" class="user-avtar">
				<span>
					<span class="user-name">
						<?php $session = session();
						echo $session->get('name'); ?>
					</span>
					<span class="user-desc"><?php echo $session->get('role_name'); ?></span>
				</span>
			</a>
			<div class="dropdown-menu dropdown-menu-right pc-h-dropdown">
				<a href="/profile" class="dropdown-item">
					<i class="material-icons-two-tone">account_circle</i>
					<span>My Account</span>
				</a>
				<a href="/logout" class="dropdown-item">
					<i class="material-icons-two-tone">chrome_reader_mode</i>
					<span>Logout</span>
				</a>
			</div>
		</li>
	</ul>
</div>