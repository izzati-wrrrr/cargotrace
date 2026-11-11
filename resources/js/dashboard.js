import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

// Hanya komponen yang dipakai yang didaftarkan, supaya ukuran file tetap kecil.
Chart.register(BarController, BarElement, CategoryScale, LinearScale, DoughnutController, ArcElement, Tooltip, Legend);

Chart.defaults.font.family = "'Plus Jakarta Sans Variable', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.color = '#5a747d';

// Warna grafik mengikuti palet merek (menggantikan warna dari server).
const STATUS_COLORS = {
    CREATED: '#a9bdc2',
    BOOKED: '#6c8ead',
    'PICKED UP': '#f2a31b',
    'IN TRANSIT': '#0e7c86',
    DELIVERED: '#1e9e6a',
    CANCELLED: '#d64545',
};
const CARRIER_COLORS = ['#0e7c86', '#f2a31b', '#134257', '#7cc6c9', '#d6603a', '#6c8ead', '#9bbf6a', '#b07cc6'];

// Batang horizontal: jumlah shipment per status.
function initStatusChart() {
    const el = document.getElementById('status-chart');
    if (!el) return;

    const data = JSON.parse(el.dataset.chart);

    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: data.labels.map((label) => STATUS_COLORS[label] || '#a9bdc2'),
                    borderRadius: 6,
                    barThickness: 18,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e4eded' }, border: { display: false } },
                y: { grid: { display: false }, border: { display: false } },
            },
        },
    });
}

// Donut: porsi shipment terlambat per carrier.
function initCarrierChart() {
    const el = document.getElementById('carrier-chart');
    if (!el) return;

    const data = JSON.parse(el.dataset.chart);

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: data.labels.map((_, i) => CARRIER_COLORS[i % CARRIER_COLORS.length]),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14 },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `${ctx.label}: ${ctx.parsed} terlambat dari ${data.totals[ctx.dataIndex]} shipment (${data.rates[ctx.dataIndex]}%)`,
                    },
                },
            },
        },
    });
}

function init() {
    initStatusChart();
    initCarrierChart();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}