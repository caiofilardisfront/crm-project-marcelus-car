$(document).ready(function() {
    // 1. Máscara para Telefone (Celular com 9 dígitos)
    $('.mask-phone').mask('(00) 00000-0000');

    // 2. Máscara para CPF
    $('.mask-cpf').mask('000.000.000-00', {reverse: true});

    // 3. Máscara para Placa de Carro (Formato Mercosul e Antigo)
    $('.mask-plate').mask('AAA-0A00', {
        'translation': {
            A: {pattern: /[A-Za-z]/},
            0: {pattern: /[0-9]/}
        },
        onKeyPress: function(val, e, field, options) {
            field.val(val.toUpperCase()); 
        }
    });

    // 4. Máscara para Dinheiro (Preço do Carro)
    $('.mask-money').mask('#.##0,00', {reverse: true});
});

/**
 * ========================================================
 * FUNÇÃO GLOBAL: Mostrar Toast do Bootstrap dinamicamente
 * ========================================================
 */
function mostrarToast(mensagem, cor = 'success') {
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>');
    }

    const toastId = 'toast-' + Date.now();
    const icone = cor === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${cor} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold">${icone} ${mensagem}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    $('#toast-container').append(toastHTML);
    const toastElement = new bootstrap.Toast(document.getElementById(toastId), { delay: 3000 });
    toastElement.show();

    $('#' + toastId).on('hidden.bs.toast', function () {
        $(this).remove();
    });
}

/**
 * ========================================================
 * EVENTO GLOBAL: Ver Detalhes (Ficha do Lead)
 * ========================================================
 */
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

/**
 * ========================================================
 * EVENTO GLOBAL: Adicionar Anotação / Agendamento de Retorno
 * ========================================================
 */
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
                form[0].reset(); // Correção do bug anterior (.reset nativo)
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
                }

                // 🔄 TOQUE SÊNIOR: Identifica dinamicamente qual tela está ativa e atualiza a listagem correspondente
                if (typeof carregarLeads === 'function') carregarLeads();
                if (typeof carregarAgenda === 'function') carregarAgenda();

            } else {
                mostrarToast(response.message, 'danger');
            }
        },
        error: function (xhr) { mostrarToast(xhr.responseJSON?.message || "Erro crítico.", 'danger'); },
        complete: function () { btnSubmit.prop('disabled', false).html(iconeOriginal); }
    });
});