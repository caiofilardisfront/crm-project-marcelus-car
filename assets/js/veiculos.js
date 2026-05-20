$(document).ready(function () {
    // 1. Assim que a página abre, manda a API buscar os carros
    carregarVeiculos();

    /**
     * ========================================================
     * DELEGAÇÃO DE EVENTO: SALVAR NOVO VEÍCULO (POST)
     * ========================================================
     */
    $('#form-add-veiculo').on('submit', function (e) {
        // Bloqueia o F5 automático do formulário HTML
        e.preventDefault();

        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();

        // UX Premium: Troca o botão para "Salvando..." com o spinner rodando
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...');

        $.ajax({
            url: 'api/vehicles/add.php',
            type: 'POST',
            data: form.serialize(), // Pega todos os campos e formata para envio
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // Fecha o Modal flutuante
                    $('#modal-add-veiculo').modal('hide');

                    // Limpa todos os campos para o próximo cadastro
                    form[0].reset();

                    // Se a função mostrarToast (do dashboard) estiver disponível, usa ela. Senão, usa alert nativo.
                    if (typeof mostrarToast === 'function') {
                        mostrarToast(response.message, 'success');
                    } else {
                        alert(response.message);
                    }

                    // A MÁGICA: Recarrega a tabela instantaneamente para mostrar o carro novo
                    carregarVeiculos();
                } else {
                    if (typeof mostrarToast === 'function') {
                        mostrarToast(response.message, 'danger');
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function (xhr) {
                console.error("Erro no servidor:", xhr.responseText);
                let errorMsg = "Erro interno ao salvar veículo.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                if (typeof mostrarToast === 'function') {
                    mostrarToast(errorMsg, 'danger');
                } else {
                    alert(errorMsg);
                }
            },
            complete: function () {
                // Devolve o botão ao estado original independente de sucesso ou falha
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });
});

/**
 * ========================================================
 * FUNÇÃO DE RENDERIZAÇÃO: BUSCAR ESTOQUE (GET)
 * ========================================================
 */
function carregarVeiculos() {
    const tabela = $('#tabela-veiculos');

    $.ajax({
        url: 'api/vehicles/list_all.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            // Limpa a mensagem de "Buscando estoque..."
            tabela.empty();

            if (response.status === 'success') {
                const veiculos = response.data;

                // Se não houver nenhum carro no estoque (Array vazio)
                if (veiculos.length === 0) {
                    tabela.html(`
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Nenhum veículo cadastrado no estoque.
                            </td>
                        </tr>
                    `);
                    return;
                }

                // Se houver carros, iteramos pelo array desenhando a tabela
                // Localize este trecho dentro da função carregarVeiculos()
                veiculos.forEach(function (carro) {
                    // 1. Formatações de Design Premium
                    const precoFormatado = parseFloat(carro.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const kmFormatado = carro.mileage ? parseInt(carro.mileage).toLocaleString('pt-BR') + ' km' : '0 km';

                    let corStatus = '';
                    if (carro.status === 'available') {
                        corStatus = 'bg-success text-white'; // Verde
                    } else if (carro.status === 'reserved') {
                        corStatus = 'bg-warning text-dark';  // Amarelo
                    } else if (carro.status === 'sold') {
                        corStatus = 'bg-danger text-white';  // Vermelho
                    }
                    // 2. Criação do Select Dinâmico (Removidos os comentários que bloqueavam a visualização)
                    const statusSelect = `
                        <select class="form-select form-select-sm border-0 fw-bold shadow-sm mx-auto ${corStatus} select-status-veiculo" 
            data-id="${carro.id}" 
            style="width: 140px; cursor: pointer; border-radius: 50rem; text-align: center; background-position: right 0.5rem center;">
        <option value="available" class="bg-dark text-light" ${carro.status === 'available' ? 'selected' : ''}>Disponível</option>
        <option value="reserved"  class="bg-dark text-light" ${carro.status === 'reserved' ? 'selected' : ''}>Indisponível</option>
        <option value="sold"      class="bg-dark text-light" ${carro.status === 'sold' ? 'selected' : ''}>Vendido</option>
    </select>
                    `;

                    // 3. Montagem da Linha (Aqui estava o erro: a <td> de status estava comentada)
                    const tr = `
    <tr class="border-secondary">
        <td class="py-3 ps-4 align-middle">
            <div class="fw-bold text-light">
                <i class="bi bi-car-front text-secondary me-2"></i> ${carro.brand} ${carro.model}
            </div>
        </td>
        <td class="py-3 align-middle text-muted small">
            ${carro.manufacture_year} / ${carro.model_year}
        </td>
        <td class="py-3 align-middle text-muted small">
            ${kmFormatado}
        </td>
        <td class="py-3 align-middle fw-semibold text-light" style="letter-spacing: 0.5px;">
            R$ ${precoFormatado}
        </td>
        <td class="py-3 align-middle text-center d-flex justify-content-center border-0">
            ${statusSelect}
        </td>
    </tr>
    `;

                    // 4. Injeta na tabela
                    tabela.append(tr);
                });
            }
        },
        error: function () {
            // Em caso de falha de comunicação com a API
            tabela.html(`
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao carregar o estoque de veículos.
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * EVENTO: ATUALIZAÇÃO RÁPIDA DE STATUS DO VEÍCULO
 */
$(document).on('change', '.select-status-veiculo', function () {
    const select = $(this);
    const vehicleId = select.data('id');
    const newStatus = select.val();

    // --- NOVA LÓGICA DE CORES ---
    // 1. Remove todas as cores antigas para limpar o elemento
    select.removeClass('bg-success bg-warning bg-danger text-white text-dark');
    
    // 2. Aplica a nova cor imediatamente baseada na escolha
    if (newStatus === 'available') {
        select.addClass('bg-success text-white');
    } else if (newStatus === 'reserved') {
        select.addClass('bg-warning text-dark');
    } else if (newStatus === 'sold') {
        select.addClass('bg-danger text-white');
    }
    // ----------------------------

    select.addClass('opacity-50').prop('disabled', true);

    $.post('api/vehicles/update_status.php', {
        vehicle_id: vehicleId,
        status: newStatus
    }, function (response) {
        select.removeClass('opacity-50').prop('disabled', false);
        if (response.status === 'success') {
            // Se a função mostrarToast (do dashboard.js) estiver disponível, usamos ela!
            if (typeof mostrarToast === 'function') {
                mostrarToast(response.message, 'success');
            }
        }
    }, 'json').fail(function () {
        select.removeClass('opacity-50').prop('disabled', false);
        alert("Erro ao comunicar com o servidor.");
    });
});
