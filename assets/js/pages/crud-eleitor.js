async function fnSalvarEleitor(){

  if (!validaCampos()){
    return;
  }

  let acao = document.getElementById('hdnAcao').value;
  acao = acao -0;

  
  const payLoad = {
      Id: $('#hdnEleitorId').val(),
      Nome: $('#txtNome').val(),
      CPF: $('#txtcpf').val(),
      DtNascimento: $('#txtDtNascimento').val(),
      Telefone: $('#txtTelefone').val(),
      Email: $('#txtEmail').val(),
      Ativo: document.getElementById('chkAtivo').checked ? 1 : 0 // true/false para 1/0
  };
    console.log(JSON.stringify(payLoad));
    let endPoint = "";
    switch(acao){
        case 1:
            endPoint = "incluir/incluir.php";
            break;
        case 2:
            endPoint = "alterar/alterar.php";
            break;
        default:
            endPoint = "excluir/excluir.php";
            break;
    }
    endPoint = API_URL + "eleitor/" + endPoint + "/";
    console.log(endPoint);
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
            if (result.isConfirmed) {
                window.location.href= `${HOST}${APP_HOST}list-eleitor.php`;
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

function validaCampos(){

  var mensagem = ""  ;

  if ($('#txtNome').val().trim() === ""){
    mensagem += "Nome do Eleitor Obrigatório<br/>";
  }

  if ($('#txtcpf').val().trim() === ""){
    mensagem += "CPF Obrigatório<br/>";
  }
  
  if ($('#txtDtNascimento').val().trim() === ""){
    mensagem += "Data de Nascimento Obrigatório<br/>";
  }
  
  if ($('#txtTelefone').val().trim() === ""){
    mensagem += "Telefone Obrigatório<br/>";
  }

  if ($('#txtEmail').val().trim() === ""){
    mensagem += "Email Obrigatório<br/>";
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

