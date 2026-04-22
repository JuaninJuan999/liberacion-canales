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
                layout: {
                    padding: {
                        top: 18,
                        right: 6,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '12%',
                        ticks: {
                            font: { size: 12 },
                            callback: function(value) {
                                return value.toFixed(2) + '%';
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 13, weight: 'bold' },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    datalabels: {
                        display: function (context) {
                            return context.dataset.label !== 'META';
                        },
                        color: '#333',
                        font: {
                            size: 11,
                            weight: 'bold'
                        },
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        formatter: function (value) {
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
