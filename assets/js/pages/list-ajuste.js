let table;

// Estado da linha/modal selecionado
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
        rowId: function (row) {
            return `cr_${row.Id}`;
        },
        columns: [
            { data: 'Nome' },
            {
                data: 'DtInicio',
                render: function (data) {
                    return formatarData(data);
                }
            },
            {
                data: 'DtFinal',
                render: function (data) {
                    return formatarData(data);
                }
            },
            {
                data: 'VlTotal',
                render: function (data, type, row) {
                    const texto = formatarDecimal(data);
                    if (type !== 'display') return texto;
                    return `<a href="#" class="lnk-ajustar-vltotal" data-id="${row.Id}">${texto}</a>`;
                }
            },
            {
                data: 'VlConciliado',
                render: function (data, type, row) {
                    const texto = formatarDecimal(data);
                    if (type !== 'display') return texto;
                    return `<a href="#" class="lnk-ajustar-vlconciliado" data-id="${row.Id}">${texto}</a>`;
                }
            },
            {
                data: 'ObservacaoAjuste',
                defaultContent: '',
                render: function (data, type, row) {
                    const observacao = data || row.Observacao || row.ObsAjuste || '';
                    return observacao;
                }
            },
            {
                data: 'DtCC',
                render: function (data) {
                    return formatarDataHora(data);
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        }
    });

    if (table.buttons) {
        table.buttons().container().appendTo('#dtCR_wrapper .col-md-6:eq(0)');
    }

    $('#cmdPesquisarPorData').on('click', function () {
        carregarGridAjuste();
    });

    // Clique no link do Valor C.R.
    $('#dtCR tbody').on('click', 'a.lnk-ajustar-vltotal', function (e) {
        e.preventDefault();

        rowSelecionada = table.row($(this).closest('tr'));
        dataSelecionada = rowSelecionada.data();

        if (!dataSelecionada || !dataSelecionada.Id) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível identificar o registro.'
            });
            return;
        }

        const atual = Number(dataSelecionada.VlTotal ?? 0);
        $('#txtNovoVlTotal').val(formatarDecimal(atual));

        const modalEl = document.getElementById('modalAjusteVlTotal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        setTimeout(() => $('#txtNovoVlTotal').trigger('focus'), 150);
    });

    // Clique no link do Valor Conciliado
    $('#dtCR tbody').on('click', 'a.lnk-ajustar-vlconciliado', function (e) {
        e.preventDefault();

        rowSelecionada = table.row($(this).closest('tr'));
        dataSelecionada = rowSelecionada.data();

        if (!dataSelecionada || !dataSelecionada.Id) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível identificar o registro.'
            });
            return;
        }

        const atual = Number(dataSelecionada.VlConciliado ?? 0);
        const observacaoAtual =
            dataSelecionada.ObservacaoAjuste ||
            dataSelecionada.Observacao ||
            dataSelecionada.ObsAjuste ||
            '';

        $('#txtNovoVlConciliado').val(formatarDecimal(atual));
        $('#hdnVlConciliadoAtual').val(atual);
        $('#txtObsAjusteConciliado').val(observacaoAtual);
        $('#txtSenhaAjusteConciliado').val('');

        const modalEl = document.getElementById('modalAjusteVlConciliado');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        setTimeout(() => $('#txtNovoVlConciliado').trigger('focus'), 150);
    });

    // Máscara Valor C.R.
    $('#txtNovoVlTotal').on('input', function () {
        $(this).val(maskMoedaBR($(this).val()));
    });

    // Máscara Valor Conciliado
    $('#txtNovoVlConciliado').on('input', function () {
        $(this).val(maskMoedaBR($(this).val()));
    });

    // Salvar Valor C.R.
    $('#cmdSalvarVlTotal').on('click', async function () {
        await salvarVlTotal();
    });

    // Salvar Valor Conciliado
    $('#cmdSalvarVlConciliado').on('click', async function () {
        await salvarVlConciliado();
    });

    // Enter no modal Valor C.R.
    $('#txtNovoVlTotal').on('keydown', async function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            await salvarVlTotal();
        }
    });

    // Enter no modal Valor Conciliado
    $('#txtNovoVlConciliado, #txtObsAjusteConciliado, #txtSenhaAjusteConciliado').on('keydown', async function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            await salvarVlConciliado();
        }
    });

    carregarCliente();
    preencherDatasDoMesCorrente();
});

async function salvarVlTotal() {
    if (!rowSelecionada || !dataSelecionada) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Nenhuma linha selecionada.'
        });
        return;
    }

    const id = dataSelecionada.Id;
    const valorTexto = String($('#txtNovoVlTotal').val() || '').trim();
    const valorNumerico = brToNumber(valorTexto);

    if (Number.isNaN(valorNumerico)) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe um valor válido.'
        });
        return;
    }

    if (valorNumerico < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'O valor não pode ser negativo.'
        });
        return;
    }

    const endPoint = API_URL + "contasreceber/atualizar-vltotal";

    try {
        $('#cmdSalvarVlTotal').prop('disabled', true);

        const response = await fetch(endPoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                Id: id,
                VlTotal: valorNumerico
            })
        });

        let result = null;
        try {
            result = await response.json();
        } catch {
        }

        if (!response.ok) {
            const msg = (result && (result.mensagem || result.message))
                ? (result.mensagem || result.message)
                : `HTTP ${response.status}`;
            throw new Error(msg);
        }

        const ok = !result || result.status === 'ok' || result.success === true;
        if (!ok) {
            const msg = (result && (result.mensagem || result.message))
                ? (result.mensagem || result.message)
                : 'A API retornou erro.';
            throw new Error(msg);
        }

        const novo = {
            ...dataSelecionada,
            VlTotal: valorNumerico
        };

        rowSelecionada.data(novo).invalidate().draw(false);

        const modalEl = document.getElementById('modalAjusteVlTotal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: 'Valor atualizado.'
        });

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: err.message || 'Falha ao atualizar valor.'
        });
    } finally {
        $('#cmdSalvarVlTotal').prop('disabled', false);
    }
}

async function salvarVlConciliado() {
    if (!rowSelecionada || !dataSelecionada) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Nenhuma linha selecionada.'
        });
        return;
    }

    const id = dataSelecionada.Id;
    const userId = USERID;
    const valorTexto = String($('#txtNovoVlConciliado').val() || '').trim();
    const valorAnterior = String($('#hdnVlConciliadoAtual').val() || '').trim();
    const descricao = String($('#txtObsAjusteConciliado').val() || '').trim();
    const senha = String($('#txtSenhaAjusteConciliado').val() || '').trim();
    const valorNumerico = brToNumber(valorTexto);

    if (Number.isNaN(valorNumerico)) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Informe um valor conciliado válido.'
        });
        return;
    }

    const endPoint = API_URL + "contasreceber/atualizar-vlconciliado";

    try {
        $('#cmdSalvarVlConciliado').prop('disabled', true);

        const payload = {
            Id: id,
            UserId: userId,
            VlConciliado: valorNumerico,
            VlAnterior: brToNumber(valorAnterior),
            ObservacaoAjuste: descricao,
            Senha: senha
        };

        console.log('Endpoint:', endPoint);
        console.log('Payload:', payload);

        const response = await fetch(endPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        let result = null;
        try {
            result = await response.json();
        } catch { }

        if (!response.ok) {
            throw new Error((result && (result.mensagem || result.message)) || `HTTP ${response.status}`);
        }

        if (result && result.status !== 'ok' && result.success !== true) {
            throw new Error(result.mensagem || result.message || 'A API retornou erro.');
        }

        const novo = {
            ...dataSelecionada,
            VlConciliado: valorNumerico,
            ObservacaoAjuste: descricao
        };

        rowSelecionada.data(novo).invalidate().draw(false);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAjusteVlConciliado')).hide();

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: 'Valor conciliado atualizado.'
        });

    } catch (err) {
        console.error('Erro no fetch:', err);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: err.message || 'Falha ao atualizar valor conciliado.'
        });
    } finally {
        $('#cmdSalvarVlConciliado').prop('disabled', false);
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
                    width: $select.data('width')
                        ? $select.data('width')
                        : $select.hasClass('w-100')
                            ? '100%'
                            : 'style',
                    placeholder: $select.data('placeholder')
                }).val("0").trigger('change');

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

    const idParaEnviar = (ClientId === '' || ClientId === '0' || ClientId === null)
        ? null
        : ClientId;

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

// ------------------ Helpers ------------------

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
    let digits = String(valor || '').replace(/\D/g, '');
    digits = digits.replace(/-/g, '');

    if (digits.length === 0) return '0,00';

    const number = parseInt(digits, 10) / 100;

    if (isNaN(number) || number < 0) return '0,00';

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

function brToNumber(valorBR) {
    if (valorBR === null || valorBR === undefined) return NaN;

    const s = String(valorBR)
        .replace(/\s/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    if (s.includes('-')) return NaN;

    return parseFloat(s);
}