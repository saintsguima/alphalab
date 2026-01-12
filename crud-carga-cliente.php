<?php
    // Opcional: mensagens de retorno (após o upload.php redirecionar)
    $msg = $_GET['msg'] ?? '';
    $err = $_GET['err'] ?? '';
?>

<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0601'] == 0) {
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
                        Clientes

                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Carga por Upload</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
                <div class="container">
                    <div class="main-body">
                        <div class="card">
                            <div class="card-body p-4">
                                <h5 class="mb-4">Arquivo de Clientes</h5>
                                <form class="row g-3"
                                    action="<?php echo $GLOBALS['HOST'] . $GLOBALS['APP_HOST']; ?>upload-carga-cliente.php"
                                    method="post" enctype="multipart/form-data">
                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <input type="file" name="arquivo" class="dropify"
                                                data-allowed-file-extensions="csv" data-max-file-size="5M" accept=".csv"
                                                required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                    </div>

                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <button type="submit" class="btn btn-primary px-4">Enviar</button>
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
        <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i
                class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
        <?php
            include "footer.php";
        ?>
    </div>
    <!--end wrapper-->

    <?php
        include "foot.php";
    ?>
    <script>
    const HOST = "<?php echo $GLOBALS['HOST'] ?>";
    const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
    </script>

    <!-- <script src="assets/js/pages/crud-extrato.js?v=2"></script> -->

    <script>
    $(function() {
        $('.dropify').dropify({
            messages: {
                'default': 'Arraste e solte um arquivo aqui ou clique',
                'replace': 'Arraste e solte ou clique para substituir',
                'remove': 'Remover',
                'error': 'Ops, algo deu errado.'
            },
            error: {
                'fileSize': 'O arquivo é muito grande (máx: {{ value }}).',
                'fileExtension': 'Extensão não permitida (apenas: {{ value }}).'
            }
        });
    });
    </script>

</body>

</html>