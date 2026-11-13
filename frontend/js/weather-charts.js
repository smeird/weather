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

  const temperatureLine = {
    boostThreshold: 0,
    custom: { temperatureLine: true }
  };

  function colourTemperatureLines(chart) {
    const gradients = new Map();

    function render() {
      chart.series.forEach(function (series) {
        if (!series.options.custom?.temperatureLine || !series.graph || !series.visible) return;

        const axis = series.yAxis;
        const cold = axis.toPixels(-10, true);
        const freezing = axis.toPixels(0, true);
        const hot = axis.toPixels(40, true);
        if (![cold, freezing, hot].every(Number.isFinite) || cold === hot) return;

        let gradient = gradients.get(axis.index);
        if (!gradient) {
          const id = Highcharts.uniqueKey();
          const element = chart.renderer.createElement('linearGradient')
            .attr({ id: id, gradientUnits: 'userSpaceOnUse', x1: 0, x2: 0 })
            .add(chart.renderer.defs);
          const stops = ['#142e78', '#3b82f6', '#ef4444', '#990015'].map(function (colour) {
            return chart.renderer.createElement('stop').attr({ 'stop-color': colour }).add(element);
          });
          gradient = { id: id, element: element, stops: stops };
          gradients.set(axis.index, gradient);
        }

        // Highcharts draws the line in pane coordinates. The two stops at
        // freezing keep the blue-to-red transition at exactly 0 °C.
        const zeroOffset = (freezing - cold) / (hot - cold);
        gradient.element.attr({ y1: cold, y2: hot });
        [0, zeroOffset, zeroOffset, 1].forEach(function (offset, index) {
          gradient.stops[index].attr({ offset: offset });
        });
        series.graph.attr({ stroke: 'url(' + chart.renderer.url + '#' + gradient.id + ')' });
      });
    }

    Highcharts.addEvent(chart, 'render', render);
    render();
  }

  function enhance(chart) {
    if (!chart) return chart;

    observeSize(chart);
    colourTemperatureLines(chart);
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

  function visibleExtremes(data, ranges, min, max) {
    let low = null;
    let high = null;
    const source = ranges && ranges.length ? ranges : data;
    const isRange = source === ranges;
    (source || []).forEach(function (point) {
      const x = point[0];
      if (!Number.isFinite(x) || x < min || x > max) return;
      const lower = point[1];
      const upper = isRange ? point[2] : lower;
      if (Number.isFinite(lower) && (!low || lower < low.y)) low = { x: x, y: lower };
      if (Number.isFinite(upper) && (!high || upper > high.y)) high = { x: x, y: upper };
    });
    return { low: low, high: high };
  }

  function markExtremes(chart, options) {
    const labels = [];
    const dots = [];

    function clear() {
      labels.splice(0).forEach(function (label) { label.destroy(); });
      dots.splice(0).forEach(function (dot) { dot.destroy(); });
    }

    function render() {
      clear();
      const series = chart.get(options.seriesId);
      if (!series || !series.visible) return;
      const axis = series.xAxis;
      const extremes = visibleExtremes(options.data, options.ranges, axis.min, axis.max);
      const dark = document.documentElement.classList.contains('dark');
      const same = extremes.low && extremes.high && extremes.low.y === extremes.high.y;
      const points = same
        ? [{ point: extremes.low, text: 'High / Low', colour: '#2563eb', below: false }]
        : [{ point: extremes.high, text: 'High', colour: '#dc2626', below: false },
          { point: extremes.low, text: 'Low', colour: '#0284c7', below: true }];

      points.forEach(function (item) {
        if (!item.point) return;
        const x = axis.toPixels(item.point.x);
        const y = series.yAxis.toPixels(item.point.y);
        if (!Number.isFinite(x) || !Number.isFinite(y)) return;
        const text = item.text + ' ' + Highcharts.numberFormat(item.point.y, 1) + (options.unit ? ' ' + options.unit : '');
        const label = chart.renderer.label(text, 0, 0, 'callout', x, y)
          .attr({ fill: dark ? '#111827' : '#ffffff', stroke: item.colour, 'stroke-width': 1, padding: 5, r: 4, zIndex: 8 })
          .css({ color: dark ? '#f8fafc' : '#0f172a', fontSize: '11px', fontWeight: '600', pointerEvents: 'none' })
          .add();
        const box = label.getBBox();
        const left = Math.max(chart.plotLeft + 4, Math.min(x - box.width / 2, chart.plotLeft + chart.plotWidth - box.width - 4));
        let top = item.below ? y + 12 : y - box.height - 12;
        top = Math.max(chart.plotTop + 4, Math.min(top, chart.plotTop + chart.plotHeight - box.height - 4));
        label.attr({ x: left, y: top, anchorX: x, anchorY: y });
        labels.push(label);
        dots.push(chart.renderer.circle(x, y, 3).attr({ fill: item.colour, stroke: dark ? '#111827' : '#ffffff', 'stroke-width': 1, zIndex: 8 }).css({ pointerEvents: 'none' }).add());
      });
    }

    const unbind = Highcharts.addEvent(chart, 'render', render);
    Highcharts.addEvent(chart, 'destroy', function () { unbind(); clear(); });
    render();
  }

  document.addEventListener('fullscreenchange', function () {
    window.requestAnimationFrame(reflowAll);
  });

  window.WeatherCharts = {
    Chart: chart,
    StockChart: stockChart,
    chart: chart,
    stockChart: stockChart,
    markExtremes: markExtremes,
    temperatureLine: temperatureLine,
    charts: Highcharts.charts,
    color: Highcharts.color.bind(Highcharts),
    dateFormat: Highcharts.dateFormat.bind(Highcharts),
    getOptions: Highcharts.getOptions.bind(Highcharts),
    numberFormat: Highcharts.numberFormat.bind(Highcharts),
    setOptions: Highcharts.setOptions.bind(Highcharts),
    Highcharts: Highcharts
  };
})(window, document);
