$(document).ready(function () {
    var table = $('#dtClienteCC').DataTable({
        lengthChange: false,
        buttons: [ 'copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo( '#dtClienteCC_wrapper .col-md-6:eq(0)' );

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "clientecc/listar";
    $.ajax({
        url: endPoint, 
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.clienteccs.map(function (clientecc) {
                        return [
                            clientecc.NomeCliente,
                            clientecc.NomeCC,
                            clientecc.NomeBanco,
                            clientecc.Agencia,
                            clientecc.CC,
                            clientecc.CPFCNPJ,
                            clientecc.TipoChavePix,
                            clientecc.ChavePix,
                            `<button class="btn btn-sm btn-primary" onclick="editarClientecc(${clientecc.Id})">Editar</button>`
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


function editarClientecc(clienteId){
    const url = `${HOST}${APP_HOST}crud-cc.php?acao=2&clienteId=${clienteId}`;
    window.location.href = url;
}