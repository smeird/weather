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


<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

            // Parse the data from an inline table using the Highcharts Data plugin
            Highcharts.chart('container2', {
                data: {
                    table: 'freqq',
                    startRow: 0,
                    endRow: 16,
                    endColumn: 5
                },
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
                    y: 00,
                    layout: 'horizontal'
                },
                xAxis: {
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
                        formatter: function() {
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
                }
            });
        });
</script>


<?php

$sql = "SELECT windDir AS wind_dir,
        COUNT(CASE WHEN windSpeed >= 3 THEN windSpeed END) AS 'D',
        COUNT(CASE WHEN windSpeed >= 2 AND windSpeed < 3 THEN windSpeed END) AS 'C',
        COUNT(CASE WHEN windSpeed >= 1 AND windSpeed < 2 THEN windSpeed END) AS 'B',
        COUNT(CASE WHEN windSpeed >= 0 AND windSpeed < 1 THEN windSpeed END) AS 'A'
   FROM
  archive
  $rangeFilter
GROUP BY wind_dir";
$result = db_query($sql);
echo "</div><div class=\"overflow-x-auto mb-3\">";
echo "<table id=\"freqq\" class=\"min-w-full divide-y divide-gray-200 text-sm text-center\">";
echo "<thead class=\"bg-gray-50\"><tr>";
echo "<th>Direction</th>";
echo "<th>&ge;3&nbsp;m/s</th>";
echo "<th>2–3&nbsp;m/s</th>";
echo "<th>1–2&nbsp;m/s</th>";
echo "<th>0–1&nbsp;m/s</th>";
echo "</tr></thead><tbody>";

$dirs = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
while ($row = mysqli_fetch_assoc($result)) {
  $index = round($row['wind_dir'] / 22.5) % 16;
  $wind_dir = $dirs[$index];
  echo "<tr class=\"hover:bg-gray-100 odd:bg-gray-50\"><td class=\"dir\">$wind_dir</td><td class=\"data\">{$row['D']}</td><td class=\"data\">{$row['C']}</td><td class=\"data\">{$row['B']}</td><td class=\"data\">{$row['A']}</td></tr>";
}

echo "</tbody></table></div>";

  mysqli_free_result($result);
  mysqli_close($link);
?>
<?php include('footer.php'); ?>
