document.getElementById('cmdOk').addEventListener('click', function () {

    Conciliar();

});

async function Conciliar() {
    endPoint = API_URL + "extratos/conciliar";    

    try {
        const response = await fetch(endPoint);

        if (!response.ok) {
            throw new Error(`Erro HTTP! Status: ${response.status}`);
        }

        const resultado = await response.json();

        if (resultado.status === 'ok') {
            // Se a conciliação foi bem-sucedida, você pode notificar o usuário
            Swal.fire({
            title: "Parabens",
            draggable: true,
            html: resultado.mensagem,
            confirmButtonText: "OK"
            }).then((result) => {

            if (result.isConfirmed) {
                window.location.href= `${HOST}${APP_HOST}dashboard.php`;
                return;
            } 
            });

            return true; // Retorna sucesso
        } else {
            // Exibe mensagem de erro da API
            Swal.fire({icon: "error", title: "Oops...", text: `Falha na conciliação: ${resultado.mensagem}`});
            return false;
        }

    } catch (error) {
        // Exibe erro de rede ou falha de conexão
        console.error("Erro na comunicação com a API:", error.message);
        Swal.fire({icon: "error", title: "Oops...", text: "Erro de conexão! Verifique o console para detalhes."});
        return false;
    }
}