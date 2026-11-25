import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const themeSwitchers = document.querySelectorAll('[data-theme-switcher]');

    if (themeSwitchers.length > 0) {
        const preferenceKey = document.documentElement.dataset.themeStorageKey;

        const updateThemeSwitcher = () => {
            const isCobaltTheme = document.documentElement.dataset.theme === 'cobalt';

            themeSwitchers.forEach((themeSwitcher) => {
                themeSwitcher.setAttribute('aria-pressed', String(isCobaltTheme));
                themeSwitcher.setAttribute('aria-label', isCobaltTheme
                    ? 'Cambiar de tema. Activar tema Jack Sparkys'
                    : 'Cambiar de tema. Activar tema Cobalto');
                themeSwitcher.querySelector('[data-theme-description]').textContent = isCobaltTheme
                    ? 'Activar tema Jack Sparkys'
                    : 'Activar tema Cobalto';
            });
        };

        themeSwitchers.forEach((themeSwitcher) => {
            themeSwitcher.addEventListener('click', () => {
                const activatingCobalt = document.documentElement.dataset.theme !== 'cobalt';

                if (activatingCobalt) {
                    document.documentElement.dataset.theme = 'cobalt';
                } else {
                    document.documentElement.dataset.theme = 'classic';
                }

                try {
                    window.localStorage.setItem(preferenceKey, activatingCobalt ? 'cobalt' : 'classic');
                } catch (error) {
                    console.error('No se pudo guardar la preferencia de tema:', error);
                }

                updateThemeSwitcher();
                renderDashboardCharts();
            });
        });

        updateThemeSwitcher();
    }
});

const renderDashboardCharts = () => {
    document.querySelectorAll('[data-dashboard-charts]').forEach((container) => {
        const dataElement = container.querySelector('[data-dashboard-chart-data]');
        if (!dataElement) return;

        let data;
        try {
            data = JSON.parse(dataElement.textContent);
        } catch (error) {
            console.error('No se pudieron leer los datos de las gráficas del dashboard:', error);
            return;
        }

        const isSecondaryTheme = document.documentElement.dataset.theme === 'classic';
        const isCobaltTheme = document.documentElement.dataset.theme === 'cobalt';
        const useLightChart = isSecondaryTheme || isCobaltTheme;
        const chartTextColor = useLightChart ? '#52636c' : '#94a3b8';
        const chartBorderColor = useLightChart ? '#aeb9bd' : '#475569';
        const chartGridColor = useLightChart ? 'rgba(82, 99, 108, 0.12)' : 'rgba(148, 163, 184, 0.12)';

        const renderTrendChart = (canvasId, label, values, color, backgroundColor) => {
            const canvas = container.querySelector(`#${canvasId}`);
            if (!canvas) return;

            Chart.getChart(canvas)?.destroy();
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label,
                        data: values,
                        borderColor: color,
                        backgroundColor,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: useLightChart ? '#ffffff' : '#0f172a',
                        pointBorderColor: color,
                        pointBorderWidth: 2,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => 'S/ ' + new Intl.NumberFormat('es-PE', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                }).format(context.parsed.y),
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: chartTextColor },
                            border: { color: chartBorderColor },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: chartTextColor,
                                callback: (value) => 'S/ ' + Number(value).toLocaleString('es-PE'),
                            },
                            grid: { color: chartGridColor },
                            border: { display: false },
                        },
                    },
                },
            });
        };

        renderTrendChart(
            'salesChart',
            'Ventas',
            data.sales,
            isCobaltTheme ? '#045c94' : (isSecondaryTheme ? '#52636c' : '#34d399'),
            isCobaltTheme ? 'rgba(4, 92, 148, 0.12)' : (isSecondaryTheme ? 'rgba(82, 99, 108, 0.12)' : 'rgba(52, 211, 153, 0.12)'),
        );
        renderTrendChart(
            'rentalsChart',
            'Alquileres',
            data.rentals,
            isCobaltTheme ? '#ffbb1c' : (isSecondaryTheme ? '#d8b600' : '#38bdf8'),
            isCobaltTheme ? 'rgba(255, 187, 28, 0.2)' : (isSecondaryTheme ? 'rgba(247, 217, 67, 0.2)' : 'rgba(56, 189, 248, 0.12)'),
        );
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderDashboardCharts, { once: true });
} else {
    renderDashboardCharts();
}

document.addEventListener('livewire:initialized', renderDashboardCharts);
document.addEventListener('livewire:navigated', renderDashboardCharts);
