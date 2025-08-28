<?php include('header.php'); ?>
<div class="bg-white shadow rounded p-4">
  <h2 class="text-xl font-bold mb-4">Seasonal Patterns</h2>
  <div class="mb-4">
    <label for="year-select" class="mr-2">Select years:</label>
    <select id="year-select" multiple class="border rounded p-2"></select>
  </div>
  <div id="seasonal-chart" class="mb-4"></div>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Year</th>
        <th class="px-4 py-2 text-left">Month</th>
        <th class="px-4 py-2 text-left">Avg Temp (°C)</th>
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
    fetch('backend/seasonal-data.php')
      .then(function(resp) { return resp.json(); })
      .then(function(data) {
        allData = data;
        Object.keys(data).sort().forEach(function(year) {
          var opt = document.createElement('option');
          opt.value = year;
          opt.text = year;
          yearSelect.appendChild(opt);
        });
        if (yearSelect.options.length) {
          yearSelect.options[yearSelect.options.length - 1].selected = true;
        }
        render();
      });

    yearSelect.addEventListener('change', render);

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
            '<td class="px-4 py-2">' + row.avgTemp.toFixed(1) + '</td>' +
            '<td class="px-4 py-2">' + row.totalRain.toFixed(1) + '</td>';
          tbody.appendChild(tr);
        });
        if (!categories.length) {
          categories = rows.map(function(r) { return r.month_name; });
        }
        series.push({ name: year, data: rows.map(function(r) { return r.avgTemp; }) });
      });
      Highcharts.chart('seasonal-chart', {
        chart: { type: 'spline' },
        title: { text: 'Average Monthly Temperature' },
        xAxis: { categories: categories },
        yAxis: { title: { text: 'Temperature (°C)' } },
        series: series,
        credits: { enabled: false }
      });
    }
  });
</script>
<?php include('footer.php'); ?>
