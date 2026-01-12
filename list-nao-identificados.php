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
  /* ==== escopo local ==== */
  #dnd-extrato {
    --bg:#f5f5f5; --panel:#ffffff; --muted:#6b7280;
    --accent:#0ea5e9; --accent-2:#9333ea; --text:#111827;
    position: relative; /* ancorar o ghost aqui */
  }
  #dnd-extrato .hint { color:var(--muted); font-size:14px; margin-bottom:12px; }
  #dnd-extrato .boards { display:grid; gap:16px; grid-template-columns:repeat(2, minmax(260px,1fr)); align-items:start; }
  #dnd-extrato .board { background:var(--panel); border:1px solid #e5e7eb; border-radius:12px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,.05); }
  #dnd-extrato .board header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
  #dnd-extrato .board h6 { margin:0; font-weight:600; }
  #dnd-extrato .counter { color:var(--muted); font-size:12px; }

  #dnd-extrato .toolbar { display:flex; gap:8px; align-items:center; margin:8px 0 10px; flex-wrap:wrap; }
  #dnd-extrato .toolbar .grow { flex:1 1 200px; }
  #dnd-extrato input[type="search"], 
  #dnd-extrato select { padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px; background:#fff; font-size:14px; width:100%; }
  #dnd-extrato .select2-container { min-width:240px; } /* Select2 largura mínima */

  #dnd-extrato .list { min-height:240px; padding:8px; border-radius:8px; background:#f9fafb; outline:1px dashed #d1d5db; transition:outline-color .15s, background-color .15s; touch-action: none; }
  #dnd-extrato .list.dragover { outline-color:var(--accent); background:#e0f2fe; }
  #dnd-extrato .list.dragover.alt { outline-color:var(--accent-2); background:#ede9fe; }

  #dnd-extrato .item {
    list-style:none; user-select:none; -webkit-user-drag:none;
    background:#fff; border:1px solid #e5e7eb; color:var(--text);
    padding:10px 12px; border-radius:8px; margin:6px;
    display:flex; align-items:center; gap:10px;
    touch-action: none; cursor: grab; position: relative;
    transition: transform .1s ease, box-shadow .1s ease, border-color .15s ease;
  }
  #dnd-extrato .item.dragging { opacity:.9; transform:rotate(1deg) scale(1.02); box-shadow:0 6px 14px rgba(0,0,0,.15); border-color:var(--accent); cursor:grabbing; }
  #dnd-extrato .item .handle { width:12px; height:12px; border-radius:3px; background:#e5e7eb; }
  #dnd-extrato .item .meta { font-size:12px; color:var(--muted); margin-left:auto; }
  #dnd-extrato .placeholder { height:38px; margin:6px; border-radius:8px; border:2px dashed #d1d5db; background:#f3f4f6; }

  /* GHOST ancorado ao wrapper (corrige “pulo” fora do card) */
  #dnd-extrato .ghost { position: absolute; left: 0; top: 0; pointer-events: none; z-index: 1000; opacity:.95; box-shadow:0 8px 16px rgba(0,0,0,.2); }

  /* Paginação */
  #dnd-extrato .pager { display:flex; gap:8px; align-items:center; justify-content:flex-end; margin-top:8px; flex-wrap:wrap; }
  #dnd-extrato .pager .info { color:var(--muted); font-size:12px; margin-right:auto; }
  #dnd-extrato .pager button { border:1px solid #e5e7eb; background:#fff; border-radius:8px; padding:6px 10px; cursor:pointer; }
  #dnd-extrato .pager button[disabled] { opacity:.5; cursor:not-allowed; }
  @media (max-width: 760px) { #dnd-extrato .boards { grid-template-columns:1fr; } } 
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
                <li class="breadcrumb-item active" aria-current="page">Não Identificados</li>
							</ol>
						</nav>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
                        <div class="card">
							<div class="card-body p-4">
								<h5 class="mb-4">Extratos não Identificados</h5>
								<form class="row g-3" action="<?php echo $GLOBALS['HOST'] . $GLOBALS['APP_HOST'];?>upload-extrato.php" method="post" enctype="multipart/form-data">

<div id="dnd-extrato" class="row g-3">
  <div class="col-12">
    <div id="dnd-status" class="text-muted mb-2">Carregando dados…</div>
  </div>

  <div class="col-12">
    <div class="boards" id="dnd-boards" style="display:none;">
      <!-- Coluna A -->
      <section class="board">
        <header>
          <h6>Clientes</h6>
          <span class="counter" data-counter="dnd-list-a"></span>
        </header>

        <!-- Toolbar A: combo (Select2), busca e page size -->
        <div class="toolbar">
          <label for="dnd-combo-origem" class="mb-0" style="font-size:13px;">Clientes:</label>
          <select id="dnd-combo-origem" aria-label="Origem de itens para Lista A" style="min-width:240px">
            <option value="">Selecione…</option>
          </select>

          <input id="search-a" class="grow" type="search" placeholder="Pesquisar em Clientes (nome, cpf)..." />
          <div>Itens/página:</div>
          <select id="pagesize-a" style="width:auto">
            <option>5</option><option selected>10</option><option>20</option><option>50</option>
          </select>
        </div>

        <ul class="list" id="dnd-list-a" aria-label="Lista A"></ul>
        <div class="pager" id="pager-a"></div>
      </section>

      <!-- Coluna B -->
      <section class="board">
        <header>
          <h6>Conta Corrente</h6>
          <span class="counter" data-counter="dnd-list-b"></span>
        </header>

        <!-- Toolbar B: busca + page size -->
        <div class="toolbar">
          <input id="search-b" class="grow" type="search" placeholder="Pesquisar em Conta Corrente (nome, cpf)..." />
          <div>Itens/página:</div>
          <select id="pagesize-b" style="width:auto">
            <option>5</option><option selected>10</option><option>20</option><option>50</option>
          </select>
        </div>

        <ul class="list" id="dnd-list-b" aria-label="Conta Corrente"></ul>
        <div class="pager" id="pager-b"></div>
      </section>
    </div>
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

	<script src="assets/js/pages/list-nao-identificados.js?v=2"></script>


</body>

</html>


