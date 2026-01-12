$(document).ready(function () {
    var table = $('#dtCR').DataTable({
        lengthChange: false,
        buttons: [ 'copy', 'excel', 'pdf', 'print']
    }); // Inicializa o DataTable

    table.buttons().container().appendTo( '#dtCR_wrapper .col-md-6:eq(0)' );

    // Chama sua API
    let endPoint = "";
    //endPoint = API_URL + "user/listar/";
    endPoint = API_URL + "contasreceber/listar";
    $.ajax({
        url: endPoint, 
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.crs.map(function (cr) {
                        return [
                            cr.Nome,
                            formatarData(cr.DtInicio),
                            formatarData(cr.DtFinal),
                            formatarDecimal(cr.VlTotal),
                            formatarDecimal(cr.VlConciliado),
                            formatarDataHora(cr.DtCC)//,
                            // `<input type="checkbox" ${cr.Ativo == 1 ? 'checked' : ''} 
                            // onchange="toggleCRAtivo(${cr.Id}, this.checked)" />`
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

function formatarData(dataString) {
    if (!dataString) return '';

    // Adiciona 'T00:00:00' para garantir que a data seja interpretada no fuso horário local
    const data = new Date(dataString + 'T00:00:00');
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();

    return `${dia}/${mes}/${ano}`;
}

function formatarDataHora(dataHoraString) {
    // Retorna string vazia se a data for nula
    if (!dataHoraString) return '';

    const data = new Date(dataHoraString);

    // Usa métodos UTC para evitar problemas de fuso horário
    const dia = String(data.getUTCDate()).padStart(2, '0');
    const mes = String(data.getUTCMonth() + 1).padStart(2, '0');
    const ano = data.getUTCFullYear();
    const horas = String(data.getUTCHours()).padStart(2, '0');
    const minutos = String(data.getUTCMinutes()).padStart(2, '0');
    const segundos = String(data.getUTCSeconds()).padStart(2, '0');

    return `${dia}/${mes}/${ano} ${horas}:${minutos}:${segundos}`;
}
function formatarMoeda(valor) {
    // Verifica se o valor é nulo ou não é um número válido e retorna uma string vazia
    if (valor === null || isNaN(valor)) {
        return '';
    }

    // Cria um objeto de formatação para o padrão brasileiro de moeda
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarDecimal(valor) {
    // Verifica se o valor é nulo ou não é um número válido
    if (valor === null || isNaN(valor)) {
        return '';
    }

    // Cria um objeto de formatação para o padrão decimal brasileiro
    return new Intl.NumberFormat('pt-BR', {
        style: 'decimal',
        minimumFractionDigits: 2, // Garante que tenha 2 casas decimais
        maximumFractionDigits: 2  // E no máximo 2 casas decimais
    }).format(valor);
}
function excluirErro(id) {
    Swal.fire({
        title: "Tem certeza?",
        text: "O registro será excluído permanentemente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            let endPoint = API_URL + "contasreceber/excluir";
            $.ajax({
                url: endPoint,
                method: 'POST',
                dataType: 'json',
                data: JSON.stringify({ Id: id }), // envia o id no corpo
                contentType: "application/json; charset=utf-8",
                success: function (response) {
                    if (response.status === 'ok') {
                        window.location.href= `${HOST}${APP_HOST}list-cr-erro.php`;
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