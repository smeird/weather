<?php
include('header.php');
require_once '../dbconn.php';

 function fetchStats($interval) {
  $sql = "SELECT round(max(archive.outTemp),1) as outTempMax,
                 round(min(archive.outTemp),1) as outTempMin,
                 round(max(archive.inTemp),1) as inTempMax,
                 round(min(archive.inTemp),1) as inTempMin,
                 round(max(archive.inHumidity),1) as inHumMax,
                 round(min(archive.inHumidity),1) as inHumMin,
                 round(max(archive.outHumidity),1) as outHumMax,
                 round(min(archive.outHumidity),1) as outHumMin,
                 round(max(archive.barometer),1) as baroMax,
                 round(min(archive.barometer),1) as baroMin,
                 round(max(archive.rain)-min(archive.rain),1) as rainTotal
          FROM weewx.archive
          WHERE from_unixtime(dateTime) >= now() - INTERVAL $interval;";
    $result = db_query($sql);
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row;
}

 $day = fetchStats('1 DAY');
 $week = fetchStats('7 DAY');
 $month = fetchStats('1 MONTH');
?>
<?php /* Content */ ?>
<div class="space-y-6">
  <h1 class="text-2xl font-bold">Extremes</h1>

  <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow rounded p-4">
    <h2 class="text-xl font-semibold mb-4">Last 24 Hours</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div id="dayChart" class="h-96"></div>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold">Metric</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Max</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Min</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr><td class="px-4 py-2 text-left">Outside Temp</td><td class="px-4 py-2 text-right"><?php echo $day['outTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $day['outTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Temp</td><td class="px-4 py-2 text-right"><?php echo $day['inTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $day['inTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Humidity</td><td class="px-4 py-2 text-right"><?php echo $day['inHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $day['inHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Outside Humidity</td><td class="px-4 py-2 text-right"><?php echo $day['outHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $day['outHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Pressure</td><td class="px-4 py-2 text-right"><?php echo $day['baroMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $day['baroMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Rain (total)</td><td class="px-4 py-2 text-right"><?php echo $day['rainTotal']; ?></td><td class="px-4 py-2 text-right">0</td></tr>
          </tbody>
        </table><!-- Last 24 Hours table -->
      </div>
    </div>
  </div>

  <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow rounded p-4">
    <h2 class="text-xl font-semibold mb-4">Last 7 Days</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div id="weekChart" class="h-96"></div>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold">Metric</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Max</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Min</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr><td class="px-4 py-2 text-left">Outside Temp</td><td class="px-4 py-2 text-right"><?php echo $week['outTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $week['outTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Temp</td><td class="px-4 py-2 text-right"><?php echo $week['inTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $week['inTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Humidity</td><td class="px-4 py-2 text-right"><?php echo $week['inHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $week['inHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Outside Humidity</td><td class="px-4 py-2 text-right"><?php echo $week['outHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $week['outHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Pressure</td><td class="px-4 py-2 text-right"><?php echo $week['baroMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $week['baroMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Rain (total)</td><td class="px-4 py-2 text-right"><?php echo $week['rainTotal']; ?></td><td class="px-4 py-2 text-right">0</td></tr>
          </tbody>
        </table><!-- Last 7 Days table -->
      </div>
    </div>
  </div>

  <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow rounded p-4">
    <h2 class="text-xl font-semibold mb-4">Last Month</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div id="monthChart" class="h-96"></div>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 text-sm">
          <thead>
            <tr>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold">Metric</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Max</th>
              <th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Min</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr><td class="px-4 py-2 text-left">Outside Temp</td><td class="px-4 py-2 text-right"><?php echo $month['outTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $month['outTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Temp</td><td class="px-4 py-2 text-right"><?php echo $month['inTempMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $month['inTempMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Inside Humidity</td><td class="px-4 py-2 text-right"><?php echo $month['inHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $month['inHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Outside Humidity</td><td class="px-4 py-2 text-right"><?php echo $month['outHumMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $month['outHumMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Pressure</td><td class="px-4 py-2 text-right"><?php echo $month['baroMax']; ?></td><td class="px-4 py-2 text-right"><?php echo $month['baroMin']; ?></td></tr>
            <tr><td class="px-4 py-2 text-left">Rain (total)</td><td class="px-4 py-2 text-right"><?php echo $month['rainTotal']; ?></td><td class="px-4 py-2 text-right">0</td></tr>
          </tbody>
        </table><!-- Last Month table -->
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  function renderChart(container, title, data) {
    Highcharts.chart(container, {
      chart: { type: 'column' },
      title: { text: title },
      xAxis: { categories: ['Temp Out', 'Temp In', 'Humidity In', 'Humidity Out', 'Pressure', 'Rain'] },

      yAxis: [{ title: { text: '' } }, { title: { text: 'Pressure (hPa)' }, opposite: true }],
      series: [
        { name: 'Max', data: data.max },
        { name: 'Min', data: data.min },
        { name: 'Pressure Max', data: data.pMax, yAxis: 1, showInLegend: false },
        { name: 'Pressure Min', data: data.pMin, yAxis: 1, showInLegend: false }
      ]

    });
  }

  const dayData = {

    max: [<?php echo $day['outTempMax']; ?>, <?php echo $day['inTempMax']; ?>, <?php echo $day['inHumMax']; ?>, <?php echo $day['outHumMax']; ?>, null, <?php echo $day['rainTotal']; ?>],
    min: [<?php echo $day['outTempMin']; ?>, <?php echo $day['inTempMin']; ?>, <?php echo $day['inHumMin']; ?>, <?php echo $day['outHumMin']; ?>, null, 0],
    pMax: [null, null, null, null, <?php echo $day['baroMax']; ?>, null],
    pMin: [null, null, null, null, <?php echo $day['baroMin']; ?>, null]
  };
  const weekData = {
    max: [<?php echo $week['outTempMax']; ?>, <?php echo $week['inTempMax']; ?>, <?php echo $week['inHumMax']; ?>, <?php echo $week['outHumMax']; ?>, null, <?php echo $week['rainTotal']; ?>],
    min: [<?php echo $week['outTempMin']; ?>, <?php echo $week['inTempMin']; ?>, <?php echo $week['inHumMin']; ?>, <?php echo $week['outHumMin']; ?>, null, 0],
    pMax: [null, null, null, null, <?php echo $week['baroMax']; ?>, null],
    pMin: [null, null, null, null, <?php echo $week['baroMin']; ?>, null]
  };
  const monthData = {
    max: [<?php echo $month['outTempMax']; ?>, <?php echo $month['inTempMax']; ?>, <?php echo $month['inHumMax']; ?>, <?php echo $month['outHumMax']; ?>, null, <?php echo $month['rainTotal']; ?>],
    min: [<?php echo $month['outTempMin']; ?>, <?php echo $month['inTempMin']; ?>, <?php echo $month['inHumMin']; ?>, <?php echo $month['outHumMin']; ?>, null, 0],
    pMax: [null, null, null, null, <?php echo $month['baroMax']; ?>, null],
    pMin: [null, null, null, null, <?php echo $month['baroMin']; ?>, null]

  };

  renderChart('dayChart', 'Last 24 Hours', dayData);
  renderChart('weekChart', 'Last 7 Days', weekData);
  renderChart('monthChart', 'Last Month', monthData);
});
</script>

<?php mysqli_close($link); ?>
<?php include('footer.php'); ?>
