(function (window, document) {
  'use strict';

  if (!window.Highcharts) return;

  const Highcharts = window.Highcharts;

  function isElement(value) {
    return Boolean(value && value.nodeType === 1);
  }

  function resolveElement(container, options) {
    const target = isElement(container) || typeof container === 'string'
      ? container
      : options && options.chart && options.chart.renderTo;

    return typeof target === 'string' ? document.getElementById(target) : target;
  }

  function normaliseArguments(containerOrOptions, optionsOrCallback, callback) {
    if (isElement(containerOrOptions) || typeof containerOrOptions === 'string') {
      return {
        container: containerOrOptions,
        options: optionsOrCallback || {},
        callback: callback
      };
    }

    return {
      container: containerOrOptions && containerOrOptions.chart
        ? containerOrOptions.chart.renderTo
        : undefined,
      options: containerOrOptions || {},
      callback: typeof optionsOrCallback === 'function' ? optionsOrCallback : callback
    };
  }

  function destroyExistingChart(element) {
    if (!element) return;

    const chartAttribute = element.getAttribute('data-highcharts-chart');
    if (chartAttribute === null) return;

    const chartIndex = Number(chartAttribute);
    const existing = Number.isInteger(chartIndex) ? Highcharts.charts[chartIndex] : null;
    if (existing && !existing.destroyed) existing.destroy();
  }

  function observeSize(chart) {
    if (!chart || !chart.renderTo || typeof window.ResizeObserver !== 'function') return;

    let frame = null;
    const observer = new window.ResizeObserver(function () {
      if (frame !== null) window.cancelAnimationFrame(frame);
      frame = window.requestAnimationFrame(function () {
        frame = null;
        if (!chart.destroyed) chart.reflow();
      });
    });

    observer.observe(chart.renderTo);
    Highcharts.addEvent(chart, 'destroy', function () {
      observer.disconnect();
      if (frame !== null) window.cancelAnimationFrame(frame);
    });
  }

  function enhance(chart) {
    if (!chart) return chart;

    observeSize(chart);
    if (window.WeatherChartTheme) window.WeatherChartTheme.applyToChart(chart);

    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () {
        if (!chart.destroyed) chart.reflow();
      });
    }

    return chart;
  }

  function create(method, containerOrOptions, optionsOrCallback, callback) {
    const args = normaliseArguments(containerOrOptions, optionsOrCallback, callback);
    const element = resolveElement(args.container, args.options);

    if (!element) {
      throw new Error('WeatherCharts could not find the requested chart container.');
    }

    destroyExistingChart(element);
    return enhance(Highcharts[method](element, args.options, args.callback));
  }

  function chart(containerOrOptions, optionsOrCallback, callback) {
    return create('chart', containerOrOptions, optionsOrCallback, callback);
  }

  function stockChart(containerOrOptions, optionsOrCallback, callback) {
    return create('stockChart', containerOrOptions, optionsOrCallback, callback);
  }

  function reflowAll() {
    Highcharts.charts.filter(Boolean).forEach(function (chartInstance) {
      if (!chartInstance.destroyed) chartInstance.reflow();
    });
  }

  document.addEventListener('fullscreenchange', function () {
    window.requestAnimationFrame(reflowAll);
  });

  window.WeatherCharts = {
    Chart: chart,
    StockChart: stockChart,
    chart: chart,
    stockChart: stockChart,
    charts: Highcharts.charts,
    color: Highcharts.color.bind(Highcharts),
    dateFormat: Highcharts.dateFormat.bind(Highcharts),
    getOptions: Highcharts.getOptions.bind(Highcharts),
    numberFormat: Highcharts.numberFormat.bind(Highcharts),
    setOptions: Highcharts.setOptions.bind(Highcharts),
    Highcharts: Highcharts
  };
})(window, document);
