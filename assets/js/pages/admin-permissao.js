// Helper para escapar texto em HTML
function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

// Carrega perfis na abertura da página
document.addEventListener('DOMContentLoaded', () => {
    loadPerfisNoCombo();
});

async function loadPerfisNoCombo() {
    const select = document.getElementById('cboPerfil');
    if (!select) return;

    // Ajuste o endpoint conforme sua rota pública desse PHP
    // Ex.: `${API_URL}perfil/listar` ou URL absoluta do seu script PHP
    const endpoint = `${API_URL}adminperfil/listar`;

    const placeholder = select.dataset.placeholder || 'Selecione...';
    // Estado "Carregando..."
    select.innerHTML = `<option value="">Carregando…</option>`;

    try {
        const res = await fetch(endpoint, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            // credentials: 'include',             // habilite se usa sessão por cookie
            // headers: { 'Authorization': 'Bearer ...' } // se usar token
        });
        if (!res.ok) {
            let extra = '';
            try { extra = ' - ' + (await res.text()).slice(0, 200); } catch (_) { }
            throw new Error(`HTTP ${res.status} ${res.statusText}${extra}`);
        }

        const json = await res.json();

        // Esperado: { status:'ok', perfils:[{Id, Nome}, ...] }
        const lista = (json?.status === 'ok' && Array.isArray(json?.perfils))
            ? json.perfils
            : Array.isArray(json) ? json
                : Array.isArray(json?.data) ? json.data
                    : [];

        if (!lista.length) {
            throw new Error(json?.mensagem || 'Nenhum Perfil encontrado.');
        }

        // Monta as options
        select.innerHTML =
            `<option value="">${escapeHtml(placeholder)}</option>` +
            lista.map(p => {
                const id = p.Id ?? p.id;
                const nome = p.Nome ?? p.nome ?? '';
                return `<option value="${escapeHtml(id)}">${escapeHtml(nome)}</option>`;
            }).join('');

        // Integra com Select2 se já estiver carregado
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            const $sel = window.jQuery(select);
            if ($sel.data('select2')) {
                $sel.trigger('change.select2');
            } else {
                $sel.select2({
                    placeholder,
                    allowClear: true,
                    width: 'resolve',
                    dropdownParent: $sel.parent()
                });
            }
        }
    } catch (err) {
        console.error('Erro ao carregar perfis:', err);
        select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>`;
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Erro ao carregar perfis', text: err.message || 'Falha ao consultar a API.' });
        }
    }
}

$(document).ready(function () {

    $('#swt01').on('change', function () {

        const estaChecado = $(this).prop('checked');

        setEstado('01', estaChecado);

    });

    $('#swt02').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt0201').prop('checked', estaChecado);
        $('#swt0202').prop('checked', estaChecado);
        $('#swt0203').prop('checked', estaChecado);

        setEstado('02,0201,0202,0203', estaChecado);

    });

    $('#swt0201').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt02').prop('checked', estaChecado);
            setEstado('02', estaChecado);
        }
        setEstado('0201', estaChecado);
    });

    $('#swt0202').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt02').prop('checked', estaChecado);
            setEstado('02', estaChecado);
        }
        setEstado('0202', estaChecado);
    });

    $('#swt0203').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt02').prop('checked', estaChecado);
            setEstado('02', estaChecado);
        }
        setEstado('0203', estaChecado);
    });

    $('#swt03').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt0301').prop('checked', estaChecado);
        $('#swt0302').prop('checked', estaChecado);
        $('#swt0303').prop('checked', estaChecado);

        setEstado('03,0301,0302,0303', estaChecado);
    });

    $('#swt0301').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt03').prop('checked', estaChecado);
            setEstado('03', estaChecado);
        }
        setEstado('0301', estaChecado);
    });

    $('#swt0302').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt03').prop('checked', estaChecado);
            setEstado('03', estaChecado);
        }
        setEstado('0302', estaChecado);
    });

    $('#swt0303').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt03').prop('checked', estaChecado);
            setEstado('03', estaChecado);
        }
        setEstado('0303', estaChecado);
    });


    $('#swt04').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt0401').prop('checked', estaChecado);
        $('#swt0402').prop('checked', estaChecado);
        $('#swt0403').prop('checked', estaChecado);
        $('#swt040301').prop('checked', estaChecado);
        $('#swt040302').prop('checked', estaChecado);

        setEstado('04,0401,0402,0403,040301,040302', estaChecado);
    });

    $('#swt0401').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt04').prop('checked', estaChecado);
            setEstado('04', estaChecado);
        }
        setEstado('0401', estaChecado);
    });

    $('#swt0402').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt04').prop('checked', estaChecado);
            setEstado('04', estaChecado);
        }
        setEstado('0402', estaChecado);
    });

    $('#swt0403').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt04').prop('checked', estaChecado);
            setEstado('04', estaChecado);
        }
        setEstado('0403', estaChecado);
    });

    $('#swt040301').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt04').prop('checked', estaChecado);
            $('#swt0403').prop('checked', estaChecado);
            setEstado('04,0403', estaChecado);
        }

        setEstado('040301', estaChecado);
    });

    $('#swt040302').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt04').prop('checked', estaChecado);
            $('#swt0403').prop('checked', estaChecado);
            setEstado('04,0403', estaChecado);
        }
        setEstado('040302', estaChecado);
    });



    $('#swt0403').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt040301').prop('checked', estaChecado);
        $('#swt040302').prop('checked', estaChecado);

        setEstado('0403,040301,040302', estaChecado);
    });

    $('#swt05').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt0501').prop('checked', estaChecado);
        $('#swt0502').prop('checked', estaChecado);
        $('#swt0503').prop('checked', estaChecado);

        setEstado('05,0501,0502,0503', estaChecado);
    });

    $('#swt0501').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt05').prop('checked', estaChecado);
            setEstado('05', estaChecado);
        }
        setEstado('0501', estaChecado);
    });

    $('#swt0502').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt05').prop('checked', estaChecado);
            setEstado('05', estaChecado);
        }
        setEstado('0502', estaChecado);
    });

    $('#swt0503').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt05').prop('checked', estaChecado);
            setEstado('05', estaChecado);
        }
        setEstado('0503', estaChecado);
    });


    $('#swt06').on('change', function () {

        const estaChecado = $(this).prop('checked');
        $('#swt0601').prop('checked', estaChecado);
        $('#swt0602').prop('checked', estaChecado);
        $('#swt0603').prop('checked', estaChecado);
        $('#swt0604').prop('checked', estaChecado);
        $('#swt0605').prop('checked', estaChecado);

        setEstado('06,0601,0602,0603,0604,0605', estaChecado);
    });

    $('#swt0601').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt06').prop('checked', estaChecado);
            setEstado('06', estaChecado);
        }
        setEstado('0601', estaChecado);
    });

    $('#swt0602').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt06').prop('checked', estaChecado);
            setEstado('06', estaChecado);
        }
        setEstado('0602', estaChecado);
    });

    $('#swt0603').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt06').prop('checked', estaChecado);
            setEstado('06', estaChecado);
        }
        setEstado('0603', estaChecado);
    });

    $('#swt0604').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt06').prop('checked', estaChecado);
            setEstado('06', estaChecado);
        }
        setEstado('0604', estaChecado);
    });

    $('#swt0605').on('change', function () {

        const estaChecado = $(this).prop('checked');
        if (estaChecado) {
            $('#swt06').prop('checked', estaChecado);
            setEstado('06', estaChecado);
        }
        setEstado('0605', estaChecado);
    });

    $('#cboPerfil').on('change', onPerfilChange);

    async function onPerfilChange(e) {
        const perfilId = e.target.value;

        if (!perfilId) {
            alert('Selecione um perfil válido.');
            return;
        }

        const url = `${API_URL}adminperfil/listar-por-perfil`;

        try {
            const payLoad = {
                perfilId: perfilId
            };

            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: JSON.stringify(payLoad)

            });

            if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.mensagem}`);

            const result = await res.json();

            // Normaliza formatos comuns de resposta
            const rows = Array.isArray(result?.permissoes) ? result.permissoes
                : [];

            if (!rows.length) {
                console.info('Nenhuma permissão retornada para o perfil:', perfilId);
                return;
            }

            // Loga um a um
            rows.forEach(r => {
                const Permissao = r.Permissao;
                const flAtivo = Number(r.flAtivo ?? 0);
                const estaChecado = flAtivo == 1 ? true : false;
                $('#swt' + Permissao).prop('checked', estaChecado)
            });

            // Tabela resumida no console (opcional)
            console.table(rows.map(r => ({
                Permissao: r.Permissao ?? r.permissao ?? r.codigo ?? r.Codigo ?? '',
                flAtivo: Number(r.flAtivo ?? r.FlAtivo ?? r.ativo ?? 0)
            })));
        } catch (err) {
            console.error('Erro ao consultar permissões do perfil:', err);
            // Se quiser exibir no UI (com SweetAlert2, por exemplo):
            // Swal?.fire({ icon:'error', title:'Erro', text: err.message || 'Falha ao consultar a API.' });
        }
    }
});

async function setEstado(estado, estaChecado) { // <-- Tornar a função assíncrona
    const perfil = $('#cboPerfil').val();
    const aEstado = estado.split(',');
    // Uso do operador ternário para garantir 0 ou 1
    const checkedIt = estaChecado ? 1 : 0;
    const url = `${API_URL}adminperfil/set-estado-perfil`;

    if (perfil === "") {
        Swal?.fire({ icon: 'error', title: 'Erro', text: 'Escolha um Perfil' });
        return;
    }

    const payLoad = {
        perfil: perfil,
        checked: checkedIt,
        permissoes: aEstado
    };

    try {
        // 1. ESPERA a resposta da rede
        const res = await fetch(url, {
            method: 'POST',
            // Adicionar 'Content-Type' é crucial para a API saber que é JSON
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payLoad)
        });

        // 2. Trata erros HTTP (4xx, 5xx)
        if (!res.ok) {
            // Tenta pegar a mensagem de erro da API, se houver
            const errorResult = await res.json();
            throw new Error(errorResult?.mensagem || `Falha na requisição: HTTP ${res.status}`);
        }

        // 3. ESPERA e decodifica o JSON de sucesso (Ex: {status: 'ok', mensagem: '...'})
        const result = await res.json();

        // 4. Exibe a mensagem de sucesso
        if (result.status === 'ok') {
            console.log({ icon: 'success', title: 'Sucesso', text: result.mensagem });
        } else {
            // Caso o status não seja 'ok', mas o HTTP seja 200 (lógica da API)
            throw new Error(result.mensagem || 'Operação concluída, mas com status inesperado.');
        }


    } catch (err) {
        console.error('Erro ao atualizar permissões:', err);
        // Exibir o erro para o usuário
        Swal?.fire({
            icon: 'error',
            title: 'Erro',
            text: err.message || 'Falha ao comunicar com a API.'
        });
    }
}