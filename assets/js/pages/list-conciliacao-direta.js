// ============================================================
// 0) "DB local" (localStorage) — sempre limpo ao entrar
//    Estrutura: { [departamento]: { [id]: {id, nome, cpf, valorOriginal, valorDigitado} } }
// ============================================================
const DB_KEY = 'dragdrop_db_v3';
function dbReset() { localStorage.removeItem(DB_KEY); }
function dbAll() { return JSON.parse(localStorage.getItem(DB_KEY) || '{}'); }
function dbSaveAll(obj) { localStorage.setItem(DB_KEY, JSON.stringify(obj)); }
function dbGetDept(dept) { const all = dbAll(); return all[dept] || {}; }
function dbSetDept(dept, data) { const all = dbAll(); all[dept] = data; dbSaveAll(all); }
function dbUpsertDept(dept, item) { const cur = dbGetDept(dept); cur[item.id] = item; dbSetDept(dept, cur); }
function dbDeleteDept(dept, id) { const cur = dbGetDept(dept); delete cur[id]; dbSetDept(dept, cur); }

// Limpa o DB ao abrir a tela
dbReset();

// ============================================================
// 1) Dados e Departamentos (mock)
// ============================================================
const DEPARTAMENTOS = [
    { id: '1', nome: 'Financeiro' },
    { id: '2', nome: 'Comercial' },
    { id: '3', nome: 'Operações' }
];
let currentDept = DEPARTAMENTOS[0].id;

let sourceItems = []; // Lista B original (base)

async function fetchListBInicial() {
    return [
        { id: "p1", nome: "Ana Maria", valor: 95.50, cpf: "123.456.789-01" },
        { id: "p2", nome: "Bruno Silva", valor: 120.00, cpf: "234.567.890-12" },
        { id: "p3", nome: "Carla Souza", valor: 79.90, cpf: "345.678.901-23" },
        { id: "p4", nome: "Daniel Costa", valor: 210.00, cpf: "456.789.012-34" }
    ];
}

// ============================================================
// 2) Renderização das listas com base no departamento atual
// ============================================================
const listA = document.getElementById('list-a');
const listB = document.getElementById('list-b');
const counterA = document.querySelector('[data-counter="list-a"]');
const counterB = document.querySelector('[data-counter="list-b"]');
const statusEl = document.getElementById('status');
const cboDepto = document.getElementById('cboDepto');

// Controles de filtro/paginação da Lista B (criamos dinamicamente no header da Lista B)
let searchTermB = '';
let pageB = 1;
let pageSizeB = 10; // default

function ensureListBControls() {
    // cria bloco de controles acima da list B, se ainda não existir
    if (document.getElementById('controls-b')) return;
    const boardB = listB.closest('.board');
    const header = boardB.querySelector('header');
    const controls = document.createElement('div');
    controls.id = 'controls-b';
    controls.style.display = 'flex';
    controls.style.gap = '8px';
    controls.style.flexWrap = 'wrap';
    controls.style.marginTop = '6px';
    controls.innerHTML = `
        <input id="txtSearchB" type="search" placeholder="Pesquisar..." style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px;min-width:220px;">
        <label style="font-size:12px;color:#6b7280;display:flex;align-items:center;gap:6px;">
          Mostrar
          <select id="selPageSizeB" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px;">
            <option>5</option>
            <option selected>10</option>
            <option>20</option>
            <option>50</option>
          </select>
          itens
        </label>
        <div style="margin-left:auto;display:flex;gap:6px;align-items:center;">
          <button id="btnPrevB" class="btn">Anterior</button>
          <span id="lblPageB" style="font-size:12px;color:#6b7280;">1/1</span>
          <button id="btnNextB" class="btn">Próxima</button>
        </div>
      `;
    header.after(controls);

    document.getElementById('txtSearchB').addEventListener('input', (e) => {
        searchTermB = e.target.value.trim().toLowerCase();
        pageB = 1;
        renderListB();
    });
    document.getElementById('selPageSizeB').addEventListener('change', (e) => {
        pageSizeB = parseInt(e.target.value, 10) || 10;
        pageB = 1;
        renderListB();
    });
    document.getElementById('btnPrevB').addEventListener('click', () => { if (pageB > 1) { pageB--; renderListB(); } });
    document.getElementById('btnNextB').addEventListener('click', () => { pageB++; renderListB(); });
}

function fmtValor(v) {
    if (v === undefined || v === null || v === '') return '';
    const n = Number(v);
    return isFinite(n) ? n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : String(v);
}

function makeItem({ id, nome, valor, cpf, data }) {
    const li = document.createElement('li');
    li.className = 'item';
    li.dataset.id = id;
    li.dataset.nome = nome || '';
    li.dataset.valor = valor ?? '';
    li.dataset.cpf = cpf || '';
    li.dataset.data = toBR(data);

    li.innerHTML = `
        <span class="handle" title="Arraste"></span>
        <span class="nome">${nome || '(sem nome)'}</span>
        <span class="cpf">${cpf || ''}</span>
        <span class="valor">${fmtValor(valor)}</span>
        <span class="data">Data: ${toBR(data)}</span>
        <button class="edit" title="Editar (Lista A)">✎</button>
      `;
    return li;
}

function fillList(listEl, items) {
    listEl.innerHTML = '';
    items.forEach(it => listEl.appendChild(makeItem(it)));
    [...listEl.querySelectorAll('.item')].forEach(attachPointerDnD);
}

function updateCounters() {
    counterA.textContent = `${listA.querySelectorAll('.item').length} item(s)`;
    counterB.textContent = `${listB.querySelectorAll('.item').length} item(s)`;
}

function renderAll() {
    // Itens selecionados no depto atual e em TODOS os deptos
    const all = dbAll();
    const selectedMapCurrent = dbGetDept(currentDept);
    const selectedIdsCurrent = new Set(Object.keys(selectedMapCurrent));
    const selectedIdsGlobal = new Set(Object.values(all).flatMap(map => Object.keys(map)));

    // List A: ids do depto atual
    const itemsA = sourceItems.filter(x => selectedIdsCurrent.has(x.id));
    fillList(listA, itemsA);

    // List B: todos que NÃO estão selecionados em nenhum depto (global)
    ensureListBControls();
    renderListB(selectedIdsGlobal);

    updateCounters();
    editor.style.display = 'none'; editingId = null;
}

function computeFilteredB(selectedIdsGlobal = new Set()) {
    // base: não selecionados globalmente
    let base = sourceItems.filter(x => !selectedIdsGlobal.has(x.id));
    // filtro por texto
    if (searchTermB) {
        base = base.filter(x =>
            (x.nome && x.nome.toLowerCase().includes(searchTermB)) ||
            (x.cpf && x.cpf.toLowerCase().includes(searchTermB)) ||
            (String(x.valor).toLowerCase().includes(searchTermB))
        );
    }
    return base;
}

function renderListB(selectedIdsGlobal = new Set()) {
    const all = dbAll();
    const globalIds = selectedIdsGlobal.size ? selectedIdsGlobal : new Set(Object.values(all).flatMap(map => Object.keys(map)));
    const data = computeFilteredB(globalIds);
    const total = data.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSizeB));
    if (pageB > totalPages) pageB = totalPages;
    const start = (pageB - 1) * pageSizeB;
    const pageData = data.slice(start, start + pageSizeB);

    fillList(listB, pageData);
    const lbl = document.getElementById('lblPageB');
    if (lbl) lbl.textContent = `${pageB}/${totalPages}`;
    updateCounters();
}

async function populateDeptCombo() {
    const opts = await fetchComboOptions();
    cboDepto.innerHTML = '';
    cboDepto.innerHTML = opts.map(o => `<option value="${o.id}">${o.nome}</option>`).join("");
    $(cboDepto).select2();

    //cboDepto.selectedIndex = 0;

}

$(cboDepto).on('change', function () {
    currentDept = $(this).val();
    renderAll();
});


// ============================================================
// 3) Editor (abre ao clicar no botão ✎ do item da Lista A)
// ============================================================
const editor = document.getElementById('editor');
const edNome = document.getElementById('ed-nome');
const edCpf = document.getElementById('ed-cpf');
const edValorObj = document.getElementById('ed-valor-obj');
const edValorEdit = document.getElementById('ed-valor-edit');
const btnSalvar = document.getElementById('btn-salvar');
const btnCancelar = document.getElementById('btn-cancelar');
let editingId = null; // id do item sendo editado

function openEditorFor(li) {
    const { id, nome, cpf, valor } = li.dataset;
    editingId = id;
    edNome.textContent = nome;
    edCpf.textContent = cpf;
    edValorObj.value = valor ?? '';
    const saved = dbGetDept(currentDept)[id];
    edValorEdit.value = saved?.valorDigitado ?? '';
    editor.style.display = 'block';
    editor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function fetchComboOptions() {
    const endPoint = `${API_URL}cliente/listar`;

    try {
        const res = await fetch(endPoint, {
            method: 'POST',                        // mude para 'POST' se sua API exigir
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            throw new Error(`HTTP ${res.status} - ${res.statusText}`);
        }

        const data = await res.json();

        if (data?.status === 'ok' && Array.isArray(data?.clientes)) {
            return data.clientes.map(c => ({
                id: c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
                nome: c.Nome ?? c.nome ?? c.RazaoSocial ?? c.razaoSocial ?? ''
            }));
        }


        if (Array.isArray(data)) {
            return data.map(c => ({
                id: c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
                nome: c.Nome ?? c.nome ?? c.RazaoSocial ?? c.razaoSocial ?? ''
            }));
        }

        if (Array.isArray(data?.data)) {
            return data.data.map(c => ({
                id: c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
                nome: c.Nome ?? c.nome ?? c.RazaoSocial ?? c.razaoSocial ?? ''
            }));
        }

        return [];
    }
    catch (err) {
        console.error('Erro ao carregar combo:', err);
        // Se estiver usando SweetAlert2 no projeto:
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao carregar Clientes',
                text: err.message || 'Falha ao consultar a API.'
            });
        }
        return [];
    }
}

async function fetchListB() {
    const url = `${API_URL}extratos/listar-nao-identificados`;

    try {
        const res = await fetch(url, {
            method: 'POST',                 // ajuste para 'GET' se sua API for GET
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                // 'Authorization': `Bearer ${SEU_TOKEN}`, // se precisar
            },
            // credentials: 'include',      // habilite se precisar enviar cookies
            // body: JSON.stringify({ ... }) // envie payload se a API exigir filtros
        });

        if (!res.ok) {
            // tenta pegar uma mensagem de erro do servidor para facilitar o debug
            let extra = '';
            try { extra = ' - ' + (await res.text()).slice(0, 200); } catch (_) { }
            throw new Error(`HTTP ${res.status} - ${res.statusText}${extra}`);
        }

        const result = await res.json();

        // Tenta detectar onde está a lista — aceita vários formatos comuns:
        // exemplo esperado: { nis: [ { Id, Nome, CPFCNPJ }, ... ] }
        let arr = [];
        if (Array.isArray(result)) arr = result;
        else if (Array.isArray(result?.nis)) arr = result.nis;
        else if (Array.isArray(result?.data)) arr = result.data;
        else if (Array.isArray(result?.itens)) arr = result.itens;
        else if (Array.isArray(result?.pessoas)) arr = result.pessoas;

        // Normaliza para o formato usado no DnD/paginação
        return arr.map(p => ({
            id: 'p' + p.Id ?? p.id ?? p.Codigo ?? p.codigo ?? String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? ''),
            nome: p.Nome ?? p.nome ?? p.NomeCompleto ?? p.nomeCompleto ?? '',
            cpf: formatCpfCnpjFromDigits(String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? '')),
            valor: p.Valor ?? 0.00,
            data: toBR(p.Data)
        }));
    } catch (err) {
        console.error('Erro ao carregar Lista B:', err);
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao carregar Lista B',
                text: err.message || 'Não foi possível obter os dados.'
            });
        }
        return [];
    }
}

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.edit');
    if (!btn) return;
    const li = btn.closest('.item');
    if (!li) return;
    if (li.parentElement?.id !== 'list-a') {
        Swal.fire({ icon: 'info', text: 'Edite itens somente na Lista A.' });
        return;
    }
    openEditorFor(li);
});

btnCancelar.addEventListener('click', () => { editor.style.display = 'none'; editingId = null; });

btnSalvar.addEventListener('click', () => {
    if (!editingId) return;
    const li = listA.querySelector(`.item[data-id="${editingId}"]`);
    if (!li) return;
    const payload = {
        id: editingId,
        nome: li.dataset.nome,
        cpf: li.dataset.cpf,
        valorOriginal: li.dataset.valor ?? null,
        valorDigitado: edValorEdit.value ?? '',
        data: li.dataset.data || ''
    };
    dbUpsertDept(currentDept, payload);
    Swal.fire({ icon: 'success', title: 'Salvo no DB local!', timer: 1200, showConfirmButton: false });
    editor.style.display = 'none'; editingId = null;
});

// ============================================================
// 4) Drag & Drop com Pointer Events (mouse + touch)
//    e integração com o DB local por departamento
// ============================================================
let dragging = null;          // <li> sendo arrastado
let placeholder = null;       // marcador visual
let ghost = null;             // "fantasma" que segue o dedo
let startOffset = { x: 0, y: 0 };
let sourceListId = null;      // de qual lista saiu o item

function createPlaceholder() { const ph = document.createElement('div'); ph.className = 'placeholder'; return ph; }
function createGhost(el, x, y) { const g = el.cloneNode(true); g.classList.add('ghost'); g.style.width = `${el.offsetWidth}px`; g.style.height = `${el.offsetHeight}px`; document.body.appendChild(g); moveGhost(g, x, y); return g; }
function moveGhost(g, x, y) { g.style.transform = `translate(${x - startOffset.x}px, ${y - startOffset.y}px)`; }
function getListUnderPoint(x, y) { const el = document.elementFromPoint(x, y); return el ? el.closest('.list') : null; }
function getAfterElement(list, y) {
    const items = [...list.querySelectorAll('.item:not(.dragging)')];
    let closest = { offset: Number.NEGATIVE_INFINITY, element: null };
    for (const child of items) {
        const rect = child.getBoundingClientRect();
        const offset = y - (rect.top + rect.height / 2);
        if (offset < 0 && offset > closest.offset) closest = { offset, element: child };
    }
    return closest.element;
}

function attachPointerDnD(item) {
    if (item._dndBound) return; item._dndBound = true;
    item.addEventListener('pointerdown', (e) => {
        // Evita começar drag ao clicar no botão ✎
        if (e.target.closest('.edit')) return;
        if (e.button !== 0 && e.pointerType === 'mouse') return;
        dragging = item; sourceListId = item.parentElement?.id || null; dragging.classList.add('dragging');
        const rect = item.getBoundingClientRect(); startOffset.x = e.clientX - rect.left; startOffset.y = e.clientY - rect.top;
        placeholder = createPlaceholder(); item.parentElement.insertBefore(placeholder, item.nextSibling);
        ghost = createGhost(item, e.clientX, e.clientY); item.setPointerCapture?.(e.pointerId);
        document.addEventListener('pointermove', onPointerMove, { passive: false });
        document.addEventListener('pointerup', onPointerUp, { passive: false });
        e.preventDefault();
    }, { passive: false });
}

function onPointerMove(e) {
    if (!dragging) return;
    const clientX = e.clientX ?? (e.touches && e.touches[0]?.clientX);
    const clientY = e.clientY ?? (e.touches && e.touches[0]?.clientY);
    if (clientX == null || clientY == null) return;
    if (ghost) moveGhost(ghost, clientX, clientY);
    const list = getListUnderPoint(clientX, clientY);
    document.querySelectorAll('.list').forEach(l => l.classList.remove('dragover', 'alt'));
    if (list) {
        list.classList.add('dragover'); if (list.id === 'list-b') list.classList.add('alt');
        const after = getAfterElement(list, clientY);
        if (!placeholder.parentElement || placeholder.parentElement !== list) list.appendChild(placeholder);
        if (after) list.insertBefore(placeholder, after); else list.appendChild(placeholder);
    }
    e.preventDefault();
}

async function onPointerUp(e) {
    if (!dragging) return;
    document.removeEventListener('pointermove', onPointerMove, { passive: false });
    document.removeEventListener('pointerup', onPointerUp);

    const targetList = placeholder.parentElement?.classList.contains('list') ? placeholder.parentElement : null;
    const toId = targetList?.id || null; const fromId = sourceListId;

    if (targetList) {
        targetList.insertBefore(dragging, placeholder);
        finalizeDbMove(fromId, toId, dragging);
    }
    dragging.classList.remove('dragging');
    placeholder?.remove(); ghost?.remove(); dragging = ghost = placeholder = null; sourceListId = null;
    document.querySelectorAll('.list').forEach(l => l.classList.remove('dragover', 'alt'));

    // Re-render conforme o depto selecionado (para manter B sem os já selecionados do depto)
    renderAll();
}

async function ensureSelect2Loaded() {
    // CSS (só injeta uma vez)
    if (!document.getElementById('select2-css')) {
        const link = document.createElement('link');
        link.id = 'select2-css';
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
        document.head.appendChild(link);
    }

    // espera jQuery do projeto
    const okJq = await waitFor(() => !!window.jQuery, 8000);
    if (!okJq) { console.warn('jQuery não detectado; Select2 não inicializado.'); return false; }

    // se plugin já estiver presente, pronto
    if (window.jQuery.fn && window.jQuery.fn.select2) return true;

    // carrega JS do Select2 quando jQuery já existe
    await new Promise(resolve => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
        s.onload = resolve;
        document.head.appendChild(s);
    });

    // aguarda plugin anexar
    return await waitFor(() => !!(window.jQuery.fn && window.jQuery.fn.select2), 4000);
}


function finalizeDbMove(fromId, toId, li) {
    const id = li.dataset.id;
    const nome = li.dataset.nome;
    const cpf = li.dataset.cpf;
    const valor = li.dataset.valor;
    const data = li.dataset.data || '';

    if (fromId === 'list-b' && toId === 'list-a') {
        const exists = dbGetDept(currentDept)[id];
        dbUpsertDept(currentDept, { id, nome, cpf, valorOriginal: valor ?? null, valorDigitado: exists?.valorDigitado ?? '', data });
    } else if (fromId === 'list-a' && toId === 'list-b') {
        dbDeleteDept(currentDept, id);
        if (editingId === id) { editor.style.display = 'none'; editingId = null; }
    }
    // Re-render global para refletir remoções/adições da Lista B em todos deptos
    renderAll();
}

// ============================================================
// 5) Exportar JSON, Testes & Reiniciar
// ============================================================
document.getElementById('btn-export').addEventListener('click', () => {
    // Exportar TODOS os registros (de todos os departamentos), incluindo o id do departamento em cada item
    const all = dbAll();
    const payload = [];
    for (const deptId of Object.keys(all)) {
        for (const it of Object.values(all[deptId])) {
            payload.push({
                depto: deptId,
                id: it.id,
                nome: it.nome,
                cpf: it.cpf,
                valorOriginal: it.valorOriginal,
                valorDigitado: it.valorDigitado ?? '',
                data: toBR(it.data)
            });
        }
    }

    const json = JSON.stringify(payload, null, 2);

    if (json === "[]") {
        Swal.fire({ title: `Conciliação Direta`, html: `Nenhum registro selecionado para fazer Conciliação.`, width: 700 });
        return;
    }

    gravaConciliacaoDireta(json);

    //Swal.fire({ title: `JSON - Todos os departamentos`, html: `<pre style="text-align:left;white-space:pre-wrap">${json}</pre>`, width: 700 });

});

//document.getElementById('btn-tests').addEventListener('click', runTests);

// document.getElementById('btn-reset').addEventListener('click', () => {
//   Swal.fire({ icon:'warning', title:'Reiniciar tela?', text:'Isso apagará o DB local (todos os departamentos) e recarregará.', showCancelButton:true, confirmButtonText:'Sim, reiniciar', cancelButtonText:'Cancelar' }).then(res => {
//     if (!res.isConfirmed) return;
//     dbReset();
//     init();
//   });
// });
function gravaConciliacaoDireta(payLoad) {
    const url = API_URL + "extratos/conciliacao-direta";
    // Monta o corpo da requisição
    const body = payLoad

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: body
    })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: 'Aviso',
                    text: response.mensagem,
                    showConfirmButton: false, // Remove o botão "OK"
                    allowOutsideClick: false, // Impede que o usuário feche o alerta clicando fora
                    timer: 2000, // Tempo em milissegundos (2 segundos)
                    timerProgressBar: true // Adiciona uma barra de progresso do timer
                }).then((result) => {
                    // Este bloco será executado quando o timer terminar
                    window.location.href = 'list-extrato.php'; // Substitua pelo URL da sua página
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    draggable: true,
                    text: response.mensagem || 'Erro ao atualizar o status.'
                });
            }
        })
        .catch(error => {
            console.error('Erro ao conectar com a API:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro de conexão',
                draggable: true,
                html: 'Erro ao conectar com a API:', error
            });
        });
}

function formatCpfCnpjFromDigits(digs) {
    if (digs.length <= 11) {
        // CPF: 000.000.000-00
        return digs
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
    }
    else {
        // CNPJ: 00.000.000/0000-00
        return digs
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
            .replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
    }
}

function toBR(dateLike) {
    if (dateLike == null || dateLike === '') return '';

    // Já é Date
    if (dateLike instanceof Date) {
        if (isNaN(dateLike)) return '';
        const dd = String(dateLike.getDate()).padStart(2, '0');
        const mm = String(dateLike.getMonth() + 1).padStart(2, '0');
        const yyyy = dateLike.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    }

    const s = String(dateLike).trim();

    // Já está em dd/MM/yyyy
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(s)) return s;

    // ISO yyyy-mm-dd (ou yyyy/mm/dd) com ou sem hora
    const mIso = s.match(/^(\d{4})[-/](\d{2})[-/](\d{2})(?:[T\s].*)?$/);
    if (mIso) {
        const [, y, m, d] = mIso;
        return `${d}/${m}/${y}`;
    }

    // Epoch segundos / milissegundos
    if (/^\d{10}$/.test(s)) return toBR(new Date(Number(s) * 1000));
    if (/^\d{13}$/.test(s)) return toBR(new Date(Number(s)));

    // Última tentativa: parsing do JS
    const d = new Date(s);
    if (!isNaN(d)) return toBR(d);

    return '';
}

// ============================================================
// 6) Inicialização
// ============================================================
async function init() {
    statusEl.textContent = 'Carregando...';
    listA.innerHTML = ''; listB.innerHTML = ''; editor.style.display = 'none'; editingId = null;

    // Combo de departamentos
    populateDeptCombo();

    // Carrega base
    sourceItems = await fetchListB();
    //alert(JSON.stringify(sourceItems));
    renderAll();
    statusEl.textContent = 'Pronto'; setTimeout(() => statusEl.textContent = '', 800);
}

init();
