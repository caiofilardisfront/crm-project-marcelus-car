$(document).ready(function () {
    // 1. Carrega os leads assim que a página abre
    carregarLeads();
    atualizarKPIs();
    carregarOpcoesVeiculos();

    $('#filtro-tempo-grafico').on('change', function () {
        atualizarGrafico();
    });

    /**
     * ========================================================
     * NOVA TAREFA: DELEGAÇÃO DE EVENTO PARA SALVAR NOVO LEAD (POST)
     * ========================================================
     */
    $('#form-add-lead').on('submit', function (e) {
        // 1. Bloqueia o comportamento padrão do navegador de recarregar a página
        e.preventDefault();

        // 2. Isolamos os elementos para manipulá-los
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();

        // 3. UX Premium: Trava o botão e mostra o 'spinner' (Loading)
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...');

        // 4. Dispara a requisição assíncrona (AJAX) para a nossa nova API
        $.ajax({
            url: 'api/leads/add.php',
            type: 'POST',
            data: form.serialize(), // Transforma todos os campos do form em um pacote de dados seguro
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // A) Esconde o modal suavemente
                    $('#modal-add-lead').modal('hide');

                    // B) Limpa todos os inputs do formulário para a próxima vez
                    form[0].reset();

                    // C) Dispara o nosso Toast elegante de sucesso
                    mostrarToast(response.message, 'success');

                    // D) A MÁGICA: Recarrega a tabela por trás dos panos para o novo lead aparecer no topo!
                    carregarLeads();
                    atualizarKPIs();
                } else {
                    mostrarToast(response.message, 'danger');
                }
            },
            error: function (xhr) {
                console.error("Erro no servidor:", xhr.responseText);
                let errorMsg = "Erro interno ao salvar lead.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                mostrarToast(errorMsg, 'danger');
            },
            complete: function () {
                // 5. Independente de sucesso ou falha, destrava o botão para o usuário
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });
});

function carregarOpcoesVeiculos() {
    $.ajax({
        url: 'api/vehicles/list_all.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const veiculos = response.data;
                const selectAdd = $('#select-veiculos-add');
                const selectEdit = $('#select-veiculos-edit');

                veiculos.forEach(v => {
                    const option = `<option value="${v.id}">${v.brand} ${v.model} (${v.manufacture_year}) - R$ ${parseFloat(v.price).toLocaleString('pt-BR')}</option>`;
                    selectAdd.append(option);
                    selectEdit.append(option);
                });
            }
        }
    });
}

/**
 * Função responsável por buscar os dados na API e preparar a tabela
 */
function carregarLeads(status = 'all', termoBusca = '') {
    const termoBusca = $('#input-pesquisa-leads').val() || '';
    console.log("Iniciando busca de leads na API...");

    // -> Atualiza os cards de cima junto com a tabela!
    atualizarKPIs();
    atualizarGrafico();

    $.ajax({
        url: 'api/leads/list_all.php',
        type: 'GET',
        data: { 
            status: status, 
            search: termoBusca 
        },
        dataType: 'json',

        success: function (response) {
            console.log("Resposta da API recebida:", response);

            // 1. Pegamos a nossa tabela
            const tabela = $('#tabela-leads');

            // 2. Limpamos qualquer HTML que esteja lá dentro (para não duplicar)
            tabela.empty();

            // 3. Verificamos se vieram dados
            if (response.length === 0) {
                // Se o array for vazio, mostramos essa mensagem elegante
                tabela.append(`
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Nenhum lead encontrado no momento.
                        </td>
                    </tr>
                `);
                return; // Encerra a função aqui
            }

            // 4. Se passou do if, significa que temos leads! Vamos varrer o array.
            response.forEach(function (lead) {

                // Tratamento de status para definir a cor da etiqueta (Badge)
                // Agora 100% sincronizado com o ENUM da tabela 'leads' no banco de dados
                let statusBadge = '';

                switch (lead.status) {
                    case 'new':
                        statusBadge = '<span class="badge bg-primary">Novo</span>';
                        break;
                    case 'in_progress':
                        statusBadge = '<span class="badge bg-warning text-dark">Em Negociação</span>';
                        break;
                    case 'proposal_sent':
                        statusBadge = '<span class="badge bg-info text-dark">Proposta Enviada</span>';
                        break;
                    case 'won':
                        statusBadge = '<span class="badge bg-success">Vendido</span>';
                        break;
                    case 'lost':
                        statusBadge = '<span class="badge bg-danger">Perdido</span>';
                        break;
                    default:
                        // Fallback seguro caso um status inesperado venha da API
                        statusBadge = `<span class="badge bg-secondary">${lead.status || 'Novo'}</span>`;
                }

                // Lógica de design para o texto do veículo
                const nomeVeiculo = (lead.brand && lead.model)
                    ? `${lead.brand} ${lead.model}`
                    : (lead.vehicle_interest || 'A definir');

                // ========================================================
                // TAREFA 3: MOTOR DE INTELIGÊNCIA DE AGENDA (FOLLOW-UP)
                // ========================================================
                let alertaAgenda = '';

                if (lead.next_contact_at) {
                    // Converte a data do banco para o formato do navegador
                    const dataAgendada = new Date(lead.next_contact_at);
                    const agora = new Date();

                    // Cria limites do dia de "Hoje" para facilitar a comparação
                    const hojeInicio = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
                    const hojeFim = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate(), 23, 59, 59);

                    if (dataAgendada < agora) {
                        // Atrasado: O horário exato agendado já ficou para trás
                        alertaAgenda = '<span class="badge bg-danger ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Retorno Atrasado!"><i class="bi bi-alarm-fill"></i> Atrasado</span>';
                    } else if (dataAgendada >= hojeInicio && dataAgendada <= hojeFim) {
                        // Hoje: A data é hoje, mas o horário exato ainda vai chegar
                        alertaAgenda = '<span class="badge bg-warning text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Ligar Hoje!"><i class="bi bi-calendar-event-fill"></i> Hoje</span>';
                    } else {
                        // Futuro: Agendado para amanhã ou dias seguintes
                        alertaAgenda = '<span class="badge bg-info text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Agendado"><i class="bi bi-calendar-check"></i> Agendado</span>';
                    }
                }

                // 5. Montamos a String HTML da nossa linha Premium
                const tr = `
                <tr class="border-secondary">
                    <td class="py-3 ps-4 align-middle">
                        <div class="fw-bold text-light d-flex align-items-center">
                            ${lead.customer_name} 
                            ${alertaAgenda}
                        </div>
                        <small class="text-muted">Data: ${new Date(lead.created_at).toLocaleDateString('pt-BR')}</small>
                    </td>
                    <td class="py-3 align-middle text-muted small">
                        <div><i class="bi bi-envelope me-1"></i> ${lead.customer_email || 'Sem email'}</div>
                        <div><i class="bi bi-telephone me-1"></i> ${lead.customer_phone || 'Não informado'}</div>
                    </td>
                    <td class="py-3 align-middle fw-semibold text-light">
                        <i class="bi bi-car-front me-1 text-secondary"></i> ${nomeVeiculo}
                    </td>
                    <td class="py-3 align-middle">
                        <!-- Select de atualização rápida do status -->
                        <select class="form-select form-select-sm bg-dark text-light border-secondary select-status" data-id="${lead.id}" style="width: 160px; cursor: pointer;">
                            <option value="new" ${lead.status === 'new' ? 'selected' : ''}>Novo</option>
                            <option value="in_progress" ${lead.status === 'in_progress' ? 'selected' : ''}>Em Negociação</option>
                            <option value="proposal_sent" ${lead.status === 'proposal_sent' ? 'selected' : ''}>Proposta Enviada</option>
                            <option value="won" ${lead.status === 'won' ? 'selected' : ''}>Vendido</option>
                            <option value="lost" ${lead.status === 'lost' ? 'selected' : ''}>Perdido</option>
                        </select>
                    </td>
                    <td class="py-3 pe-4 align-middle text-end">
                        <button class="btn btn-sm btn-outline-light me-1 btn-ver-detalhes" data-id="${lead.id}" title="Ver Detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary me-1 btn-editar-lead" data-id="${lead.id}" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-excluir-lead" data-id="${lead.id}" title="Excluir">
    <i class="bi bi-trash"></i>
</button>
                    </td>
                </tr>
            `;

                // 6. Injetamos a linha pronta no final da tabela
                tabela.append(tr);
            });
        },

        error: function (xhr, status, error) {
            console.error("Erro crítico ao buscar leads:", error);
            $('#tabela-leads').html(`
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao carregar os dados.
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * ========================================================
 * Delegação de Eventos: Atualização de Status
 * ========================================================
 */
$(document).on('change', '.select-status', function () {
    // Salvamos o 'this' em uma variável para não perdermos a referência dentro das funções
    const selectElement = $(this);

    // 1. Pegamos os dados do select
    const lead_id = selectElement.data('id');
    const new_status = selectElement.val();

    // Feedback visual (fica meio transparente enquanto carrega)
    selectElement.addClass('opacity-50');

    // 2. O famoso $.post (mais limpo e direto que o $.ajax)
    $.post('api/leads/update_status.php', {
        lead_id: lead_id,
        status: new_status
    }, function (response) {

        if (response.status === 'success') {
            selectElement.removeClass('opacity-50');
            // Chama a nossa nova função de Toast (Sucesso)
            mostrarToast(response.message, 'success');
            atualizarKPIs();
        }

    }, 'json') // Avisamos que esperamos um JSON de volta
        .fail(function () {
            // Se a API der erro 500 ou não encontrar o arquivo
            selectElement.removeClass('opacity-50');
            mostrarToast("Falha ao atualizar o status. Tente novamente.", 'danger');

            // Recarrega a tabela para voltar o select para a opção anterior
            carregarLeads();
        });
});

/**
 * ========================================================
 * Função Helper: Mostrar Toast do Bootstrap dinamicamente
 * ========================================================
 */
function mostrarToast(mensagem, cor = 'success') {
    // 1. Verifica se já existe um contêiner de toasts na tela. Se não, cria um no canto inferior direito.
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>');
    }

    // 2. Gera um ID único para este toast específico
    const toastId = 'toast-' + Date.now();

    // 3. Ícone dinâmico baseado na cor
    const icone = cor === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';

    // 4. Monta o HTML do Toast (Padrão Bootstrap 5)
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${cor} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    ${icone} ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    // 5. Injeta o HTML no container
    $('#toast-container').append(toastHTML);

    // 6. Inicializa o Toast usando o JS Nativo do Bootstrap e dá o 'play' (show)
    const toastElement = new bootstrap.Toast(document.getElementById(toastId), { delay: 3000 });
    toastElement.show();

    // 7. Limpeza: Quando o Toast sumir, ele se auto-apaga do HTML para não deixar lixo!
    $('#' + toastId).on('hidden.bs.toast', function () {
        $(this).remove();
    });
}

/**
 * ========================================================
 * Delegação de Eventos: Abrir Modal de Detalhes
 * ========================================================
 */
$(document).on('click', '.btn-ver-detalhes', function () {
    const btn = $(this);
    const leadId = btn.data('id');

    // 1. Efeito Premium: Troca o ícone do olho por um spinner girando (Loading)
    const iconeOriginal = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    btn.prop('disabled', true); // Trava o botão para não clicarem duas vezes

    // 2. Chama a nossa API passando o ID
    $.ajax({
        url: 'api/leads/get_details.php',
        type: 'GET',
        data: { id: leadId }, // Isso vira get_details.php?id=X
        dataType: 'json',

        success: function (response) {
            // Devolve o ícone original e destrava o botão
            btn.html(iconeOriginal);
            btn.prop('disabled', false);

            if (response.status === 'success') {
                const lead = response.data;

                // 3. Preenche os campos do Modal
                $('#modal-nome-cliente').text(lead.customer_name);
                const dataCriacao = new Date(lead.created_at).toLocaleDateString('pt-BR');
                $('#modal-data-criacao').html(`<i class="bi bi-calendar3 me-1"></i> Cadastrado em: ${dataCriacao}`);
                $('#modal-email-cliente').text(lead.customer_email || 'Não informado');
                $('#modal-telefone-cliente').text(lead.customer_phone || 'Não informado');
                $('#modal-note-lead-id').val(lead.id);

                // Formatação Premium do Veículo de Interesse
                let textoVeiculo = 'A definir';
                if (lead.brand && lead.model) {
                    textoVeiculo = `${lead.brand} ${lead.model}`;
                    // Adiciona Ano e Preço se existirem para dar mais contexto ao vendedor
                    if (lead.manufacture_year) textoVeiculo += ` (${lead.manufacture_year})`;
                    if (lead.price) {
                        textoVeiculo += ` - R$ ${parseFloat(lead.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                    }
                } else if (lead.vehicle_interest) {
                    textoVeiculo = lead.vehicle_interest;
                }

                $('#modal-veiculo-interesse').text(textoVeiculo);

                // 4. Lógica da Etiqueta (Badge) de Status
                let statusBadge = '';
                switch (lead.status) {
                    case 'new': statusBadge = '<span class="badge bg-primary">Novo</span>'; break;
                    case 'in_progress': statusBadge = '<span class="badge bg-warning text-dark">Em Negociação</span>'; break;
                    case 'proposal_sent': statusBadge = '<span class="badge bg-info text-dark">Proposta Enviada</span>'; break;
                    case 'won': statusBadge = '<span class="badge bg-success">Vendido</span>'; break;
                    case 'lost': statusBadge = '<span class="badge bg-danger">Perdido</span>'; break;
                    default: statusBadge = `<span class="badge bg-secondary">${lead.status || 'Novo'}</span>`;
                }
                $('#modal-status-badge').html(statusBadge);

                // 5. Lógica de Observações (Buscando o histórico na tabela lead_interactions via get_logs.php)
                const obsContainer = $('#modal-observacoes');

                // Injeta um Loading premium enquanto a segunda API busca os dados
                obsContainer.html(`
                        <div class="d-flex align-items-center text-primary py-2">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> 
                            <span style="font-size: 0.85rem;">Buscando histórico de interações...</span>
                        </div>
                    `);

                // Dispara a chamada AJAX para o endpoint de logs
                $.ajax({
                    url: 'api/leads/get_logs.php',
                    type: 'GET',
                    data: { lead_id: leadId }, // Passa o ID do Lead
                    dataType: 'json',
                    success: function (logResponse) {
                        if (logResponse.status === 'success' && logResponse.data.length > 0) {
                            let logsHtml = '<div class="d-flex flex-column gap-2">';

                            // Varre o array de interações e monta o layout do histórico
                            logResponse.data.forEach(function (log) {
                                const dataLog = new Date(log.created_at).toLocaleString('pt-BR');
                                let badgeType = 'bg-secondary';
                                let icone = '<i class="bi bi-sticky"></i>';
                                let logTypeName = 'Anotação';
                                let textoConteudo = log.content;

                                // Lógica de design combinando com o ENUM da tabela lead_interactions
                                switch (log.type) {
                                    case 'status_change':
                                        badgeType = 'bg-info text-dark';
                                        icone = '<i class="bi bi-arrow-left-right"></i>';
                                        logTypeName = 'Status';

                                        // 2. Dicionário de tradução (De-Para)
                                        const statusMap = {
                                            'new': 'Novo',
                                            'in_progress': 'Em Negociação',
                                            'proposal_sent': 'Proposta Enviada',
                                            'won': 'Vendido',
                                            'lost': 'Perdido'
                                        };

                                        // 3. Substitui a palavra em inglês pela versão elegante em português
                                        Object.keys(statusMap).forEach(key => {
                                            textoConteudo = textoConteudo.replace(key, `<strong>${statusMap[key]}</strong>`);
                                        });
                                        break;
                                    case 'whatsapp':
                                        badgeType = 'bg-success';
                                        icone = '<i class="bi bi-whatsapp"></i>';
                                        logTypeName = 'WhatsApp';
                                        break;
                                    case 'call':
                                        badgeType = 'bg-warning text-dark';
                                        icone = '<i class="bi bi-telephone"></i>';
                                        logTypeName = 'Ligação';
                                        break;
                                    case 'note':
                                    default:
                                        badgeType = 'bg-secondary';
                                        icone = '<i class="bi bi-journal-text"></i>';
                                        logTypeName = 'Nota';
                                        break;
                                }

                                logsHtml += `
                                        <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge ${badgeType} d-flex align-items-center gap-1" style="font-size: 0.70rem;">
                                                    ${icone} ${logTypeName}
                                                </span>
                                                <small class="text-muted" style="font-size: 0.70rem;">${dataLog}</small>
                                            </div>
                                            <!-- Aqui usamos a variável textoConteudo traduzida em vez do log.content puro -->
                                            <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">
                                                ${textoConteudo} 
                                            </div>
                                        </div>
                                    `;
                            });

                            logsHtml += '</div>';
                            obsContainer.html(logsHtml);
                        } else {
                            // Caso não haja nenhum registro na API
                            obsContainer.html('<span class="text-muted fst-italic">Nenhum registro de interação para este cliente.</span>');
                        }
                    },
                    error: function () {
                        // Tratamento de falha silenciosa para a API secundária
                        obsContainer.html('<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Falha ao carregar o histórico.</span>');
                    }
                });

                // 6. Lógica Premium do WhatsApp
                const btnWhats = $('#btn-whatsapp-lead');
                if (lead.customer_phone) {
                    // Limpa o número de telefone tirando parênteses, traços e espaços
                    let numeroLimpo = lead.customer_phone.replace(/\D/g, '');

                    // Mostra o botão e configura o link direto para o WhatsApp Web/App
                    btnWhats.show().off('click').on('click', function () {
                        window.open(`https://wa.me/55${numeroLimpo}`, '_blank');
                    });
                } else {
                    // Se não tiver telefone, esconde o botão do WhatsApp
                    btnWhats.hide();
                }

                // 7. Comando do Bootstrap 5 para exibir o modal na tela
                const modal = new bootstrap.Modal(document.getElementById('modal-lead'));
                modal.show();

            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr, status, error) {
            console.error("Erro ao buscar detalhes:", error);
            btn.html(iconeOriginal);
            btn.prop('disabled', false);
            mostrarToast("Erro de comunicação com o servidor.", 'danger');
        }
    });
});

/**
 * ========================================================
 * DELEGAÇÃO DE EVENTOS: MOTOR DE NOVA ANOTAÇÃO (LOG)
 * ========================================================
 */
$(document).on('submit', '#form-add-note', function (e) {
    e.preventDefault();

    const form = $(this);
    const btnSubmit = form.find('button[type="submit"]');
    const iconeOriginal = btnSubmit.html();

    // 1. Capturamos os dados ANTES de limpar o formulário
    const conteudoAnotacao = $('#modal-note-content').val();
    const dataAgendada = $('#modal-note-date').val();

    // Efeito visual Premium: Loading no botão
    btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

    $.ajax({
        url: 'api/leads/add_note.php',
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                // Limpa o form e mostra o aviso de sucesso
                form[0].reset();
                mostrarToast(response.message, 'success');

                // Cria a div base da timeline se ela estiver vazia
                if ($('#modal-observacoes .d-flex.flex-column').length === 0) {
                    $('#modal-observacoes').html('<div class="d-flex flex-column gap-2"></div>');
                }

                // 2. Injeta a anotação principal de texto primeiro (ficará embaixo do agendamento)
                const novaAnotacaoHTML = `
                    <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary d-flex align-items-center gap-1" style="font-size: 0.70rem;">
                                <i class="bi bi-journal-text"></i> Nota
                            </span>
                            <small class="text-muted fw-bold" style="font-size: 0.70rem; color: var(--brand-primary) !important;">Agora mesmo</small>
                        </div>
                        <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">
                            ${conteudoAnotacao}
                        </div>
                    </div>
                `;
                $('#modal-observacoes .d-flex.flex-column').prepend(novaAnotacaoHTML);

                // 3. A MÁGICA: Se houver data, cria e injeta o card do agendamento por cima de tudo
                if (dataAgendada) {
                    // Converte de YYYY-MM-DDTHH:MM para DD/MM/AAAA às HH:MM com o JS
                    const dataObj = new Date(dataAgendada);
                    const dataFormatada = dataObj.toLocaleDateString('pt-BR') + ' às ' + dataObj.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                    const agendamentoHTML = `
                        <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary d-flex align-items-center gap-1" style="font-size: 0.70rem;">
                                    <i class="bi bi-journal-text"></i> Nota
                                </span>
                                <small class="text-muted fw-bold" style="font-size: 0.70rem; color: var(--brand-primary) !important;">Agora mesmo</small>
                            </div>
                            <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">
                                🕒 <strong>Retorno agendado para:</strong> ${dataFormatada}
                            </div>
                        </div>
                    `;
                    $('#modal-observacoes .d-flex.flex-column').prepend(agendamentoHTML);

                    // Como nós agendamos uma data nova, pedimos para a tabela de trás recarregar os leads ocultamente
                    // (Isso será crucial para a Tarefa 3 que vem a seguir)
                    carregarLeads();
                }

            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr) {
            let errorMsg = "Erro crítico ao salvar anotação.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            mostrarToast(errorMsg, 'danger');
        },
        complete: function () {
            btnSubmit.prop('disabled', false).html(iconeOriginal);
        }
    });
});

/**
 * ========================================================
 * DELEGAÇÃO DE EVENTOS: ABRIR MODAL DE EDIÇÃO
 * ========================================================
 */
$(document).on('click', '.btn-editar-lead', function () {
    const btn = $(this);
    const leadId = btn.data('id'); // Pega o ID do lead injetado no botão [1]
    const iconeOriginal = btn.html();

    // 1. Feedback visual Premium: Spinner no botão de edição
    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    btn.prop('disabled', true);

    // 2. Reutilizamos a API de detalhes para buscar os dados atuais do banco [5, 6]
    $.ajax({
        url: 'api/leads/get_details.php',
        type: 'GET',
        data: { id: leadId },
        dataType: 'json',
        success: function (response) {
            // Devolve o ícone original ao botão
            btn.html(iconeOriginal);
            btn.prop('disabled', false);

            if (response.status === 'success') {
                const lead = response.data;

                // 3. O Pulo do Gato: Preenchemos cada campo do modal de edição com os dados reais [2-4, 7, 8]
                $('#edit_lead_id').val(lead.id);
                $('#edit_customer_name').val(lead.customer_name);
                $('#edit_customer_phone').val(lead.customer_phone);
                $('#edit_customer_email').val(lead.customer_email || '');
                $('#edit_origin_id').val(lead.origin_id);
                $('#edit_vehicle_interest').val(lead.vehicle_interest || '');

                // 4. Disparamos a abertura do Modal de Edição via Bootstrap [9]
                const modalEdit = new bootstrap.Modal(document.getElementById('modal-edit-lead'));
                modalEdit.show();

            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function () {
            btn.html(iconeOriginal);
            btn.prop('disabled', false);
            mostrarToast("Erro ao buscar dados do cliente para edição.", 'danger');
        }
    });
});

/**
 * ========================================================
 * DELEGAÇÃO DE EVENTOS: SALVAR ALTERAÇÕES DO LEAD (POST)
 * ========================================================
 */
$(document).on('submit', '#form-edit-lead', function (e) {
    // 1. Bloqueia o recarregamento da página (F5)
    e.preventDefault();

    const form = $(this);
    const btnSubmit = form.find('button[type="submit"]');
    const textoOriginal = btnSubmit.html();

    // 2. Feedback visual Premium: Spinner no botão
    btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...');

    // 3. Dispara a requisição para a nossa nova API de Update
    $.ajax({
        url: 'api/leads/update.php',
        type: 'POST',
        data: form.serialize(), // Envia todos os campos do modal de uma vez
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                // A) Fecha o modal de edição suavemente
                $('#modal-edit-lead').modal('hide');

                // B) Limpa o formulário usando o elemento nativo 
                form[0].reset();

                // C) Exibe o brinde de sucesso no canto da tela
                mostrarToast(response.message, 'success');

                // D) A MÁGICA: Recarrega a tabela principal silenciosamente
                carregarLeads();
                atualizarKPIs();
            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr) {
            let errorMsg = "Erro crítico ao atualizar o lead.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            mostrarToast(errorMsg, 'danger');
        },
        complete: function () {
            // 4. Independente do resultado, devolve o estado original ao botão
            btnSubmit.prop('disabled', false).html(textoOriginal);
        }
    });
});

/**
 * ========================================================
 * MOTOR DE EXCLUSÃO PREMIUM (CUSTOM MODAL + AJAX)
 * ========================================================
 */
let leadIdParaExcluir = null; // Variável global temporária para o ID

// 1. Ao clicar na lixeira da tabela, apenas abre o modal customizado
$(document).on('click', '.btn-excluir-lead', function () {
    leadIdParaExcluir = $(this).data('id'); // Guarda o ID do lead [4]
    const modalConfirm = new bootstrap.Modal(document.getElementById('modal-confirm-delete'));
    modalConfirm.show();
});

/**
 * ========================================================
 * DELEGAÇÃO DE EVENTOS: EXCLUIR LEAD (DELETE)
 * ========================================================
 */
$(document).on('click', '#btn-confirmar-exclusao-real', function () {
    const btn = $(this);
    const textoOriginal = btn.html();

    if (!leadIdParaExcluir) return;

    // Feedback visual de carregamento no botão do modal
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Excluindo...');

    $.ajax({
        url: 'api/leads/delete.php',
        type: 'POST',
        data: { lead_id: leadIdParaExcluir },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                // A) Fecha o modal de confirmação
                $('#modal-confirm-delete').modal('hide');

                // B) Exibe o Toast Premium que você tanto gostou [5]
                mostrarToast(response.message, 'success');

                // C) Recarrega a tabela por trás [6]
                carregarLeads();
                atualizarKPIs();
            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr) {
            mostrarToast("Erro crítico na comunicação com o servidor.", 'danger');
        },
        complete: function () {
            // Limpeza e reset do botão
            btn.prop('disabled', false).html(textoOriginal);
            leadIdParaExcluir = null;
        }
    });
});

/**
 * ========================================================
 * MOTOR DE ESTATÍSTICAS: ATUALIZA OS CARDS DE KPI
 * ========================================================
 */
function atualizarKPIs() {
    $.ajax({
        url: 'api/leads/get_kpis.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const dados = response.data;
                
                // Injeta os números com uma leve animação de opacidade (UX Premium)
                $('#kpi-total-leads').hide().text(dados.total_leads).fadeIn(300);
                $('#kpi-em-negociacao').hide().text(dados.negociacao_ativa).fadeIn(300);
                $('#kpi-fechados').hide().text(dados.total_won).fadeIn(300);
                $('#kpi-perdidos').hide().text(dados.total_lost).fadeIn(300);
            }
        },
        error: function () {
            console.error("Falha ao carregar os KPIs do Dashboard.");
        }
    });
}

/**
 * ========================================================
 * EVENTO: FILTRAGEM RÁPIDA DE LEADS
 * ========================================================
 */
$(document).on('click', '.btn-filtro', function() {
    const btn = $(this);
    const statusEscolhido = btn.data('status'); // Pega o valor do data-status que criamos no HTML [4]

    // 1. UX Visual: Gerencia as classes dos botões (Toggle active)
    $('.btn-filtro').removeClass('btn-primary').addClass('btn-outline-secondary text-light');
    
    // Especial: Se for o botão 'Todos', 'Vendido' ou 'Perdido', aplicamos cores semânticas
    if(statusEscolhido === 'all') {
        btn.addClass('btn-primary').removeClass('btn-outline-secondary text-light');
    } else if(statusEscolhido === 'won') {
        btn.addClass('btn-success').removeClass('btn-outline-secondary');
    } else if(statusEscolhido === 'lost') {
        btn.addClass('btn-danger').removeClass('btn-outline-secondary');
    } else {
        btn.addClass('btn-primary').removeClass('btn-outline-secondary text-light');
    }

    // 2. Ação Real: Recarrega apenas a tabela com o filtro novo
    carregarLeads(statusEscolhido);
});

/**
 * ========================================================
 * EVENTO: PESQUISA EM TEMPO REAL (LIVE SEARCH)
 * ========================================================
 */
let timerPesquisa; // Variável global para o controle do Debounce

$(document).on('input', '#input-pesquisa-leads', function() {
    // 1. Limpa o timer anterior toda vez que o usuário digita uma letra
    clearTimeout(timerPesquisa);

    // 2. Define um novo timer de 300ms
    timerPesquisa = setTimeout(function() {
        // A) Identifica qual status está selecionado no momento nos botões de filtro
        // Procuramos o botão que não tem a classe 'btn-outline-secondary' (ou seja, o ativo)
        const statusAtivo = $('.btn-filtro.btn-primary, .btn-filtro.btn-success, .btn-filtro.btn-danger').data('status') || 'all';
        
        // B) Dispara a busca atualizando a tabela
        carregarLeads(statusAtivo);
        
    }, 300); // Espera 300ms de silêncio no teclado para agir
});

/**
 * ========================================================
 * EVENTO: BARRA DE PESQUISA EM TEMPO REAL
 * ========================================================
 */
$(document).on('keyup', '#input-busca', function() {
    // 1. Pega no que o utilizador acabou de digitar
    const valorDigitado = $(this).val();
    
    // 2. Verifica qual botão de filtro (Novos, Vendidos, etc) está activo no momento
    const statusAtivo = $('.btn-filtro.btn-primary, .btn-filtro.btn-success, .btn-filtro.btn-danger').data('status') || 'all';
    
    // 3. Dispara a busca cruzando as duas informações!
    carregarLeads(statusAtivo, valorDigitado);
});

/**
 * ========================================================
 * MOTOR DE RENDERIZAÇÃO: GRÁFICO CHART.JS
 * ========================================================
 */
let graficoPerformance = null; // Variável global para guardar a instância do gráfico

function atualizarGrafico() {
    const periodoSelecionado = $('#filtro-tempo-grafico').val();

    $.ajax({
        url: 'api/leads/get_chart_data.php',
        type: 'GET',
        data: { periodo: periodoSelecionado },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                desenharGraficoPerformance(response.data);
            }
        },
        error: function () {
            console.error("Falha ao carregar os dados do gráfico.");
        }
    });
}

function desenharGraficoPerformance(dados) {
    const canvas = document.getElementById('grafico-performance');
    const ctx = canvas.getContext('2d');

    // REGRA DE OURO DO CHART.JS: Destruir o gráfico antigo antes de desenhar o novo
    if (graficoPerformance !== null) {
        graficoPerformance.destroy();
    }

    // UX Premium: Criando gradientes de preenchimento para as linhas
    const gradientLeads = ctx.createLinearGradient(0, 0, 0, 400);
    gradientLeads.addColorStop(0, 'rgba(239, 68, 68, 0.4)'); // brand-primary (vermelho) com opacidade
    gradientLeads.addColorStop(1, 'rgba(239, 68, 68, 0.0)'); // some no final

    const gradientVendas = ctx.createLinearGradient(0, 0, 0, 400);
    gradientVendas.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); // verde (success) com opacidade
    gradientVendas.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    // Inicia a nova pintura
    graficoPerformance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.labels, // As datas formatadas (Eixo X)
            datasets: [
                {
                    label: 'Total de Leads Gerados',
                    data: dados.leads,
                    borderColor: '#ef4444',
                    backgroundColor: gradientLeads,
                    borderWidth: 3,
                    tension: 0.4, // Curva suave na linha
                    fill: true,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Carros Vendidos',
                    data: dados.vendas,
                    borderColor: '#10b981',
                    backgroundColor: gradientVendas,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index', // Ao passar o mouse, mostra os dois valores ao mesmo tempo
                intersect: false,
            },
            plugins: {
                legend: {
                    labels: { color: '#a1a1aa', font: { family: "'Inter', sans-serif", size: 13 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(24, 24, 27, 0.95)', // Fundo dark do nosso CSS
                    titleColor: '#f4f4f5',
                    bodyColor: '#a1a1aa',
                    borderColor: '#27272a',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                x: {
                    grid: { color: '#27272a', drawBorder: false }, // Linhas de grade sutis
                    ticks: { color: '#a1a1aa' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#27272a', drawBorder: false },
                    ticks: { color: '#a1a1aa', stepSize: 1 } // Como vendemos carros, pula de 1 em 1
                }
            }
        }
    });
}