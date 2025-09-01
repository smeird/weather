document.addEventListener('DOMContentLoaded', () => {
  function applyChartTheme() {
    if (!window.Highcharts) return;
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#f3f4f6' : '#111827';
    const gridColor = isDark ? '#374151' : '#e5e7eb';
    const bgColor = isDark ? '#1f2937' : '#ffffff';
    Highcharts.setOptions({
      chart: { backgroundColor: bgColor, style: { color: textColor } },
      title: { style: { color: textColor } },
      xAxis: { labels: { style: { color: textColor } }, gridLineColor: gridColor },
      yAxis: { labels: { style: { color: textColor } }, gridLineColor: gridColor, title: { style: { color: textColor } } },
      legend: { itemStyle: { color: textColor } }
    });
    Highcharts.charts.forEach(chart => {
      if (chart) {
        chart.update({
          chart: { backgroundColor: bgColor },
          xAxis: { labels: { style: { color: textColor } }, gridLineColor: gridColor },
          yAxis: { labels: { style: { color: textColor } }, gridLineColor: gridColor },
          legend: { itemStyle: { color: textColor } }
        }, false);
        chart.redraw();
      }
    });
  }
  applyChartTheme();
  const observer = new MutationObserver(applyChartTheme);
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
