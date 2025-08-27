<?php
include('header.php');
include('dbconn.php');

function fetchStats($link, $interval) {
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
  $result = mysqli_query($link, $sql);
  if (! $result) {
    die('Invalid query: ' . mysqli_error($link));
  }
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row;
}

$day = fetchStats($link, '1 DAY');
$week = fetchStats($link, '7 DAY');
$month = fetchStats($link, '1 MONTH');
?>
</head>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-2">
    <h1 class="h3 mb-0 text-gray-800">Extremes</h1>
  </div>
  <div class="card shadow mb-3">
    <div class="card-body">
      <div id="dayChart" style="height: 400px"></div>
    </div>
  </div>
  <div class="card shadow mb-3">
    <div class="card-body">
      <div id="weekChart" style="height: 400px"></div>
    </div>
  </div>
  <div class="card shadow mb-3">
    <div class="card-body">
      <div id="monthChart" style="height: 400px"></div>
    </div>
  </div>
</div>

<script>
function renderChart(container, title, data) {
  Highcharts.chart(container, {
    chart: { type: 'column' },
    title: { text: title },
    xAxis: { categories: ['Temp Out', 'Temp In', 'Humidity In', 'Humidity Out', 'Pressure', 'Rain'] },
    yAxis: { title: { text: '' } },
    series: [{ name: 'Max', data: data.max }, { name: 'Min', data: data.min }]
  });
}

const dayData = {
  max: [<?php echo $day['outTempMax']; ?>, <?php echo $day['inTempMax']; ?>, <?php echo $day['inHumMax']; ?>, <?php echo $day['outHumMax']; ?>, <?php echo $day['baroMax']; ?>, <?php echo $day['rainTotal']; ?>],
  min: [<?php echo $day['outTempMin']; ?>, <?php echo $day['inTempMin']; ?>, <?php echo $day['inHumMin']; ?>, <?php echo $day['outHumMin']; ?>, <?php echo $day['baroMin']; ?>, 0]
};
const weekData = {
  max: [<?php echo $week['outTempMax']; ?>, <?php echo $week['inTempMax']; ?>, <?php echo $week['inHumMax']; ?>, <?php echo $week['outHumMax']; ?>, <?php echo $week['baroMax']; ?>, <?php echo $week['rainTotal']; ?>],
  min: [<?php echo $week['outTempMin']; ?>, <?php echo $week['inTempMin']; ?>, <?php echo $week['inHumMin']; ?>, <?php echo $week['outHumMin']; ?>, <?php echo $week['baroMin']; ?>, 0]
};
const monthData = {
  max: [<?php echo $month['outTempMax']; ?>, <?php echo $month['inTempMax']; ?>, <?php echo $month['inHumMax']; ?>, <?php echo $month['outHumMax']; ?>, <?php echo $month['baroMax']; ?>, <?php echo $month['rainTotal']; ?>],
  min: [<?php echo $month['outTempMin']; ?>, <?php echo $month['inTempMin']; ?>, <?php echo $month['inHumMin']; ?>, <?php echo $month['outHumMin']; ?>, <?php echo $month['baroMin']; ?>, 0]
};

renderChart('dayChart', 'Last 24 Hours', dayData);
renderChart('weekChart', 'Last 7 Days', weekData);
renderChart('monthChart', 'Last Month', monthData);
</script>

<?php mysqli_close($link); ?>
