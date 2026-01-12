let table;

$(document).ready(function () {

    table = $('#dtExtratoCliente').DataTable({
        // Adicione destroy: true aqui para garantir a primeira inicialização limpa,
        // embora geralmente não seja necessário se o init for fora da função de re-carga.
        // destroy: true, 
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        lengthChange: false,
        dom: 'Bfrtip', // Necessário para os botões aparecerem
        buttons: ['copy', 'excel', 'pdf', 'print']
    });

    // Anexa os botões
    table.buttons().container().appendTo('#dtExtratoCliente_wrapper .col-md-6:eq(0)');

    carregarCliente();

    $('#cboCliente').on('change', function () {
        carregarGridExtrato();
    });

    $('#cmdPesquisarPorData').on('click', function () {
        carregarGridExtrato();
    });

    preencherDatasDoMesCorrente();

});

function carregarCliente() {
    let endPoint = "";
    endPoint = API_URL + "cliente/listar";

    fetch(endPoint)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const $select = $('#cboCliente');

                $select.empty();
                data.clientes.forEach(cliente => {
                    $select.append(new Option(
                        cliente.Nome,
                        cliente.Id
                    ));
                });

                $select.select2({
                    theme: "bootstrap-5",
                    width: $select.data('width') ? $select.data('width') : $select.hasClass('w-100') ? '100%' : 'style',
                    placeholder: $select.data('placeholder'),
                    closeOnSelect: false,
                });

                setTimeout(function () {
                    carregarGridExtrato();
                }, 500);

            } else {
                console.error('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao chamar a API:', error);
        });


}

// A variável 'table' deve ser definida fora desta função (como discutido anteriormente)
// Ex: var table; 

function carregarGridExtrato() {
    // 1. Obtém o ID do cliente
    let ClientId = $('#cboCliente').val();
    let DtInicio = $('#txtDtInicial').val();
    let DtFinal = $('#txtDtFinal').val();
    // Converte para null se o valor for vazio ou 0
    const idParaEnviar = (ClientId === '' || ClientId === '0' || ClientId === null) ? null : ClientId;

    const payload = {
        Id: idParaEnviar,
        dtInicial: DtInicio,
        dtFinal: DtFinal
    };

    // Define o endpoint da API
    let endPoint = API_URL + "extratos/listar";

    // 2. Utiliza a API Fetch
    fetch(endPoint, {
        method: 'POST',
        // Define o tipo de conteúdo que estamos ENVIANDO (JSON)
        headers: {
            'Content-Type': 'application/json'
        },
        // Converte o objeto JavaScript em uma string JSON para o corpo da requisição
        body: JSON.stringify(payload)
    })
        .then(response => {
            // Verifica se a resposta HTTP foi bem-sucedida (status 200-299)
            if (!response.ok) {
                // Se o status HTTP não for OK, lança um erro para cair no .catch()
                throw new Error('HTTP status ' + response.status);
            }
            // Converte a resposta do corpo para JSON
            return response.json();
        })
        .then(response => {
            // 3. Processamento da Resposta Lógica (status: 'ok')
            if (response.status === 'ok') {
                // Limpa e popula o DataTable
                table.clear().rows.add(
                    response.ecs.map(function (ec) {
                        return [
                            formatarData(ec.Data),
                            ec.Historico,
                            formatarDecimal(ec.Credito),
                            formatarDecimal(ec.Debito),
                            formatarDecimal(ec.Saldo)
                        ];
                    })
                ).draw();
            } else {
                // Lógica de erro da API (e.g., status: 'erro')
                table.clear().draw();
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    draggable: true,
                    html: response.mensagem
                });
            }
        })
        .catch(error => {
            // 4. Tratamento de Erro de Rede ou HTTP
            console.error('Fetch error:', error);
            Swal.fire({
                icon: "error",
                title: "Erro",
                draggable: true,
                text: "Erro ao conectar com a API ou requisição falhou: " + error.message
            });
        });
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

function formatarData(dataString) {
    if (!dataString) return '';

    // Adiciona 'T00:00:00' para garantir que a data seja interpretada no fuso horário local
    const data = new Date(dataString + 'T00:00:00');
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();

    return `${dia}/${mes}/${ano}`;
}

function preencherDatasDoMesCorrente() {
    // 1. Obter a data atual
    const hoje = new Date();

    // 2. Calcular o primeiro dia do mês
    // O construtor Date(ano, mês, dia) é usado. Mês é 0-indexado.
    const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);

    // 3. Calcular o último dia do mês
    // Obter o primeiro dia do *próximo* mês e subtrair 1 milissegundo (ou 1 dia).
    const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

    // 4. Formatar as datas para o padrão ISO (YYYY-MM-DD)
    // Este é o formato obrigatório para o atributo 'value' de input type="date".
    const dataInicialFormatada = formatarParaInputDate(primeiroDia);
    const dataFinalFormatada = formatarParaInputDate(ultimoDia);

    // 5. Preencher os atributos (campos) HTML
    $('#txtDtInicial').val(dataInicialFormatada);
    $('#txtDtFinal').val(dataFinalFormatada);
    const inputDataFinal = document.getElementById('datafinal');

}
function formatarParaInputDate(dateObj) {
    const ano = dateObj.getFullYear();
    // Mês é 0-indexado, então adicionamos 1. Usamos padStart para garantir 2 dígitos.
    const mes = String(dateObj.getMonth() + 1).padStart(2, '0');
    const dia = String(dateObj.getDate()).padStart(2, '0');

    return `${ano}-${mes}-${dia}`;
}