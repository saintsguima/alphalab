$(document).ready(function () {
    
    const primeiroDia = getPrimeiroDiaDoMes();
    const ultimoDia = getUltimoDiaDoMes();

    $('#txtDtInicio').val(formatarParaISO(primeiroDia));
    $('#txtDtFinal').val(formatarParaISO(ultimoDia));
});


function formatarParaISO(data) {
  const ano = data.getFullYear();
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const dia = String(data.getDate()).padStart(2, '0');
  return `${ano}-${mes}-${dia}`;
}

// 1. Pegar o primeiro dia do mês atual
function getPrimeiroDiaDoMes() {
  const hoje = new Date();
  return new Date(hoje.getFullYear(), hoje.getMonth(), 1);
}

// 2. Pegar o último dia do mês atual
function getUltimoDiaDoMes() {
  const hoje = new Date();
  return new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
}