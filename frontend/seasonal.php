<?php include('header.php'); ?>
<div class="bg-white shadow rounded p-4">
  <h2 class="text-xl font-bold mb-4">Seasonal Patterns</h2>
  <div id="seasonal-chart" class="mb-4"></div>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
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
    fetch('backend/seasonal-data.php')
      .then(function(resp) { return resp.json(); })
      .then(function(data) {
        var tbody = document.getElementById('seasonal-table');
        data.forEach(function(row) {
          var tr = document.createElement('tr');
          tr.innerHTML = '<td class="px-4 py-2">' + row.month_name + '</td>' +
            '<td class="px-4 py-2">' + row.avgTemp.toFixed(1) + '</td>' +
            '<td class="px-4 py-2">' + row.totalRain.toFixed(1) + '</td>';
          tbody.appendChild(tr);
        });
        Highcharts.chart('seasonal-chart', {
          chart: { type: 'spline' },
          title: { text: 'Average Monthly Temperature' },
          xAxis: { categories: data.map(function(r) { return r.month_name; }) },
          yAxis: { title: { text: 'Temperature (°C)' } },
          series: [{ name: 'Avg Temp', data: data.map(function(r) { return r.avgTemp; }) }],
          credits: { enabled: false }
        });
      });
  });
</script>
<?php include('footer.php'); ?>
