<?php include('header.php'); ?>
<div class="bg-white shadow rounded p-4">
  <h2 class="text-xl font-bold mb-4">Seasonal Patterns</h2>

  <div class="mb-4 flex flex-wrap gap-4">
    <div class="flex items-center gap-2">
      <label for="type-select">Type:</label>
      <select id="type-select" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="temp">Temperature</option>
        <option value="rain">Rain</option>
      </select>
    </div>
    <div id="stat-container" class="flex items-center gap-2">
      <label for="stat-select">Statistic:</label>
      <select id="stat-select" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="avg">Average</option>
        <option value="min">Minimum</option>
        <option value="max">Maximum</option>
        <option value="median">Median</option>
      <option value="std">Std Dev</option>
      </select>
    </div>
    <div class="flex items-center gap-2">
      <label for="year-select">Select years:</label>
      <select id="year-select" multiple class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></select>
    </div>

  </div>
  <div id="seasonal-chart" class="mb-4"></div>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Year</th>
        <th class="px-4 py-2 text-left">Month</th>
        <th id="value-header" class="px-4 py-2 text-left"></th>
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
    var typeSelect = document.getElementById('type-select');
    var statContainer = document.getElementById('stat-container');
    var valueHeader = document.getElementById('value-header');

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
          var val = typeSelect.value === 'temp' ? row.temp.toFixed(1) : row.totalRain.toFixed(1);
          var tr = document.createElement('tr');
          tr.innerHTML = '<td class="px-4 py-2">' + year + '</td>' +
            '<td class="px-4 py-2">' + row.month_name + '</td>' +
            '<td class="px-4 py-2">' + val + '</td>';
          tbody.appendChild(tr);
        });
        if (!categories.length) {
          categories = rows.map(function(r) { return r.month_name; });
        }

        series.push({
          name: year,
          data: rows.map(function(r) {
            return typeSelect.value === 'temp' ? r.temp : r.totalRain;
          })
        });

      });

      if (typeSelect.value === 'temp') {
        valueHeader.textContent = getStatLabel() + ' Temp (°C)';
        Highcharts.chart('seasonal-chart', {
          chart: { type: 'spline' },
          title: { text: getStatLabel() + ' Monthly Temperature' },
          xAxis: { categories: categories },
          yAxis: { title: { text: 'Temperature (°C)' } },
          series: series,
          credits: { enabled: false }
        });
      } else {
        valueHeader.textContent = 'Total Rain (mm)';
        Highcharts.chart('seasonal-chart', {
          chart: { type: 'spline' },
          title: { text: 'Total Monthly Rainfall' },
          xAxis: { categories: categories },
          yAxis: { title: { text: 'Rainfall (mm)' } },
          series: series,
          credits: { enabled: false }
        });
      }
    }

    statSelect.addEventListener('change', loadData);
    yearSelect.addEventListener('change', render);
    typeSelect.addEventListener('change', function() {
      if (typeSelect.value === 'rain') {
        statContainer.classList.add('hidden');
        render();
      } else {
        statContainer.classList.remove('hidden');
        loadData();
      }
    });

    loadData();
  });
</script>
<?php include('footer.php'); ?>
