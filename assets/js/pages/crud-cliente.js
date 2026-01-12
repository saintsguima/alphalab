document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    if (!validaForm()) {
        return;
    }
    let acao = document.getElementById('hdnAcao').value;
    acao = acao - 0;

    const payLoad = {
        clienteid: document.getElementById('hdnClienteId').value,
        nome: document.getElementById('txtNome').value,
        cpf: document.getElementById('txtCPF').value,
        telefone: removerMascaraTelefone(document.getElementById('txtTelefone').value),
        email: document.getElementById('txtEmail').value,
        ativo: document.getElementById('chkAtivo').checked ? 1 : 0,
        enviowhatsapp: document.getElementById('chkEnvioWhatsapp').checked ? 1 : 0,
        envioemail: document.getElementById('chkEnvioEmail').checked ? 1 : 0,

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
    endPoint = API_URL + "cliente/" + endPoint;

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
                        window.location.href = `${HOST}${APP_HOST}list-cliente.php`;
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
            // console.log('Resposta da API:', data);
            // alert('Dados enviados com sucesso!');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar os dados.');
        });
});

function validaForm() {
    let mensagem = ""
    if (document.getElementById('txtNome').value.trim() === "") {
        mensagem += "Nome Obrigatório<br/>";
    }
    if (document.getElementById('txtCPF').value.trim() === "") {
        mensagem += "CPF / CNPJ Obrigatório<br/>";
    }

    if (document.getElementById('txtCPF').value.length < 14) {
        mensagem += "CPF /CNPJ Invalido<br/>";
    }
    if (document.getElementById('txtCPF').value.length > 14 &&
        document.getElementById('txtCPF').value.length < 18) {
        mensagem += "CPF /CNPJ Invalido<br/>";
    }

    if (document.getElementById('txtTelefone').value.trim() === "") {
        mensagem += "Telefone Obrigatório<br/>";
    }
    if (document.getElementById('txtEmail').value.trim() === "") {
        mensagem += "Email Obrigatório<br/>";
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


function getClienteById(clienteId) {
    const payLoad = {
        clienteId: clienteId
    };

    endPoint = API_URL + "cliente/getclientebyid";
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
                $('#txtNome').val(data.Nome);
                $('#txtCPF').val(formatarDocumento(data.CPF));
                $('#txtTelefone').val(formatarTelefone(data.Telefone));
                $('#txtEmail').val(data.Email);

                if (data.EnvioWhatsapp == 1) {
                    $('#chkEnvioWhatsapp').prop('checked', true);
                } else {
                    $('#chkEnvioWhatsapp').prop('checked', false);
                }

                if (data.EnvioEmail == 1) {
                    $('#chkEnvioEmail').prop('checked', true);
                } else {
                    $('#chkEnvioEmail').prop('checked', false);
                }

                if (data.Ativo == 1) {
                    $('#chkAtivo').prop('checked', true);
                } else {
                    $('#chkAtivo').prop('checked', false);
                }
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

function formatarTelefone(valor) {
    valor = valor.replace(/\D/g, ''); // Remove tudo que não é dígito

    // DDD
    let ddd = valor.substring(0, 2);

    // Número principal
    let numeroPrincipal = valor.substring(2);

    // Se não tem DDD, retorna só o que tem
    if (!ddd) return valor;

    // Decide formato: fixo (8 dígitos) ou celular (9 dígitos)
    let telefoneFormatado = '';
    if (numeroPrincipal.length > 0) {
        telefoneFormatado = '(' + ddd + ') ';
        if (numeroPrincipal.length > 4) {
            // Se celular
            if (numeroPrincipal.length >= 9) {
                telefoneFormatado += numeroPrincipal.substring(0, 5) + '-' + numeroPrincipal.substring(5, 9);
            } else {
                telefoneFormatado += numeroPrincipal.substring(0, 4) + '-' + numeroPrincipal.substring(4);
            }
        } else {
            telefoneFormatado += numeroPrincipal;
        }
    } else {
        // Só DDD
        telefoneFormatado = '(' + ddd;
    }

    // Limita sempre a 15 caracteres
    return telefoneFormatado.substring(0, 15);
}


function removerMascaraTelefone(telefoneMascara) {
    // O método replace() com a expressão regular /\D/g substitui
    // globalmente (g) todos os caracteres que NÃO SÃO dígitos (\D)
    // por uma string vazia ('').
    return telefoneMascara.replace(/\D/g, '');
}