$(document).ready(function() {
    
    // 1. O PRIMEIRO COMANDO: Assim que a página abre, mandamos carregar a tabela
    carregarOrigens();

    // 2. ESCUTANDO O FORMULÁRIO: Quando o usuário clicar em "Adicionar"
    $('#form-add-origin').on('submit', function(e) {
        e.preventDefault(); // Impede a página de dar F5 (recarregar)
        
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();
        
        // UX Premium: Mostra o efeito visual de "Salvando..."
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');

        // O pacote de dados viaja para a API via POST
        $.ajax({
            url: 'api/settings/add_origin.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Limpa o campo digitado
                    form.reset();
                    
                    // Exibe a notificação verde na tela (função do nosso app.js)
                    mostrarToast(response.message, 'success');
                    
                    // A MÁGICA: Recarrega a tabela de origens instantaneamente, sem o usuário perceber!
                    carregarOrigens();
                } else {
                    mostrarToast(response.message, 'danger');
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || "Erro crítico ao salvar a origem.";
                mostrarToast(errorMsg, 'danger');
            },
            complete: function() {
                // Devolve o botão ao normal, independentemente de dar erro ou sucesso
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });
});

/**
 * Função responsável por buscar as origens na API e desenhar o HTML da tabela
 */
function carregarOrigens() {
    const tabela = $('#tabela-origens');
    
    $.ajax({
        url: 'api/settings/list_origins.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            tabela.empty(); // Apaga a mensagem inicial de "Carregando origens..."

            if (response.status === 'success') {
                const origens = response.data;
                
                // Se o banco estiver vazio...
                if (origens.length === 0) {
                    tabela.html(`<tr><td colspan="3" class="text-center py-4 text-muted">Nenhuma origem cadastrada.</td></tr>`);
                    return;
                }

                // Para cada origem que veio do banco de dados, montamos uma linha (<tr>)
                origens.forEach(function(origem) {
                    
                    // Lógica para pintar a bolinha de status
                    const statusBadge = origem.is_active == 1 
                        ? '<span class="badge bg-success">Ativo</span>' 
                        : '<span class="badge bg-danger">Inativo</span>';

                    const tr = `
                        <tr class="border-secondary">
                            <td class="py-3 ps-3 align-middle text-light fw-bold">
                                <i class="bi bi-tag-fill text-secondary me-2"></i>${origem.name}
                            </td>
                            <td class="py-3 align-middle text-center">
                                ${statusBadge}
                            </td>
                            <td class="py-3 pe-3 align-middle text-end">
                                <!-- Deixamos o botão desativado no momento, pronto para a Versão 2.0 -->
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Ativar/Desativar em breve">
                                    <i class="bi bi-power"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    // Injeta a linha pronta no HTML da tabela
                    tabela.append(tr);
                });
            }
        },
        error: function() {
            tabela.html(`<tr><td colspan="3" class="text-center py-4 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao carregar as origens do banco.</td></tr>`);
        }
    });
}