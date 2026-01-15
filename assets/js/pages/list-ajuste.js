let table;

// Estado do modal/linha selecionada
let rowSelecionada = null;
let dataSelecionada = null;

$(document).ready(function () {

    table = $('#dtCR').DataTable({
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        data: [],
        // Guardamos o Id no DataTable para facilitar atualizações
        rowId: function (row) { return `cr_${row.Id}`; },
        columns: [
            { data: 'Nome' },
            {
                data: 'DtInicio',
                render: function (data) { return formatarData(data); }
            },
            {
                data: 'DtFinal',
                render: function (data) { return formatarData(data); }
            },
            {
                data: 'VlTotal',
                render: function (data, type, row) {
                    // Exibe como link clicável (abre modal)
                    const texto = formatarDecimal(data);
                    // "type" pode ser 'display' ou 'filter' etc. Mantemos string limpa para exportar.
                    if (type !== 'display') return texto;
                    return `<a href="#" class="lnk-ajustar-vltotal" data-id="${row.Id}">${texto}</a>`;
                }
            },
            {
                data: 'VlConciliado',
                render: function (data) { return formatarDecimal(data); }
            },
            {
                data: 'DtCC',
                render: function (data) { return formatarDataHora(data); }
            }
        ]
    });

    table.buttons().container().appendTo('#dtCR_wrapper .col-md-6:eq(0)');

    $('#cmdPesquisarPorData').on('click', function () {
        carregarGridAjuste();
    });

    // Clique no link do valor
    $('#dtCR tbody').on('click', 'a.lnk-ajustar-vltotal', function (e) {
        e.preventDefault();

        rowSelecionada = table.row($(this).closest('tr'));
        dataSelecionada = rowSelecionada.data();

        if (!dataSelecionada || !dataSelecionada.Id) {
            Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível identificar o registro.' });
            return;
        }

        // Preenche o input com valor atual
        const atual = Number(dataSelecionada.VlTotal ?? 0);
        $('#txtNovoVlTotal').val(formatarDecimal(atual));

        // Foca e abre modal
        const modalEl = document.getElementById('modalAjusteVlTotal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        setTimeout(() => $('#txtNovoVlTotal').trigger('focus'), 150);
    });

    // Máscara BR: somente números, sempre em centavos, sem negativo
    $('#txtNovoVlTotal').on('input', function () {
        const masked = maskMoedaBR($(this).val());
        $(this).val(masked);
    });

    // Salvar
    $('#cmdSalvarVlTotal').on('click', async function () {
        await salvarVlTotal();
    });

    // Enter no input salva
    $('#txtNovoVlTotal').on('keydown', async function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            await salvarVlTotal();
        }
    });

    carregarCliente();
    preencherDatasDoMesCorrente();
});

async function salvarVlTotal() {
    if (!rowSelecionada || !dataSelecionada) {
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Nenhuma linha selecionada.' });
        return;
    }

    const id = dataSelecionada.Id;
    const valorTexto = String($('#txtNovoVlTotal').val() || '').trim();
    const valorNumerico = brToNumber(valorTexto);

    if (Number.isNaN(valorNumerico)) {
        Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Informe um valor válido.' });
        return;
    }

    if (valorNumerico < 0) {
        Swal.fire({ icon: 'warning', title: 'Atenção', text: 'O valor não pode ser negativo.' });
        return;
    }

    // Se quiser bloquear valor vazio (0,00), descomente:
    // if (valorNumerico === 0) { ... }

    const endPoint = API_URL + "contasreceber/atualizar-vltotal"; // <- ajuste aqui se sua API usar outro caminho

    try {
        $('#cmdSalvarVlTotal').prop('disabled', true);

        const response = await fetch(endPoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ Id: id, VlTotal: valorNumerico })
        });

        // tenta interpretar como JSON; se falhar, joga erro
        let result = null;
        try { result = await response.json(); } catch { /* ignore */ }

        if (!response.ok) {
            const msg = (result && (result.mensagem || result.message)) ? (result.mensagem || result.message) : `HTTP ${response.status}`;
            throw new Error(msg);
        }

        // Considera ok se:
        // - result.status === 'ok'
        // - ou result.success === true
        // - ou não veio payload (algumas APIs respondem 204)
        const ok = !result || result.status === 'ok' || result.success === true;
        if (!ok) {
            const msg = (result && (result.mensagem || result.message)) ? (result.mensagem || result.message) : 'A API retornou erro.';
            throw new Error(msg);
        }

        // ✅ Atualiza imediatamente a linha na tela
        const novo = { ...dataSelecionada, VlTotal: valorNumerico };
        rowSelecionada.data(novo).invalidate().draw(false);

        // Fecha modal
        const modalEl = document.getElementById('modalAjusteVlTotal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();

        Swal.fire({ icon: 'success', title: 'Sucesso', text: 'Valor atualizado.' });

    } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Erro', text: err.message || 'Falha ao atualizar valor.' });
    } finally {
        $('#cmdSalvarVlTotal').prop('disabled', false);
    }
}

function carregarCliente() {
    let endPoint = API_URL + "cliente/listar";

    fetch(endPoint)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const $select = $('#cboCliente');
                $select.empty();
                $select.append(new Option("TODOS", "0"));

                data.clientes.forEach(cliente => {
                    $select.append(new Option(cliente.Nome, cliente.Id));
                });

                $select.select2({
                    theme: "bootstrap-5",
                    width: $select.data('width') ? $select.data('width') : $select.hasClass('w-100') ? '100%' : 'style',
                    placeholder: $select.data('placeholder')
                }).val("0").trigger('change');

                // setTimeout(function () {
                //     carregarGridAjuste();
                // }, 300);

            } else {
                console.error('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao chamar a API:', error);
        });
}

function carregarGridAjuste() {

    let ClientId = $('#cboCliente').val();
    let DtInicio = getLastDateOfMonth($('#txtDtInicial').val());
    let DtFinal = getLastDateOfMonth($('#txtDtFinal').val());

    const idParaEnviar = (ClientId === '' || ClientId === '0' || ClientId === null) ? null : ClientId;

    const payload = {
        Id: idParaEnviar,
        dtInicial: DtInicio,
        dtFinal: DtFinal
    };

    let endPoint = API_URL + "contasreceber/listar";

    fetch(endPoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP status ' + response.status);
            }
            return response.json();
        })
        .then(response => {
            if (response.status === 'ok') {
                // Esperado: response.crs = [{Id, Nome, DtInicio, DtFinal, VlTotal, VlConciliado, DtCC, ...}]
                table.clear().rows.add(response.crs || []).draw();
            } else {
                table.clear().draw();
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    draggable: true,
                    html: response.mensagem
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            Swal.fire({
                icon: "error",
                title: "Erro",
                draggable: true,
                text: "Erro ao conectar com a API ou requisição falhou: " + error.message
            });
        });
}

// ------------------ Helpers (mesmos do list-cr) ------------------

function formatarData(dataString) {
    if (!dataString) return '';
    const data = new Date(dataString + 'T00:00:00');
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    return `${dia}/${mes}/${ano}`;
}

function formatarDataHora(dataHoraString) {
    if (!dataHoraString) return '';
    const data = new Date(dataHoraString);
    const dia = String(data.getUTCDate()).padStart(2, '0');
    const mes = String(data.getUTCMonth() + 1).padStart(2, '0');
    const ano = data.getUTCFullYear();
    const horas = String(data.getUTCHours()).padStart(2, '0');
    const minutos = String(data.getUTCMinutes()).padStart(2, '0');
    const segundos = String(data.getUTCSeconds()).padStart(2, '0');
    return `${dia}/${mes}/${ano} ${horas}:${minutos}:${segundos}`;
}

function formatarDecimal(valor) {
    if (valor === null || valor === undefined || isNaN(valor)) {
        return '';
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(valor);
}

function preencherDatasDoMesCorrente() {
    const hoje = new Date();
    const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

    const dataInicialFormatada = formatarParaInputDate(primeiroDia);
    const dataFinalFormatada = formatarParaInputDate(ultimoDia);

    $('#txtDtInicial').val(dataInicialFormatada);
    $('#txtDtFinal').val(dataFinalFormatada);
}

function formatarParaInputDate(dateObj) {
    const ano = dateObj.getFullYear();
    const mes = String(dateObj.getMonth() + 1).padStart(2, '0');
    const dia = String(dateObj.getDate()).padStart(2, '0');
    return `${ano}-${mes}-${dia}`;
}

function getLastDateOfMonth(e) {
    let aE = (e || '').split("-");
    if (aE.length < 2) return '';

    let year = parseInt(aE[0], 10);
    let month = parseInt(aE[1], 10) - 1;
    let day = new Date(year, month + 1, 0).getDate();

    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

// ------------------ Máscara BR e conversão ------------------

function maskMoedaBR(valor) {
    // Mantém somente dígitos
    let digits = String(valor || '').replace(/\D/g, '');

    // Remove qualquer tentativa de negativo
    digits = digits.replace(/-/g, '');

    if (digits.length === 0) return '0,00';

    // Trata como centavos
    const number = parseInt(digits, 10) / 100;

    if (isNaN(number) || number < 0) return '0,00';

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

function brToNumber(valorBR) {
    // Ex.: "1.234,56" => 1234.56
    if (valorBR === null || valorBR === undefined) return NaN;

    const s = String(valorBR)
        .replace(/\s/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    // Evita negativo
    if (s.includes('-')) return NaN;

    const n = parseFloat(s);
    return n;
}
