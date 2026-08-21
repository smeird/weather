<?php
  include('header.php');
  require_once '../dbconn.php';

  $lastMonth = date('Y-m', strtotime('first day of last month'));
  $daterange  = $_POST['DATE'] ?? $lastMonth;
  $daterange2 = $_POST['DATEEND'] ?? $lastMonth;
  $startMonth = DateTime::createFromFormat('!Y-m', $daterange) ?: new DateTime('first day of last month');
  $endMonth = DateTime::createFromFormat('!Y-m', $daterange2) ?: clone $startMonth;
  if ($endMonth < $startMonth) { [$startMonth, $endMonth] = [$endMonth, $startMonth]; }
  $rangeStart = $startMonth->getTimestamp();
  $rangeEnd = (clone $endMonth)->modify('first day of next month')->getTimestamp();
  $rangeFilter = "WHERE dateTime >= $rangeStart AND dateTime < $rangeEnd";
?>
<div class="site-workspace">
  <header class="workspace-header"><div><span class="workspace-eyebrow">Directional analysis</span><h1>Wind rose</h1><p>Understand prevailing direction and the distribution of wind-speed bands for any monthly range.</p></div><span class="workspace-badge"><i class="fas fa-compass"></i> St Albans</span></header>
    <form action="/windrose.php" method="POST" class="workspace-toolbar">
      <label class="flex items-center">
        <i class="fas fa-calendar-alt mr-2"></i>
        <input type="month" name="DATE" value="<?php echo $daterange; ?>" class="border rounded p-1">
      </label>
      <span>to</span>
      <label class="flex items-center">
        <i class="fas fa-calendar-alt mr-2"></i>
        <input type="month" name="DATEEND" value="<?php echo $daterange2; ?>" class="border rounded p-1">
      </label>
      <input class="workspace-action" type="submit" value="Apply range">
    </form>
  <div class="workspace-panel">
    <div class="workspace-panel-head"><div><h2>Directional frequency</h2><p><?php echo htmlspecialchars($startMonth->format('M Y')); ?> to <?php echo htmlspecialchars($endMonth->format('M Y')); ?></p></div></div>
    <div id="container2" class="workspace-chart"></div>
  </div>
<?php

$sql = "SELECT
    ROUND(windDir / 22.5) % 16 AS dir_index,
    COUNT(CASE WHEN windSpeed >= 3 THEN 1 END) AS 'D',
    COUNT(CASE WHEN windSpeed >= 2 AND windSpeed < 3 THEN 1 END) AS 'C',
    COUNT(CASE WHEN windSpeed >= 1 AND windSpeed < 2 THEN 1 END) AS 'B',
    COUNT(CASE WHEN windSpeed >= 0 AND windSpeed < 1 THEN 1 END) AS 'A'
  FROM
    archive
  $rangeFilter
  GROUP BY dir_index
  ORDER BY dir_index";
  $result = db_query($sql);

  echo "<div class=\"workspace-panel workspace-table-scroll\">";
  echo "<table id=\"freqq\" class=\"workspace-table\">";
  echo "<thead><tr>";
  echo "<th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold\">Direction</th>";
  echo "<th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold\">&ge;3&nbsp;m/s</th>";
  echo "<th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold\">2–3&nbsp;m/s</th>";
  echo "<th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold\">1–2&nbsp;m/s</th>";
  echo "<th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold\">0–1&nbsp;m/s</th>";
  echo "</tr></thead><tbody class=\"divide-y divide-gray-200 dark:divide-gray-700\">";

  $dirs = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
  $data = array_fill(0, 16, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0]);
  while ($row = mysqli_fetch_assoc($result)) {
    $idx = (int)$row['dir_index'];
    $data[$idx]['A'] = $row['A'];
    $data[$idx]['B'] = $row['B'];
    $data[$idx]['C'] = $row['C'];
    $data[$idx]['D'] = $row['D'];
  }

  for ($i = 0; $i < 16; $i++) {
    $wind_dir = $dirs[$i];
    echo "<tr><td class=\"px-4 py-2 text-left\">$wind_dir</td><td class=\"px-4 py-2 text-right\">{$data[$i]['D']}</td><td class=\"px-4 py-2 text-right\">{$data[$i]['C']}</td><td class=\"px-4 py-2 text-right\">{$data[$i]['B']}</td><td class=\"px-4 py-2 text-right\">{$data[$i]['A']}</td></tr>";
  }

  echo "</tbody></table></div>";

  // Prepare data for the wind rose
  $categories = $dirs;
  $categories[] = $dirs[0];
  // Ensure the series data is numeric for charting
  $seriesD = array_map('intval', array_column($data, 'D'));
  $seriesC = array_map('intval', array_column($data, 'C'));
  $seriesB = array_map('intval', array_column($data, 'B'));
  $seriesA = array_map('intval', array_column($data, 'A'));
  $seriesD[] = (int)$data[0]['D'];
  $seriesC[] = (int)$data[0]['C'];
  $seriesB[] = (int)$data[0]['B'];
  $seriesA[] = (int)$data[0]['A'];

  mysqli_free_result($result);
  mysqli_close($link);
?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  WeatherCharts.chart('container2', {
    chart: {
      polar: true,
      type: 'column',
      backgroundColor: 'transparent',
      plotBackgroundColor: 'transparent',
      plotBorderWidth: 0,
      plotShadow: false
    },
    title: { text: null },
    pane: {
      size: '84%'
    },
    legend: {
      reversed: true,
      align: 'center',
      verticalAlign: 'bottom',
      y: 0,
      layout: 'horizontal'
    },
    xAxis: {
      categories: <?php echo json_encode($categories); ?>,
      tickmarkPlacement: 'on'
    },
    yAxis: {
      min: 0,
      endOnTick: false,
      showLastLabel: true,
      title: {
        text: 'Count'
      },
      labels: {
        formatter: function () {
          return this.value + ' count';
        }
      }
    },
    tooltip: {
      valueSuffix: ' count',
      followPointer: true
    },
    plotOptions: {
      series: {
        stacking: 'normal',
        shadow: false,
        groupPadding: 0,
        pointPlacement: 'on'
      }
    },
    series: [
      { name: '\u22653 m/s', data: <?php echo json_encode($seriesD); ?> },
      { name: '2–3 m/s', data: <?php echo json_encode($seriesC); ?> },
      { name: '1–2 m/s', data: <?php echo json_encode($seriesB); ?> },
      { name: '0–1 m/s', data: <?php echo json_encode($seriesA); ?> }
    ]
  });
});
</script>

<?php include('footer.php'); ?>
