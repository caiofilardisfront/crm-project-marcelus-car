$(document).ready(function () {
    // 1. Assim que a página abre, manda a API buscar os carros
    carregarVeiculos();

    /**
     * ========================================================
     * DELEGAÇÃO DE EVENTO: SALVAR NOVO VEÍCULO (POST)
     * ========================================================
     */
    $('#form-add-veiculo').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();

        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...');

        // 🛠️ TOQUE SÊNIOR: Cria o objeto FormData que empacota texto e imagem
        const formData = new FormData(this);

        $.ajax({
            url: 'api/vehicles/add.php',
            type: 'POST',
            data: formData, // Usa o formData em vez de form.serialize()
            contentType: false, // OBRIGATÓRIO: Avisa o jQuery para não mexer no cabeçalho do arquivo
            processData: false, // OBRIGATÓRIO: Avisa o jQuery para não converter o arquivo em string
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#modal-add-veiculo').modal('hide');
                    form[0].reset();
                    mostrarToast(response.message, 'success');
                    carregarVeiculos(); // Recarrega a tabela de estoque
                } else {
                    mostrarToast(response.message, 'danger');
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || "Erro interno ao salvar veículo.";
                mostrarToast(errorMsg, 'danger');
            },
            complete: function () {
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
            tabela.empty();

            if (response.status === 'success') {
                const veiculos = response.data;

                if (veiculos.length === 0) {
                    tabela.html(`
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Nenhum veículo cadastrado no estoque.
                            </td>
                        </tr>
                    `);
                    return;
                }

                // Usando 'v' como variável para representar cada veículo
                veiculos.forEach(function (v) {
                    // 1. Formatações
                    const precoFormatado = parseFloat(v.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const kmFormatado = v.mileage ? parseInt(v.mileage).toLocaleString('pt-BR') + ' km' : '0 km';

                    // 2. Select Dinâmico de Status
                    let corStatus = '';
                    if (v.status === 'available') corStatus = 'bg-success text-white';
                    else if (v.status === 'reserved') corStatus = 'bg-warning text-dark';
                    else if (v.status === 'sold') corStatus = 'bg-danger text-white';

                    const statusSelect = `
                        <select class="form-select form-select-sm border-0 fw-bold shadow-sm mx-auto ${corStatus} select-status-veiculo" 
                            data-id="${v.id}" 
                            style="width: 140px; cursor: pointer; border-radius: 50rem; text-align: center;">
                            <option value="available" class="bg-dark text-light" ${v.status === 'available' ? 'selected' : ''}>Disponível</option>
                            <option value="reserved"  class="bg-dark text-light" ${v.status === 'reserved' ? 'selected' : ''}>Indisponível</option>
                            <option value="sold"      class="bg-dark text-light" ${v.status === 'sold' ? 'selected' : ''}>Vendido</option>
                        </select>
                    `;

                    // 3. Lógica de Exibição da Imagem (Foto ou Ícone)
                    let iconeOuImagem = '<i class="bi bi-car-front fs-4 text-secondary"></i>';
                    if (v.image_path) {
                        iconeOuImagem = `<img src="${v.image_path}" alt="Foto ${v.model}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">`;
                    }

                    // 4. Montagem da Linha
                    const tr = `
                    <tr class="border-secondary align-middle">
                        <td class="py-3 ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark rounded p-1 me-3 border border-secondary d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                    ${iconeOuImagem}
                                </div>
                                <div>
                                    <div class="fw-bold text-light">${v.brand} ${v.model}</div>
                                    <small class="text-muted">Placa: ${v.license_plate || 'Não info.'}</small>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 text-muted">${v.manufacture_year} / ${v.model_year}</td>
                        <td class="py-3 text-muted">${kmFormatado}</td>
                        <td class="py-3 fw-semibold text-light">R$ ${precoFormatado}</td>
                        <td class="py-3">${statusSelect}</td>
                        <td class="py-3 pe-4 text-end">
                            <button class="btn btn-sm btn-outline-primary btn-edit me-1" data-id="${v.id}" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${v.id}" title="Excluir"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;

                    tabela.append(tr);
                });
            }
        },
        error: function () {
            tabela.html(`
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger fw-bold">
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
