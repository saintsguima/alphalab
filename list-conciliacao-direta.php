<?php
	include "if-logged.php";
	if ($_SESSION['perfil_permissoes']['0603'] == 0){
		header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "index.php");
	}	
?>

<!doctype html>
<html lang="en" class="semi-dark">

<?php
	include "head.php"
?>

<body>
	<!--wrapper-->
  <style>
    :root { --bg:#f5f5f5; --panel:#ffffff; --muted:#6b7280; --accent:#0ea5e9; --accent-2:#9333ea; --text:#111827; --ok:#16a34a; --danger:#dc2626; }
    * { box-sizing: border-box; }
    body { margin:0; padding:24px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial; color:var(--text); background:var(--bg); }
    h1 { margin:0 0 4px; font-size:20px; font-weight:600; }
    p.hint { margin:0 0 20px; color:var(--muted); font-size:14px; }

    .toolbar { display:flex; gap:8px; align-items:center; margin:10px 0 18px; flex-wrap:wrap; }
    .btn { appearance:none; border:1px solid #e5e7eb; background:#fff; padding:8px 12px; border-radius:8px; cursor:pointer; font-size:14px; }
    .btn.primary { background:var(--accent); color:#fff; border-color:transparent; }
    .btn.danger { background:var(--danger); color:#fff; border-color:transparent; }

    .boards { display:grid; gap:16px; grid-template-columns:repeat(2, minmax(280px,1fr)); align-items:start; }
    .board { background:var(--panel); border:1px solid #e5e7eb; border-radius:12px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
    .board header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; gap:10px; }
    .board header .left { display:flex; align-items:center; gap:10px; }
    .board header .dot { width:10px; height:10px; border-radius:999px; margin-right:2px; background:var(--accent); }
    .board h2 { display:flex; align-items:center; gap:8px; font-size:16px; font-weight:600; margin:0; }
    .counter { color:var(--muted); font-size:12px; }

    .dept-select { display:flex; align-items:center; gap:6px; }
    .dept-select label { font-size:12px; color:var(--muted); }
    .dept-select select { padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; background:#fff; }

    .list { min-height:220px; padding:8px; border-radius:8px; background:#f9fafb; outline:1px dashed #d1d5db; transition:outline-color .15s, background-color .15s; }
    .list.dragover { outline-color:var(--accent); background:#e0f2fe; }
    .list.dragover.alt { outline-color:var(--accent-2); background:#ede9fe; }

    .item { list-style:none; user-select:none; -webkit-user-drag:none; background:#fff; border:1px solid #e5e7eb; color:var(--text); padding:10px 12px; border-radius:8px; margin:6px; display:grid; grid-template-columns:auto 1fr auto auto; grid-template-rows:auto auto; column-gap:10px; row-gap:4px; touch-action: none; cursor: grab; position: relative; transition: transform .1s ease, box-shadow .1s ease, border-color .15s ease; }
    .item.dragging { opacity:.9; transform:rotate(1deg) scale(1.02); box-shadow:0 6px 14px rgba(0,0,0,.15); border-color:var(--accent); cursor:grabbing; }
    .item .handle { width:12px; height:12px; border-radius:3px; background:#e5e7eb; grid-row:1 / span 2; align-self:center; }
    .item .nome { font-weight:600; }
    .item .valor { font-size:12px; color:#065f46; justify-self:start; }
    .item .cpf { font-size:12px; color:var(--muted); justify-self:end; }
    .item .edit { align-self:center; justify-self:end; border:1px solid #e5e7eb; background:#fff; border-radius:6px; padding:4px 6px; font-size:12px; cursor:pointer; }

    .placeholder { height:44px; margin:6px; border-radius:8px; border:2px dashed #d1d5db; background:#f3f4f6; }
    .ghost { position: fixed; left:0; top:0; pointer-events:none; transform: translate(-9999px,-9999px); z-index: 1000; opacity:.95; box-shadow:0 8px 16px rgba(0,0,0,.2); }

    @media (max-width: 760px) { .boards { grid-template-columns:1fr; } }

    .editor { margin-top:12px; padding:12px; border:1px dashed #d1d5db; border-radius:10px; background:#fafafa; display:none; }
    .editor .row { display:grid; grid-template-columns:160px 1fr; gap:8px; margin:6px 0; }
    .editor input { padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; }
  </style>

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
                Extratos
                </div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
                <li class="breadcrumb-item active" aria-current="page">Conciliação Direta</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
                        <div class="card">
							<div class="card-body p-4">
								<h5 class="mb-4">Conciação Direta</h5>
								<!-- <form class="row g-3" action="<?php echo $GLOBALS['HOST'] . $GLOBALS['APP_HOST'];?>upload-extrato.php" method="post" enctype="multipart/form-data"> -->

                                <div class="boards">
                                    <section class="board">
                                    <header>
                                        <div class="left">
                                        <div class="dept-select">
                                            <label for="cboDepto">Cliente:</label>
                                            <select id="cboDepto"></select>
                                        </div>
                                        </div>
                                        <span class="counter" data-counter="list-a"></span>
                                    </header>
                                    <ul class="list" id="list-a" aria-label="Lista A"></ul>

                                    <!-- Editor aparece ao clicar no item da Lista A -->
                                    <div id="editor" class="editor" aria-live="polite">
                                        <div class="row"><strong>Item:</strong><span id="ed-nome"></span></div>
                                        <div class="row"><strong>CPF/CNPJ:</strong><span id="ed-cpf"></span></div>
                                        <div class="row">
                                        <label for="ed-valor-obj">Valor (original do objeto)</label>
                                        <input id="ed-valor-obj" type="text" readonly>
                                        </div>
                                        <div class="row">
                                        <label for="ed-valor-edit">Valor (digitar)</label>
                                        <input id="ed-valor-edit" type="text" placeholder="Ex.: 123,45">
                                        </div>
                                        <div class="row" style="grid-template-columns:1fr 1fr; gap:10px;">
                                        <button id="btn-salvar" class="btn primary">Salvar no DB local</button>
                                        <button id="btn-cancelar" class="btn">Cancelar</button>
                                        </div>
                                    </div>
                                        <div class="toolbar">
                                            <button id="btn-export" class="btn primary" title="Exporta JSON de todos os departamentos">Gravar</button>
                                            <span id="status" style="color:var(--muted);"></span>
                                        </div>
                                    </section>



                                    <section class="board">
                                    <header>
                                        <span class="counter" data-counter="list-b"></span>
                                    </header>
                                    <ul class="list" id="list-b" aria-label="Lista B"></ul>
                                    </section>
                                </div>


                                <!-- </form> -->
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

	<?php
		include "foot.php";
	?>
	<script>
		const HOST = "<?=$GLOBALS['HOST']?>";
		const API_URL = "<?=$GLOBALS['API_URL']?>";
	</script>

	<script src="assets/js/pages/list-conciliacao-direta.js?v=1"></script>


</body>

</html>


