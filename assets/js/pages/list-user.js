$(document).ready(function () {
    var table = $('#dtUsuarios').DataTable({
        lengthChange: false,
        buttons: [ 'copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo( '#dtUsuarios_wrapper .col-md-6:eq(0)' );

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "user/listar";
    $.ajax({
        url: endPoint, 
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.usuarios.map(function (usuario) {
                        return [
                            usuario.Nome,
                            usuario.UserName,
                            usuario.Email,
                            usuario.NomePerfil,
                            `<input type="checkbox" ${usuario.Ativo == 1 ? 'checked' : ''} onchange="toggleUsuarioAtivo(${usuario.Id}, this.checked)" />`,
                            `<button class="btn btn-sm btn-primary" onclick="editarUsuario(${usuario.Id})">Editar</button>`
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

function toggleUsuarioAtivo(userId, ativo) {
    const url = API_URL + "user/ativar";
    // Monta o corpo da requisição
    const body = JSON.stringify({
        userId: userId,
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

function editarUsuario(userId){
    const url = `${HOST}${APP_HOST}crud-user.php?acao=2&userId=${userId}`;
    window.location.href = url;
}