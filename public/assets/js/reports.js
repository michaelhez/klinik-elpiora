(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined' || !window.REPORT_DATA) return;

        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            const data = window.REPORT_DATA.revenue || [];
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: data.map((row) => row.date),
                    datasets: [{
                        label: 'Pendapatan',
                        data: data.map((row) => Number(row.total)),
                        backgroundColor: 'rgba(37, 99, 235, 0.6)',
                        borderRadius: 4,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                },
            });
        }

        const visitsCtx = document.getElementById('visitsChart');
        if (visitsCtx) {
            const data = window.REPORT_DATA.visits || [];
            new Chart(visitsCtx, {
                type: 'line',
                data: {
                    labels: data.map((row) => row.date),
                    datasets: [{
                        label: 'Kunjungan',
                        data: data.map((row) => Number(row.total)),
                        borderColor: '#16A34A',
                        backgroundColor: 'rgba(22, 163, 74, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        }
    });
})();
