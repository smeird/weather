<?php
include('header.php');
?>
<div class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow rounded p-4 mb-4">
  <h2 class="text-xl font-bold mb-2">Historical Data Explorer</h2>
  <p>Use the handles on the timeline to choose a start and end date. Tick the boxes to show multiple data sets at once.</p>
</div>
<div class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow rounded p-4 mb-4">
  <div class="mb-2">
    <label class="mr-4"><input type="checkbox" id="dataset-rain" class="mr-1" checked>Rain</label>
    <label class="mr-4"><input type="checkbox" id="dataset-outTemp" class="mr-1" checked>Temperature</label>
  </div>
  <div id="history-chart" style="height: 600px;"></div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const datasets = {
      rain: { label: 'Rain', item: 'rain', color: '#3b82f6' },
      outTemp: { label: 'Temperature', item: 'outTemp', color: '#ef4444' }
    };
    const selected = new Set(Object.keys(datasets));
    const chart = Highcharts.stockChart('history-chart', {
      rangeSelector: { selected: 1 },
      navigator: { adaptToUpdatedData: false },
      title: { text: 'Historical Data' },
      series: [],
      xAxis: {
        events: { afterSetExtremes: updateSeries }
      },
      plotOptions: {
        series: { animation: { duration: 800 } },
        areasplinerange: { fillOpacity: 0.2 }
      }
    });
    Object.keys(datasets).forEach(key => {
      document.getElementById(`dataset-${key}`).addEventListener('change', function () {
        if (this.checked) {
          selected.add(key);
        } else {
          selected.delete(key);
        }
        updateSeries();
      });
    });
    function fetchSeries(key, start, end) {
      const item = datasets[key].item;
      return fetch(`backend/range-data.php?itemmm=${item}&start=${start}&end=${end}`)
        .then(r => r.json())
        .then(data => ({ key, data }));
    }
    function updateSeries() {
      const extremes = chart.xAxis[0].getExtremes();
      const start = Math.round(extremes.min);
      const end = Math.round(extremes.max);
      const promises = Array.from(selected).map(key => fetchSeries(key, start, end));
      Promise.all(promises).then(results => {
        while (chart.series.length) {
          chart.series[0].remove(false);
        }
        let allTimes = [];
        results.forEach(result => {
          const sdata = result.data.map(point => [point[0], point[1], point[2]]);
          allTimes = allTimes.concat(sdata.map(p => p[0]));
          chart.addSeries({
            type: 'areasplinerange',
            name: datasets[result.key].label,
            data: sdata,
            color: datasets[result.key].color
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
    updateSeries();
  });
</script>
<?php include('footer.php'); ?>
