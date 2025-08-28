<?php
include('header.php');
require_once '../dbconn.php';

$allowedWhat = ['outTemp','outHumidity','windSpeed','windDir','windGust','windGustDir','barometer','rain','inTemp','inHumidity'];

$month = isset($_GET['MONTH']) ? intval($_GET['MONTH']) : null;
$what = isset($_GET['WHAT']) ? $_GET['WHAT'] : null;

if ($month < 1 || $month > 12) {
  $month = null;
}
if ($what && !in_array($what, $allowedWhat, true)) {
  $what = null;
}

$seriesData = [];
if ($month && $what) {
  $sql = "SELECT YEAR(FROM_UNIXTIME(dateTime)) AS yr, DAY(FROM_UNIXTIME(dateTime)) AS dy, ROUND(AVG($what),1) AS val FROM archive WHERE MONTH(FROM_UNIXTIME(dateTime)) = ? GROUP BY yr, dy ORDER BY yr, dy";
  $stmt = mysqli_prepare($link, $sql);
  mysqli_stmt_bind_param($stmt, 'i', $month);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $seriesData[$row['yr']][] = [(int)$row['dy'], (float)$row['val']];
  }
  mysqli_free_result($result);
  mysqli_stmt_close($stmt);
}
?>
<div class="bg-white shadow rounded p-4 mb-4">
  <form method="get" class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div>
      <label for="what" class="block mb-2 text-sm font-medium text-gray-900">Data</label>
      <select id="what" name="WHAT" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        <option value="outTemp">Outside Temperature</option>
        <option value="outHumidity">Outside Humidity</option>
        <option value="windSpeed">Wind Speed</option>
        <option value="windDir">Wind Direction</option>
        <option value="windGust">Wind Gust Speed</option>
        <option value="windGustDir">Wind Gust Direction</option>
        <option value="barometer">Barometer</option>
        <option value="rain">Rain</option>
        <option value="inTemp">Inside Temperature</option>
        <option value="inHumidity">Inside Humidity</option>
      </select>
    </div>
    <div>
      <label for="month" class="block mb-2 text-sm font-medium text-gray-900">Month</label>
      <select id="month" name="MONTH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        <option value="1">January</option>
        <option value="2">February</option>
        <option value="3">March</option>
        <option value="4">April</option>
        <option value="5">May</option>
        <option value="6">June</option>
        <option value="7">July</option>
        <option value="8">August</option>
        <option value="9">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="w-full rounded border border-green-700 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-700 hover:text-white">Show</button>
    </div>
  </form>
</div>
<script>
document.getElementById('what').value = '<?php echo $what ? htmlspecialchars($what, ENT_QUOTES) : ''; ?>';
document.getElementById('month').value = '<?php echo $month ? htmlspecialchars((string)$month, ENT_QUOTES) : ''; ?>';
</script>
<?php if ($month && $what) { ?>
<div class="bg-white shadow rounded p-4">
  <div id="lastTimeChart" class="w-full h-96 animate-pulse bg-gray-200 flex items-center justify-center">Loading...</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rawData = <?php echo json_encode($seriesData, JSON_NUMERIC_CHECK); ?>;
  const series = Object.keys(rawData).map(function (year) {
    return { name: year, data: rawData[year] };
  });
  Highcharts.chart('lastTimeChart', {
    chart: {
      type: 'area',
      events: {
        load: function () {
          var container = this.renderTo;
          container.classList.remove('animate-pulse', 'bg-gray-200', 'flex', 'items-center', 'justify-center');
        }
      }
    },
    title: { text: '<?php echo $what; ?> for <?php echo $month ? date('F', mktime(0,0,0,$month,1)) : ''; ?>' },
    xAxis: { title: { text: 'Day of Month' } },
    yAxis: { title: { text: '<?php echo $what; ?>' } },
    tooltip: { shared: true },
    plotOptions: {
      series: { marker: { enabled: false } },
      area: { fillOpacity: 0.1 }
    },
    series: series
  });
});
</script>
<?php } ?>
<?php include('footer.php'); ?>
