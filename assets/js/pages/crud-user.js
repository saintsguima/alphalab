$(document).ready(function () {
    carregarPerfil();
});
document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    if (!validaForm()) {
        return;
    }
    let acao = document.getElementById('hdnAcao').value;
    acao = acao - 0;

    const payLoad = {
        userId: document.getElementById('hdnUserId').value,
        nome: document.getElementById('txtNome').value,
        userName: document.getElementById('txtUserName').value,
        telefone: removerMascaraTelefone(document.getElementById('txtTelefone').value),
        email: document.getElementById('txtEmail').value,
        senha: document.getElementById('txtSenha').value,
        confirmaSenha: document.getElementById('txtConfirmaSenha').value,
        perfil: document.getElementById('cboPerfil').value,
        ativo: document.getElementById('chkAtivo').checked ? 1 : 0 // true/false para 1/0
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
    endPoint = API_URL + "user/" + endPoint;

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
                        window.location.href = `${HOST}${APP_HOST}list-user.php`;
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
    if (document.getElementById('txtUserName').value.trim() === "") {
        mensagem += "User Name Obrigatório<br/>";
    }
    if (document.getElementById('txtTelefone').value.trim() === "") {
        mensagem += "Telefone Obrigatório<br/>";
    }
    if (document.getElementById('txtEmail').value.trim() === "") {
        mensagem += "Email Obrigatório<br/>";
    }
    if (document.getElementById('txtSenha').value.trim() === "") {
        mensagem += "Senha Obrigatória<br/>";
    }
    if (document.getElementById('txtConfirmaSenha').value.trim() === "") {
        mensagem += "Confirmação de Senha Obrigatória<br/>";
    }

    if (document.getElementById('txtSenha').value.trim() !== document.getElementById('txtConfirmaSenha').value.trim()) {
        mensagem += "Senha e Confirmação de senha devem ser identicas<br/>";
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

function aplicarMascaraTelefone(input) {
    input.addEventListener('input', function () {
        let numero = input.value.replace(/\D/g, ''); // Remove tudo que não for dígito

        if (numero.length > 11) {
            numero = numero.slice(0, 11); // Limita ao máximo permitido
        }

        // Formata com base na quantidade de dígitos
        if (numero.length <= 10) {
            // Formato fixo: (99) 9999-9999
            numero = numero.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else {
            // Formato celular: (99) 99999-9999
            numero = numero.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        }

        input.value = numero;
    });
}

function getUserById(userId) {
    const payLoad = {
        userId: userId
    };

    endPoint = API_URL + "user/getuserbyid";
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
                $('#txtNome').val(data.nome);
                $('#txtUserName').val(data.username);
                $('#txtTelefone').val(formatarTelefone(data.telefone));
                $('#txtEmail').val(data.email);

                setTimeout(function () {
                    $('#cboPerfil').val(data.PerfilId);
                }, 500);

                if (data.ativo == 1) {
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

function carregarPerfil() {
    let endPoint = "";
    endPoint = API_URL + "adminperfil/listar";

    fetch(endPoint)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const $select = $('#cboPerfil');

                $select.empty();
                data.perfils.forEach(perfil => {
                    $select.append(new Option(
                        perfil.Nome,
                        perfil.Id
                    ));
                });

                // $select.select2({
                //     theme: "bootstrap-5",
                //     width: $select.data( 'width' ) ? $select.data( 'width' ) : $select.hasClass( 'w-100' ) ? '100%' : 'style',
                //     placeholder: $select.data( 'placeholder' ),
                //     closeOnSelect: false,
                // });
            } else {
                console.error('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao chamar a API:', error);
        });
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