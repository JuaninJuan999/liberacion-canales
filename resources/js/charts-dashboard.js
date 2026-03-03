import Chart from 'chart.js/auto';

export function initDashboardCharts(chartData) {
    const createChart = (elementId, datasets) => {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    };

    createChart('chartSobrebarriga', chartData.datasets.sobrebarriga);
    createChart('chartHematomas', chartData.datasets.hematomas);
    createChart('chartCortePiernas', chartData.datasets.cortes_piernas);
    createChart('chartCoberturaGrasa', chartData.datasets.cobertura_grasa);
}
