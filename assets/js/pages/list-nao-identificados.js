  // ===================== helpers =====================
  const debounce = (fn, ms=300) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); }; };

  function formatCPF(v) {
    v = (v || '').toString().replace(/\D/g, '').slice(0, 11);
    return v.length === 11 ? v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : v;
  }

  // ---- Loader Select2 aguardando jQuery da página ----
  function waitFor(testFn, timeout=8000, interval=50) {
    return new Promise(res => {
      const start = Date.now();
      (function loop() {
        if (testFn()) return res(true);
        if (Date.now() - start >= timeout) return res(false);
        setTimeout(loop, interval);
      })();
    });
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

  // ===================== mock APIs (troque por fetch na sua API) =====================
  async function fetchComboOptions() {
    const endPoint = `${API_URL}cliente/listar`;

    try {
        const res = await fetch(endPoint, {
        method: 'POST',                        // mude para 'POST' se sua API exigir
        headers: { 'Accept': 'application/json' }
    });

    if (!res.ok){
        throw new Error(`HTTP ${res.status} - ${res.statusText}`);
    } 
    
    const data = await res.json();

    if (data?.status === 'ok' && Array.isArray(data?.clientes)) {
        return data.clientes.map(c => ({
        id:   c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
        nome: c.Nome ?? c.nome ?? c.RazaoSocial ?? c.razaoSocial ?? ''
        }));
    }


    if (Array.isArray(data)) {
        return data.map(c => ({
            id:   c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
            nome: c.Nome ?? c.nome ?? c.RazaoSocial ?? c.razaoSocial ?? ''
        }));
    }

    if (Array.isArray(data?.data)) {
        return data.data.map(c => ({
        id:   c.Id ?? c.id ?? c.Codigo ?? c.codigo ?? String(c.Id),
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

  async function fetchItemsForCombo(origemId) {
if (!origemId) return [];

  const url = `${API_URL}clientecc/get-clientecc-by-idcliente`;
  const payLoad = { clienteId: origemId };

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
        // 'Authorization': `Bearer ${SEU_TOKEN}` // se precisar
      },
      body: JSON.stringify(payLoad)
    });

    if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.statusText}`);

    const result = await res.json();

    // aceita formatos comuns
    const raw =
      Array.isArray(result?.clienteccs) ? result.clienteccs :
      Array.isArray(result)             ? result            : [];

    // normaliza para { id, nome, cpf }
    return raw.map(p => ({
      id:   p.Id ?? p.id ?? p.codigo ?? p.Codigo ?? String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? ''),
      nome: p.NomeCC ?? p.nome ?? p.Nome ?? '',
      cpf:  formatCpfCnpjFromDigits(String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? ''))
    }));
  } catch (err) {
    console.error('Erro ao carregar itens da Lista A:', err);
    if (window.Swal) {
      Swal.fire({
        icon: 'error',
        title: 'Erro ao carregar Lista A',
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
      try { extra = ' - ' + (await res.text()).slice(0, 200); } catch(_) {}
      throw new Error(`HTTP ${res.status} - ${res.statusText}${extra}`);
    }

    const result = await res.json();

    // Tenta detectar onde está a lista — aceita vários formatos comuns:
    // exemplo esperado: { nis: [ { Id, Nome, CPFCNPJ }, ... ] }
    let arr = [];
    if (Array.isArray(result))              arr = result;
    else if (Array.isArray(result?.nis))    arr = result.nis;
    else if (Array.isArray(result?.data))   arr = result.data;
    else if (Array.isArray(result?.itens))  arr = result.itens;
    else if (Array.isArray(result?.pessoas))arr = result.pessoas;

    // Normaliza para o formato usado no DnD/paginação
    return arr.map(p => ({
      id:   p.Id   ?? p.id   ?? p.Codigo ?? p.codigo ?? String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? ''),
      nome: p.Nome ?? p.nome ?? p.NomeCompleto ?? p.nomeCompleto ?? '',
      cpf:  formatCpfCnpjFromDigits(String(p.CPFCNPJ ?? p.cpf ?? p.CPF ?? ''))
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

  // ===================== elementos =====================
  const dndWrap   = document.getElementById("dnd-extrato");
  const listA     = document.getElementById("dnd-list-a");
  const listB     = document.getElementById("dnd-list-b");
  const statusBox = document.getElementById("dnd-status");
  const boards    = document.getElementById("dnd-boards");
  const combo     = document.getElementById("dnd-combo-origem");
  const pagerA    = document.getElementById("pager-a");
  const pagerB    = document.getElementById("pager-b");
  const searchA   = document.getElementById("search-a");
  const searchB   = document.getElementById("search-b");
  const pageSizeAEl = document.getElementById("pagesize-a");
  const pageSizeBEl = document.getElementById("pagesize-b");

  // ===================== estado de paginação =====================
  const stateA = { all: [], filtered: [], page: 1, pageSize: parseInt(pageSizeAEl.value,10) || 10, search: '' };
  const stateB = { all: [], filtered: [], page: 1, pageSize: parseInt(pageSizeBEl.value,10) || 10, search: '' };

  // ===================== renderização itens =====================
  function makeItem({ id, nome, cpf }) {
    const li = document.createElement("li");
    li.className = "item";
    li.dataset.id   = id;
    li.dataset.nome = nome || "";
    li.dataset.cpf  = cpf  || "";
    li.innerHTML = `
      <span class="handle" title="Arraste"></span>
      <strong>${nome || "(Sem nome)"}</strong>
      <span class="meta">${cpf || ""}</span>
    `;
    return li;
  }

  function renderListInto(listEl, items) {
    listEl.innerHTML = "";
    items.forEach(obj => listEl.appendChild(makeItem(obj)));
    [...listEl.querySelectorAll(".item")].forEach(attachPointerDnD);
  }

  function updateCounters() {
    // Total (não apenas da página)
    dndWrap.querySelector('[data-counter="dnd-list-a"]').textContent = `${stateA.all.length} item${stateA.all.length===1?'':'s'}`;
    dndWrap.querySelector('[data-counter="dnd-list-b"]').textContent = `${stateB.all.length} item${stateB.all.length===1?'':'s'}`;
  }

  function getPage(items, page, pageSize) {
    const total = items.length;
    const pages = Math.max(1, Math.ceil(total / pageSize));
    const p = Math.min(Math.max(1, page), pages);
    const start = (p - 1) * pageSize;
    return { slice: items.slice(start, start + pageSize), page: p, pages, total };
  }

  function buildPager(el, state, label) {
    const { pageSize } = state;
    const items = (state === stateA ? stateA.filtered : stateB.filtered);
    let { page, pages, total } = getPage(items, state.page, pageSize);

    if (label === "Lista A"){
        label = "Cliente";
    } else {
        label = "Conta Corrente";
    }
    el.innerHTML = `
      <div class="info">${label}: Página ${page} de ${pages} — ${total} registro(s) filtrado(s)</div>
      <div class="actions">
        <button data-act="prev" ${page<=1?'disabled':''}>◀ Anterior</button>
        <button data-act="next" ${page>=pages?'disabled':''}>Próxima ▶</button>
      </div>
    `;

    el.querySelector('[data-act="prev"]').onclick = () => {
      if (state.page > 1) { state.page--; renderAllFor(el===pagerA?'A':'B'); }
    };
    el.querySelector('[data-act="next"]').onclick = () => {
      if (state.page < pages) { state.page++; renderAllFor(el===pagerA?'A':'B'); }
    };
  }

  function renderAllFor(which) {
    if (which === 'A') {
      const pageData = getPage(stateA.filtered, stateA.page, stateA.pageSize);
      stateA.page = pageData.page;
      renderListInto(listA, pageData.slice);
      buildPager(pagerA, stateA, 'Lista A');
    } else if (which === 'B') {
      const pageData = getPage(stateB.filtered, stateB.page, stateB.pageSize);
      stateB.page = pageData.page;
      renderListInto(listB, pageData.slice);
      buildPager(pagerB, stateB, 'Lista B');
    } else {
      renderAllFor('A'); renderAllFor('B');
    }
    updateCounters();
  }

  // ===================== filtros =====================
  function applyFilterA() {
    const q = stateA.search.trim().toLowerCase();
    if (!q) {
      stateA.filtered = [...stateA.all];
    } else {
      stateA.filtered = stateA.all.filter(p => {
        const id = String(p.id||'').toLowerCase();
        const nome = String(p.nome||'').toLowerCase();
        const cpf = String(p.cpf||'').toLowerCase();
        return id.includes(q) || nome.includes(q) || cpf.includes(q);
      });
    }
    stateA.page = 1;
  }

  function applyFilterB() {
    const q = stateB.search.trim().toLowerCase();
    if (!q) {
      stateB.filtered = [...stateB.all];
    } else {
      stateB.filtered = stateB.all.filter(p => {
        const id = String(p.id||'').toLowerCase();
        const nome = String(p.nome||'').toLowerCase();
        const cpf = String(p.cpf||'').toLowerCase();
        return id.includes(q) || nome.includes(q) || cpf.includes(q);
      });
    }
    stateB.page = 1;
  }

  // ===================== carregar dados =====================
  async function renderComboOptions() {
    const opts = await fetchComboOptions();
    combo.innerHTML = `<option value="">Selecione…</option>` + opts.map(o => `<option value="${o.id}">${o.nome}</option>`).join("");

    // Inicializa Select2 (local) com busca no dropdown
    const ready = await ensureSelect2Loaded();
    if (ready) {
      const $ = window.jQuery;
      $(combo).select2({
        placeholder: 'Selecione um Cliente…',
        allowClear: true,
        width: 'resolve',
        dropdownParent: $(dndWrap), // evita problemas de z-index
        minimumResultsForSearch: 0   // sempre mostra campo de busca
        // Para busca remota de departamentos, você pode usar:
        // ajax: {
        //   url: API_URL + 'departamentos',
        //   dataType: 'json',
        //   delay: 250,
        //   data: params => ({ q: params.term }),
        //   processResults: data => ({
        //     results: data.map(d => ({ id: d.id, text: d.nome }))
        //   })
        // }
      }).on('change', () => refreshListAFromCombo());
    }
  }

  async function refreshListAFromCombo() {
    const origemId = combo.value;
    stateA.page = 1;
    stateA.search = (searchA.value || '').trim();

    if (!origemId) {
      stateA.all = []; applyFilterA(); renderAllFor('A'); return;
    }
    listA.innerHTML = `<li class="item"><em>Carregando…</em></li>`;

    // troque por sua API real, se desejar (GET/POST)
    const data = await fetchItemsForCombo(origemId);
    stateA.all = (data || []).map(x => ({ id:x.id, nome:x.nome, cpf:formatCPF(x.cpf) }));
    applyFilterA();
    renderAllFor('A');
  }

  async function loadB() {
    const dataB = await fetchListB();   // troque por sua API
    stateB.all = (dataB || []).map(x => ({ id:x.id, nome:x.nome, cpf:formatCPF(x.cpf) }));
    applyFilterB();
    renderAllFor('B');
  }

  // ===================== DnD + confirmação =====================
  let dragging = null, placeholder = null, ghost = null;
  let startOffset = { x: 0, y: 0 };
  let sourceListId = null;

  function createPlaceholder() { const ph = document.createElement("div"); ph.className = "placeholder"; return ph; }

  function createGhost(el, x, y) {
    const g = el.cloneNode(true);
    g.classList.add("ghost");
    g.style.width = `${el.offsetWidth}px`;
    g.style.height = `${el.offsetHeight}px`;
    dndWrap.appendChild(g);
    moveGhost(g, x, y);
    return g;
  }

  function moveGhost(g, x, y) {
    const wrapRect = dndWrap.getBoundingClientRect();
    const relX = x - wrapRect.left + window.scrollX + dndWrap.scrollLeft;
    const relY = y - wrapRect.top  + window.scrollY + dndWrap.scrollTop;
    g.style.left = (relX - startOffset.x) + 'px';
    g.style.top  = (relY - startOffset.y) + 'px';
  }

  function getListUnderPoint(x, y) { const el = document.elementFromPoint(x, y); return el ? el.closest(".list") : null; }

  function getAfterElement(list, y) {
    const items = [...list.querySelectorAll(".item:not(.dragging)")];
    let closest = { offset: Number.NEGATIVE_INFINITY, element: null };
    for (const child of items) {
      const rect = child.getBoundingClientRect();
      const offset = y - (rect.top + rect.height / 2);
      if (offset < 0 && offset > closest.offset) closest = { offset, element: child };
    }
    return closest.element;
  }

  function getListLabel(id) { return id === "dnd-list-a" ? "Lista A" : id === "dnd-list-b" ? "Lista B" : id; }

  async function confirmMove(fromId, toId, li) {
    const id   = li.dataset.id || "";
    const nome = li.dataset.nome || "";
    const cpf  = li.dataset.cpf  || "";
    const from = getListLabel(fromId);
    const to   = getListLabel(toId);
    let pode;
    pode = true
    if (from === "Lista B"){
        pode = await verificaB2A(cpf);
    }
    
    if (from === "Lista A"){
        pode = await verificaA2B(cpf);
    }


    if (cpf === ''){
        Swal.fire({
            icon: "error",
            title: "Oops...",
            draggable: true,
            html: "Não é possivel fazer a movimentação, pois o item não possui CPF.<br/>Tente fazer uma <b>Conciliação Direta</b>"
        });

      pode = false;
    }

    if (pode){
        return Swal.fire({
        icon: 'question',
        title: 'Confirmar movimentação?',
        html: `
            <div style="text-align:left">
            <p><strong>ID:</strong> ${id}</p>
            <p><strong>Item:</strong> ${nome}</p>
            <p><strong>CPF:</strong> ${cpf}</p>
            <p><strong>De:</strong> ${from} &nbsp;&nbsp; <strong>Para:</strong> ${to}</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sim, mover',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true
        }).then(res => res.isConfirmed);
    }else {
        return pode;
    }
  }

function onlyDigits(v)
{
    return v.replace(/\D/g, '');
}


async function verificaA2B(cpf) {
    
    cpf = onlyDigits(cpf);
    
    const payLoad = {
        theCPF: cpf
    };

    let endPoint = API_URL + "extratos/pode-a-2-b";
    
    try {
        // 2. Use 'await' para esperar a resposta do fetch
        const response = await fetch(endPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },  
            body: JSON.stringify(payLoad)
        });

        if (!response.ok) {
            // Se a requisição HTTP falhar (ex: erro 404, 500)
            throw new Error('Erro na requisição: ' + response.status);
        }

        // 3. Use 'await' para esperar a conversão para JSON
        const data = await response.json();

        if (data.status !== "ok") {
            // Se o status retornado pela API for "nok"
            Swal.fire({
                icon: "error",
                title: "Oops...",
                draggable: true,
                html: data.mensagem
            });
            // 4. Retorna FALSE, que é o valor final da função
            return false;
        } else {
            // Se o status retornado pela API for "ok"
            // 4. Retorna TRUE, que é o valor final da função
            return true;
        }
    } catch (error) {
        // Trata erros de rede ou o erro lançado acima
        console.error("Erro em verificaB2A:", error);
        
        // Opcional: mostrar um erro genérico para o usuário
        Swal.fire({
            icon: "error",
            title: "Erro de Comunicação",
            html: "Não foi possível verificar a situação do CPF. Tente novamente."
        });
        
        // Retorna FALSE em caso de qualquer falha
        return false; 
    }
}

async function verificaB2A(cpf) {
    
    cpf = onlyDigits(cpf);
    
    const payLoad = {
        theCPF: cpf
    };

    let endPoint = API_URL + "extratos/pode-b-2-a";
    
    try {
        // 2. Use 'await' para esperar a resposta do fetch
        const response = await fetch(endPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payLoad)
        });

        if (!response.ok) {
            // Se a requisição HTTP falhar (ex: erro 404, 500)
            throw new Error('Erro na requisição: ' + response.status);
        }

        // 3. Use 'await' para esperar a conversão para JSON
        const data = await response.json();

        if (data.status !== "ok") {
            // Se o status retornado pela API for "nok"
            Swal.fire({
                icon: "error",
                title: "Oops...",
                draggable: true,
                html: data.mensagem
            });
            // 4. Retorna FALSE, que é o valor final da função
            return false;
        } else {
            // Se o status retornado pela API for "ok"
            // 4. Retorna TRUE, que é o valor final da função
            return true;
        }

    } catch (error) {
        // Trata erros de rede ou o erro lançado acima
        console.error("Erro em verificaB2A:", error);
        
        // Opcional: mostrar um erro genérico para o usuário
        Swal.fire({
            icon: "error",
            title: "Erro de Comunicação",
            html: "Não foi possível verificar a situação do CPF. Tente novamente."
        });
        
        // Retorna FALSE em caso de qualquer falha
        return false; 
    }
}

async function insertA2B(cpf){
    cpf = onlyDigits(cpf);

    const payLoad = {
        theCPF: cpf
    };

    let endPoint = API_URL + "extratos/insert-a-2-b";

    try{
        const response = await fetch(endPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },  
            body: JSON.stringify(payLoad)
        });

        if (!response.ok) {
            // Se a requisição HTTP falhar (ex: erro 404, 500)
            throw new Error('Erro na requisição: ' + response.status);
        }

        const data = await response.json();

        if (data.status !== "ok") {
            // Se o status retornado pela API for "nok"
            Swal.fire({
                icon: "error",
                title: "Oops...",
                draggable: true,
                html: data.mensagem
            });

            // 4. Retorna FALSE, que é o valor final da função
            return false;
        } else {
            // Se o status retornado pela API for "ok"
            // 4. Retorna TRUE, que é o valor final da função
            return true;
        }
    } catch (error) {
        // Trata erros de rede ou o erro lançado acima
        console.error("Erro em verificaB2A:", error);
    
        // Opcional: mostrar um erro genérico para o usuário
        Swal.fire({
            icon: "error",
            title: "Erro de Comunicação",
            html: "Não foi possível verificar a situação do CPF. Tente novamente."
        });
        
        // Retorna FALSE em caso de qualquer falha
        return false; 
    }
}

async function insertB2A(cpf, nome){
    cpf = onlyDigits(cpf);
    let Id = $('#dnd-combo-origem').val();
    let Nome = nome;
    const payLoad = {
        IdCliente: Id,
        NomeCC: nome,
        theCPF: cpf
    };

    let endPoint = API_URL + "extratos/insert-b-2-a";

    try{
        const response = await fetch(endPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },  
            body: JSON.stringify(payLoad)
        });

        if (!response.ok) {
            // Se a requisição HTTP falhar (ex: erro 404, 500)
            throw new Error('Erro na requisição: ' + response.status);
        }

        const data = await response.json();

        if (data.status !== "ok") {
            // Se o status retornado pela API for "nok"
            Swal.fire({
                icon: "error",
                title: "Oops...",
                draggable: true,
                html: data.mensagem
            });

            // 4. Retorna FALSE, que é o valor final da função
            return false;
        } else {
            // Se o status retornado pela API for "ok"
            // 4. Retorna TRUE, que é o valor final da função
            return true;
        }
    } catch (error) {
        // Trata erros de rede ou o erro lançado acima
        console.error("Erro em verificaB2A:", error);
    
        // Opcional: mostrar um erro genérico para o usuário
        Swal.fire({
            icon: "error",
            title: "Erro de Comunicação",
            html: "Não foi possível verificar a situação do CPF. Tente novamente."
        });
        
        // Retorna FALSE em caso de qualquer falha
        return false; 
    }
}


  function onPointerMove(e) {
    if (!dragging) return;
    const clientX = e.clientX ?? (e.touches && e.touches[0]?.clientX);
    const clientY = e.clientY ?? (e.touches && e.touches[0]?.clientY);
    if (clientX == null || clientY == null) return;

    if (ghost) moveGhost(ghost, clientX, clientY);
    const list = getListUnderPoint(clientX, clientY);
    dndWrap.querySelectorAll(".list").forEach(l => l.classList.remove("dragover", "alt"));

    if (list) {
      list.classList.add("dragover");
      if (list.id === "dnd-list-b") list.classList.add("alt");
      const after = getAfterElement(list, clientY);
      if (!placeholder.parentElement || placeholder.parentElement !== list) list.appendChild(placeholder);
      if (after) list.insertBefore(placeholder, after); else list.appendChild(placeholder);
    }
    e.preventDefault();
  }

  async function onPointerUp(e) {
    if (!dragging) return;
    const cpf = dragging.dataset.cpf;
    const nome =  dragging.dataset.nome;

    document.removeEventListener("pointermove", onPointerMove, { passive:false });
    document.removeEventListener("pointerup", onPointerUp);

    const targetList = placeholder.parentElement?.classList.contains("list") ? placeholder.parentElement : null;
    const toId   = targetList?.id || null;
    const fromId = sourceListId;

    const crossAB =
      fromId && toId && fromId !== toId &&
      ((fromId === "dnd-list-a" && toId === "dnd-list-b") || (fromId === "dnd-list-b" && toId === "dnd-list-a"));

    let podeMover = true;
    if (crossAB) podeMover = await confirmMove(fromId, toId, dragging);

    if (podeMover && crossAB) {
      const obj = { id: dragging.dataset.id, nome: dragging.dataset.nome, cpf: dragging.dataset.cpf };

      if (fromId === "dnd-list-b" && toId === "dnd-list-a") {
        result = await insertB2A(cpf, nome);
        if (result)
        {
            // Remove de B / adiciona em A
            stateB.all = stateB.all.filter(x => String(x.id) !== String(obj.id));
            applyFilterB();

            stateA.all.push(obj);
            applyFilterA();

            // Ajusta páginas
            const { pages: pagesB } = getPage(stateB.filtered, stateB.page, stateB.pageSize);
        
            if (stateB.page > pagesB) stateB.page = pagesB;
        }

      } else if (fromId === "dnd-list-a" && toId === "dnd-list-b") {
        
        result = await insertA2B(cpf);

        if (result)
        {
            stateA.all = stateA.all.filter(x => String(x.id) !== String(obj.id));
            applyFilterA();

            stateB.all.push(obj);
            applyFilterB();

            const { pages: pagesA } = getPage(stateA.filtered, stateA.page, stateA.pageSize);
        
            if (stateA.page > pagesA) stateA.page = pagesA;
        }
      }
      renderAllFor();
    } else {
      // Cancela: re-renderiza a lista de origem para limpar placeholder
      if (fromId === "dnd-list-a") renderAllFor('A'); else if (fromId === "dnd-list-b") renderAllFor('B');
    }

    dragging?.classList.remove("dragging");
    placeholder?.remove();
    ghost?.remove();
    dragging = ghost = placeholder = null;
    sourceListId = null;

    dndWrap.querySelectorAll(".list").forEach(l => l.classList.remove("dragover", "alt"));
    updateCounters();
  }

  function attachPointerDnD(item) {
    if (item._dndBound) return;
    item._dndBound = true;

    item.addEventListener("pointerdown", (e) => {
      if (e.button !== 0 && e.pointerType === "mouse") return;

      dragging = item;
      sourceListId = item.parentElement?.id || null;
      dragging.classList.add("dragging");

      const rect = item.getBoundingClientRect();
      startOffset.x = e.clientX - rect.left;
      startOffset.y = e.clientY - rect.top;

      placeholder = createPlaceholder();
      item.parentElement.insertBefore(placeholder, item.nextSibling);

      ghost = createGhost(item, e.clientX, e.clientY);
      item.setPointerCapture?.(e.pointerId);

      document.addEventListener("pointermove", onPointerMove, { passive:false });
      document.addEventListener("pointerup", onPointerUp, { passive:false });

      e.preventDefault();
    }, { passive:false });
  }

  // ===================== inicialização =====================
  async function initDndExtrato() {
    try {
      statusBox.textContent = "Carregando dados…";
      boards.style.display = "none";

      await renderComboOptions();   // carrega combo + select2
      await refreshListAFromCombo();
      await loadB();

      statusBox.textContent = "";
      boards.style.display = "";
    } catch (err) {
      console.error(err);
      statusBox.textContent = "Erro ao carregar dados.";
    }
  }

function formatCpfCnpjFromDigits(digs) {
    if (digs.length <= 11) 
    {
        // CPF: 000.000.000-00
        return digs
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
    } 
    else 
    {
        // CNPJ: 00.000.000/0000-00
        return digs
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
        .replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
    }
}

  // Eventos UI
  pageSizeAEl.addEventListener("change", () => { stateA.pageSize = parseInt(pageSizeAEl.value,10)||10; stateA.page = 1; renderAllFor('A'); });
  pageSizeBEl.addEventListener("change", () => { stateB.pageSize = parseInt(pageSizeBEl.value,10)||10; stateB.page = 1; renderAllFor('B'); });

  searchA.addEventListener("input", debounce(() => {
    stateA.search = searchA.value || '';
    applyFilterA();
    renderAllFor('A');
  }, 250));

  searchB.addEventListener("input", debounce(() => {
    stateB.search = searchB.value || '';
    applyFilterB();
    renderAllFor('B');
  }, 250));

  initDndExtrato();