$(document).ready(function () {
    // 1. Defina a URL da sua API PHP
    const apiEndpoint = endPoint = API_URL + "send/get-w-e";

    // 2. Chama a função assíncrona
    buscarStatusEnvio(apiEndpoint)
        .then(dadosRetornados => {
            if (dadosRetornados) {
                // Exemplo de como preencher um campo HTML (assumindo que você tem <span id="next-whatsapp"></span>)
                $('#spnUltimoDisparo').text(formatarDataParaExibicao(dadosRetornados.LastWhatsapp));
                $('#spnProximoDisparo').text(formatarDataParaExibicao(dadosRetornados.NextWhatsapp));
            } else {
                console.warn("Nenhum dado de status de envio foi retornado.");
                // Exemplo: mostrar um erro na tela
                // $('#mensagem-erro').text('Não foi possível carregar o status de envio.').show();
            }
        })
        .catch(error => {
            console.error("Erro fatal ao executar a função buscarStatusEnvio:", error);
        });
});

function formatarDataParaExibicao(dataStringMysql) {
    if (!dataStringMysql || dataStringMysql === '0000-00-00 00:00:00') {
        return "N/A"; // Retorna algo amigável para datas nulas ou inválidas
    }

    // A string é no formato: "YYYY-MM-DD HH:MM:SS"

    // 1. Divide a string em duas partes: Data e Hora
    // Ex: ["2025-11-04", "22:29:36"]
    const partes = dataStringMysql.split(' ');
    const dataParte = partes[0]; // "2025-11-04"
    const horaParte = partes[1]; // "22:29:36"

    // 2. Divide a parte da Data:
    // Ex: ["2025", "11", "04"]
    const [ano, mes, dia] = dataParte.split('-');

    // 3. Reorganiza para o formato DD/MM/YYYY e junta com a hora
    return `${dia}/${mes}/${ano} ${horaParte}`;
}
async function buscarStatusEnvio(urlApi) {
    try {
        const response = await fetch(urlApi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const jsonResponse = await response.json();

        if (jsonResponse.status === 'success' && jsonResponse.data) {
            return jsonResponse.data; // Retorna apenas o objeto de dados
        } else {
            console.error('Falha na busca dos dados (Status PHP:', jsonResponse.status, ')', jsonResponse.message);
            return null;
        }

    } catch (error) {
        console.error('Ocorreu um erro na requisição fetch:', error);
        // Em um ambiente real, você pode querer reportar este erro de forma mais visível.
        return null;
    }
}

document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    const payLoad = {
        flWhatsapp: 1
    };

    let endPoint = "";
    endPoint = API_URL + "send/whatsapp";

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
                        //window.location.href = `${HOST}${APP_HOST}list-user.php`;
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
