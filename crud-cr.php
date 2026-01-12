<?php
    // Opcional: mensagens de retorno (após o upload.php redirecionar)
    $msg = $_GET['msg'] ?? '';
    $err = $_GET['err'] ?? '';
?>

<?php
    include "if-logged.php";
    if ($_SESSION['perfil_permissoes']['0502'] == 0) {
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
                        Contas a Receber
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Upload</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
                <div class="container">
                    <div class="main-body">
                        <div class="card">
                            <div class="card-body p-4">
                                <h5 class="mb-4">Arquivo de Contas a Receber</h5>
                                <form class="row g-3"
                                    action="<?php echo $GLOBALS['HOST'] . $GLOBALS['APP_HOST']; ?>uploadcr.php"
                                    method="post" enctype="multipart/form-data">
                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <input type="file" name="arquivo" class="dropify"
                                                data-allowed-file-extensions="csv" data-max-file-size="5M" accept=".csv"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="txtDtInicio" class="form-label">Data Início</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-calendar-alt'></i></div>
                                            <input type="date" class="form-control" id="txtDtInicio" name="txtDtInicio"
                                                placeholder="Data Início" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="txtDtFinal" class="form-label">Data Final</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text"><i class='bx bx-calendar-alt'></i></div>
                                            <input type="date" class="form-control" id="txtDtFinal" name="txtDtFinal"
                                                placeholder="Data Final" required />
                                        </div>
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
    const HOST = "<?php echo $GLOBALS['HOST']?>";
    const API_URL = "<?php echo $GLOBALS['API_URL']?>";
    </script>

    <script src="assets/js/pages/crud-cr.js?1"></script>

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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form[action*="uploadcr.php"]');
        const dtInicio = document.getElementById('txtDtInicio');
        const dtFinal = document.getElementById('txtDtFinal');

        function validarDatas() {
            // limpa mensagens
            dtInicio.setCustomValidity('');
            dtFinal.setCustomValidity('');

            const v1 = dtInicio.value; // YYYY-MM-DD
            const v2 = dtFinal.value;

            // Regras de min/max para guiar o usuário
            if (v1) dtFinal.min = v1;
            else dtFinal.removeAttribute('min');
            if (v2) dtInicio.max = v2;
            else dtInicio.removeAttribute('max');

            // Regra: Início <= Final (ajuste a mensagem conforme sua regra)
            if (v1 && v2 && v1 > v2) {
                dtFinal.setCustomValidity('A Data Final deve ser igual ou posterior à Data Início.');
            }
        }

        dtInicio.addEventListener('change', validarDatas);
        dtFinal.addEventListener('change', validarDatas);

        form.addEventListener('submit', (e) => {
            validarDatas();
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
            }
        });
    });
    </script>
</body>

</html>