<?php
  include('header.php');
  require_once 'dbconn.php';
 
  $lastMonth = date('Y-m', strtotime('first day of last month'));
  $daterange  = $_POST['DATE'] ?? $lastMonth;
  $daterange2 = $_POST['DATEEND'] ?? $lastMonth;
  $rangeFilter = "WHERE DATE_FORMAT(FROM_UNIXTIME(dateTime), '%Y%m') = '" . str_replace('-', '', $daterange) . "'";
  if (!empty($_POST['DATE']) && !empty($_POST['DATEEND'])) {
    $rangeFilter = "WHERE DATE_FORMAT(FROM_UNIXTIME(dateTime), '%Y%m') BETWEEN '" . str_replace('-', '', $daterange) . "' AND '" . str_replace('-', '', $daterange2) . "'";
  }

  echo " <div class=\"container\"><legend>Windrose</legend><div class=\"card mb-3\"><h4 class=\"card-header\">";
  echo "Current Status";
  echo "</h4>";
  echo "<div class=card-body>";
  echo "<p>Select TimeScales </p>";
  echo "<form action=\"/windrose.php\" method=\"POST\" class=\"flex items-center space-x-2\">";
  echo "<label class=\"flex items-center\"><i class=\"fas fa-calendar-alt mr-2\"></i><input type=\"month\" name=\"DATE\" value='" . $daterange . "' class=\"border rounded p-1\"></label>";
  echo "<span>to</span>";
  echo "<label class=\"flex items-center\"><i class=\"fas fa-calendar-alt mr-2\"></i><input type=\"month\" name=\"DATEEND\" value='" . $daterange2 . "' class=\"border rounded p-1\"></label>";
  echo "<input class=\"btn\" type=\"submit\" value=\"Select Date\"></form>";
 
?>
</div></div>
<div class="card mb-3">
  <div id="container2"></div>
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

  echo "<div class=\"overflow-x-auto mb-3\">";
  echo "<table id=\"freqq\" class=\"min-w-full divide-y divide-gray-200 border border-gray-300 text-sm text-center\" data-tabulator=\"true\">";
  echo "<thead class=\"bg-gray-50\"><tr>";
  echo "<th>Direction</th>";
  echo "<th>&ge;3&nbsp;m/s</th>";
  echo "<th>2–3&nbsp;m/s</th>";
  echo "<th>1–2&nbsp;m/s</th>";
  echo "<th>0–1&nbsp;m/s</th>";
  echo "</tr></thead><tbody>";

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
    echo "<tr class=\"hover:bg-gray-100 odd:bg-gray-50\"><td class=\"dir\">$wind_dir</td><td class=\"data\">{$data[$i]['D']}</td><td class=\"data\">{$data[$i]['C']}</td><td class=\"data\">{$data[$i]['B']}</td><td class=\"data\">{$data[$i]['A']}</td></tr>";
  }

  echo "</tbody></table></div>";

  // Prepare data for Highcharts
  $categories = $dirs;
  $categories[] = $dirs[0];
  $seriesD = array_column($data, 'D');
  $seriesC = array_column($data, 'C');
  $seriesB = array_column($data, 'B');
  $seriesA = array_column($data, 'A');
  $seriesD[] = $data[0]['D'];
  $seriesC[] = $data[0]['C'];
  $seriesB[] = $data[0]['B'];
  $seriesA[] = $data[0]['A'];

  mysqli_free_result($result);
  mysqli_close($link);
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  Highcharts.chart('container2', {
    chart: {
      polar: true,
      type: 'column'
    },
    title: {
      text: 'Wind rose for st Albans'
    },
    subtitle: {
      text: 'Source: Smeird'
    },
    pane: {
      size: '95%'
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
        shadow: true,
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
