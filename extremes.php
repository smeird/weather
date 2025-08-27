<?php
include('header.php');
require_once 'dbconn.php';

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
</head>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-2">
    <h1 class="h3 mb-0 text-gray-800">Extremes</h1>
  </div>

  <div class="card shadow mb-3">
    <div class="card-header">
      <h2 class="h5 mb-0">Last 24 Hours</h2>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div id="dayChart" style="height: 400px"></div>
        </div>
        <div class="col-md-6">
          <table class="table table-sm">
            <thead>
              <tr><th>Metric</th><th>Max</th><th>Min</th></tr>
            </thead>
            <tbody>
              <tr><td>Outside Temp</td><td><?php echo $day['outTempMax']; ?></td><td><?php echo $day['outTempMin']; ?></td></tr>
              <tr><td>Inside Temp</td><td><?php echo $day['inTempMax']; ?></td><td><?php echo $day['inTempMin']; ?></td></tr>
              <tr><td>Inside Humidity</td><td><?php echo $day['inHumMax']; ?></td><td><?php echo $day['inHumMin']; ?></td></tr>
              <tr><td>Outside Humidity</td><td><?php echo $day['outHumMax']; ?></td><td><?php echo $day['outHumMin']; ?></td></tr>
              <tr><td>Pressure</td><td><?php echo $day['baroMax']; ?></td><td><?php echo $day['baroMin']; ?></td></tr>
              <tr><td>Rain (total)</td><td><?php echo $day['rainTotal']; ?></td><td>0</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow mb-3">
    <div class="card-header">
      <h2 class="h5 mb-0">Last 7 Days</h2>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div id="weekChart" style="height: 400px"></div>
        </div>
        <div class="col-md-6">
          <table class="table table-sm">
            <thead>
              <tr><th>Metric</th><th>Max</th><th>Min</th></tr>
            </thead>
            <tbody>
              <tr><td>Outside Temp</td><td><?php echo $week['outTempMax']; ?></td><td><?php echo $week['outTempMin']; ?></td></tr>
              <tr><td>Inside Temp</td><td><?php echo $week['inTempMax']; ?></td><td><?php echo $week['inTempMin']; ?></td></tr>
              <tr><td>Inside Humidity</td><td><?php echo $week['inHumMax']; ?></td><td><?php echo $week['inHumMin']; ?></td></tr>
              <tr><td>Outside Humidity</td><td><?php echo $week['outHumMax']; ?></td><td><?php echo $week['outHumMin']; ?></td></tr>
              <tr><td>Pressure</td><td><?php echo $week['baroMax']; ?></td><td><?php echo $week['baroMin']; ?></td></tr>
              <tr><td>Rain (total)</td><td><?php echo $week['rainTotal']; ?></td><td>0</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow mb-3">
    <div class="card-header">
      <h2 class="h5 mb-0">Last Month</h2>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div id="monthChart" style="height: 400px"></div>
        </div>
        <div class="col-md-6">
          <table class="table table-sm">
            <thead>
              <tr><th>Metric</th><th>Max</th><th>Min</th></tr>
            </thead>
            <tbody>
              <tr><td>Outside Temp</td><td><?php echo $month['outTempMax']; ?></td><td><?php echo $month['outTempMin']; ?></td></tr>
              <tr><td>Inside Temp</td><td><?php echo $month['inTempMax']; ?></td><td><?php echo $month['inTempMin']; ?></td></tr>
              <tr><td>Inside Humidity</td><td><?php echo $month['inHumMax']; ?></td><td><?php echo $month['inHumMin']; ?></td></tr>
              <tr><td>Outside Humidity</td><td><?php echo $month['outHumMax']; ?></td><td><?php echo $month['outHumMin']; ?></td></tr>
              <tr><td>Pressure</td><td><?php echo $month['baroMax']; ?></td><td><?php echo $month['baroMin']; ?></td></tr>
              <tr><td>Rain (total)</td><td><?php echo $month['rainTotal']; ?></td><td>0</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
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
</script>

<?php mysqli_close($link); ?>
