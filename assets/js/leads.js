$(document).ready(function () {
    // 1. Inicializa o Funil
    carregarLeads();
    carregarOpcoesVeiculos();

    // 2. MÁGICA: Live Search Otimizado (Debounce)
    let timerPesquisa;
    $(document).on('input', '#input-busca', function() {
        clearTimeout(timerPesquisa);
        const termoBusca = $(this).val();
        
        timerPesquisa = setTimeout(function() {
            const statusAtivo = $('.btn-filtro.btn-primary, .btn-filtro.btn-success, .btn-filtro.btn-danger').data('status') || 'all';
            carregarLeads(statusAtivo, termoBusca);
        }, 300);
    });

    // 3. Cadastrar Novo Lead (POST)
    $('#form-add-lead').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();

        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...');

        $.ajax({
            url: 'api/leads/add.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#modal-add-lead').modal('hide');
                    form.reset();
                    mostrarToast(response.message, 'success');
                    carregarLeads(); // Apenas recarrega a tabela, sem KPIs!
                } else {
                    mostrarToast(response.message, 'danger');
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || "Erro interno ao salvar lead.";
                mostrarToast(errorMsg, 'danger');
            },
            complete: function () {
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

function carregarLeads(status = 'all', termoBusca = '') {
    $.ajax({
        url: 'api/leads/list_all.php',
        type: 'GET',
        data: { status: status, search: termoBusca },
        dataType: 'json',
        success: function (response) {
            const tabela = $('#tabela-leads');
            tabela.empty();

            if (response.length === 0) {
                tabela.append(`<tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>Nenhum lead encontrado no momento.</td></tr>`);
                return;
            }

            response.forEach(function (lead) {
                let statusBadge = '';
                switch (lead.status) {
                    case 'new': statusBadge = '<span class="badge bg-primary">Novo</span>'; break;
                    case 'in_progress': statusBadge = '<span class="badge bg-warning text-dark">Em Negociação</span>'; break;
                    case 'proposal_sent': statusBadge = '<span class="badge bg-info text-dark">Proposta Enviada</span>'; break;
                    case 'won': statusBadge = '<span class="badge bg-success">Vendido</span>'; break;
                    case 'lost': statusBadge = '<span class="badge bg-danger">Perdido</span>'; break;
                    default: statusBadge = `<span class="badge bg-secondary">${lead.status || 'Novo'}</span>`;
                }

                const nomeVeiculo = (lead.brand && lead.model) ? `${lead.brand} ${lead.model}` : (lead.vehicle_interest || 'A definir');

                let alertaAgenda = '';
                if (lead.next_contact_at) {
                    const dataAgendada = new Date(lead.next_contact_at);
                    const agora = new Date();
                    const hojeInicio = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
                    const hojeFim = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate(), 23, 59, 59);

                    if (dataAgendada < agora) {
                        alertaAgenda = '<span class="badge bg-danger ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Retorno Atrasado!"><i class="bi bi-alarm-fill"></i> Atrasado</span>';
                    } else if (dataAgendada >= hojeInicio && dataAgendada <= hojeFim) {
                        alertaAgenda = '<span class="badge bg-warning text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Ligar Hoje!"><i class="bi bi-calendar-event-fill"></i> Hoje</span>';
                    } else {
                        alertaAgenda = '<span class="badge bg-info text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;" title="Agendado"><i class="bi bi-calendar-check"></i> Agendado</span>';
                    }
                }

                const tr = `
                <tr class="border-secondary">
                    <td class="py-3 ps-4 align-middle">
                        <div class="fw-bold text-light d-flex align-items-center">${lead.customer_name} ${alertaAgenda}</div>
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
                        <select class="form-select form-select-sm bg-dark text-light border-secondary select-status" data-id="${lead.id}" style="width: 160px; cursor: pointer;">
                            <option value="new" ${lead.status === 'new' ? 'selected' : ''}>Novo</option>
                            <option value="in_progress" ${lead.status === 'in_progress' ? 'selected' : ''}>Em Negociação</option>
                            <option value="proposal_sent" ${lead.status === 'proposal_sent' ? 'selected' : ''}>Proposta Enviada</option>
                            <option value="won" ${lead.status === 'won' ? 'selected' : ''}>Vendido</option>
                            <option value="lost" ${lead.status === 'lost' ? 'selected' : ''}>Perdido</option>
                        </select>
                    </td>
                    <td class="py-3 pe-4 align-middle text-end">
                        <button class="btn btn-sm btn-outline-light me-1 btn-ver-detalhes" data-id="${lead.id}" title="Ver Detalhes"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-primary me-1 btn-editar-lead" data-id="${lead.id}" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-excluir-lead" data-id="${lead.id}" title="Excluir"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
                tabela.append(tr);
            });
        },
        error: function () {
            $('#tabela-leads').html(`<tr><td colspan="5" class="text-center py-4 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao carregar os dados.</td></tr>`);
        }
    });
}

// Filtros Rápidos (Pills)
$(document).on('click', '.btn-filtro', function() {
    const btn = $(this);
    const statusEscolhido = btn.data('status');
    const termoBusca = $('#input-busca').val() || ''; // Lê o input atual

    $('.btn-filtro').removeClass('btn-primary btn-success btn-danger').addClass('btn-outline-secondary text-light');
    
    if(statusEscolhido === 'all') { btn.addClass('btn-primary').removeClass('btn-outline-secondary text-light'); } 
    else if(statusEscolhido === 'won') { btn.addClass('btn-success').removeClass('btn-outline-secondary text-light'); } 
    else if(statusEscolhido === 'lost') { btn.addClass('btn-danger').removeClass('btn-outline-secondary text-light'); } 
    else { btn.addClass('btn-primary').removeClass('btn-outline-secondary text-light'); }

    carregarLeads(statusEscolhido, termoBusca);
});

// Update Status (Select na Tabela)
$(document).on('change', '.select-status', function () {
    const selectElement = $(this);
    const lead_id = selectElement.data('id');
    const new_status = selectElement.val();

    selectElement.addClass('opacity-50');

    $.post('api/leads/update_status.php', { lead_id: lead_id, status: new_status }, function (response) {
        if (response.status === 'success') {
            selectElement.removeClass('opacity-50');
            mostrarToast(response.message, 'success');
        }
    }, 'json').fail(function () {
        selectElement.removeClass('opacity-50');
        mostrarToast("Falha ao atualizar o status.", 'danger');
        carregarLeads();
    });
});

// Ver Detalhes (Modal)
$(document).on('click', '.btn-ver-detalhes', function () {
    const btn = $(this);
    const leadId = btn.data('id');
    const iconeOriginal = btn.html();

    btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

    $.ajax({
        url: 'api/leads/get_details.php',
        type: 'GET',
        data: { id: leadId },
        dataType: 'json',
        success: function (response) {
            btn.html(iconeOriginal).prop('disabled', false);

            if (response.status === 'success') {
                const lead = response.data;
                $('#modal-nome-cliente').text(lead.customer_name);
                $('#modal-data-criacao').html(`<i class="bi bi-calendar3 me-1"></i> Cadastrado em: ${new Date(lead.created_at).toLocaleDateString('pt-BR')}`);
                $('#modal-email-cliente').text(lead.customer_email || 'Não informado');
                $('#modal-telefone-cliente').text(lead.customer_phone || 'Não informado');
                $('#modal-note-lead-id').val(lead.id);

                let textoVeiculo = 'A definir';
                if (lead.brand && lead.model) {
                    textoVeiculo = `${lead.brand} ${lead.model}`;
                    if (lead.manufacture_year) textoVeiculo += ` (${lead.manufacture_year})`;
                    if (lead.price) textoVeiculo += ` - R$ ${parseFloat(lead.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                } else if (lead.vehicle_interest) {
                    textoVeiculo = lead.vehicle_interest;
                }
                $('#modal-veiculo-interesse').text(textoVeiculo);

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

                const obsContainer = $('#modal-observacoes');
                obsContainer.html(`<div class="d-flex align-items-center text-primary py-2"><span class="spinner-border spinner-border-sm me-2"></span> <span style="font-size: 0.85rem;">Buscando histórico...</span></div>`);

                $.ajax({
                    url: 'api/leads/get_logs.php',
                    type: 'GET',
                    data: { lead_id: leadId },
                    dataType: 'json',
                    success: function (logResponse) {
                        if (logResponse.status === 'success' && logResponse.data.length > 0) {
                            let logsHtml = '<div class="d-flex flex-column gap-2">';
                            logResponse.data.forEach(function (log) {
                                const dataLog = new Date(log.created_at).toLocaleString('pt-BR');
                                let badgeType = 'bg-secondary';
                                let icone = '<i class="bi bi-sticky"></i>';
                                let logTypeName = 'Anotação';
                                let textoConteudo = log.content;

                                switch (log.type) {
                                    case 'status_change':
                                        badgeType = 'bg-info text-dark'; icone = '<i class="bi bi-arrow-left-right"></i>'; logTypeName = 'Status';
                                        const statusMap = { 'new': 'Novo', 'in_progress': 'Em Negociação', 'proposal_sent': 'Proposta Enviada', 'won': 'Vendido', 'lost': 'Perdido' };
                                        Object.keys(statusMap).forEach(key => { textoConteudo = textoConteudo.replace(key, `<strong>${statusMap[key]}</strong>`); });
                                        break;
                                    case 'whatsapp': badgeType = 'bg-success'; icone = '<i class="bi bi-whatsapp"></i>'; logTypeName = 'WhatsApp'; break;
                                    case 'call': badgeType = 'bg-warning text-dark'; icone = '<i class="bi bi-telephone"></i>'; logTypeName = 'Ligação'; break;
                                    case 'note': default: badgeType = 'bg-secondary'; icone = '<i class="bi bi-journal-text"></i>'; logTypeName = 'Nota'; break;
                                }

                                logsHtml += `
                                <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge ${badgeType} d-flex align-items-center gap-1" style="font-size: 0.70rem;">${icone} ${logTypeName}</span>
                                        <small class="text-muted" style="font-size: 0.70rem;">${dataLog}</small>
                                    </div>
                                    <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">${textoConteudo}</div>
                                </div>`;
                            });
                            logsHtml += '</div>';
                            obsContainer.html(logsHtml);
                        } else {
                            obsContainer.html('<span class="text-muted fst-italic">Nenhum registro de interação para este cliente.</span>');
                        }
                    },
                    error: function () { obsContainer.html('<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Falha ao carregar o histórico.</span>'); }
                });

                const btnWhats = $('#btn-whatsapp-lead');
                if (lead.customer_phone) {
                    let numeroLimpo = lead.customer_phone.replace(/\D/g, '');
                    btnWhats.show().off('click').on('click', function () { window.open(`https://wa.me/55${numeroLimpo}`, '_blank'); });
                } else {
                    btnWhats.hide();
                }

                new bootstrap.Modal(document.getElementById('modal-lead')).show();
            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function () {
            btn.html(iconeOriginal).prop('disabled', false);
            mostrarToast("Erro de comunicação com o servidor.", 'danger');
        }
    });
});

// Adicionar Anotação / Agendamento (Modal)
$(document).on('submit', '#form-add-note', function (e) {
    e.preventDefault();
    const form = $(this);
    const btnSubmit = form.find('button[type="submit"]');
    const iconeOriginal = btnSubmit.html();
    const conteudoAnotacao = $('#modal-note-content').val();
    const dataAgendada = $('#modal-note-date').val();

    btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: 'api/leads/add_note.php',
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                form.reset();
                mostrarToast(response.message, 'success');

                if ($('#modal-observacoes .d-flex.flex-column').length === 0) {
                    $('#modal-observacoes').html('<div class="d-flex flex-column gap-2"></div>');
                }

                const novaAnotacaoHTML = `
                <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary d-flex align-items-center gap-1" style="font-size: 0.70rem;"><i class="bi bi-journal-text"></i> Nota</span>
                        <small class="text-muted fw-bold" style="font-size: 0.70rem; color: var(--brand-primary) !important;">Agora mesmo</small>
                    </div>
                    <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">${conteudoAnotacao}</div>
                </div>`;
                $('#modal-observacoes .d-flex.flex-column').prepend(novaAnotacaoHTML);

                if (dataAgendada) {
                    const dataObj = new Date(dataAgendada);
                    const dataFormatada = dataObj.toLocaleDateString('pt-BR') + ' às ' + dataObj.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    const agendamentoHTML = `
                    <div class="p-2 border-bottom border-secondary" style="background-color: var(--bg-base); border-radius: 6px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary d-flex align-items-center gap-1" style="font-size: 0.70rem;"><i class="bi bi-journal-text"></i> Nota</span>
                            <small class="text-muted fw-bold" style="font-size: 0.70rem; color: var(--brand-primary) !important;">Agora mesmo</small>
                        </div>
                        <div class="text-light mt-2" style="font-size: 0.85rem; line-height: 1.4;">🕒 <strong>Retorno agendado para:</strong> ${dataFormatada}</div>
                    </div>`;
                    $('#modal-observacoes .d-flex.flex-column').prepend(agendamentoHTML);
                    carregarLeads(); // Recarrega a tabela sutilmente 
                }
            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr) { mostrarToast(xhr.responseJSON?.message || "Erro crítico.", 'danger'); },
        complete: function () { btnSubmit.prop('disabled', false).html(iconeOriginal); }
    });
});

// Editar Lead (Modal - Abrir e Salvar)
$(document).on('click', '.btn-editar-lead', function () {
    const btn = $(this);
    const leadId = btn.data('id');
    const iconeOriginal = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

    $.ajax({
        url: 'api/leads/get_details.php',
        type: 'GET',
        data: { id: leadId },
        dataType: 'json',
        success: function (response) {
            btn.html(iconeOriginal).prop('disabled', false);
            if (response.status === 'success') {
                const lead = response.data;
                $('#edit_lead_id').val(lead.id);
                $('#edit_customer_name').val(lead.customer_name);
                $('#edit_customer_phone').val(lead.customer_phone);
                $('#edit_customer_email').val(lead.customer_email || '');
                $('#edit_origin_id').val(lead.origin_id);
                $('#edit_vehicle_interest').val(lead.vehicle_interest || '');
                new bootstrap.Modal(document.getElementById('modal-edit-lead')).show();
            } else { mostrarToast(response.message, 'danger'); }
        },
        error: function () { btn.html(iconeOriginal).prop('disabled', false); mostrarToast("Erro ao buscar dados.", 'danger'); }
    });
});

$(document).on('submit', '#form-edit-lead', function (e) {
    e.preventDefault();
    const form = $(this);
    const btnSubmit = form.find('button[type="submit"]');
    const textoOriginal = btnSubmit.html();
    btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');

    $.ajax({
        url: 'api/leads/update.php',
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#modal-edit-lead').modal('hide');
                form.reset();
                mostrarToast(response.message, 'success');
                carregarLeads(); // Limpo, sem chamar atualizarKPIs()
            } else { mostrarToast(response.message, 'danger'); }
        },
        error: function (xhr) { mostrarToast(xhr.responseJSON?.message || "Erro crítico.", 'danger'); },
        complete: function () { btnSubmit.prop('disabled', false).html(textoOriginal); }
    });
});

// Excluir Lead (Modal de Confirmação)
let leadIdParaExcluir = null;
$(document).on('click', '.btn-excluir-lead', function () {
    leadIdParaExcluir = $(this).data('id');
    new bootstrap.Modal(document.getElementById('modal-confirm-delete')).show();
});

$(document).on('click', '#btn-confirmar-exclusao-real', function () {
    if (!leadIdParaExcluir) return;
    const btn = $(this);
    const textoOriginal = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Excluindo...');

    $.ajax({
        url: 'api/leads/delete.php',
        type: 'POST',
        data: { lead_id: leadIdParaExcluir },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#modal-confirm-delete').modal('hide');
                mostrarToast(response.message, 'success');
                carregarLeads(); // Limpo, sem chamar atualizarKPIs()
            } else { mostrarToast(response.message, 'danger'); }
        },
        error: function () { mostrarToast("Erro crítico.", 'danger'); },
        complete: function () { btn.prop('disabled', false).html(textoOriginal); leadIdParaExcluir = null; }
    });
});