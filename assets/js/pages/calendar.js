
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        initialView: 'dayGridMonth',
        initialDate: startDate,
        navLinks: true, // can click day/week names to navigate views
        selectable: true,
        nowIndicator: true,
        dayMaxEvents: true, // allow "more" link when too many events
        editable: false,
        selectable: true,
        businessHours: true,
        dayMaxEvents: true, // allow "more" link when too many events
        locale: 'pt-br',
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        eventDrop: function(info) {
            // A função 'revert' é usada para desfazer a ação de arrastar
            if (info.event.id === '123') {
                alert("Este evento não pode ser movido!");
                info.revert();
            }        
        },
        // eventClick: function(info) {
        //    //alert('Evento selecionado: ' + info.event.title + '\nID: ' + info.event.id);
        //    $('#eventoModal').modal('show');
        // },
        events: async function(fetchInfo, successCallback, failureCallback) {
            try {
                // Parâmetros para o POST:
                let params = {
                    userId: 1,  
                    startDate: fetchInfo.startStr,
                    endDate : fetchInfo.endStr
                };

                //let response = await fetch(API_URL + "calendario/listar/", {
                let response = await fetch(API_URL + "calendario/listar/listar.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(params)
                });

                let dados = await response.json();

                let eventos = dados.calendario.map(ev => ({
                    id: ev.Id,
                    title: ev.Titulo,
                    start: ev.DtInicio,
                    end: ev.DtFinal,
                    color: ev.IdTipoAgenda === 1 ? "orange" : "blue"
                }));
                successCallback(eventos);
            } catch (error) {
                console.error("Erro ao carregar eventos:", error);
                failureCallback(error);
            }
        }   
    });
    calendar.render();
    let dataAtual = calendar.getDate();  // retorna um objeto Date
    let mes = dataAtual.getMonth() + 1;  // de 0 a 11, por isso +1
    let ano = dataAtual.getFullYear();

    //alert(`Mês: ${mes}, Ano: ${ano}`);

});

function ultimoDiaDoMes(data) {
  // Cria uma nova data baseada na data recebida.
  const novaData = new Date(data);
  
  // Define o mês para o próximo mês (adicionando 1) e o dia para 0.
  // O dia 0 de um mês é o último dia do mês anterior.
  novaData.setMonth(novaData.getMonth() + 1);
  novaData.setDate(0);
  
  // Retorna a nova data, que agora é o último dia do mês.
  return novaData;
}