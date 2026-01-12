<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0201'] == 0) {
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
                    <div class="breadcrumb-title pe-3">Admin</div>
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
                                    <table id="dtAdminPerfil" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Perfil</th>
                                                <th>Ação</th>
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
        <?php
            include "footer.php";
        ?>

    </div>


    <!--end wrapper-->

    <?php
        include "foot.php";
    ?>
    <script src="assets/js/pages/list-admin-perfil.js?v1"></script>
    <script src="assets/js/pages/crud-admin-perfil.js?v=1"></script>
    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script>
    const HOST = "<?php echo $GLOBALS['HOST'] ?>";
    const APP_HOST = "<?php echo $GLOBALS['APP_HOST'] ?>";
    const API_URL = "<?php echo $GLOBALS['API_URL'] ?>";
    </script>
</body>

</html>