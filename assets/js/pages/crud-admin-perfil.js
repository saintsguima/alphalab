
document.getElementById('cmdOk').addEventListener('click', function () {
    // Coleta os dados do formulário
    if (!validaForm()){
        return;
    }
    let acao = document.getElementById('hdnAcao').value;
    acao = acao -0;
    
    const payLoad = {
        Id: document.getElementById('hdnAdminPerfilId').value,
        Nome: document.getElementById('txtNome').value
    };

    let endPoint = "";
    switch(acao){
        case 1:
            endPoint = "incluir";
            break;
        case 2:
            endPoint = "alterar";
            break;
    }
    endPoint = API_URL + "adminperfil/" + endPoint;
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
        if (data.status === "ok"){
            Swal.fire({
            title: "Parabens",
            draggable: true,
            html: data.mensagem,
            confirmButtonText: "OK"
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location.href= `${HOST}${APP_HOST}list-admin-perfil.php`;
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

function excluirPerfil(thisId){
    const payLoad = {
        Id: thisId
    };

    let endPoint = API_URL + "adminperfil/excluir";

    fetch(endPoint, { 
        method: 'DELETE',
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
        if (data.status === "ok"){
            Swal.fire({
            title: "Parabens",
            draggable: true,
            html: data.mensagem,
            confirmButtonText: "OK"
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location.href= `${HOST}${APP_HOST}list-admin-perfil.php`;
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
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar os dados.');
    });


}
function validaForm(){
        let mensagem = ""
        if (document.getElementById('txtNome').value.trim() === ""){
            mensagem += "Nome Obrigatório<br/>";
        }
        if (mensagem.trim() !== ""){
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

function getPerfilById(perfilId){
    const payLoad = {
        perfilId: perfilId
    };

    endPoint = API_URL + "adminperfil/getperfilbyid";    
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
        if (data.status === "ok"){
            $('#txtNome').val(data.Nome);
        } else {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                draggable: true,
                html: data.mensagem
			});
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar os dados.');
    });
    

}
