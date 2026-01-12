<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0203'] == 0) {
        header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "index.php");
    }
?>

<!doctype html>
<html lang="en" class="semi-dark">

<?php
    include "head.php";
?>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<?php
            include "side-bar.php";
        ?>
		<!--end sidebar wrapper -->
		<!--start header -->
		<?php
            include "header.php";
        ?>
		<!--end header -->
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">
                        Admin
                    </div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Perfil</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
                        <div class="card">
							<div class="card-body p-4">
								<h5 class="mb-4">Perfil</h5>
								<form class="row g-3">
									<div class="col-md-6">
										<label for="cboCliente" class="form-label">Perfil</label>
										<select class="form-select" id="cboPerfil" data-placeholder="Perfil ...">
										</select>
									</div>

									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt01" checked="">
                                            <label class="form-check-label" for="swt01">01 - Dashboard</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt02" checked="">
                                            <label class="form-check-label" for="swt02">02 - Admin</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0201" checked="">
                                            <label class="form-check-label" for="swt0201">0201 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0202" checked="">
                                            <label class="form-check-label" for="swt0202">0202 - Adicionar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0203" checked="">
                                            <label class="form-check-label" for="swt0203">0203 - Perfil</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt03" checked="">
                                            <label class="form-check-label" for="swt03">03 - Usuário</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0301" checked="">
                                            <label class="form-check-label" for="swt0301">0301 - Perfil</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0302" checked="">
                                            <label class="form-check-label" for="swt0302">0302 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0303" checked="">
                                            <label class="form-check-label" for="swt0303">0303 - Adicionar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt04" checked="">
                                            <label class="form-check-label" for="swt04">04 - Clientes</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0401" checked="">
                                            <label class="form-check-label" for="swt0401">0401 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0402" checked="">
                                            <label class="form-check-label" for="swt0402">0402 - Adicionar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0403" checked="">
                                            <label class="form-check-label" for="swt0403">0403 - Conta Corrente</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:60px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt040301" checked="">
                                            <label class="form-check-label" for="swt040301">040301 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:60px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt040302" checked="">
                                            <label class="form-check-label" for="swt040302">040302 - Adicionar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt05" checked="">
                                            <label class="form-check-label" for="swt05">05 - Contas a Receber</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0501" checked="">
                                            <label class="form-check-label" for="swt0501">0501 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0502" checked="">
                                            <label class="form-check-label" for="swt0502">0502 - Upload</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0503" checked="">
                                            <label class="form-check-label" for="swt0503">0503 - Erro de Carga</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt06" checked="">
                                            <label class="form-check-label" for="swt06">06 - Extratos</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0601" checked="">
                                            <label class="form-check-label" for="swt0601">0601 - Upload</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0602" checked="">
                                            <label class="form-check-label" for="swt0602">0602 - Listar</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0603" checked="">
                                            <label class="form-check-label" for="swt0603">0603 - Não Identificados</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0604" checked="">
                                            <label class="form-check-label" for="swt0603">0604 - Fazer Conciliação</label>
                                        </div>
									</div>
									<div class="col-md-12">
                                        <div class="form-check form-switch" style="margin-left:30px">
                                            <input class="form-check-input" type="checkbox" role="switch" id="swt0605" checked="">
                                            <label class="form-check-label" for="swt0603">0605 - Conciliação Direta</label>
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

	<?php
        include "foot.php";
    ?>
	<script>
		const HOST = "<?php echo $GLOBALS['HOST'] ?>";
		const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
	</script>

	<script src="assets/js/pages/admin-permissao.js?v=1"></script>

</body>

</html>


