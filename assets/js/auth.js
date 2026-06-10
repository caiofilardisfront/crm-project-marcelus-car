$(document).ready(function() {
    
    // Escuta o evento de "submit" (envio) do formulário
    $('#form-login').on('submit', function(e) {
        
        // 1. O SEGREDO DO AJAX: Isso bloqueia o comportamento padrão do HTML de recarregar a página
        e.preventDefault(); 


        // Esconde o alerta de erro antigo sempre que o usuário tentar logar novamente
        $('#login-alert').addClass('d-none');

        // 2. Feedback visual no botão
        let btn = $(this).find('button[type="submit"]');
        let textoOriginal = btn.text();
        btn.prop('disabled', true).text('Aguarde...');

        // 3. Coleta os dados que o usuário digitou
        let dados = {
            email: $('#email').val(),
            password: $('#password').val()
        };

        console.log("Enviando dados via AJAX:", dados); // Para testarmos no F12

        // 4. Dispara a requisição POST para a nossa API
        $.post('api/auth/login.php', dados, function(resposta) {
            
            console.log("O Servidor PHP devolveu:", resposta);
            
            if (resposta.status === 'success') {
                // Sucesso! Redireciona para o painel principal
                window.location.href = 'dashboard.php';
            } else {
                // Erro: Mostra um alerta com a mensagem do PHP (ex: E-mail incorreto)
                $('#login-alert').text(resposta.message).removeClass('d-none');
                
                // Volta o botão ao normal para o usuário tentar de novo
                btn.prop('disabled', false).text(textoOriginal);
            }

        }).fail(function() {
            // Se o arquivo PHP der erro 500 ou não for encontrado (404), cai aqui
            $('#login-alert').text("Erro crítico: Servidor inacessível. Verifique o console.").removeClass('d-none');
            btn.prop('disabled', false).text(textoOriginal);
        });

    });

});