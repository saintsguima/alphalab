$(document).ready(function () {
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
