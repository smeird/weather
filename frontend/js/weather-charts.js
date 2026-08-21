(function (window) {
  'use strict';

  if (!window.echarts) return;

  const palette = ['#2563eb', '#0891b2', '#e8793f', '#6d5bd0', '#d24b4b', '#5b8c3a', '#c58a18', '#64748b'];
  const charts = [];

  function asArray(value) {
    if (value === undefined || value === null) return [];
    return Array.isArray(value) ? value : [value];
  }

  function resolveElement(container, config) {
    if (typeof container === 'string') return document.getElementById(container);
    if (container && container.nodeType === 1) return container;
    const renderTo = config && config.chart && config.chart.renderTo;
    return typeof renderTo === 'string' ? document.getElementById(renderTo) : renderTo;
  }

  function resolveColour(value, fallback) {
    if (typeof value === 'string') return value;
    if (value && Array.isArray(value.stops) && value.stops.length) {
      return value.stops[0][1];
    }
    return fallback;
  }

  function dashType(value) {
    const map = {
      Dash: 'dashed',
      ShortDash: 'dashed',
      Dot: 'dotted',
      ShortDot: 'dotted',
      DashDot: 'dashed'
    };
    return map[value] || 'solid';
  }

  function formatDate(timestamp, pattern) {
    const date = new Date(Number(timestamp));
    if (!Number.isFinite(date.getTime())) return '';
    const replacements = {
      '%Y': String(date.getFullYear()),
      '%y': String(date.getFullYear()).slice(-2),
      '%m': String(date.getMonth() + 1).padStart(2, '0'),
      '%b': date.toLocaleDateString('en-GB', { month: 'short' }),
      '%B': date.toLocaleDateString('en-GB', { month: 'long' }),
      '%e': String(date.getDate()),
      '%d': String(date.getDate()).padStart(2, '0'),
      '%H': String(date.getHours()).padStart(2, '0'),
      '%M': String(date.getMinutes()).padStart(2, '0'),
      '%S': String(date.getSeconds()).padStart(2, '0'),
      '%A': date.toLocaleDateString('en-GB', { weekday: 'long' }),
      '%a': date.toLocaleDateString('en-GB', { weekday: 'short' })
    };
    return Object.keys(replacements).reduce((result, token) => result.split(token).join(replacements[token]), pattern || '%e %b %Y %H:%M');
  }

  function numberFormat(value, decimals) {
    return Number(value).toLocaleString('en-GB', {
      minimumFractionDigits: decimals || 0,
      maximumFractionDigits: decimals || 0
    });
  }

  function rgba(colour, opacity) {
    if (!colour || colour[0] !== '#') return colour;
    let hex = colour.slice(1);
    if (hex.length === 3) hex = hex.split('').map(char => char + char).join('');
    if (hex.length !== 6) return colour;
    const values = [0, 2, 4].map(index => parseInt(hex.slice(index, index + 2), 16));
    return `rgba(${values[0]}, ${values[1]}, ${values[2]}, ${opacity})`;
  }

  function pointValues(data) {
    return asArray(data).map((point, index) => {
      if (Array.isArray(point)) return point;
      if (point && typeof point === 'object') {
        if (point.x !== undefined && point.y !== undefined) return [point.x, point.y];
        if (point.value !== undefined) return point.value;
      }
      return point;
    });
  }

  function pointObjects(data) {
    return asArray(data).map((point, index) => {
      if (Array.isArray(point)) return { x: point[0], y: point[1], value: point };
      if (point && typeof point === 'object') {
        const value = point.value !== undefined ? point.value : point.y;
        return Object.assign({ x: point.x !== undefined ? point.x : index, y: value, value }, point);
      }
      return { x: index, y: point, value: point };
    });
  }

  function rawExtent(series) {
    let min;
    let max;
    asArray(series).forEach(item => {
      pointObjects(item.data).forEach(point => {
        const value = Array.isArray(point.value) ? point.value[0] : point.x;
        const numeric = Number(value);
        if (!Number.isFinite(numeric)) return;
        min = min === undefined ? numeric : Math.min(min, numeric);
        max = max === undefined ? numeric : Math.max(max, numeric);
      });
    });
    return { min, max };
  }

  function seriesApi(owner, config) {
    let dataMin;
    let dataMax;
    pointObjects(config.data).forEach(point => {
      const value = Number(point.y);
      if (!Number.isFinite(value)) return;
      dataMin = dataMin === undefined ? value : Math.min(dataMin, value);
      dataMax = dataMax === undefined ? value : Math.max(dataMax, value);
    });
    return {
      options: config,
      data: pointObjects(config.data),
      dataMin,
      dataMax,
      setData(data, redraw) {
        config.data = data;
        owner.refreshSeriesApi();
        if (redraw !== false && !owner.inLoad) owner.render();
      },
      remove(redraw) {
        const index = owner.config.series.indexOf(config);
        if (index !== -1) owner.config.series.splice(index, 1);
        owner.refreshSeriesApi();
        if (redraw !== false && !owner.inLoad) owner.render();
      }
    };
  }

  function rangeSeries(config, index, colour) {
    const isArea = config.type !== 'columnrange';
    return {
      id: config.id || `series-${index}`,
      name: config.name || '',
      type: 'custom',
      coordinateSystem: 'cartesian2d',
      xAxisIndex: config.xAxis || 0,
      yAxisIndex: config.yAxis || 0,
      data: pointValues(config.data),
      encode: { x: 0, y: [1, 2] },
      tooltip: { valueFormatter: value => `${numberFormat(value, config.tooltip && config.tooltip.valueDecimals)}${config.tooltip && config.tooltip.valueSuffix || ''}` },
      itemStyle: { color: rgba(resolveColour(config.color, colour), isArea ? 0.32 : 0.82) },
      renderItem(params, api) {
        const low = api.coord([api.value(0), api.value(1)]);
        const high = api.coord([api.value(0), api.value(2)]);
        const shape = window.echarts.graphic.clipRectByRect({
          x: low[0] - (isArea ? 3 : 5),
          y: high[1],
          width: isArea ? 6 : 10,
          height: Math.max(1, low[1] - high[1])
        }, params.coordSys);
        return shape && { type: 'rect', shape, style: api.style() };
      }
    };
  }

  function normalSeries(config, index, colour, polar) {
    const type = config.type || 'line';
    const isColumn = type === 'column' || type === 'bar';
    const isArea = type === 'area' || type === 'areaspline';
    const isFlags = type === 'flags';
    const output = {
      id: config.id || `series-${index}`,
      name: config.name || '',
      type: isColumn || polar ? 'bar' : (isFlags ? 'scatter' : 'line'),
      data: isFlags ? pointObjects(config.data).map(point => ({
        value: [point.x, point.y],
        title: point.title || '',
        name: point.title || ''
      })) : pointValues(config.data),
      xAxisIndex: polar ? undefined : (config.xAxis || 0),
      yAxisIndex: polar ? undefined : (config.yAxis || 0),
      coordinateSystem: polar ? 'polar' : 'cartesian2d',
      stack: config.stack || (polar ? 'wind' : undefined),
      smooth: type === 'spline' || type === 'areaspline',
      connectNulls: Boolean(config.connectNulls),
      symbol: isFlags ? 'pin' : 'circle',
      symbolSize: isFlags ? 16 : ((config.marker && config.marker.enabled) ? 6 : 3),
      showSymbol: isFlags || Boolean(config.marker && config.marker.enabled),
      lineStyle: {
        width: config.lineWidth === undefined ? 2 : config.lineWidth,
        type: dashType(config.dashStyle),
        color: resolveColour(config.color, colour)
      },
      itemStyle: {
        color: resolveColour(config.color, colour),
        borderRadius: isColumn ? [4, 4, 0, 0] : 0
      },
      areaStyle: isArea ? { color: rgba(resolveColour(config.fillColor, resolveColour(config.color, colour)), 0.25) } : undefined,
      barMaxWidth: polar ? 20 : 32,
      tooltip: {
        valueFormatter: value => `${numberFormat(Array.isArray(value) ? value[value.length - 1] : value, config.tooltip && config.tooltip.valueDecimals)}${config.tooltip && config.tooltip.valueSuffix || ''}`
      }
    };
    if (isFlags) {
      output.label = { show: true, formatter: params => params.data && params.data.title || '', color: '#ffffff', fontSize: 9 };
    }
    return output;
  }

  function axisLabelFormatter(axis) {
    const labels = axis.labels || {};
    if (typeof labels.formatter === 'function') {
      return value => labels.formatter.call({ value });
    }
    if (labels.format) {
      return value => labels.format.replace('{value}', value);
    }
    return undefined;
  }

  function yAxisOption(axis, index, sideIndex, dark) {
    const opposite = Boolean(axis.opposite);
    const text = dark ? '#cbd5e1' : '#475569';
    const grid = dark ? 'rgba(148, 163, 184, 0.2)' : 'rgba(148, 163, 184, 0.18)';
    return {
      type: 'value',
      name: axis.title && axis.title.text || '',
      nameLocation: 'end',
      nameGap: 10,
      position: opposite ? 'right' : 'left',
      offset: axis.offset === undefined ? sideIndex * 46 : Number(axis.offset),
      min: axis.min,
      max: axis.max,
      scale: axis.min === undefined,
      splitLine: { show: index === 0, lineStyle: { color: grid } },
      axisLine: { show: index > 0, lineStyle: { color: grid } },
      axisTick: { show: false },
      axisLabel: { formatter: axisLabelFormatter(axis), color: text, fontSize: 11, margin: 8 },
      nameTextStyle: { color: text, fontSize: 11, fontWeight: 600, padding: [0, 0, 4, 0] }
    };
  }

  class WeatherChart {
    constructor(container, config, stock) {
      this.config = config || {};
      this.config.series = asArray(this.config.series);
      this.stock = Boolean(stock);
      this.renderTo = resolveElement(container, this.config);
      if (!this.renderTo) throw new Error('WeatherCharts could not find the chart container.');
      this.renderTo.setAttribute('role', 'img');
      this.renderTo.setAttribute('aria-label', this.config.title && this.config.title.text || 'Interactive weather chart');
      this.chart = window.echarts.init(this.renderTo, 'smeird', { renderer: 'canvas' });
      this.loaded = false;
      this.inLoad = false;
      this.plotLines = new Map();
      this.dataExtent = rawExtent(this.config.series);
      this.currentExtremes = Object.assign({}, this.dataExtent);
      this.refreshSeriesApi();
      this.xAxis = [this.axisApi()];
      this.installRangeSelector();
      this.bindEvents();
      this.render();
      charts.push(this);
      if (window.ResizeObserver) {
        this.resizeObserver = new ResizeObserver(() => this.chart.resize());
        this.resizeObserver.observe(this.renderTo);
      } else {
        window.addEventListener('resize', () => this.chart.resize());
      }
    }

    refreshSeriesApi() {
      this.series = this.config.series.map(config => seriesApi(this, config));
    }

    axisApi() {
      return {
        getExtremes: () => {
          const raw = rawExtent(this.config.series);
          return {
            min: this.currentExtremes.min === undefined ? raw.min : this.currentExtremes.min,
            max: this.currentExtremes.max === undefined ? raw.max : this.currentExtremes.max,
            dataMin: raw.min,
            dataMax: raw.max
          };
        },
        setExtremes: (min, max) => {
          this.currentExtremes = { min, max };
          this.chart.dispatchAction({ type: 'dataZoom', startValue: min, endValue: max });
        },
        addPlotLine: line => {
          this.plotLines.set(line.id || `line-${this.plotLines.size}`, line);
          if (!this.inLoad) this.render();
        },
        removePlotLine: id => {
          this.plotLines.delete(id);
          if (!this.inLoad) this.render();
        }
      };
    }

    installRangeSelector() {
      const selector = this.config.rangeSelector;
      if (!selector || !Array.isArray(selector.buttons) || !this.renderTo.parentNode) return;
      const controls = document.createElement('div');
      controls.className = 'weather-range-selector';
      controls.setAttribute('aria-label', 'Chart time range');

      selector.buttons.forEach(definition => {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = definition.text;
        button.addEventListener('click', () => {
          const extent = this.dataExtent;
          if (extent.max === undefined) return;
          if (definition.type === 'all') {
            this.xAxis[0].setExtremes(extent.min, extent.max);
            return;
          }
          const end = new Date(extent.max);
          const start = new Date(extent.max);
          const count = definition.count || 1;
          if (definition.type === 'day') start.setDate(start.getDate() - count);
          if (definition.type === 'week') start.setDate(start.getDate() - count * 7);
          if (definition.type === 'month') start.setMonth(start.getMonth() - count);
          if (definition.type === 'year') start.setFullYear(start.getFullYear() - count);
          this.xAxis[0].setExtremes(start.getTime(), end.getTime());
        });
        controls.appendChild(button);
      });

      if (selector.inputEnabled) {
        const startInput = document.createElement('input');
        const endInput = document.createElement('input');
        startInput.type = 'date';
        endInput.type = 'date';
        startInput.setAttribute('aria-label', 'Chart start date');
        endInput.setAttribute('aria-label', 'Chart end date');
        const applyDates = () => {
          if (!startInput.value || !endInput.value) return;
          const start = new Date(`${startInput.value}T00:00:00`).getTime();
          const end = new Date(`${endInput.value}T23:59:59`).getTime();
          if (Number.isFinite(start) && Number.isFinite(end) && start <= end) this.xAxis[0].setExtremes(start, end);
        };
        startInput.addEventListener('change', applyDates);
        endInput.addEventListener('change', applyDates);
        controls.appendChild(startInput);
        controls.appendChild(endInput);
      }

      this.renderTo.parentNode.insertBefore(controls, this.renderTo);
      this.rangeControls = controls;
    }

    bindEvents() {
      this.chart.on('datazoom', () => {
        const option = this.chart.getOption();
        const zoom = option.dataZoom && option.dataZoom[0];
        if (zoom) {
          this.currentExtremes = {
            min: zoom.startValue === undefined ? this.currentExtremes.min : zoom.startValue,
            max: zoom.endValue === undefined ? this.currentExtremes.max : zoom.endValue
          };
        }
        this.emitAfterSetExtremes();
      });
    }

    emitAfterSetExtremes() {
      const xAxis = asArray(this.config.xAxis)[0] || {};
      const handler = xAxis.events && xAxis.events.afterSetExtremes;
      if (typeof handler === 'function') {
        const raw = rawExtent(this.config.series);
        handler.call(this.xAxis[0], {
          min: this.currentExtremes.min,
          max: this.currentExtremes.max,
          dataMin: raw.min,
          dataMax: raw.max
        });
      }
    }

    buildOption() {
      const config = this.config;
      const chartConfig = config.chart || {};
      const dark = document.documentElement.classList.contains('dark');
      const text = dark ? '#e2e8f0' : '#334155';
      const muted = dark ? '#94a3b8' : '#64748b';
      const axisColour = dark ? 'rgba(148, 163, 184, 0.38)' : 'rgba(100, 116, 139, 0.34)';
      const xAxis = asArray(config.xAxis)[0] || {};
      const yAxes = asArray(config.yAxis);
      const polar = Boolean(chartConfig.polar || config.pane);
      const series = config.series.map((item, index) => {
        const colour = palette[index % palette.length];
        const type = item.type || chartConfig.type || 'line';
        const sharedDefaults = config.plotOptions && config.plotOptions.series || {};
        const typeDefaults = config.plotOptions && config.plotOptions[type] || {};
        const normalised = Object.assign({}, sharedDefaults, typeDefaults, item, {
          type,
          marker: Object.assign({}, sharedDefaults.marker || {}, typeDefaults.marker || {}, item.marker || {}),
          tooltip: Object.assign({}, config.tooltip || {}, item.tooltip || {})
        });
        if (['arearange', 'areasplinerange', 'columnrange'].includes(type)) return rangeSeries(normalised, index, colour);
        return normalSeries(normalised, index, colour, polar);
      });
      const plotLines = asArray(xAxis.plotLines).concat(Array.from(this.plotLines.values()));
      if (plotLines.length && series.length) {
        series[0].markLine = {
          silent: true,
          symbol: ['none', 'none'],
          data: plotLines.map(line => ({
            xAxis: line.value,
            lineStyle: { color: resolveColour(line.color, '#64748b'), type: dashType(line.dashStyle), width: line.width || 1 },
            label: { show: Boolean(line.label && line.label.text), formatter: line.label && line.label.text || '', fontSize: 10 }
          }))
        };
      }
      yAxes.forEach((axis, axisIndex) => {
        const lines = asArray(axis.plotLines);
        if (lines.length && series.length) {
          series[0].markLine = series[0].markLine || { silent: true, symbol: ['none', 'none'], data: [] };
          series[0].markLine.data.push(...lines.map(line => ({
            yAxis: line.value,
            lineStyle: { color: resolveColour(line.color, '#64748b'), type: dashType(line.dashStyle), width: line.width || 1 },
            label: { show: Boolean(line.label && line.label.text), formatter: line.label && line.label.text || '', fontSize: 10 }
          })));
        }
      });
      const legendSelected = {};
      config.series.forEach(item => { if (item.visible === false) legendSelected[item.name] = false; });
      const needsZoom = this.stock || String(chartConfig.zoomType || '').includes('x') || config.navigator || config.rangeSelector;
      const hasRightAxes = yAxes.filter(axis => axis && axis.opposite).length;
      const hasLeftAxes = yAxes.length - hasRightAxes;
      const option = {
        animationDuration: 350,
        color: palette,
        backgroundColor: 'transparent',
        aria: { enabled: true, decal: { show: false } },
        textStyle: { fontFamily: 'Inter, system-ui, sans-serif' },
        title: {
          show: Boolean(config.title && config.title.text),
          text: config.title && config.title.text || '',
          subtext: config.subtitle && config.subtitle.text || '',
          left: config.title && config.title.align === 'left' ? 16 : (config.title && config.title.align === 'right' ? 'right' : 'center'),
          top: 0,
          textStyle: { color: text, fontFamily: 'Roboto, sans-serif', fontSize: 15, fontWeight: 700 },
          subtextStyle: { color: muted, fontSize: 11 }
        },
        grid: polar ? undefined : {
          left: 20 + Math.max(1, hasLeftAxes) * 42,
          right: 20 + hasRightAxes * 48,
          top: (config.title && config.title.text) ? 54 : 20,
          bottom: needsZoom ? 76 : 50,
          containLabel: true
        },
        legend: {
          show: !(config.legend && config.legend.enabled === false),
          type: 'scroll',
          bottom: needsZoom ? 42 : 5,
          selected: legendSelected,
          itemWidth: 18,
          itemHeight: 8,
          textStyle: { color: text, fontSize: 11 }
        },
        tooltip: {
          trigger: 'axis',
          confine: true,
          order: 'seriesDesc',
          axisPointer: { type: 'cross', snap: false },
          backgroundColor: 'rgba(15, 23, 42, 0.94)',
          borderWidth: 0,
          textStyle: { color: '#f8fafc', fontSize: 12 }
        },
        series
      };
      if (polar) {
        option.polar = { radius: ['12%', '72%'] };
        option.angleAxis = { type: 'category', data: xAxis.categories || [], startAngle: 90, axisLabel: { fontSize: 11 } };
        option.radiusAxis = { min: 0, axisLabel: { fontSize: 10 }, splitLine: { lineStyle: { color: 'rgba(148, 163, 184, 0.18)' } } };
      } else {
        option.xAxis = {
          type: xAxis.type === 'datetime' || !xAxis.categories ? 'time' : 'category',
          data: xAxis.categories,
          min: xAxis.min,
          max: xAxis.max,
          boundaryGap: Boolean(xAxis.categories),
          axisLine: { lineStyle: { color: axisColour } },
          axisTick: { show: false },
          axisLabel: { formatter: axisLabelFormatter(xAxis), color: muted, hideOverlap: true, fontSize: 11 },
          splitLine: { show: false }
        };
        let leftAxisIndex = 0;
        let rightAxisIndex = 0;
        option.yAxis = yAxes.length ? yAxes.map((axis, index) => {
          const sideIndex = axis && axis.opposite ? rightAxisIndex++ : leftAxisIndex++;
          return yAxisOption(axis, index, sideIndex, dark);
        }) : [yAxisOption({}, 0, 0, dark)];
        if (needsZoom) {
          const zoomState = {};
          if (this.currentExtremes.min !== undefined) zoomState.startValue = this.currentExtremes.min;
          if (this.currentExtremes.max !== undefined) zoomState.endValue = this.currentExtremes.max;
          option.dataZoom = [
            Object.assign({ type: 'inside', xAxisIndex: [0], filterMode: 'none', zoomOnMouseWheel: true, moveOnMouseMove: true }, zoomState),
            Object.assign({ type: 'slider', xAxisIndex: [0], height: 20, bottom: 10, borderColor: 'transparent', fillerColor: 'rgba(37, 99, 235, 0.16)', handleSize: '70%' }, zoomState)
          ];
        }
      }
      return option;
    }

    render() {
      this.chart.setOption(this.buildOption(), true);
      if (!this.loaded) {
        this.loaded = true;
        const load = this.config.chart && this.config.chart.events && this.config.chart.events.load;
        if (typeof load === 'function') {
          this.inLoad = true;
          load.call(this);
          this.inLoad = false;
          this.refreshSeriesApi();
          this.chart.setOption(this.buildOption(), true);
        }
      }
    }

    addSeries(config, redraw) {
      this.config.series.push(config);
      this.refreshSeriesApi();
      if (redraw !== false && !this.inLoad) this.render();
      return this.series[this.series.length - 1];
    }

    redraw() {
      this.render();
    }

    showLoading(message) {
      this.renderTo.classList.add('weather-chart-loading');
      this.renderTo.setAttribute('data-loading-label', message || 'Loading…');
    }

    hideLoading() {
      this.renderTo.classList.remove('weather-chart-loading');
      this.renderTo.removeAttribute('data-loading-label');
    }
  }

  function create(container, config, stock) {
    if (container && typeof container === 'object' && !container.nodeType && config === undefined) {
      config = container;
      container = undefined;
    }
    return new WeatherChart(container, config || {}, stock);
  }

  window.WeatherCharts = {
    charts,
    chart(container, config) { return create(container, config, false); },
    stockChart(container, config) { return create(container, config, true); },
    Chart: function (container, config) { return create(container, config, false); },
    StockChart: function (container, config) { return create(container, config, true); },
    getOptions() { return { colors: palette.slice() }; },
    setOptions() {},
    dateFormat: formatDate,
    numberFormat,
    color(colour) {
      let opacity = 1;
      return {
        setOpacity(value) { opacity = value; return this; },
        get() { return rgba(colour, opacity); }
      };
    }
  };

  new MutationObserver(() => charts.forEach(chart => chart.render())).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
})(window);
