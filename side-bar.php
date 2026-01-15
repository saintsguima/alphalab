		<div class="sidebar-wrapper" data-simplebar="true">
		    <div class="sidebar-header">
		        <div>
		            <img src="assets/images/alphalabs-logo-final.png" class="logo-icon" alt="logo icon">
		        </div>
		        <div>
		            <h4 class="logo-text">ALPHA Labs</h4>
		        </div>
		        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
		        </div>
		    </div>
		    <!--navigation-->
		    <ul class="metismenu" id="menu">
		        <?php
                    $permissoes = $_SESSION['perfil_permissoes'];
                    if ($permissoes['01'] == 1) {
                        $menu01 = '
							<li>
								<li>
									<a href="' . $GLOBALS["APP_HOST"] . 'dashboard.php">
										<div class="parent-icon"><i class="bx bx-home-alt"></i>
										</div>
										<div class="menu-title">Dashboard</div>
									</a>
								</li>
							</li>
							<li class="menu-separator"><hr/></li>

						';

                        echo $menu01;
                    }

                    if ($permissoes['02'] == 1) {
                        $menu02 = '
							<li>
								<a href="javascript:;" class="has-arrow">
									<div class="parent-icon"><i class="bx bx-check-shield"></i>
									</div>
									<div class="menu-title">Admin</div>
								</a>
								<ul>
						';

                        if ($permissoes['0201'] == 1) {
                            $menu02 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-admin-perfil.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['0202'] == 1) {
                            $menu02 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-admin-perfil.php?acao=1"><i class="bx bx-user-plus"></i>Adicionar</a></li>';
                        }
                        if ($permissoes['0203'] == 1) {
                            $menu02 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'admin-permissao.php"><i class="bx bx-transfer-alt"></i>Perfil</a></li>';
                        }
                        $menu02 .= '</ul> </li> <li class="menu-separator"><hr/></li>';

                        echo $menu02;
                    }

                    if ($permissoes['03'] == 1) {
                        $menu03 = '
							<li>
								<a href="javascript:;" class="has-arrow">
									<div class="parent-icon"><i class="bx bx-user-pin"></i>
									</div>
									<div class="menu-title">Usuário</div>
								</a>
								<ul>';
                        if ($permissoes['0301'] == 1) {
                            $menu03 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'user-profile.php"><i class="bx bx-user-circle"></i>Perfil</a></li>';
                        }
                        if ($permissoes['0302'] == 1) {
                            $menu03 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-user.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['0303'] == 1) {
                            $menu03 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-user.php?acao=1"><i class="bx bx-user-plus"></i>Adicionar</a></li>';
                        }
                        $menu03 .= '</ul></li><li class="menu-separator"><hr/></li>';

                        echo $menu03;

                    }

                    if ($permissoes['04'] == 1) {
                        $menu04 = '
						<li>
							<a href="javascript:;" class="has-arrow">
								<div class="parent-icon"><i class="bx bx-user-circle"></i>
								</div>
								<div class="menu-title">Clientes</div>
							</a>
							<ul>';
                        if ($permissoes['0401'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-cliente.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['0402'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-cliente.php?acao=1"><i class="bx bx-user-check"></i>Adicionar</a></li>';
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-carga-cliente.php"><i class="bx bx-cloud-upload"></i>Carga por Upload</a></li>';
                        }
                        if ($permissoes['0403'] == 1) {
                            $menu04 .= '<li> <a class="has-arrow" href="javascript:;"><i class="bx bx-dollar"></i>Conta Corrente</a>';
                        }

                        $menu04 .= '<ul>';
                        if ($permissoes['040301'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-cc.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['040302'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-cc.php?acao=1"><i class="bx bx-list-plus"></i>Adicionar</a></li>';
                        }

                        $menu04 .= '</ul>';
                        if ($permissoes['0404'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-excecao.php?acao=1"><i class="bx bx-notification-off"></i>Exceção</a></li>';
                        }
                        if ($permissoes['0405'] == 1) {
                            $menu04 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-plano.php?acao=1"><i class="bx bx-plus-medical"></i>Plano</a></li>';
                        }

                        $menu04 .= '</li></ul></li><li class="menu-separator"><hr/></li>';

                        echo $menu04;

                    }

                    if ($permissoes['05'] == 1) {
                        $menu05 = '
							<li>
								<a href="javascript:;" class="has-arrow">
									<div class="parent-icon"><i class="bx bx-book-reader"></i>
									</div>
									<div class="menu-title">Contas a receber</div>
								</a>
								<ul>';
                        if ($permissoes['0501'] == 1) {
                            $menu05 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-cr.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['0502'] == 1) {
                            $menu05 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-cr.php"><i class="bx bx-layer-plus"></i>Upload</a></li>';
                        }
                        if ($permissoes['0503'] == 1) {
                            $menu05 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-cr-erro.php"><i class="bx bx-error"></i>Erro de Carga</a></li>';
                        }
                        if ($permissoes['0504'] == 1) {
                            $menu05 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-ajuste.php"><i class="bx bx-slider"></i>Ajuste</a></li>';
                        }

                        $menu05 .= '</ul></li><li class="menu-separator"><hr/></li>';

                        echo $menu05;
                    }

                    if ($permissoes['06'] == 1) {
                        $menu06 = '
							<li>
								<a href="javascript:;" class="has-arrow">
									<div class="parent-icon"><i class="bx bx-carousel"></i>
									</div>
									<div class="menu-title">Extratos</div>
								</a>
								<ul>';
                        if ($permissoes['0601'] == 1) {
                            $menu06 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-extrato.php"><i class="bx bx-upload"></i>Upload</a></li>';
                        }
                        if ($permissoes['0602'] == 1) {
                            $menu06 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-extrato.php"><i class="bx bx-list-ol"></i>Listar</a></li>';
                        }
                        if ($permissoes['0603'] == 1) {
                            $menu06 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-nao-identificados.php"><i class="bx bx-dislike"></i>Não Identificados</a></li>';
                        }
                        if ($permissoes['0604'] == 1) {
                            $menu06 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'crud-conciliacao.php"><i class="bx bx-pen"></i>Fazer Conciliação</a></li>';
                        }
                        if ($permissoes['0605'] == 1) {
                            $menu06 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'list-conciliacao-direta.php"><i class="bx bx-bookmark-plus"></i>Conciliação Direta</a></li>';
                        }

                        $menu06 .= '</ul></li><li class="menu-separator"><hr/></li>';

                        echo $menu06;
                    }

                    if ($permissoes['07'] == 1) {
                        $menu07 = '
							<li>
								<a href="javascript:;" class="has-arrow">
									<div class="parent-icon"><i class="bx bx-bot"></i>
									</div>
									<div class="menu-title">Envios</div>
								</a>
								<ul>';

                        if ($permissoes['0701'] == 1) {
                            $menu07 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'send-whatsapp.php"><i class="bx bx-phone-outgoing"></i>Whatsapp</a></li>';
                        }

                        if ($permissoes['0702'] == 1) {
                            $menu07 .= '<li> <a href="' . $GLOBALS["APP_HOST"] . 'send-email.php"><i class="bx bx-mail-send"></i>E-mail</a></li>';
                        }

                        $menu07 .= '</ul></li><li class="menu-separator"><hr/></li>';

                        echo $menu07;

                    }
                ?>
		    </ul>
		    <!--end navigation-->
		</div>