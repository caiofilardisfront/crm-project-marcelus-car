$(document).ready(function() {
    // 1. Carrega a agenda assim que a tela abre
    carregarAgenda();

    // 2. O PULO DO GATO: Se o vendedor abrir a ficha do cliente (Modal) e fizer alterações,
    // nós recarregamos a tabela de fundo assim que ele fechar o modal.
    $('#modal-lead').on('hidden.bs.modal', function () {
        carregarAgenda();
    });
});

function carregarAgenda() {
    const tabela = $('#tabela-agenda');

    $.ajax({
        url: 'api/leads/get_agenda.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            tabela.empty(); // Limpa o "Buscando compromissos..."

            if (response.status === 'success' && response.data.length > 0) {
                response.data.forEach(function(lead) {
                    let alertaAgenda = '';
                    let corTextoData = 'text-light';
                    
                    const dataAgendada = new Date(lead.next_contact_at);
                    const agora = new Date();
                    
                    const hojeInicio = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
                    const hojeFim = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate(), 23, 59, 59);

                    // Motor Visual do Tempo
                    if (dataAgendada < agora) {
                        alertaAgenda = '<span class="badge bg-danger ms-2 px-2 shadow-sm" style="font-size: 0.65rem;"><i class="bi bi-alarm-fill"></i> Atrasado</span>';
                        corTextoData = 'text-danger fw-bold';
                    } else if (dataAgendada >= hojeInicio && dataAgendada <= hojeFim) {
                        alertaAgenda = '<span class="badge bg-warning text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;"><i class="bi bi-calendar-event-fill"></i> Hoje</span>';
                        corTextoData = 'text-warning fw-bold';
                    } else {
                        alertaAgenda = '<span class="badge bg-info text-dark ms-2 px-2 shadow-sm" style="font-size: 0.65rem;"><i class="bi bi-calendar-check"></i> Agendado</span>';
                    }

                    const dataFormatada = dataAgendada.toLocaleDateString('pt-BR') + ' às ' + dataAgendada.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});

                    // Lógica das Etiquetas de Status
                    let statusBadge = '';
                    switch (lead.status) {
                        case 'new': statusBadge = '<span class="badge bg-primary">Novo</span>'; break;
                        case 'in_progress': statusBadge = '<span class="badge bg-warning text-dark">Em Negociação</span>'; break;
                        case 'proposal_sent': statusBadge = '<span class="badge bg-info text-dark">Proposta Enviada</span>'; break;
                        default: statusBadge = `<span class="badge bg-secondary">${lead.status || 'Novo'}</span>`;
                    }

                    // Montagem da Linha (Repare que reaproveitamos a classe btn-ver-detalhes do dashboard!)
                    const tr = `
                    <tr class="border-secondary">
                        <td class="py-3 ps-4 align-middle">
                            <div class="fw-bold text-light d-flex align-items-center">
                                ${lead.customer_name} 
                                ${alertaAgenda}
                            </div>
                        </td>
                        <td class="py-3 align-middle text-muted small">
                            <div><i class="bi bi-telephone me-1 text-secondary"></i> ${lead.customer_phone || 'Não informado'}</div>
                        </td>
                        <td class="py-3 align-middle ${corTextoData} small">
                            <i class="bi bi-clock me-1"></i> ${dataFormatada}
                        </td>
                        <td class="py-3 align-middle">
                            ${statusBadge}
                        </td>
                        <td class="py-3 pe-4 align-middle text-end">
                            <button class="btn btn-sm btn-outline-light btn-ver-detalhes" data-id="${lead.id}" title="Ver Ficha">
                                <i class="bi bi-person-lines-fill me-1"></i> Abrir
                            </button>
                        </td>
                    </tr>
                    `;
                    tabela.append(tr);
                });
            } else {
                // UI de Sucesso ("Zero Inbox")
                tabela.html(`
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar2-check fs-2 d-block mb-2 text-success"></i>
                            Nenhum retorno pendente. Você está em dia!
                        </td>
                    </tr>
                `);
            }
        },
        error: function() {
            tabela.html(`
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao carregar a agenda.
                    </td>
                </tr>
            `);
        }
    });
}