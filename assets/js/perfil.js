$(document).ready(function() {
    
    // Escuta o momento em que o usuário clica em "Salvar Alterações"
    $('#form-perfil').on('submit', function(e) {
        e.preventDefault(); // Impede a página de piscar/recarregar
        
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const textoOriginal = btnSubmit.html();
        
        // Efeito visual de carregamento no botão
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Salvando...');

        // Dispara o pacote de dados para a API
        $.ajax({
            url: 'api/auth/update_profile.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Chama o nosso Toast de sucesso (lá do app.js)
                    mostrarToast(response.message, 'success');
                    
                    // Limpa o campo de senha para não ficar preenchido
                    $('input[name="password"]').val('');
                    
                    // Atualiza a página suavemente após 1.5 segundos 
                    // Isso fará o novo nome do usuário aparecer no Menu Superior!
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } else {
                    mostrarToast(response.message, 'danger');
                    btnSubmit.prop('disabled', false).html(textoOriginal);
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || "Erro crítico de comunicação.";
                mostrarToast(errorMsg, 'danger');
                btnSubmit.prop('disabled', false).html(textoOriginal);
            }
        });
    });
});