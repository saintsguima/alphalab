<?php
	include "if-logged.php";
	if ($_SESSION['perfil_permissoes']['0303'] == 0){
		header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "index.php");
	}	
?>

<!doctype html>
<html lang="en" class="semi-dark">

<?php
	include "head.php"
?>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<?php
			include "side-bar.php"
		?>
		<!--end sidebar wrapper -->
		<!--start header -->
		<?php
			include "header.php"
		?>
		<!--end header -->
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">
						Usuário
					</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">
									<?php
										$acaoGet  = $_GET["acao"];

										switch($acaoGet){
											case 1:
												$acao = "Incluir";
												break;

											case 2:
												$acao = "Alterar";
												break;
											default:
												$acao = "Excluir";
												break;
										}

										echo $acao;
									?>
									 - Usuário
								</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
                        <div class="card">
							<div class="card-body p-4">
								<h5 class="mb-4">Usuário</h5>
								<form class="row g-3">
									<input type="hidden" id="hdnAcao" name="hdnAcao" value="<?= $_GET["acao"]?>"/>
									<input type="hidden" id="hdnUserId" name="hdnUserId" value="<?= $_GET["userId"] ?? ''?>"/>

									<div class="col-md-6">
										<label for="txtNome" class="form-label">Nome</label>
										<div class="position-relative input-icon">
											<input type="text" class="form-control" id="txtNome" name="txtNome" placeholder="Nome">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-user'></i></span>
										</div>
									</div>
									<div class="col-md-6">
										<label for="txtUserName" class="form-label">User Name</label>
										<div class="position-relative input-icon">
											<input type="text" class="form-control" id="txtUserName" name="txtUserName" placeholder="User Name">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-user'></i></span>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtTelefone" class="form-label">Telefone</label>
										<div class="position-relative input-icon">
											<input type="text" class="form-control" id="txtTelefone" name="txtTelefone" placeholder="Telefone" data-type="telefone">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-phone'></i></span>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtEmail" class="form-label">Email</label>
										<div class="position-relative input-icon">
											<input type="text" class="form-control" id="txtEmail" name="txtEmail" placeholder="Email">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-envelope'></i></span>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtSenha" class="form-label">Senha</label>
										<div class="position-relative input-icon">
											<input type="password" class="form-control" id="txtSenha" name="txtSenha" placeholder="Senha" autocomplete="new-password">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-lock-alt'></i></span>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtConfirmaSenha" class="form-label">Confirma Senha</label>
										<div class="position-relative input-icon">
											<input type="password" class="form-control" id="txtConfirmaSenha" name="txtConfirmaSenha" placeholder="Confirma Senha">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-lock-alt'></i></span>
										</div>
									</div>
									<div class="col-md-6">
										<label for="cboPerfil" class="form-label">Perfil</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-transfer-alt'></i></div>
                                            <select class="form-select" id="cboPerfil" data-placeholder="Perfil ...">
                                            </select>
                                        </div>
									</div>
									<div class="col-md-12">
										<div class="form-check">
											<input class="form-check-input" type="checkbox" id="chkAtivo" name="chkAtivo">
											<label class="form-check-label" for="chkAtivo">Ativo</label>
										</div>
									</div>

									<div class="col-md-12">
										<div class="d-md-flex d-grid align-items-center gap-3">
											<button type="button" id="cmdOk" class="btn btn-primary px-4">Ok</button>
										</div>
									</div>
								</form>
							</div>
						</div>
	                </div>
				</div>
			</div>
		</div>
		<!--end page wrapper -->
		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<?php
			include "footer.php";
		?>
	</div>
	<!--end wrapper-->

	<!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
		  <div class="modal-content">
			<div class="modal-header gap-2">
			  <div class="position-relative popup-search w-100">
				<input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search" placeholder="Search">
				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4"><i class='bx bx-search'></i></span>
			  </div>
			  <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="search-list">
				   <p class="mb-1">Html Templates</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action active align-items-center d-flex gap-2 py-1"><i class='bx bxl-angular fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Web Designe Company</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-windows fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-dropbox fs-4' ></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Software Development</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-zoom fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Online Shoping Portals</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-slack fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-skype fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>
				   </div>
				</div>
			</div>
		  </div>
		</div>
	  </div>
    <!-- end search modal -->



	<!--start switcher-->
	<div class="switcher-wrapper">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<h6 class="mb-0">Theme Styles</h6>
			<hr/>
			<div class="d-flex align-items-center justify-content-between">
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode">
					<label class="form-check-label" for="lightmode">Light</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
					<label class="form-check-label" for="darkmode">Dark</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark" checked>
					<label class="form-check-label" for="semidark">Semi Dark</label>
				</div>
			</div>
			<hr/>
			<div class="form-check">
				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>
			</div>
			<hr/>
			<h6 class="mb-0">Header Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator headercolor1" id="headercolor1"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor2" id="headercolor2"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor3" id="headercolor3"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor4" id="headercolor4"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor5" id="headercolor5"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor6" id="headercolor6"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor7" id="headercolor7"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor8" id="headercolor8"></div>
					</div>
				</div>
			</div>
			<hr/>
			<h6 class="mb-0">Sidebar Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--end switcher-->
	<?php
		include "foot.php";
	?>
	<script>
		const HOST = "<?=$GLOBALS['HOST']?>";
		const APP_HOST = "<?=$GLOBALS['APP_HOST']?>";
		const API_URL = "<?=$GLOBALS['API_URL']?>";
	</script>

	<script src="assets/js/pages/crud-user.js?v=1"></script>

	<script>
		aplicarMascaraTelefone(document.getElementById('txtTelefone'));
		
		acao = document.getElementById("hdnAcao").value;
		acao = acao -0;
		if (acao === 2){
			getUserById('<?=$_GET["userId"]?>')
		}
	</script>
</body>

</html>


