$(document).ready(function () {
    var table = $('#dtAdminPerfil').DataTable({
        lengthChange: false,
        buttons: [ 'copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo( '#dtAdminPerfil_wrapper .col-md-6:eq(0)' );

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "adminperfil/listar";
    $.ajax({
        url: endPoint, 
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.perfils.map(function (perfil) {
                        return [
                            perfil.Nome,
                            `<button class="btn btn-sm btn-primary" onclick="editarPerfil(${perfil.Id})">Editar</button> <button class="btn btn-sm btn-danger" onclick="excluirPerfil(${perfil.Id})">Excluir</button>`
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


function editarPerfil(perfilId){
    const url = `${HOST}${APP_HOST}crud-admin-perfil.php?acao=2&perfilId=${perfilId}`;
    window.location.href = url;
}

