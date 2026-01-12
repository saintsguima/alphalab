<!doctype html>
<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $mensagem = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();

        require_once 'Globals/globals.php';
        require_once __DIR__ . '/DbConnection/ALPHAConnection.php';
        $pdo = db_pdo();

        $username = $_POST['inputEmailAddress'] ?? '';
        $password = $_POST['inputChoosePassword'] ?? '';

        $senhaCriptografada = hash('sha256', $password);

        $sql = "select
		u.Id,
		u.Nome,
		u.UserName,
		u.Email,
		u.Telefone,
		u.PerfilId
	from
		usuario u
	WHERE
		u.email = :username AND u.pwd = :password AND ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username, ':password' => $senhaCriptografada]);

        if ($stmt->rowCount() === 1) {
            $resultado            = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION["NOME"]     = $resultado['Nome'];
            $_SESSION["USERNAME"] = $resultado['UserName'];
            $_SESSION["EMAIL"]    = $resultado['Email'];
            $_SESSION["USERID"]   = $resultado['Id'];
            $_SESSION["TELEFONE"] = $resultado['Telefone'];
            $_SESSION["PERFIL"]   = $resultado['PerfilId'];

            $sql  = "SELECT Permissao, flAtivo FROM PerfilPermissao WHERE PerfilId = :PerfilId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':PerfilId' => $_SESSION["PERFIL"]]);

            // Array temporário para armazenar as permissões
            $permissoesDoPerfil = [];

            // Loop para montar o array associativo
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissao = $row['Permissao'];
                $flAtivo   = $row['flAtivo'];

                // Armazena a permissão como CHAVE e o flAtivo como VALOR
                // Ex: ['0201' => 1, '0202' => 0]
                $permissoesDoPerfil[$permissao] = $flAtivo;
            }

            // Armazena o array na variável de sessão
            $_SESSION['perfil_permissoes'] = $permissoesDoPerfil;

            // echo "Permissões carregadas na sessão para o Perfil $perfilId:<br>";
            // print_r($_SESSION['perfil_permissoes']);
            // die;
            header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "dashboard.php");
            die;

        } else {
            $mensagem = 'Swal.fire({icon: "error", title: "Oops...", text: "Usuário e/ou senha não cadastrados!"});';
        }

    } else {
        session_start();

        // Remove todas as variáveis da sessão
        $_SESSION = [];

        // Destroi a sessão completamente
        session_destroy();

    }
?>
<html lang="en" class="semi-dark">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="assets/images/alphalabs-logo-final.png" type="image/png" />
    <!--plugins-->
    <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
    <!-- loader-->
    <link href="assets/css/pace.min.css" rel="stylesheet" />
    <script src="assets/js/pace.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet">
    <title>ALPHA Labs</title>
</head>

<body class="bg-login">
    <?php

    ?>
    <!--wrapper-->
    <div class="wrapper">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
            <div class="container">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="p-4">
                                    <div class="mb-3 text-center">
                                        <img src="assets/images/alphalabs-logo-final.png" width="60" alt="" />
                                    </div>
                                    <div class="text-center mb-4">
                                        <h5 class="">ALPHA Labs</h5>
                                        <p class="mb-0">Por favor, faça login na sua conta</p>
                                    </div>
                                    <div class="form-body">
                                        <form class="row g-3" id="loginForm" method='post'>
                                            <div class="col-12">
                                                <label for="inputEmailAddress" class="form-label">Email</label>
                                                <input type="email" class="form-control" name="inputEmailAddress"
                                                    id="inputEmailAddress" placeholder="jhon@example.com">
                                            </div>
                                            <div class="col-12">
                                                <label for="inputChoosePassword" class="form-label">Senha</label>
                                                <div class="input-group" id="show_hide_password">
                                                    <input type="password" class="form-control border-end-0"
                                                        name="inputChoosePassword" id="inputChoosePassword"
                                                        placeholder="Enter Password"> <a href="javascript:;"
                                                        class="input-group-text bg-transparent"><i
                                                            class='bx bx-hide'></i></a>
                                                </div>
                                            </div>
                                            <!-- <div class="col-md-6">
												<div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
													<label class="form-check-label" for="flexSwitchCheckChecked">Lembre-me</label>
												</div>
											</div>
											<div class="col-md-6 text-end"><a href="auth-basic-forgot-password.html">Esqueceu a senha ?</a>
											</div>-->
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary">Entrar</button>
                                                </div>
                                            </div>
                                            <!-- <div class="col-12">
												<div class="text-center ">
													<p class="mb-0">Don't have an account yet? <a href="auth-basic-signup.html">Sign up here</a>
													</p>
												</div>
											</div> -->
                                        </form>
                                    </div>
                                    <!-- <div class="login-separater text-center mb-5"> <span>OR SIGN IN WITH</span>
										<hr/>
									</div>
									<div class="list-inline contacts-social text-center">
										<a href="javascript:;" class="list-inline-item bg-facebook text-white border-0 rounded-3"><i class="bx bxl-facebook"></i></a>
										<a href="javascript:;" class="list-inline-item bg-twitter text-white border-0 rounded-3"><i class="bx bxl-twitter"></i></a>
										<a href="javascript:;" class="list-inline-item bg-google text-white border-0 rounded-3"><i class="bx bxl-google"></i></a>
										<a href="javascript:;" class="list-inline-item bg-linkedin text-white border-0 rounded-3"><i class="bx bxl-linkedin"></i></a>
									</div> -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>
    </div>
    <!--end wrapper-->
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <!--Password show & hide js -->
    <script>
    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });

        // $('#loginForm').on('submit', function(e) {
        // 	e.preventDefault();

        // 	const userName = $('#inputEmailAddress').val();
        // 	const userPwd = $('#inputChoosePassword').val();

        // 	if (userName === '' || userPwd ==='')	{
        // 		Swal.fire({
        // 		icon: "error",
        // 		title: "Oops...",
        // 		text: "Usuário e senha devem ser preenchidos!"
        // 		});

        // 		return;
        // 	}

        // 	console.log(JSON.stringify({ userName, userPwd }));

        // 	$.ajax({
        // 	url: '<?php //echo $GLOBALS['API_URL'];?>login/',
        // 	method: 'POST',
        // 	contentType: 'application/json',
        // 	data: JSON.stringify({ userName, userPwd }),
        // 	success: function(response) {
        // 		if (response.status === 'ok') {
        // 			document.cookie = "nome=" + response.nome + "; path=/; max-age=3600";
        // 			document.cookie = "username=" + response.username + "; path=/; max-age=3600";
        // 			document.cookie = "email=" + response.email + "; path=/; max-age=3600";
        // 			document.cookie = "userId=" + response.userId + "; path=/; max-age=3600";

        // 			window.location.href="<?php //echo $GLOBALS['HOST']?>/assessor/index.html"
        // 		}
        // 	},
        // 	error: function(xhr) {
        // 		alert('Erro na requisição: ' + xhr.status);
        // 	}
        // 	});
        // });
        <?php echo $mensagem; ?>
    });
    </script>
    <!--app JS-->
    <script src="assets/js/app.js"></script>
</body>

</html>