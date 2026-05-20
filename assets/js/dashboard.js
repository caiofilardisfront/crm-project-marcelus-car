$(document).ready(function () {
    // 1. Inicializa o Painel
    atualizarKPIs();
    atualizarGrafico();

    // 2. Gatilho de mudança do período do gráfico
    $('#filtro-tempo-grafico').on('change', function () {
        atualizarGrafico();
    });
});

/**
 * ========================================================
 * MOTOR DE ESTATÍSTICAS: ATUALIZA OS CARDS DE KPI
 * ========================================================
 */
function atualizarKPIs() {
    $.ajax({
        url: 'api/leads/get_kpis.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const dados = response.data;
                $('#kpi-total-leads').hide().text(dados.total_leads).fadeIn(300);
                $('#kpi-em-negociacao').hide().text(dados.negociacao_ativa).fadeIn(300);
                $('#kpi-fechados').hide().text(dados.total_won).fadeIn(300);
                $('#kpi-perdidos').hide().text(dados.total_lost).fadeIn(300);
            }
        },
        error: function () {
            console.error("Falha ao carregar os KPIs do Dashboard.");
        }
    });
}

/**
 * ========================================================
 * MOTOR DE RENDERIZAÇÃO: GRÁFICO CHART.JS
 * ========================================================
 */
let graficoPerformance = null;

function atualizarGrafico() {
    const periodoSelecionado = $('#filtro-tempo-grafico').val();

    $.ajax({
        url: 'api/leads/get_chart_data.php',
        type: 'GET',
        data: { periodo: periodoSelecionado },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                desenharGraficoPerformance(response.data);
            }
        },
        error: function () {
            console.error("Falha ao carregar os dados do gráfico.");
        }
    });
}

function desenharGraficoPerformance(dados) {
    const canvas = document.getElementById('grafico-performance');
    if(!canvas) return; // Trava de segurança caso o canvas não exista
    
    const ctx = canvas.getContext('2d');

    if (graficoPerformance !== null) {
        graficoPerformance.destroy();
    }

    const gradientLeads = ctx.createLinearGradient(0, 0, 0, 400);
    gradientLeads.addColorStop(0, 'rgba(239, 68, 68, 0.4)'); 
    gradientLeads.addColorStop(1, 'rgba(239, 68, 68, 0.0)'); 

    const gradientVendas = ctx.createLinearGradient(0, 0, 0, 400);
    gradientVendas.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); 
    gradientVendas.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    graficoPerformance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.labels,
            datasets: [
                {
                    label: 'Total de Leads Gerados',
                    data: dados.leads,
                    borderColor: '#ef4444',
                    backgroundColor: gradientLeads,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Carros Vendidos',
                    data: dados.vendas,
                    borderColor: '#10b981',
                    backgroundColor: gradientVendas,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#a1a1aa', font: { family: "'Inter', sans-serif", size: 13 } } },
                tooltip: { backgroundColor: 'rgba(24, 24, 27, 0.95)', titleColor: '#f4f4f5', bodyColor: '#a1a1aa', borderColor: '#27272a', borderWidth: 1, padding: 12 }
            },
            scales: {
                x: { grid: { color: '#27272a', drawBorder: false }, ticks: { color: '#a1a1aa' } },
                y: { beginAtZero: true, grid: { color: '#27272a', drawBorder: false }, ticks: { color: '#a1a1aa', stepSize: 1 } }
            }
        }
    });
}