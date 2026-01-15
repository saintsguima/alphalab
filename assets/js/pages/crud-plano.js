$(document).ready(function () {
    var table = $('#dtPlano').DataTable({
        lengthChange: false,
        buttons: ['copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo('#dtPlano_wrapper .col-md-6:eq(0)');

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "plano/listar";
    $.ajax({
        url: endPoint,
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.plano.map(function (plano) {
                        return [
                            plano.Nome,
                            `<button class="btn btn-sm btn-danger" onclick="ExcluirPlano(${plano.Id})">Excluir</button>`
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

document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    if (!validaForm()) {
        return;
    }

    const payLoad = {
        nome: document.getElementById('txtNome').value,
    };

    endPoint = API_URL + "plano/incluir";

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
                        window.location.href = `${HOST}${APP_HOST}crud-plano.php`;
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

function ExcluirPlano(id) {
    Swal.fire({
        title: "Tem certeza?",
        text: "O registro será excluído permanentemente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            let endPoint = API_URL + "plano/excluir";
            $.ajax({
                url: endPoint,
                method: 'POST',
                dataType: 'json',
                data: JSON.stringify({ Id: id }), // envia o id no corpo
                contentType: "application/json; charset=utf-8",
                success: function (response) {
                    if (response.status === 'ok') {
                        window.location.href = `${HOST}${APP_HOST}crud-plano.php`;
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Erro",
                            text: response.mensagem || "Não foi possível excluir o registro."
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Erro",
                        text: "Falha ao conectar com a API."
                    });
                }
            });
        }
    });
}
