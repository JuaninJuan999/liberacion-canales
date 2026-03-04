import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels);

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
                    },
                    datalabels: {
                        display: true,
                        color: '#333',
                        font: {
                            size: 11,
                            weight: 'bold'
                        },
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        formatter: function(value) {
                            if (value === null) return '';
                            return Number(value).toFixed(2) + '%';
                        }
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
