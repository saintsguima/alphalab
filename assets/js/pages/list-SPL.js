$(document).ready(function () {

    let endPoint = "";
    //endPoint = API_URL + "spl/listar/";
    endPoint = API_URL + "spl/listar/listar.php";

    var tabela = $('#dtSPL').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: endPoint,
            type: 'POST',
            contentType: 'application/json',
            data: function (d) {
                d.searchTerm = $('#dtSPLL_filter input').val();
                return JSON.stringify(d);
            }
        },
        columns: [
            {data: 'Id'},
            {data: 'DataInclusao'},
            {data: 'Proposta'},
            {data: 'Resumo'},
            {data: 'TextoCompleto'},
            {data: 'Descricao'},
            {data: 'Impacto'},
            {data: 'PalavraChave'},
            {data: 'Autor'},
            {data: 'Autorizo'},
            {data: 'flPublicado'}
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
    $('#dtSPL_filter input').unbind();

    // Adiciona filtro ao pressionar Enter
    $('#dtSPL_filter input').bind('keyup', function (e) {
        if (e.keyCode === 13) {
            tabela.draw();
        }
    });
});

function abrirProduto(produto){
    window.location.href = `${HOST}${APP_HOST}crud-produto.php?acao=2&produto=${produto}`;
}