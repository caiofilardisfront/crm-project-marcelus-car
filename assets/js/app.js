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
                <div class="toast-body fw-semibold">
                    ${icone} ${mensagem}
                </div>
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