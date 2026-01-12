$(document).ready(function () {
    let endPoint = "";
    //endPoint = API_URL + "eleitor/listar/";
    endPoint = API_URL + "eleitor/listar/listar.php";
    
    var tabela = $('#dtEleitor').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: endPoint,
            type: 'POST',
            contentType: 'application/json',
            data: function (d) {
                d.searchTerm = $('#dtEleitor_filter input').val();
                return JSON.stringify(d);
            }
        },
        columns: [
            {
                data: 'Id',
                render: function (data, type, row) {
                    return `<a href="#" onclick="abrirProduto(${data}); return false;">${data}</a>`;
                }
            },
            { data: 'Nome' },
            { data: 'CPF' },
            { data: 'DtNascimento',
                render: function (data, type, row) {
                        var partes = data.split('-');
                        // Reorganiza para "dd/mm/yyyy"
                        return partes[2] + '/' + partes[1] + '/' + partes[0];
                }             
             },
            { data: 'Telefone'},
            { data: 'email' },
            {data: 'Ativo',
                render: function (data, type, row) {
                    return data == 1 ? 'Sim' : 'Não';
                }
            }
        ],
        searching: true,
        ordering: false,
        lengthChange: false,
        language: {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sSearch": "Pesquisar:",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            }            
        },
        oAria: {
            "sSortAscending": ": Ordenar colunas de forma ascendente",
            "sSortDescending": ": Ordenar colunas de forma descendente"
            
        }
        
    });

    // Desvincula o filtro padrão
    $('#dtEleitor_filter input').unbind();

    // Adiciona filtro ao pressionar Enter
    $('#dtEleitor_filter input').bind('keyup', function (e) {
        if (e.keyCode === 13) {
            tabela.draw();
        }
    });
});

function abrirProduto(produto){
    window.location.href = `${HOST}${APP_HOST}crud-produto.php?acao=2&produto=${produto}`;
}