$(document).ready(function () {
    var table = $('#dtClientes').DataTable({
        lengthChange: false,
        buttons: ['copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo('#dtClientes_wrapper .col-md-6:eq(0)');

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "cliente/listar";
    $.ajax({
        url: endPoint,
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.clientes.map(function (cliente) {
                        return [
                            cliente.Nome,
                            cliente.CPF,
                            formatarTelefone(cliente.Telefone),
                            cliente.Email,
                            `<input type="checkbox" ${cliente.EnvioWhatsapp == 1 ? 'checked' : ''} 
                            onchange="toggleEnvioWhatsapp(${cliente.Id}, this.checked)" />`,
                            `<input type="checkbox" ${cliente.EnvioEmail == 1 ? 'checked' : ''} 
                            onchange="toggleEnvioEmail(${cliente.Id}, this.checked)" />`,
                            `<input type="checkbox" ${cliente.Ativo == 1 ? 'checked' : ''} 
                            onchange="toggleUsuarioAtivo(${cliente.Id}, this.checked)" />`,
                            `<button class="btn btn-sm btn-primary" onclick="editarCliente(${cliente.Id})">Editar</button>`
                        ];
                    })
                ).draw();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    draggable: true,
                    html: response.mensagem
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Erro",
                draggable: true,
                text: "Erro ao conectar com a API."
            });
        }
    });
});

function toggleUsuarioAtivo(clienteId, ativo) {
    const url = API_URL + "cliente/ativar";
    // Monta o corpo da requisição
    const body = JSON.stringify({
        clienteId: clienteId,
        ativo: ativo ? 1 : 0
    });

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
                console.log('Status atualizado com sucesso!');
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

function toggleEnvioWhatsapp(clienteId, ativo) {
    const url = API_URL + "cliente/enviowhatsapp";
    // Monta o corpo da requisição
    const body = JSON.stringify({
        clienteId: clienteId,
        ativo: ativo ? 1 : 0
    });

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
                console.log('Status atualizado com sucesso!');
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

function toggleEnvioEmail(clienteId, ativo) {
    const url = API_URL + "cliente/envioemail";
    // Monta o corpo da requisição
    const body = JSON.stringify({
        clienteId: clienteId,
        ativo: ativo ? 1 : 0
    });

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
                console.log('Status atualizado com sucesso!');
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

function editarCliente(clienteId) {
    const url = `${HOST}${APP_HOST}crud-cliente.php?acao=2&clienteId=${clienteId}`;
    window.location.href = url;
}

function formatarTelefone(telefone) {
    // 1. Converte para string e remove todos os caracteres não numéricos
    let numeroLimpo = String(telefone).replace(/\D/g, '');

    // O número deve ter 10 (DDD + 8 dígitos) ou 11 (DDD + 9 dígitos) dígitos
    const tamanho = numeroLimpo.length;

    if (tamanho < 10 || tamanho > 11) {
        return telefone; // Retorna o valor original se não for um telefone válido
    }

    // 2. Aplica a máscara de formatação
    if (tamanho === 11) {
        // Formato: (XX) XXXXX-XXXX
        // $1: DDD, $2: 5 primeiros, $3: 4 últimos
        return numeroLimpo.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    } else if (tamanho === 10) {
        // Formato: (XX) XXXX-XXXX
        // $1: DDD, $2: 4 primeiros, $3: 4 últimos
        return numeroLimpo.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    }
}