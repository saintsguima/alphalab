<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0501'] == 0) {
    header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "index.php");
    exit;
    }
?>

<!doctype html>
<html lang="pt-br" class="semi-dark">

<?php include "head.php"; ?>

<body>
    <div class="wrapper">

        <?php include "side-bar.php"; ?>
        <?php include "header.php"; ?>

        <div class="page-wrapper">
            <div class="page-content">

                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">Contas a Receber</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Listar (Ajuste)</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="container">
                    <div class="main-body">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">

                                    <div class="row mb-3">

                                        <div class="col-md-4">
                                            <label for="cboCliente" class="form-label">Cliente</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><i class='bx bx-male'></i></div>
                                                <select class="form-select" id="cboCliente"
                                                    data-placeholder="Clientes ..."></select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="txtDtInicial" class="form-label">Data Início</label>
                                            <div class="position-relative input-icon">
                                                <input type="month" class="form-control" id="txtDtInicial"
                                                    name="txtDtInicial" placeholder="Data Início">
                                                <span class="position-absolute top-50 translate-middle-y">
                                                    <i class='bx bx-calendar-alt'></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="txtDtFinal" class="form-label">Data Final</label>
                                            <div class="position-relative input-icon">
                                                <input type="month" class="form-control" id="txtDtFinal"
                                                    name="txtDtFinal" placeholder="Data Final">
                                                <span class="position-absolute top-50 translate-middle-y">
                                                    <i class='bx bx-calendar-alt'></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-2" style="padding-top:30px">
                                            <button type="button" id="cmdPesquisarPorData"
                                                class="btn btn-success px-4">Pesquisar</button>
                                        </div>

                                    </div>

                                    <table id="dtCR" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Data Início</th>
                                                <th>Data Final</th>
                                                <th>Valor C.R.</th>
                                                <th>Valor Conciliado</th>
                                                <th>Observação</th>
                                                <th>Data Criação</th>
                                            </tr>
                                        </thead>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="overlay toggle-icon"></div>

        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>

        <?php include "footer.php"; ?>
    </div>

    <!-- MODAL AJUSTE VALOR C.R. -->
    <div class="modal fade" id="modalAjusteVlTotal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Valor C.R.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label for="txtNovoVlTotal" class="form-label">Novo valor</label>
                    <input type="text" class="form-control" id="txtNovoVlTotal" placeholder="0,00" inputmode="numeric"
                        autocomplete="off">

                    <small class="text-muted">
                        Digite apenas números. Formato brasileiro. Não aceita valor negativo.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="cmdSalvarVlTotal">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AJUSTE VALOR CONCILIADO -->
    <div class="modal fade" id="modalAjusteVlConciliado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Valor Conciliado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="txtNovoVlConciliado" class="form-label">Valor conciliado</label>
                        <input type="text" class="form-control" id="txtNovoVlConciliado" placeholder="0,00"
                            inputmode="numeric" autocomplete="off">
                        <input type="hidden" id="hdnVlConciliadoAtual">

                        <small class="text-muted">
                            Digite apenas números. Formato brasileiro. Não aceita valor negativo.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="txtObsAjusteConciliado" class="form-label">Descrição do ajuste</label>
                        <textarea class="form-control" id="txtObsAjusteConciliado" rows="3" maxlength="200"
                            placeholder="Digite a descrição do ajuste (máximo 200 caracteres)"></textarea>
                        <small class="text-muted">
                            Máximo de 200 caracteres.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="txtSenhaAjusteConciliado" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="txtSenhaAjusteConciliado" maxlength="100"
                            autocomplete="off" placeholder="Informe sua senha">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="cmdSalvarVlConciliado">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header gap-2">
                    <div class="position-relative popup-search w-100">
                        <input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search"
                            placeholder="Search">
                        <span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4">
                            <i class='bx bx-search'></i>
                        </span>
                    </div>
                    <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="search-list">
                        <p class="mb-1">Html Templates</p>
                        <div class="list-group">
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action active align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-angular fs-4'></i>Best Html Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>
                        </div>

                        <p class="mb-1 mt-3">Web Designe Company</p>
                        <div class="list-group">
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-windows fs-4'></i>Best Html Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-dropbox fs-4'></i>Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>
                        </div>

                        <p class="mb-1 mt-3">Software Development</p>
                        <div class="list-group">
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-zoom fs-4'></i>Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>
                        </div>

                        <p class="mb-1 mt-3">Online Shoping Portals</p>
                        <div class="list-group">
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-slack fs-4'></i>Best Html Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-skype fs-4'></i>Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>
                            <a href="javascript:;"
                                class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i
                                    class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "foot.php"; ?>

    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/pages/list-ajuste.js?v=0"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script>
    const HOST = "<?php echo $GLOBALS['HOST'] ?>";
    const APP_HOST = "<?php echo $GLOBALS['APP_HOST'] ?>";
    const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
    const USERID = "<?php echo $_SESSION["USERID"] ?>";
    </script>
</body>

</html>