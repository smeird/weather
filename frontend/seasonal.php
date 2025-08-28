<?php include('header.php'); ?>
<div class="bg-white shadow rounded p-4">
  <h2 class="text-xl font-bold mb-4">Seasonal Patterns</h2>

  <div class="mb-4">
    <label for="year-select" class="mr-2">Select years:</label>
    <select id="year-select" multiple class="border rounded p-2"></select>
  </div>
  <div class="mb-4">
    <label for="stat-select" class="mr-2">Statistic:</label>
    <select id="stat-select" class="border rounded p-2">
      <option value="avg">Average</option>
      <option value="min">Minimum</option>
      <option value="max">Maximum</option>
      <option value="median">Median</option>
      <option value="std">Std Dev</option>
    </select>

  </div>
  <div id="seasonal-chart" class="mb-4"></div>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Year</th>
        <th class="px-4 py-2 text-left">Month</th>
        <th id="temp-header" class="px-4 py-2 text-left">Avg Temp (°C)</th>
        <th class="px-4 py-2 text-left">Total Rain (mm)</th>
      </tr>
    </thead>
    <tbody id="seasonal-table" class="bg-white divide-y divide-gray-200"></tbody>
  </table>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var allData = {};
    var yearSelect = document.getElementById('year-select');
    var statSelect = document.getElementById('stat-select');

    function loadData() {
      var selectedYears = Array.from(yearSelect.selectedOptions).map(function(o) { return o.value; });
      fetch('backend/seasonal-data.php?stat=' + statSelect.value)
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
          allData = data;
          yearSelect.innerHTML = '';
          Object.keys(data).sort().forEach(function(year) {
            var opt = document.createElement('option');
            opt.value = year;
            opt.text = year;
            if (selectedYears.indexOf(year) !== -1) {
              opt.selected = true;
            }
            yearSelect.appendChild(opt);
          });
          if (yearSelect.selectedOptions.length === 0 && yearSelect.options.length) {
            yearSelect.options[yearSelect.options.length - 1].selected = true;
          }
          render();
        });
    }

    function getStatLabel() {
      var map = { min: 'Min', max: 'Max', avg: 'Avg', median: 'Median', std: 'Std Dev' };
      return map[statSelect.value] || 'Avg';
    }

    function render() {
      var selected = Array.from(yearSelect.selectedOptions).map(function(o) { return o.value; });
      var tbody = document.getElementById('seasonal-table');
      tbody.innerHTML = '';
      var categories = [];
      var series = [];
      selected.forEach(function(year) {
        var rows = allData[year] || [];
        rows.forEach(function(row) {
          var tr = document.createElement('tr');
          tr.innerHTML = '<td class="px-4 py-2">' + year + '</td>' +
            '<td class="px-4 py-2">' + row.month_name + '</td>' +
            '<td class="px-4 py-2">' + row.temp.toFixed(1) + '</td>' +
            '<td class="px-4 py-2">' + row.totalRain.toFixed(1) + '</td>';
          tbody.appendChild(tr);
        });
        if (!categories.length) {
          categories = rows.map(function(r) { return r.month_name; });
        }
        series.push({ name: year, data: rows.map(function(r) { return r.temp; }) });
      });
      var stat = statSelect.value;
      document.getElementById('temp-header').textContent = stat === 'std'
        ? 'Temp ' + getStatLabel() + ' (°C)'
        : getStatLabel() + ' Temp (°C)';
      Highcharts.chart('seasonal-chart', {
        chart: { type: 'spline' },
        title: { text: stat === 'std' ? 'Monthly Temp ' + getStatLabel() : getStatLabel() + ' Monthly Temperature' },
        xAxis: { categories: categories },
        yAxis: { title: { text: 'Temperature (°C)' } },
        series: series,
        credits: { enabled: false }
      });
    }

    statSelect.addEventListener('change', loadData);
    yearSelect.addEventListener('change', render);

    loadData();
  });
</script>
<?php include('footer.php'); ?>
