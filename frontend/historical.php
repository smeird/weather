<?php
include('header.php');
?>
<div class="site-workspace">
<header class="workspace-header"><div><span class="workspace-eyebrow">Full station archive</span><h1>Historical explorer</h1><p>Layer sensors, zoom through the archive and inspect long-range relationships from one timeline.</p></div><span class="workspace-badge"><i class="fas fa-database"></i> Interactive range</span></header>
<div class="workspace-panel">
  <div class="workspace-panel-head"><div><h2>Sensor timeline</h2><p>Toggle series, then drag the navigator to focus the date range</p></div><div id="dataset-controls"></div></div>

  <div id="history-chart" class="workspace-chart"></div>
</div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const datasets = {
      rain: { label: 'Rain', item: 'rain', color: '#3b82f6', type: 'column' },
      outTemp: { label: 'Temperature', item: 'outTemp', color: '#ef4444' },
      outHumidity: { label: 'Humidity', item: 'outHumidity', color: '#10b981' },
      windSpeed: { label: 'Wind Speed', item: 'windSpeed', color: '#f59e0b' },
      barometer: { label: 'Pressure', item: 'barometer', color: '#8b5cf6' }
    };
    const selected = new Set(Object.keys(datasets));
    const controls = document.getElementById('dataset-controls');
    Object.keys(datasets).forEach(key => {
      const label = document.createElement('label');
      label.className = 'mr-4';
      const input = document.createElement('input');
      input.type = 'checkbox';
      input.id = `dataset-${key}`;
      input.className = 'mr-1';
      input.checked = true;
      label.appendChild(input);
      label.appendChild(document.createTextNode(datasets[key].label));
      controls.appendChild(label);
      input.addEventListener('change', function () {
        if (this.checked) {
          selected.add(key);
        } else {
          selected.delete(key);
        }
        updateSeries();
      });
    });

    let chart;
    let fullRange = { min: 0, max: Date.now() };

    function fetchSeries(key, start, end) {
      const item = datasets[key].item;
      return fetch(`backend/range-data.php?itemmm=${item}&start=${start}&end=${end}`)
        .then(r => r.json())
        .then(data => ({ key, data }));
    }
    function updateSeries() {
      const extremes = chart.xAxis[0].getExtremes();
      let start = Math.round(extremes.min);
      let end = Math.round(extremes.max);
      if (!isFinite(start) || !isFinite(end) || (start === 0 && end === 1)) {
        end = fullRange.max;
        start = end - 30 * 24 * 3600 * 1000;
        chart.xAxis[0].setExtremes(start, end, false);
      }

      const promises = Array.from(selected).map(key => fetchSeries(key, start, end));
      Promise.all(promises).then(results => {
        while (chart.series.length) {
          chart.series[0].remove(false);
        }
        let allTimes = [];
        results.forEach(result => {
          const ds = datasets[result.key];
          const sdata = ds.type === 'column'
            ? result.data.map(point => [point[0], point[1]])
            : result.data.map(point => [point[0], point[1], point[2]]);
          allTimes = allTimes.concat(sdata.map(p => p[0]));
          chart.addSeries({
            type: ds.type || 'areasplinerange',
            name: ds.label,
            data: sdata,
            color: ds.color
          }, false);
        });
        chart.redraw();
        if (allTimes.length) {
          const minTime = Math.min.apply(null, allTimes);
          const maxTime = Math.max.apply(null, allTimes);
          chart.xAxis[0].removePlotLine('min-range');
          chart.xAxis[0].removePlotLine('max-range');
          chart.xAxis[0].addPlotLine({ value: minTime, color: '#10b981', dashStyle: 'ShortDash', width: 2, id: 'min-range' });
          chart.xAxis[0].addPlotLine({ value: maxTime, color: '#10b981', dashStyle: 'ShortDash', width: 2, id: 'max-range' });
        }
      });
    }

    fetch('backend/range-limits.php')
      .then(r => r.json())
      .then(range => {
        fullRange = range;
        chart = WeatherCharts.stockChart('history-chart', {
          chart: {
            backgroundColor: 'transparent',
            plotBackgroundColor: 'transparent',
            plotBorderWidth: 0,
            plotShadow: false
          },
          rangeSelector: { selected: 0 },
          navigator: { adaptToUpdatedData: false },
          title: { text: 'Historical Data' },
          series: [],
          xAxis: {
            min: range.min,
            max: range.max,
            events: { afterSetExtremes: updateSeries }
          },
          plotOptions: {
            series: { animation: { duration: 800 } },
            areasplinerange: { fillOpacity: 0.2 }
          }
        });
        const initEnd = range.max;
        const initStart = initEnd - 30 * 24 * 3600 * 1000;
        chart.xAxis[0].setExtremes(initStart, initEnd, false);
        updateSeries();
      });
  });
</script>
<?php include('footer.php'); ?>
