<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0303'] == 0) {
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
                        Envio
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Whatsapp
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
                                <h5 class="mb-4">Whatsapp</h5>
                                <form class="row g-3">
                                    <div class="col-md-6 data-display-container">
                                        <div class="label-with-icon-container">
                                            <h2>
                                                <label class="form-label mb-0">
                                                    Último Disparo
                                                </label>
                                                <span class="field-icon">
                                                    <i class='bx bx-run'></i>
                                                </span>
                                            </h2>
                                        </div>
                                        <div class="position-relative input-icon">
                                            <span class="field-value" id="spnUltimoDisparo">.</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 data-display-container">
                                        <div class="label-with-icon-container">
                                            <h2>
                                                <label class="form-label mb-0">
                                                    Próximo Disparo
                                                </label>
                                                <span class="field-icon">
                                                    <i class='bx bx-bell'></i>
                                                </span>
                                            </h2>
                                        </div>
                                        <div class="position-relative input-icon">
                                            <span class="field-value" id="spnProximoDisparo">.</span>
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
    const APP_HOST = "<?php echo $GLOBALS['APP_HOST'] ?>";
    const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
    </script>

    <script src="assets/js/pages/send-whatsapp.js?v=1"></script>

    <script>
    aplicarMascaraTelefone(document.getElementById('txtTelefone'));

    acao = document.getElementById("hdnAcao").value;
    acao = acao - 0;
    if (acao === 2) {
        getUserById('<?php echo $_GET["userId"] ?>')
    }
    </script>
</body>

</html>