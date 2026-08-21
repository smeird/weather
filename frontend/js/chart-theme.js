(function (window) {
  'use strict';

  if (!window.echarts) return;

  const dark = document.documentElement.classList.contains('dark');
  const text = dark ? '#e2e8f0' : '#334155';
  const muted = dark ? '#94a3b8' : '#64748b';
  const axis = dark ? 'rgba(148, 163, 184, 0.32)' : 'rgba(100, 116, 139, 0.28)';

  window.echarts.registerTheme('smeird', {
    color: ['#2563eb', '#0891b2', '#e8793f', '#6d5bd0', '#d24b4b', '#5b8c3a', '#c58a18', '#64748b'],
    backgroundColor: 'transparent',
    textStyle: {
      fontFamily: 'Inter, system-ui, sans-serif',
      color: text
    },
    title: {
      textStyle: { color: text, fontFamily: 'Roboto, sans-serif', fontWeight: 700 },
      subtextStyle: { color: muted }
    },
    legend: { textStyle: { color: text } },
    categoryAxis: {
      axisLine: { lineStyle: { color: axis } },
      axisTick: { lineStyle: { color: axis } },
      axisLabel: { color: muted },
      splitLine: { lineStyle: { color: axis } }
    },
    timeAxis: {
      axisLine: { lineStyle: { color: axis } },
      axisTick: { lineStyle: { color: axis } },
      axisLabel: { color: muted },
      splitLine: { lineStyle: { color: axis } }
    },
    valueAxis: {
      axisLine: { lineStyle: { color: axis } },
      axisTick: { lineStyle: { color: axis } },
      axisLabel: { color: muted },
      splitLine: { lineStyle: { color: axis } }
    },
    dataZoom: {
      textStyle: { color: muted },
      dataBackground: { lineStyle: { color: '#94a3b8' }, areaStyle: { color: 'rgba(148, 163, 184, 0.12)' } },
      selectedDataBackground: { lineStyle: { color: '#2563eb' }, areaStyle: { color: 'rgba(37, 99, 235, 0.18)' } }
    }
  });
})(window);
