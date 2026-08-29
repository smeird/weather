(function (window, document) {
  'use strict';

  if (!window.Highcharts) return;

  const Highcharts = window.Highcharts;
  const palette = ['#2563eb', '#0891b2', '#e8793f', '#6d5bd0', '#d24b4b', '#5b8c3a', '#c58a18', '#64748b'];

  function isDarkMode() {
    return document.documentElement.classList.contains('dark');
  }

  function colours(dark) {
    return {
      surface: dark ? '#111827' : '#ffffff',
      text: dark ? '#e2e8f0' : '#334155',
      muted: dark ? '#94a3b8' : '#64748b',
      axis: dark ? 'rgba(148, 163, 184, 0.34)' : 'rgba(100, 116, 139, 0.28)',
      grid: dark ? 'rgba(148, 163, 184, 0.16)' : 'rgba(148, 163, 184, 0.22)',
      tooltip: dark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(15, 23, 42, 0.94)',
      navigator: dark ? 'rgba(71, 85, 105, 0.42)' : 'rgba(148, 163, 184, 0.28)',
      navigatorSelection: dark ? 'rgba(59, 130, 246, 0.22)' : 'rgba(37, 99, 235, 0.15)'
    };
  }

  function axisTheme(theme) {
    return {
      lineColor: theme.axis,
      tickColor: theme.axis,
      gridLineColor: theme.grid,
      labels: {
        style: {
          color: theme.muted,
          fontFamily: 'Inter, system-ui, sans-serif',
          fontSize: '12px',
          fontWeight: '500',
          textOutline: 'none'
        }
      },
      title: {
        style: {
          color: theme.text,
          fontFamily: 'Inter, system-ui, sans-serif',
          fontSize: '12px',
          fontWeight: '600'
        }
      }
    };
  }

  function darkTrack(theme) {
    return theme.surface === '#111827'
      ? 'rgba(30, 41, 59, 0.7)'
      : 'rgba(226, 232, 240, 0.7)';
  }

  function interactiveTheme(theme) {
    return {
      chart: {
        backgroundColor: 'transparent',
        plotBackgroundColor: 'transparent',
        style: { fontFamily: 'Inter, system-ui, sans-serif' }
      },
      title: {
        style: {
          color: theme.text,
          fontFamily: 'Roboto, sans-serif',
          fontSize: '16px',
          fontWeight: '700'
        }
      },
      subtitle: {
        style: {
          color: theme.muted,
          fontFamily: 'Inter, system-ui, sans-serif',
          fontSize: '11px',
          fontWeight: '500'
        }
      },
      legend: {
        itemStyle: {
          color: theme.text,
          fontFamily: 'Inter, system-ui, sans-serif',
          fontSize: '12px',
          fontWeight: '600',
          textOutline: 'none'
        },
        itemHoverStyle: { color: palette[0] },
        itemHiddenStyle: { color: theme.muted }
      },
      tooltip: {
        backgroundColor: theme.tooltip,
        borderColor: 'rgba(148, 163, 184, 0.28)',
        style: {
          color: '#f8fafc',
          fontFamily: 'Inter, system-ui, sans-serif',
          fontSize: '12px'
        }
      },
      rangeSelector: {
        buttonTheme: {
          fill: 'transparent',
          stroke: theme.axis,
          style: { color: theme.text, fontWeight: '600' },
          states: {
            hover: { fill: theme.navigatorSelection, style: { color: theme.text } },
            select: { fill: palette[0], style: { color: '#ffffff' } }
          }
        },
        inputBoxBorderColor: theme.axis,
        inputStyle: { color: theme.text },
        labelStyle: { color: theme.muted }
      },
      navigator: {
        maskFill: theme.navigatorSelection,
        outlineColor: theme.axis,
        handles: {
          backgroundColor: theme.surface,
          borderColor: theme.axis
        },
        xAxis: axisTheme(theme)
      },
      scrollbar: {
        barBackgroundColor: theme.navigator,
        barBorderColor: theme.axis,
        buttonBackgroundColor: theme.surface,
        buttonBorderColor: theme.axis,
        rifleColor: theme.muted,
        trackBackgroundColor: darkTrack(theme),
        trackBorderColor: theme.axis
      },
      navigation: {
        buttonOptions: {
          theme: {
            fill: 'transparent',
            stroke: theme.axis,
            style: { color: theme.text },
            states: {
              hover: { fill: theme.navigatorSelection },
              select: { fill: theme.navigatorSelection }
            }
          }
        }
      }
    };
  }

  function applyToChart(chart) {
    if (!chart || chart.destroyed) return;

    const theme = colours(isDarkMode());
    const shared = interactiveTheme(theme);
    chart.update({
      chart: shared.chart,
      title: shared.title,
      subtitle: shared.subtitle,
      legend: shared.legend,
      tooltip: shared.tooltip,
      rangeSelector: shared.rangeSelector,
      navigator: shared.navigator,
      scrollbar: shared.scrollbar,
      navigation: shared.navigation
    }, false);

    chart.xAxis.forEach(function (axis) {
      axis.update(axisTheme(theme), false);
    });
    chart.yAxis.forEach(function (axis) {
      axis.update(axisTheme(theme), false);
    });
    chart.redraw(false);
  }

  function apply() {
    const theme = colours(isDarkMode());
    Highcharts.setOptions(interactiveTheme(theme));
    Highcharts.charts.filter(Boolean).forEach(applyToChart);
  }

  Highcharts.setOptions({
    colors: palette,
    time: { timezone: 'Europe/London' },
    lang: { decimalPoint: '.', thousandsSep: ',' },
    credits: { enabled: false },
    exporting: { enabled: false },
    chart: {
      animation: { duration: 300 },
      spacing: [16, 16, 12, 16]
    },
    accessibility: {
      enabled: true,
      keyboardNavigation: { enabled: true }
    },
    plotOptions: {
      series: {
        animation: { duration: 300 },
        boostThreshold: 5000,
        lineWidth: 1.6,
        marker: { enabled: false },
        states: { inactive: { opacity: 0.55 } }
      },
      column: { borderWidth: 0 }
    }
  });

  apply();

  new MutationObserver(function (mutations) {
    if (mutations.some(function (mutation) { return mutation.attributeName === 'class'; })) {
      apply();
    }
  }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

  window.WeatherChartTheme = {
    apply: apply,
    applyToChart: applyToChart,
    palette: palette
  };
})(window, document);
