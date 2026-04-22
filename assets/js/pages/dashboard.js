
$(document).ready(function () {
    fillDate();

    (async () => {
        const Totais = await buscarTotalExtratos();

        const formatarMoeda = (valor) => {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor || 0); // O '|| 0' evita erro se o valor vier vazio
        };
        //if (Totais.total !== null) {
        // Exibe o valor na página, por exemplo
        if (Totais) {
            // Aplicando a formatação em cada elemento
            document.getElementById('h5PNI').textContent = formatarMoeda(Totais.TotalNaoConciliado);
            document.getElementById('h5Inadimplencia').textContent = formatarMoeda(Totais.TotalAbertos);
            document.getElementById('h5FaturadoNoMes').textContent = formatarMoeda(Totais.FaturadoNoMes);
            document.getElementById('h5AReceber').textContent = formatarMoeda(Totais.TotalAReceber);
            document.getElementById('h5MesFaturamento').textContent = formatarMoeda(Totais.FaturadoNoMes);
        }        //}
    })();
});

async function buscarTotalExtratos() {
    // 1. Defina o URL da sua API
    const url = `${API_URL}dashboard/pagamentos-nao-identificados`;

    try {
        // 2. Faz a requisição GET (o método 'GET' é o padrão, não precisa ser especificado)
        const response = await fetch(url);

        // 3. Verifica se a resposta HTTP foi bem-sucedida (status 200-299)
        if (!response.ok) {
            // Se o status for 4xx ou 5xx, lança um erro
            throw new Error(`Erro HTTP! Status: ${response.status}`);
        }

        // 4. Converte a resposta para JSON (e espera por essa conversão)
        const resultado = await response.json();

        // A estrutura JSON de resposta esperada é: { status: 'ok', Total: 1234.56 }

        //alert(resultado.status);
        if (resultado.status === 'ok') {
            const valorTotal = resultado.dados;// = resultado.Total;

            console.log("Sucesso! O Total Não Conciliado é:", valorTotal);

            // Você pode retornar o valor para usá-lo onde a função for chamada
            return valorTotal;
        } else {
            // Trata erros que sua API PHP pode ter enviado com status: 'erro' (código 500)
            throw new Error(resultado.mensagem || "A API retornou um status de erro.");
        }

    } catch (error) {
        // Captura erros de rede, erros HTTP ou erros na lógica da API
        console.error("Falha ao buscar o total:", error.message);
        // Retorna um valor padrão ou lança o erro novamente, dependendo da sua necessidade
        return null;
    }
}


function fillDate() {
    // Pega a data atual
    const dataAtual = new Date();

    // Extrai o ano (YYYY)
    const ano = dataAtual.getFullYear();

    // Extrai o mês (adiciona 1 porque os meses começam em 0 no JavaScript)
    // O padStart(2, '0') garante que meses menores que 10 fiquem com 2 dígitos (ex: '04')
    const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');

    // Formata no padrão exigido pelo input type="month" (YYYY-MM)
    const valorFormatado = `${ano}-${mes}`;

    // Atribui o valor ao campo
    document.getElementById('txtMesFaturamento').value = valorFormatado;


}

document.addEventListener('DOMContentLoaded', function () {

    const campoMes = document.getElementById('txtMesFaturamento');

    // Associa o evento 'change' à função externa
    campoMes.addEventListener('change', buscarDadosFaturamento);

});
function buscarDadosFaturamento(evento) {

    const valorSelecionado = evento.target.value;
    let AnoMes = document.getElementById('txtMesFaturamento').value;

    // Se o usuário limpar o campo, não faz nada
    if (!valorSelecionado) {
        console.log("Campo vazio, nenhuma chamada foi feita.");
        return;
    }

    // Separa o ano e o mês
    const [anoSelecionado, mesSelecionado] = valorSelecionado.split('-');

    // Monta a URL

    const payload = {
        Ano: anoSelecionado,
        Mes: mesSelecionado
    };

    let endPoint = `${API_URL}dashboard/pagamentos-no-mes-de`;

    fetch(endPoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP status ' + response.status);
            }
            return response.json();
        })
        .then(response => {
            if (response.status === 'ok') {
                const formatarMoeda = (valor) => {
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }).format(valor || 0);
                };
                const Total = response.dados;
                document.getElementById('h5MesFaturamento').textContent = formatarMoeda(Total.TotalAReceberNoMes);
            } else {
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
            console.error('Fetch error:', error);
            Swal.fire({
                icon: "error",
                title: "Erro",
                draggable: true,
                text: "Erro ao conectar com a API ou requisição falhou: " + error.message
            });
        });
}