document.addEventListener('DOMContentLoaded', () => {
  const fallbackPalette = {
    light: ['#1d4ed8', '#2563eb', '#0ea5e9', '#38bdf8', '#60a5fa', '#a855f7'],
    dark: ['#38bdf8', '#60a5fa', '#22d3ee', '#f472b6', '#facc15', '#f97316']
  };

  function getComputedVar(styles, name, fallback) {
    const value = styles.getPropertyValue(name);
    if (value && value.trim().length) {
      return value.trim();
    }
    return fallback;
  }

  function parsePalette(value, fallback) {
    if (!value) return fallback;
    const parsed = value
      .split(',')
      .map(color => color.trim())
      .filter(Boolean);
    return parsed.length ? parsed : fallback;
  }

  function applyChartTheme() {
    if (!window.Highcharts) return;
    const isDark = document.documentElement.classList.contains('dark');
    const styles = getComputedStyle(document.documentElement);
    const textColor = isDark ? '#f3f4f6' : '#111827';
    const gridColor = getComputedVar(styles, isDark ? '--chart-grid-dark' : '--chart-grid-light', isDark ? 'rgba(59, 130, 246, 0.35)' : 'rgba(148, 163, 184, 0.28)');
    const surfaceColor = getComputedVar(styles, isDark ? '--chart-surface-dark' : '--chart-surface-light', isDark ? 'rgba(15, 23, 42, 0.74)' : 'rgba(255, 255, 255, 0.42)');
    const plotBg = getComputedVar(styles, isDark ? '--chart-plot-dark' : '--chart-plot-light', 'transparent');
    const tooltipBorder = getComputedVar(styles, isDark ? '--chart-tooltip-border-dark' : '--chart-tooltip-border-light', isDark ? 'rgba(56, 189, 248, 0.45)' : 'rgba(59, 130, 246, 0.45)');
    const markerFill = getComputedVar(styles, isDark ? '--chart-marker-fill-dark' : '--chart-marker-fill-light', isDark ? 'rgba(15, 23, 42, 0.85)' : 'rgba(255, 255, 255, 0.85)');
    const areaOpacity = parseFloat(getComputedVar(styles, '--chart-area-opacity', '0.45')) || 0.45;
    const paletteVars = getComputedVar(styles, isDark ? '--chart-palette-dark' : '--chart-palette-light', '');
    const colors = parsePalette(paletteVars, isDark ? fallbackPalette.dark : fallbackPalette.light);

    const opts = {
      colors,
      chart: {
        backgroundColor: 'transparent',
        plotBackgroundColor: 'transparent',
        plotBorderColor: gridColor,
        style: { color: textColor, fontFamily: 'Inter, sans-serif' }
      },
      title: { style: { color: textColor, fontWeight: '700', letterSpacing: '0.02em' } },
      subtitle: { style: { color: textColor } },
      xAxis: {
        labels: { style: { color: textColor } },
        lineColor: gridColor,
        tickColor: gridColor,
        gridLineColor: gridColor,
        crosshair: { color: tooltipBorder, dashStyle: 'ShortDot' }
      },
      yAxis: {
        labels: { style: { color: textColor } },
        lineColor: gridColor,
        tickColor: gridColor,
        gridLineColor: gridColor,
        title: { style: { color: textColor } }
      },
      legend: {
        backgroundColor: 'transparent',
        itemStyle: { color: textColor },
        itemHoverStyle: { color: tooltipBorder }
      },
      tooltip: {
        backgroundColor: surfaceColor,
        borderColor: tooltipBorder,
        style: { color: textColor, fontFamily: 'Inter, sans-serif' },
        shared: true,
        shadow: false,
        xDateFormat: '%e %b %Y %H:%M'
      },
      credits: { style: { color: textColor } },
      plotOptions: {
        series: {
          borderWidth: 0,
          shadow: false,
          dataLabels: { color: textColor },
          marker: {
            lineWidth: 1.5,
            lineColor: tooltipBorder,
            fillColor: markerFill
          }
        },
        areaspline: { fillOpacity: areaOpacity },
        area: { fillOpacity: areaOpacity },
        column: {
          borderRadius: 6,
          pointPadding: 0.1,
          groupPadding: 0.08
        }
      },
      rangeSelector: {
        buttonTheme: {
          fill: 'transparent',
          stroke: 'transparent',
          style: { color: textColor, fontFamily: 'Source Sans Pro, sans-serif' },
          states: {
            hover: {
              fill: plotBg,
              style: { color: textColor }
            },
            select: {
              fill: tooltipBorder,
              style: { color: isDark ? '#0f172a' : '#ffffff' }
            }
          }
        },
        inputStyle: { color: textColor, backgroundColor: surfaceColor },
        labelStyle: { color: textColor }
      },
      navigator: {
        maskFill: isDark ? 'rgba(56, 189, 248, 0.2)' : 'rgba(37, 99, 235, 0.2)'
      }
    };

    Highcharts.setOptions(opts);
    if (Highcharts.charts) {
      Highcharts.charts.forEach(chart => {
        if (chart) {
          chart.update(opts, false);
          chart.redraw();
        }
      });
    }
  }

  applyChartTheme();
  const observer = new MutationObserver(applyChartTheme);
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
