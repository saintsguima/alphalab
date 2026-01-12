<?php
    include "if-logged.php";
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

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0">Total de Faturamento do Mês</p>
                                        <h5 class="mb-0" id="h5AReceber">***********</h5>
                                    </div>
                                    <!-- <div id="chart1"></div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0">Total Faturado do Mês</p>
                                        <h5 class="mb-0" id="h5FaturadoNoMes">***********</h5>
                                    </div>
                                    <!-- <div id="chart2"></div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0">Inadimplência</p>
                                        <h5 class="mb-0" id="h5Inadimplencia">***********</h5>
                                    </div>
                                    <!-- <div id="chart3"></div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0">Total de pagamentos não idenficados</p>
                                        <h5 class="mb-0" id="h5PNI">***********</h5>
                                    </div>
                                    <!-- <div id="chart4"></div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
                <div class="row" style='display:none'>
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-md-2 g-3 align-items-center">
                                    <div class="col-lg-7 col-xl-7 col-xxl-8">
                                        <div class="chart-js-container4 p-4">
                                            <div class="piechart-legend">
                                                <h2 class="mb-1">68%</h2>
                                                <h6 class="mb-0">Total Traffic</h6>
                                            </div>
                                            <canvas id="chart6"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-xl-5 col-xxl-4">
                                        <div class="">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                                    <i class='bx bxs-circle text-info'></i>Organic (12%)</span>
                                                </li>
                                                <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                                    <i class='bx bxs-circle text-danger'></i><span>Direct (22%)</span>
                                                </li>
                                                <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                                    <i class='bx bxs-circle text-success'></i><span>Referral
                                                        (34%)</span>
                                                </li>
                                                <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                                    <i class='bx bxs-circle text-primary'></i><span>Others (18%)</span>
                                                </li>
                                                <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                                    <i class='bx bxs-circle text-warning'></i><span>Social (37%)</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-12 col-xl-12 col-xxl-6 d-flex">

                    </div>
                </div>
                <!--end row-->

                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="mb-0 mb-md-0">Faturamento x Faturado</h5>
                            </div>
                            <div class="col-md-9">
                                <form class="float-md-end">
                                    <div class="row row-cols-md-auto g-lg-3 align-items-center">
                                        <label for="inputFromDate" class="col-md-2 col-form-label text-md-end">Data
                                            de</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control" id="inputFromDate">
                                        </div>
                                        <label for="inputToDate" class="col-md-2 col-form-label text-md-end">Data
                                            até</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control" id="inputToDate">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <div class="chart-js-container3">
                            <canvas id="chart5"></canvas>
                        </div>
                    </div>
                </div>



                <!--end row-->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3" style="display:none">
                    <div class="col d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h5 class="mb-0">Browser Statistics</h5>
                                    </div>
                                    <div class="dropdown options ms-auto">
                                        <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                                            <i class='bx bx-dots-horizontal-rounded'></i>
                                        </div>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                            <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                                            <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-js-container3">
                                    <canvas id="chart7"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col d-flex">
                        <div class="card radius-10 w-100 overflow-hidden">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h5 class="mb-0">Origem das Informações</h5>
                                    </div>
                                    <div class="dropdown options ms-auto">
                                        <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                                            <i class='bx bx-dots-horizontal-rounded'></i>
                                        </div>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                            <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                                            <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-js-container2">
                                    <canvas id="chart8"></canvas>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center border-top">
                                    IA
                                    <span class="badge bg-primary rounded-pill">558</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Site
                                    <span class="badge bg-success rounded-pill">204</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Cadastro
                                    <span class="badge bg-danger rounded-pill">108</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 font-weight-bold">Trafego Social</h5>
                                    <p class="mb-0 ms-auto"><i
                                            class="bx bx-dots-horizontal-rounded float-right font-22"></i>
                                    </p>
                                </div>
                                <div class="d-flex mt-2 mb-4">
                                    <h2 class="mb-0 font-weight-bold">89,421</h2>
                                    <p class="mb-0 ms-1 font-14 align-self-end text-secondary">Total Visitas</p>
                                </div>
                                <div class="progress radius-10" style="height: 10px">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 45%"
                                        aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 20%"
                                        aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 15%"
                                        aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 25%"
                                        aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="table-responsive mt-4">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <div><i class="bx bxs-checkbox me-2 font-22 text-primary"></i>
                                                        </div>
                                                        <div>Instagram</div>
                                                    </div>
                                                </td>
                                                <td>46 Visits</td>
                                                <td class="px-0 text-right">33%</td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <div><i class="bx bxs-checkbox me-2 font-22 text-success"></i>
                                                        </div>
                                                        <div>Whatsapp</div>
                                                    </div>
                                                </td>
                                                <td>12 Visits</td>
                                                <td class="px-0 text-right">17%</td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <div><i class="bx bxs-checkbox me-2 font-22 text-info"></i>
                                                        </div>
                                                        <div>Facebook</div>
                                                    </div>
                                                </td>
                                                <td>29 Visits</td>
                                                <td class="px-0 text-right">21%</td>
                                            </tr>
                                            <tr>
                                                <td class="px-0">
                                                    <div class="d-flex align-items-center">
                                                        <div><i class="bx bxs-checkbox me-2 font-22 text-warning"></i>
                                                        </div>
                                                        <div>X</div>
                                                    </div>
                                                </td>
                                                <td>34 Visits</td>
                                                <td class="px-0 text-right">23%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->

            </div>
        </div>
        <!--end page wrapper -->
        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
        <?php
            include "footer.php";
        ?>
    </div>
    <!--end wrapper-->



    <?php
        include "foot.php";
        $permissoes = $_SESSION['perfil_permissoes'];
        if ($permissoes['01'] == 1) {
            $script = '
				<script>
				const HOST = "' . $GLOBALS['HOST'] . '";
        		const APP_HOST = "' . $GLOBALS['APP_HOST'] . '";
				const API_URL = "' . $GLOBALS['API_URL'] . '";
    			</script>
				<script src="assets/js/pages/dashboard.js?v=0"></script>
			';

            echo $script;
        }
    ?>

</body>

</html>