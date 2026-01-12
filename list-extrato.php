<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0602'] == 0) {
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
                    <div class="breadcrumb-title pe-3">Extrato</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Listar</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
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
                                                    data-placeholder="Clientes ...">
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="txtDtInicial" class="form-label">Data Início</label>
                                            <div class="position-relative input-icon">
                                                <input type="date" class="form-control" id="txtDtInicial"
                                                    name="txtDtInicial" placeholder="Data Início">
                                                <span class="position-absolute top-50 translate-middle-y"><i
                                                        class='bx bx-calendar-alt'></i></span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="txtDtFinal" class="form-label">Data Final</label>
                                            <div class="position-relative input-icon">
                                                <input type="date" class="form-control" id="txtDtFinal"
                                                    name="txtDtFinal" placeholder="Data Final">
                                                <span class="position-absolute top-50 translate-middle-y"><i
                                                        class='bx bx-calendar-alt'></i></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2" style="padding-top:30px">
                                            <button type="button" id="cmdPesquisarPorData"
                                                class="btn btn-success px-4">Pesquisar</button>
                                        </div>

                                    </div>
                                    <table id="dtExtratoCliente" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Histórico</th>
                                                <th>Crédito</th>
                                                <th>Débito</th>
                                                <th>Saldo</th>
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
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    </div>
    <!--end wrapper-->

    <!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header gap-2">
                    <div class="position-relative popup-search w-100">
                        <input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search"
                            placeholder="Search">
                        <span
                            class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4"><i
                                class='bx bx-search'></i></span>
                    </div>
                    <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    <!-- end search modal -->



    <?php
        include "foot.php";
    ?>
    <?php
        include "footer.php";
    ?>

    <script src="assets/js/pages/list-extrato.js?v=2"></script>
    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script>
    const HOST = "<?php echo $GLOBALS['HOST'] ?>";
    const APP_HOST = "<?php echo $GLOBALS['APP_HOST'] ?>";
    const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
    </script>
</body>

</html>