$(document).ready(function () {

    carregarCliente();
    carregarBanco();

    let acao = "";
    acao = document.getElementById("hdnAcao").value;
    acao = acao - 0;

    if (acao === 2) {
        getClienteccById(document.getElementById("hdnClienteCCId").value)
    }
});

function carregarCliente() {
    let endPoint = "";
    endPoint = API_URL + "cliente/listar";

    fetch(endPoint)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const $select = $('#cboCliente');

                $select.empty();
                data.clientes.forEach(cliente => {
                    $select.append(new Option(
                        cliente.Nome,
                        cliente.Id
                    ));
                });

                $select.select2({
                    theme: "bootstrap-5",
                    width: $select.data('width') ? $select.data('width') : $select.hasClass('w-100') ? '100%' : 'style',
                    placeholder: $select.data('placeholder'),
                    closeOnSelect: false,
                });
            } else {
                console.error('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao chamar a API:', error);
        });
}

function carregarBanco() {
    let endPoint = "";
    endPoint = API_URL + "banco/listar";

    fetch(endPoint)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const $select = $('#cboBanco');

                $select.empty();
                data.bancos.forEach(banco => {
                    $select.append(new Option(
                        banco.Nome,
                        banco.Id
                    ));
                });

                $select.select2({
                    theme: "bootstrap-5",
                    width: $select.data('width') ? $select.data('width') : $select.hasClass('w-100') ? '100%' : 'style',
                    placeholder: $select.data('placeholder'),
                    closeOnSelect: false,
                });
            } else {
                console.error('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao chamar a API:', error);
        });
}

document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    if (!validaForm()) {
        return;
    }
    let acao = document.getElementById('hdnAcao').value;
    acao = acao - 0;

    const payLoad = {
        Id: document.getElementById('hdnClienteCCId').value,
        IdCliente: document.getElementById('cboCliente').value,
        NomeCC: document.getElementById('txtNomeCC').value,
        IdBanco: document.getElementById('cboBanco').value,
        Agencia: document.getElementById('txtAgencia').value,
        CC: document.getElementById('txtCC').value,
        CPFCNPJ: document.getElementById("txtCPFCNPJ").value,
        TipoChavePix: document.getElementById('cboTipoChavePix').value,
        ChavePix: document.getElementById('txtChavePix').value,
        Ativo: document.getElementById('chkAtivo').checked ? 1 : 0 // true/false para 1/0
    };

    let endPoint = "";
    switch (acao) {
        case 1:
            endPoint = "incluir";
            break;
        case 2:
            endPoint = "alterar";
            break;
        default:
            endPoint = "excluir/excluir.php";
            break;
    }
    endPoint = API_URL + "clientecc/" + endPoint;

    // Envia via POST usando fetch
    fetch(endPoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payLoad)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === "ok") {
                Swal.fire({
                    title: "Parabens",
                    draggable: true,
                    html: data.mensagem,
                    confirmButtonText: "OK"
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        window.location.href = `${HOST}${APP_HOST}list-cc.php`;
                        return;
                    }
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    draggable: true,
                    html: data.mensagem
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar os dados.');
        });
});

function validaForm() {
    let mensagem = ""
    if (document.getElementById('txtNomeCC').value.trim() === "") {
        mensagem += "Nome da CC Obrigatório<br/>";
    }
    if (document.getElementById('txtAgencia').value.trim() === "") {
        mensagem += "Agência Obrigatória<br/>";
    }
    if (document.getElementById('txtCC').value.trim() === "") {
        mensagem += "Conta Corrente Obrigatória<br/>";
    }
    if (document.getElementById('txtCPFCNPJ').value.trim() === "") {
        mensagem += "CPF/CNPJ Obrigatória<br/>";
    }
    if (document.getElementById('txtCPFCNPJ').value.length < 14) {
        mensagem += "CPF /CNPJ Invalido<br/>";
    }
    if (document.getElementById('txtCPFCNPJ').value.length > 14 &&
        document.getElementById('txtCPFCNPJ').value.length < 18) {
        mensagem += "CPF /CNPJ Invalido<br/>";
    }

    let tipochavepixvalue = document.getElementById('cboTipoChavePix').value;
    tipochavepixvalue = tipochavepixvalue - 0;

    if (tipochavepixvalue > 0) {
        if (document.getElementById('txtChavePix').value.trim() === "") {
            mensagem += "Tipo ChavePix Obrigatório<br/>";
        }
    } else {
        if (document.getElementById('txtChavePix').value.trim() !== "") {
            mensagem += "Escolher o Tipo de Chave Pix<br/>";
        }
    }


    if (mensagem.trim() !== "") {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            draggable: true,
            html: mensagem
        });

        return false;
    }

    return true;
}

function getClienteccById(clienteId) {
    const payLoad = {
        clienteId: clienteId
    };

    endPoint = API_URL + "clientecc/getclienteccbyid";
    // Envia via POST usando fetch
    fetch(endPoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payLoad)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === "ok") {

                $('#hdnClienteCCId').val(data.Id);
                $('#txtNomeCC').val(data.NomeCC);
                $('#txtAgencia').val(data.Agencia);
                $('#txtCC').val(data.CC);
                $('#txtCPFCNPJ').val(formatarDocumento(data.CPFCNPJ))
                $('#cboTipoChavePix option').filter(function () {
                    return $(this).text() === data.TipoChavePix;
                }).prop('selected', true);
                //$('#cboTipoChavePix').val(data.TipoChavePix);
                $('#txtChavePix').val(data.ChavePix);
                if (data.Ativo == 1) {
                    $('#chkAtivo').prop('checked', true);
                } else {
                    $('#chkAtivo').prop('checked', false);
                }

                setTimeout(function () {
                    $('#cboCliente').val(data.IdCliente).trigger('change');
                    $('#cboBanco').val(data.IdBanco).trigger('change');
                }, 500);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    draggable: true,
                    html: data.mensagem
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar os dados.');
        });
}

function formatarDocumento(documento) {
    // 1. Limpa a string: remove todos os caracteres não-numéricos
    const numLimpo = String(documento).replace(/\D/g, '');
    const tamanho = numLimpo.length;

    // 2. Detecta o tipo de documento pelo tamanho e aplica a máscara

    if (tamanho === 11) {
        // É um CPF: 999.999.999-99
        // Captura: $1 (3 digitos), $2 (3 digitos), $3 (3 digitos), $4 (2 digitos)
        return numLimpo.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    } else if (tamanho === 14) {
        // É um CNPJ: 99.999.999/9999-99
        // Captura: $1 (2), $2 (3), $3 (3), $4 (4), $5 (2)
        return numLimpo.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    } else {
        // Não é um CPF nem CNPJ válido
        return documento;
    }
}