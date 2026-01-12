<?php
	include "if-logged.php";
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
					</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Sugestão de Projeto de Lei</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
                        <div class="card">
							<div class="card-body p-4">
								<form class="row g-3">
                                    <h5 class="mb-4">Identificação</h5>
                                    <hr/>
                                    <input type="hidden" id="hdnAcao" name="hdnAcao" value="<?= $_GET["acao"]?>"/>
									<input type="hidden" id="hdnProjetoId" name="hdnProjetoId"/>

									<div class="col-md-12">
										<label for="txtProposta" class="form-label">Proposta</label>
										<div class="position-relative input-icon">
											<input type="text" id="txtProposta" name="txtProposta" class="form-control" maxlength="120" placeholder="Ex.: Política de Incentivo à Reciclagem nas Escolas">
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-layer'></i></span>
										</div>
                                        <div><span id="propostaCount">0</span></div>
									</div>
									<div class="col-md-12">
										<label for="txtNome" class="form-label">Resumo / Justificativa</label>
										<div class="position-relative input-icon">
											<textarea id="txtResumo" name="txtResumo" class="form-control" rows="3" maxlength="600" placeholder="Explique o problema e por que a proposta é necessária." required></textarea>
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-pyramid'></i></span>
                                            <div class="d-flex justify-content-between">
                                                <small class="help-hint">Seja breve e direto (até 600 caracteres).</small>
                                                <div><small class="help-hint"><span id="resumoCount">0</span></small></div>
                                            </div>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtNome" class="form-label">Texto completo da sugestão</label>
										<div class="position-relative input-icon">
											<textarea id="txtSugestao" name="txtSugestao" class="form-control" rows="10" maxlength="3000" placeholder="Redação sugerida (pode usar estrutura de artigos, incisos, etc.)" required></textarea>
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-align-justify'></i></span>
                                            <div class="d-flex justify-content-between">
                                                <small class="help-hint">Descreva a sugestão (até 3000 caracteres).</small>
                                                <div><small class="help-hint"><span id="sugestaoCount">0</span></small></div>
                                            </div>
										</div>
									</div>
                                    <h5 class="mb-4">Classificação e Contexto</h5>
                                    <hr/>
                                    <div class="col-md-12">
                                        <label class="form-label required" for="area">Área temática</label>
                                        <select id="cboarea" name="cboarea" class="form-select" required>
                                            <option value="" selected disabled>Selecione...</option>
                                            <option value="1">Saúde</option>
                                            <option value="2">Educação</option>
                                            <option value="3">Segurança</option>
                                            <option value="4">Meio Ambiente</option>
                                            <option value="5">Transparência</option>
                                            <option value="6">Direitos Humanos</option>
                                            <option value="7">Mobilidade Urbana</option>
                                            <option value="8">Economia</option>
                                        </select>
                                    </div>
									<div class="col-md-12">
										<label for="txtImpacto" class="form-label">Impacto esperado</label>
										<div class="position-relative input-icon">
											<textarea id="txtImpacto" name="txtImpacto" class="form-control" rows="3" maxlength="600" placeholder="Descreva benefícios sociais, econômicos e ambientais."></textarea>
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-bullseye'></i></span>
                                            <div class="d-flex justify-content-between">
                                                <div><small class="help-hint"><span id="impactoCount">0</span></small></div>
                                            </div>
										</div>
									</div>
									<div class="col-md-12">
										<label for="txtPalavraChave" class="form-label">Palavras‑chave</label>
										<div class="position-relative input-icon">
											<input type="text" id="txtPalavraChave" name="txtPalavraChave" class="form-control"  maxlength="150" placeholder="Digite as palavras, separadas por vírgula ( , )."></textarea>
											<span class="position-absolute top-50 translate-middle-y"><i class='bx bx-tag'></i></span>
                                            <div class="d-flex justify-content-between">
                                                
                                            </div>
										</div>
                                        <small class="help-hint">Use termos que facilitem a busca. Ex.: transparência, educação cívica.</small>
                                        <div><small class="help-hint"><span id="palavraChaveCount">0</span></small></div>
									</div>
                                    <h5 class="mb-4">Autoria e Contato</h5>
                                    <hr/>
                                    <div class="col-md-6">
										<label for="txtAutor" class="form-label">Autor(a)</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-user'></i></div>
                                            <input type="text" class="form-control" id="txtAutor" name="txtAutor" maxlenght="150" placeholder="Nome Completo">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
										<label for="txtCPF" class="form-label">CPF(opcional)</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-id-card'></i></div>
                                            <input type="text" class="form-control" id="txtCPF" name="txtCPF" maxlenght="150">
                                        </div>
                                        <small class="help-hint">Usado para verificação opcional de autenticidade (não será exposto publicamente).</small>
                                    </div>
                                    <div class="col-md-6">
										<label for="txtEmai" class="form-label">E-mail(opcional)</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i>@</i></div>
                                            <input type="text" class="form-control" id="txtEmail" name="txtEmail" maxlenght="150" placeholder="Ex.: voce@exemplo.com.br">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
										<label for="txtTelefone" class="form-label">Telefone(opcional)</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-phone'></i></div>
                                            <input type="text" class="form-control" id="txtTelefone" name="txtTelefone" maxlenght="150" placeholder="Ex.: (00) 00000-0000">
                                        </div>
                                    </div>

									<div class="col-md-12">
										<div class="form-check">
											<input class="form-check-input" type="checkbox" id="chkAutorizo" name="chkAutorizo">
											<label class="form-check-label" for="chkAutorizo">Autorizo o tratamento dos dados para fins de análise desta sugestão.</label>
										</div>
									</div>
									<div class="col-md-12">
										<div class="d-md-flex d-grid align-items-center gap-3">
											<button type="button" id="cmdOk" onclick="fnSalvarEleitor();" class="btn btn-primary px-4">Ok</button>
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
		const API_URL = "<?=$GLOBALS['API_URL']?>";
		const empresaId = "<?=$_SESSION["EMPRESAID"]?>";
	</script>

	<script src="assets/js/pages/crud-eleitor.js?v=1"></script>

  </body>

<script>
(() => {
  const bindings = new Map(); // inputEl -> spanEl

  function resolve(elOrId) {
    return typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
  }

  function format(len, max) {
    return (max && max > 0) ? `${len}/${max}` : `${len}`;
  }

  function atualizar(input) {
    const span = bindings.get(input);
    if (!span) return;
    span.textContent = format(input.value.length, input.maxLength || 0);
  }

  // ÚNICO listener para todos os inputs “bindados”
  document.addEventListener('input', (e) => {
    if (bindings.has(e.target)) atualizar(e.target);
  });

  // API pública
  window.bindContador = (inputIdOrEl, spanIdOrEl) => {
    const input = resolve(inputIdOrEl);
    const span  = resolve(spanIdOrEl);
    if (!input || !span) {
      console.warn('bindContador: input ou span não encontrado(s).');
      return;
    }
    bindings.set(input, span);
    atualizar(input); // inicializa já mostrando o valor atual
  };

  window.unbindContador = (inputIdOrEl) => {
    const input = resolve(inputIdOrEl);
    bindings.delete(input);
  };
})();

$(function () {
  bindContador('txtResumo',  'resumoCount');
  bindContador('txtProposta',  'propostaCount');
  bindContador('txtSugestao',  'sugestaoCount');
  bindContador('txtImpacto',  'impactoCount');
  bindContador('txtPalavraChave',  'palavraChaveCount');

});
</script>  
</html>


