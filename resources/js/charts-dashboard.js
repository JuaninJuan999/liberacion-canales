import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels);

/**
 * Gerencia: solo mostrar etiqueta de % en días donde el indicador cumple o supera la META.
 * El trazado de la línea sigue viéndose todos los días (esto solo afecta a las cajitas de texto).
 */
function shouldShowLabelWhenMeetsOrExceedsMeta(chart, dataset, dataIndex) {
    if (dataset.label === 'META') {
        return false;
    }
    const metaDs = chart.data.datasets.find((d) => d.label === 'META');
    if (!metaDs || !Array.isArray(metaDs.data)) {
        return false;
    }
    const y = Number(dataset.data[dataIndex]);
    const goal = Number(metaDs.data[dataIndex]);
    if (Number.isNaN(y) || Number.isNaN(goal)) {
        return false;
    }
    const eps = 1e-9;
    return y + eps >= goal;
}

function hexToRgba(color, alpha) {
    if (!color || typeof color !== 'string' || color[0] !== '#') {
        return `rgba(100, 100, 100, ${alpha})`;
    }
    const h = color.slice(1);
    if (h.length !== 6) {
        return `rgba(100, 100, 100, ${alpha})`;
    }
    const r = parseInt(h.slice(0, 2), 16);
    const g = parseInt(h.slice(2, 4), 16);
    const b = parseInt(h.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function roundRectPath(ctx, x, y, w, h, r) {
    const rr = Math.min(r, w / 2, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + rr, y);
    ctx.arcTo(x + w, y, x + w, y + h, rr);
    ctx.arcTo(x + w, y + h, x, y + h, rr);
    ctx.arcTo(x, y + h, x, y, rr);
    ctx.arcTo(x, y, x + w, y, rr);
    ctx.closePath();
}

/**
 * Etiquetas % con línea fina al punto (callout) y separación vertical si se pisan.
 */
const percentageCalloutPlugin = {
    id: 'percentageCallouts',

    afterDatasetsDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) {
            return;
        }

        const items = [];

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            if (dataset.label === 'META') {
                return;
            }
            const meta = chart.getDatasetMeta(datasetIndex);
            if (meta.hidden === true) {
                return;
            }
            const raw = dataset.data;
            for (let i = 0; i < raw.length; i++) {
                const el = meta.data[i];
                if (!el || el.skip) {
                    continue;
                }
                if (!shouldShowLabelWhenMeetsOrExceedsMeta(chart, dataset, i)) {
                    continue;
                }
                const v = raw[i];
                if (v === null || v === undefined) {
                    continue;
                }
                const { x, y } = el.getProps(['x', 'y'], true);
                const text = `${Number(v).toFixed(2)}%`;
                const color = dataset.borderColor || '#6b7280';
                items.push({ x, y, text, color });
            }
        });

        if (items.length === 0) {
            return;
        }

        items.sort((a, b) => a.x - b.x);

        ctx.save();
        ctx.font = '600 9px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

        const padX = 5;
        const padY = 3;
        const lineHeight = 13;
        const boxH = lineHeight + padY * 2;
        const baseLift = 20;
        const stackStep = 15;
        const minHorizGap = 5;

        const placed = [];

        for (const it of items) {
            const w = ctx.measureText(it.text).width + padX * 2;
            it.w = w;
            it.h = boxH;
            let labelCy = it.y - baseLift - boxH / 2;
            let attempts = 0;
            while (attempts < 16) {
                const box = {
                    left: it.x - w / 2,
                    right: it.x + w / 2,
                    top: labelCy - boxH / 2,
                    bottom: labelCy + boxH / 2,
                };
                let clash = false;
                for (const p of placed) {
                    const horizOverlap = !(box.right + minHorizGap < p.left || box.left - minHorizGap > p.right);
                    const vertOverlap = !(box.bottom < p.top || box.top > p.bottom);
                    if (horizOverlap && vertOverlap) {
                        clash = true;
                        break;
                    }
                }
                if (!clash) {
                    break;
                }
                labelCy -= stackStep;
                attempts++;
            }
            placed.push({
                left: it.x - w / 2,
                right: it.x + w / 2,
                top: labelCy - boxH / 2,
                bottom: labelCy + boxH / 2,
            });
            it.labelCy = labelCy;
        }

        for (const it of items) {
            const { x, y, text, color, labelCy, w, h } = it;
            const boxTop = labelCy - h / 2;
            const boxBottom = labelCy + h / 2;
            const boxLeft = x - w / 2;

            const lineEndY = Math.min(y, boxBottom) - 1;
            const lineStartY = y;

            ctx.strokeStyle = hexToRgba(color, 0.4);
            ctx.lineWidth = 0.85;
            ctx.setLineDash([]);
            ctx.beginPath();
            ctx.moveTo(x, lineStartY);
            ctx.lineTo(x, lineEndY);
            ctx.stroke();

            ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
            ctx.strokeStyle = 'rgba(0, 0, 0, 0.09)';
            ctx.lineWidth = 1;
            roundRectPath(ctx, boxLeft, boxTop, w, h, 4);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#1f2937';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(text, x, labelCy);
        }

        ctx.restore();
    },
};

Chart.register(percentageCalloutPlugin);

function normalizeMonthlyIndicatorDatasets(datasets) {
    if (!Array.isArray(datasets)) {
        return [];
    }

    return datasets.map((ds) => {
        const isMeta = String(ds.label || '') === 'META';

        return {
            ...ds,
            fill: false,
            borderWidth: isMeta ? 2.5 : ds.borderWidth ?? 2,
            order: isMeta ? 2 : 1,
            spanGaps: false,
        };
    });
}

export function initDashboardCharts(chartData) {
    const labelCount = Array.isArray(chartData.labels) ? chartData.labels.length : 0;
    const xTickFontSize = labelCount > 22 ? 8 : labelCount > 16 ? 9 : 10;

    const createChart = (elementId, datasetsRaw) => {
        const canvas = document.getElementById(elementId);
        if (!canvas) {
            return;
        }

        const datasets = normalizeMonthlyIndicatorDatasets(datasetsRaw);

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 56,
                        right: 10,
                        left: 6,
                        bottom: labelCount > 12 ? 18 : 10,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '12%',
                        ticks: {
                            font: { size: 12 },
                            callback: function (value) {
                                return `${Number(value).toFixed(2)}%`;
                            },
                        },
                    },
                    x: {
                        ticks: {
                            font: { size: xTickFontSize },
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: false,
                            color: '#374151',
                        },
                    },
                },
                plugins: {
                    datalabels: {
                        display: false,
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 13, weight: 'bold' },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                        },
                    },
                },
            },
        });
    };

    createChart('chartSobrebarriga', chartData.datasets.sobrebarriga);
    createChart('chartHematomas', chartData.datasets.hematomas);
    createChart('chartCortePiernas', chartData.datasets.cortes_piernas);
    createChart('chartCoberturaGrasa', chartData.datasets.cobertura_grasa);
}
