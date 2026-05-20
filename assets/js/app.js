$(document).ready(function() {
    
    // 1. Máscara para Telefone (Celular com 9 dígitos)
    $('.mask-phone').mask('(00) 00000-0000');

    // 2. Máscara para CPF
    $('.mask-cpf').mask('000.000.000-00', {reverse: true});

    // 3. Máscara para Placa de Carro (Formato Mercosul e Antigo)
    // Usamos uma tradução para aceitar letras e números
    $('.mask-plate').mask('AAA-0A00', {
        'translation': {
            A: {pattern: /[A-Za-z]/},
            0: {pattern: /[0-9]/}
        },
        onKeyPress: function(val, e, field, options) {
            field.val(val.toUpperCase()); // Força ficar em maiúsculo
        }
    });

    // 4. Máscara para Dinheiro (Preço do Carro)
    $('.mask-money').mask('#.##0,00', {reverse: true});

    console.log("🚀 Máscaras carregadas com sucesso!");
});