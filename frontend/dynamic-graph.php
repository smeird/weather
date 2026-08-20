<?php
include('header.php');
require_once '../dbconn.php';
$allowedWhat = ['rain','rainRate','inTemp','outTemp','barometer','outHumidity','inHumidity','windSpeed','windGust','windDir','windGustDir','dewpoint','windchill'];
$allowedScale = ['hour','12h','day','48','week','month','qtr','6m','year','all'];
$allowedType = ['MINMAX','STANDARD','AVG'];
$allowedCompare = ['','dewpoint','windchill','outHumidity','windGust','barometer'];

$conditions = [
  'rain' => 'Rain',
  'rainRate' => 'Rain Rate',
  'inTemp' => 'Inside Temperature',
  'outTemp' => 'Outside Temperature',
  'barometer' => 'Barometric Pressure',
  'outHumidity' => 'Outside Humidity',
  'inHumidity' => 'Inside Humidity',
  'windSpeed' => 'Wind Speed',
  'windGust' => 'Highest Wind Gust',
  'windDir' => 'Wind Direction',
  'windGustDir' => 'Wind Gust Direction',
  'dewpoint' => 'Dew Point',
  'windchill' => 'Wind Chill',
];

$what = isset($_GET['WHAT']) ? $_GET['WHAT'] : null;
if (!in_array($what, $allowedWhat, true)) {
    http_response_code(400);
    exit('Invalid WHAT parameter');
}
$scale = isset($_GET['SCALE']) ? $_GET['SCALE'] : 'day';
if (!in_array($scale, $allowedScale, true)) {
    http_response_code(400);
    exit('Invalid SCALE parameter');
}
$type = isset($_GET['TYPE']) ? $_GET['TYPE'] : 'STANDARD';
if (!in_array($type, $allowedType, true)) {
    http_response_code(400);
    exit('Invalid TYPE parameter');
}
$compare = $_GET['COMPARE'] ?? '';
if (!in_array($compare, $allowedCompare, true) || $compare === $what) {
    $compare = '';
}

$date = isset($_GET['DATE']) ? $_GET['DATE'] : null;
if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  http_response_code(400);
  exit('Invalid DATE parameter');
}

$label = $conditions[$what] ?? $what;

$calc = 'AVG';

switch ($what) {
    case "rain":
        $gt = "column";
        $gscale = "mm";
        $calc = "SUM";
        $units = 10;
        break;
    case "rainRate":
        $gt = "spline";
        $gscale = "mm/h";
        $units = 1;
        break;
    case "inTemp":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "outTemp":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "dewpoint":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "windchill":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "barometer":
        $gt = "areaspline";
        $gscale = "mbar";
        $units = 1;
        break;

    case "outHumidity":
        $gt = "spline";
        $gscale = "%";
        $units = 1;
        break;
    case "inHumidity":
        $gt = "areaspline";
        $gscale = "%";
        $units = 1;
        break;
    case "windSpeed":
        $gt = "spline";
        $gscale = "kph";
        $units = 3.6;
        break;
    case "windGust":
        $gt = "spline";
        $gscale = "kph";
        $units = 3.6;
        break;
    case "windDir":
        $gt = "scatter";
        $gscale = "deg";
        $units = 1;
        break;
    case "windGustDir":
        $gt = "scatter";
        $gscale = "deg";
        $units = 1;
        break;
    default:
        $gt = "spline";
        $calc = "AVG";
        $units = 1;
}


if ($date) {
  $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP('$date 00:00:00') AND UNIX_TIMESTAMP('$date 23:59:59') ";
  $interval = 1800; // 30 min
} else {
  switch ($scale) {
    case "hour":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 2 HOUR) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 300; // 5 min
      break;
    case "12h":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 12 HOUR) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 900; // 15 min
      break;
    case "day":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 1800; // 30 min
      break;
    case "48":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 2 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 3600; // 1 hour
      break;
    case "week":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 10800; // 3 hours
      break;
    case "month":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 86400; // 1 day
      break;
    case "qtr":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 3 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 604800; // 1 week
      break;
    case "6m":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 1209600; // 2 weeks
      break;
    case "year":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 YEAR) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 2592000; // 1 month
      break;
    case "all":
      $scalesql = "WHERE dateTime <= UNIX_TIMESTAMP(NOW()) ";
      $interval = 2592000; // 1 month
      break;
    default:
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $interval = 1800; // 30 min
  }
}

$groupby = "GROUP BY FLOOR(dateTime/$interval)";
$xscale = $interval * 1000;
$timesql = "FLOOR(dateTime/$interval)*$interval";

$unitMap = [
  'dewpoint' => '°C', 'windchill' => '°C', 'outHumidity' => '%',
  'windGust' => 'kph', 'barometer' => 'mbar'
];
$compareGraphData = '[]';
$compareLabel = $compare ? ($conditions[$compare] ?? $compare) : '';
$compareUnit = $compare ? ($unitMap[$compare] ?? '') : '';
if ($compare) {
    $compareMultiplier = $compare === 'windGust' ? 3.6 : 1.0;
    $compareSql = "SELECT $timesql * 1000 AS datetime, ROUND(AVG($compare) * ?, 1) AS data FROM weewx.archive $scalesql $groupby ORDER BY datetime ASC";
    $compareStmt = mysqli_prepare($link, $compareSql);
    mysqli_stmt_bind_param($compareStmt, 'd', $compareMultiplier);
    mysqli_stmt_execute($compareStmt);
    $compareResult = mysqli_stmt_get_result($compareStmt);
    $compareRows = [];
    while ($compareRow = mysqli_fetch_assoc($compareResult)) {
        $compareRows[] = "[{$compareRow['datetime']},{$compareRow['data']}]";
    }
    $compareGraphData = "[\n" . join(",\n", $compareRows) . "\n]";
    mysqli_free_result($compareResult);
    mysqli_stmt_close($compareStmt);
}

  $scaleLabel = $date ? $date : 'Last ' . $scale;

  switch ($type) {

    case "MINMAX":
        $rangeCalc = $calc === 'SUM' ? 'SUM' : 'AVG';
        $sql = "select $timesql * 1000 as datetime, round($rangeCalc($what),1) * ? as dataavg, round(MIN($what),1) * ? as datamin, round(MAX($what),1) * ? as datamax FROM weewx.archive $scalesql  $groupby  ORDER BY datetime ASC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'ddd', $units, $units, $units);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rowr = array();
        $rowa = array();
        $minTimestamp = null;
        $maxTimestamp = null;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($minTimestamp === null) {
                $minTimestamp = $row['datetime'];
            }
            $maxTimestamp = $row['datetime'];
            $rowa[] = "[{$row['datetime']},{$row['dataavg']}]";
            $rowr[] = "[{$row['datetime']},{$row['datamin']},{$row['datamax']}]";
        }
        $graphaveragedata = "[\n" . join(",\n", $rowa) . "\n]";
        $graphrangedata = "[\n" . join(",\n", $rowr) . "\n]";

        renderTrendWorkspace($gt, $what, $label, $graphaveragedata, $graphrangedata, $gscale, $scale, $type, $date, $compare, $compareLabel, $compareUnit, $compareGraphData, $minTimestamp, $maxTimestamp);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        break;

    case "AVG":
        $rangeCalc = $calc === 'SUM' ? 'SUM' : 'AVG';
        $sql = "select $timesql * 1000 as datetime, round($rangeCalc($what),1) * ? as dataavg, round(MIN($what),1) * ? as datamin, round(MAX($what),1) * ? as datamax FROM weewx.archive $scalesql  $groupby  ORDER BY datetime ASC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'ddd', $units, $units, $units);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rowr = array();
        $rowa = array();
        $minTimestamp = null;
        $maxTimestamp = null;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($minTimestamp === null) {
                $minTimestamp = $row['datetime'];
            }
            $maxTimestamp = $row['datetime'];
            $rowa[] = "[{$row['datetime']},{$row['dataavg']}]";
            $rowr[] = "[{$row['datetime']},{$row['datamin']},{$row['datamax']}]";
        }
        $graphaveragedata = "[\n" . join(",\n", $rowa) . "\n]";
        $graphrangedata = "[\n" . join(",\n", $rowr) . "\n]";

        renderTrendWorkspace('spline', $what, $label, $graphaveragedata, $graphrangedata, $gscale, $scale, $type, $date, $compare, $compareLabel, $compareUnit, $compareGraphData, $minTimestamp, $maxTimestamp);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        break;



    default:
        if ($calc === "SUM") {
            $sql = "SELECT $timesql * 1000 AS datetime, ifnull(round($calc($what),1),0) * ? AS data FROM weewx.archive $scalesql $groupby ORDER BY datetime ASC";
        } else {
            $sql = "SELECT dateTime *1000 AS datetime, ifnull(round($what,1),0) * ? AS data FROM weewx.archive $scalesql ORDER BY dateTime ASC";
        }
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'd', $units);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = array();
        $minTimestamp = null;
        $maxTimestamp = null;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($minTimestamp === null) {
                $minTimestamp = $row['datetime'];
            }
            $maxTimestamp = $row['datetime'];
            $rows[] = "[{$row['datetime']},{$row['data']}]";
        }
        $graphdata = "[\n" . join(",\n", $rows) . "\n]";

        renderTrendWorkspace($gt, $what, $label, $graphdata, '[]', $gscale, $scale, $type, $date, $compare, $compareLabel, $compareUnit, $compareGraphData, $minTimestamp, $maxTimestamp);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
}

function renderTrendWorkspace($gt, $metric, $label, $primaryData, $rangeData, $unit, $scale, $mode, $date, $compare, $compareLabel, $compareUnit, $compareData, $xmin = null, $xmax = null)
{
    $metricOptions = [
      'outTemp' => 'Outside temperature', 'outHumidity' => 'Outside humidity',
      'windSpeed' => 'Wind speed', 'windGust' => 'Wind gust',
      'windDir' => 'Wind direction', 'windGustDir' => 'Gust direction',
      'barometer' => 'Barometric pressure', 'rain' => 'Rainfall',
      'rainRate' => 'Rain rate', 'inTemp' => 'Inside temperature',
      'inHumidity' => 'Inside humidity', 'dewpoint' => 'Dew point',
      'windchill' => 'Wind chill'
    ];
    $scaleOptions = ['hour' => '2H', '12h' => '12H', 'day' => '24H', '48' => '48H', 'week' => '7D', 'month' => '1M', 'qtr' => '3M', '6m' => '6M', 'year' => '1Y', 'all' => 'All'];
    $modeOptions = ['STANDARD' => 'Line', 'MINMAX' => 'Min / max', 'AVG' => 'Average range'];
    $compareOptions = ['' => 'No comparison', 'dewpoint' => 'Dew point', 'windchill' => 'Wind chill', 'outHumidity' => 'Outside humidity', 'windGust' => 'Wind gust', 'barometer' => 'Barometer'];
?>
<main class="trend-workspace">
  <header class="trend-header">
    <div>
      <span class="trend-eyebrow">Trend workspace</span>
      <h1><?php echo htmlspecialchars($label, ENT_QUOTES); ?></h1>
      <p>Explore station observations, compare related signals and inspect the selected period.</p>
    </div>
    <div class="trend-header-actions">
      <span class="trend-quality" data-quality><i class="fas fa-circle"></i><span>Checking latest point</span></span>
      <button type="button" class="trend-icon-button" data-share-chart><i class="fas fa-link"></i><span>Copy link</span></button>
    </div>
  </header>

  <form class="trend-toolbar" method="get" action="/dynamic-graph.php" data-trend-form>
    <label class="trend-control trend-sensor"><span>Sensor</span><select name="WHAT">
      <?php foreach ($metricOptions as $value => $text) { ?><option value="<?php echo $value; ?>"<?php echo $metric === $value ? ' selected' : ''; ?>><?php echo htmlspecialchars($text, ENT_QUOTES); ?></option><?php } ?>
    </select></label>
    <fieldset class="trend-segments"><legend>Period</legend><div>
      <?php foreach ($scaleOptions as $value => $text) { ?><label><input type="radio" name="SCALE" value="<?php echo $value; ?>"<?php echo $scale === $value && !$date ? ' checked' : ''; ?>><span><?php echo $text; ?></span></label><?php } ?>
    </div></fieldset>
    <label class="trend-control"><span>Display</span><select name="TYPE">
      <?php foreach ($modeOptions as $value => $text) { ?><option value="<?php echo $value; ?>"<?php echo $mode === $value ? ' selected' : ''; ?>><?php echo $text; ?></option><?php } ?>
    </select></label>
    <label class="trend-control"><span>Compare</span><select name="COMPARE">
      <?php foreach ($compareOptions as $value => $text) { if ($value === $metric) continue; ?><option value="<?php echo $value; ?>"<?php echo $compare === $value ? ' selected' : ''; ?>><?php echo htmlspecialchars($text, ENT_QUOTES); ?></option><?php } ?>
    </select></label>
    <label class="trend-control trend-date"><span>Specific day</span><input type="date" name="DATE" value="<?php echo htmlspecialchars((string)$date, ENT_QUOTES); ?>"></label>
    <button class="trend-apply" type="submit"><i class="fas fa-arrow-rotate-right"></i><span>Update</span></button>
  </form>

  <section class="trend-summary" aria-label="Period summary">
    <div><span>Latest</span><strong data-summary="latest">—</strong><small><?php echo htmlspecialchars($unit, ENT_QUOTES); ?></small></div>
    <div><span>Low</span><strong data-summary="min">—</strong><small><?php echo htmlspecialchars($unit, ENT_QUOTES); ?></small></div>
    <div><span>High</span><strong data-summary="max">—</strong><small><?php echo htmlspecialchars($unit, ENT_QUOTES); ?></small></div>
    <div><span>Average</span><strong data-summary="avg">—</strong><small><?php echo htmlspecialchars($unit, ENT_QUOTES); ?></small></div>
    <div><span>Period change</span><strong data-summary="change">—</strong><small><?php echo htmlspecialchars($unit, ENT_QUOTES); ?></small></div>
    <div><span>Observations</span><strong data-summary="count">—</strong><small>points</small></div>
  </section>

  <section class="trend-chart-panel">
    <div class="trend-chart-heading">
      <div><h2><?php echo htmlspecialchars($label, ENT_QUOTES); ?> over time</h2><p data-period-label><?php echo $date ? htmlspecialchars($date, ENT_QUOTES) : htmlspecialchars($scaleOptions[$scale] ?? $scale, ENT_QUOTES); ?> · Local station time</p></div>
      <div class="trend-date-nav"<?php echo $date ? '' : ' hidden'; ?>><button type="button" data-shift-day="-1" aria-label="Previous day"><i class="fas fa-chevron-left"></i></button><button type="button" data-shift-day="1" aria-label="Next day"><i class="fas fa-chevron-right"></i></button></div>
    </div>
    <div id="container" class="trend-chart" aria-label="<?php echo htmlspecialchars($label, ENT_QUOTES); ?> chart">Loading observations…</div>
  </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.Highcharts) return;
  var primary = <?php echo $primaryData; ?>;
  var ranges = <?php echo $rangeData; ?>;
  var comparison = <?php echo $compareData; ?>;
  var unit = <?php echo json_encode($unit); ?>;
  var metricKey = <?php echo json_encode($metric); ?>;
  var compareUnit = <?php echo json_encode($compareUnit); ?>;
  var mode = <?php echo json_encode($mode); ?>;
  var chartType = <?php echo json_encode($gt); ?>;
  var values = primary.map(function (point) { return Number(point[1]); }).filter(Number.isFinite);
  var zeroBased = ['rain', 'rainRate', 'windSpeed', 'windGust'].indexOf(metricKey) !== -1;

  function setSummary(name, value) {
    var node = document.querySelector('[data-summary="' + name + '"]');
    if (node) node.textContent = value;
  }
  if (values.length) {
    var min = Math.min.apply(null, values);
    var max = Math.max.apply(null, values);
    var avg = values.reduce(function (sum, value) { return sum + value; }, 0) / values.length;
    var change = values[values.length - 1] - values[0];
    setSummary('latest', values[values.length - 1].toFixed(1));
    setSummary('min', min.toFixed(1));
    setSummary('max', max.toFixed(1));
    setSummary('avg', avg.toFixed(1));
    setSummary('change', (change > 0 ? '+' : '') + change.toFixed(1));
    setSummary('count', values.length.toLocaleString());
  }

  var latestTimestamp = primary.length ? Number(primary[primary.length - 1][0]) : 0;
  var quality = document.querySelector('[data-quality]');
  if (quality) {
    quality.classList.toggle('is-stale', !latestTimestamp || Date.now() - latestTimestamp > 45 * 60 * 1000);
    quality.querySelector('span').textContent = latestTimestamp ? 'Latest ' + Highcharts.dateFormat('%H:%M', latestTimestamp) : 'No observations';
  }

  var series = [{
    name: <?php echo json_encode($label); ?>,
    type: chartType,
    data: primary,
    color: '#2563eb',
    lineWidth: 2,
    yAxis: 0,
    zIndex: 3,
    tooltip: { valueSuffix: ' ' + unit }
  }];
  if (ranges.length && mode === 'MINMAX') {
    series.push({ name: 'Observed range', type: 'columnrange', data: ranges, color: 'rgba(37,99,235,.22)', borderWidth: 0, yAxis: 0, zIndex: 1, tooltip: { valueSuffix: ' ' + unit } });
  } else if (ranges.length && mode === 'AVG') {
    series.push({ name: 'Observed range', type: 'areasplinerange', data: ranges, color: 'rgba(37,99,235,.18)', lineWidth: 0, yAxis: 0, zIndex: 1, tooltip: { valueSuffix: ' ' + unit } });
  }
  if (comparison.length) {
    series.push({ name: <?php echo json_encode($compareLabel); ?>, type: 'spline', data: comparison, color: '#e8793f', lineWidth: 1.7, dashStyle: 'ShortDash', yAxis: compareUnit === unit ? 0 : 1, tooltip: { valueSuffix: ' ' + compareUnit } });
  }

  var yAxes = [{
    title: { text: <?php echo json_encode($label . ' (' . $unit . ')'); ?> },
    startOnTick: false, endOnTick: false, minPadding: .12, maxPadding: .12,
    softThreshold: false, min: zeroBased ? 0 : undefined
  }];
  if (comparison.length && compareUnit !== unit) {
    yAxes.push({ title: { text: <?php echo json_encode($compareLabel); ?> + ' (' + compareUnit + ')' }, opposite: true, startOnTick: false, endOnTick: false, minPadding: .12, maxPadding: .12 });
  }

  window.trendChart = Highcharts.chart('container', {
    chart: { zoomType: 'x', backgroundColor: 'transparent', spacing: [12, 12, 8, 8] },
    title: { text: null }, credits: { enabled: false },
    xAxis: { type: 'datetime', crosshair: { width: 1, color: '#94a3b8', dashStyle: 'ShortDot' } },
    yAxis: yAxes,
    legend: { enabled: series.length > 1, align: 'left', verticalAlign: 'top' },
    tooltip: { shared: true, xDateFormat: '%A %e %b, %H:%M' },
    plotOptions: { series: { animation: false, threshold: zeroBased ? 0 : null, softThreshold: false, marker: { enabled: false, states: { hover: { enabled: true, radius: 3 } } } }, areaspline: { fillOpacity: .18 }, column: { borderRadius: 3 } },
    responsive: { rules: [{ condition: { maxWidth: 620 }, chartOptions: { chart: { spacing: [8, 4, 6, 2] }, yAxis: yAxes.map(function (axis) { return Object.assign({}, axis, { title: { text: null }, labels: { style: { fontSize: '10px' } } }); }), xAxis: { labels: { style: { fontSize: '10px' }, maxStaggerLines: 1 } }, legend: { itemStyle: { fontSize: '10px' } } } }] },
    series: series
  });

  var share = document.querySelector('[data-share-chart]');
  if (share) share.addEventListener('click', function () {
    navigator.clipboard.writeText(window.location.href).then(function () { share.querySelector('span').textContent = 'Copied'; setTimeout(function () { share.querySelector('span').textContent = 'Copy link'; }, 1600); });
  });
  document.querySelectorAll('[data-shift-day]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.querySelector('.trend-toolbar input[name="DATE"]');
      if (!input || !input.value) return;
      var day = new Date(input.value + 'T12:00:00');
      day.setDate(day.getDate() + Number(button.dataset.shiftDay));
      input.value = day.toISOString().slice(0, 10);
      document.querySelector('[data-trend-form]').submit();
    });
  });
});
</script>
<?php
}

function minmaxgraph($gt, $what, $graphrangedata, $graphaveragedata, $gscale, $scale, $xscale, $xmin = null, $xmax = null)
{

    $xAxisBounds = '';
    if ($xmin !== null) {
        $xAxisBounds .= "      min: $xmin,\n";
    }
    if ($xmax !== null) {
        $xAxisBounds .= "      max: $xmax,\n";
    }

    echo "  <div class=\"container-fluid\">
      <div class=\"chart-frame\">
 <div style=\"height: 75vh;\" id=\"container\" class=\"flex items-center justify-center bg-gray-200 animate-pulse\">Loading graph...</div></div></div>
<script type=\"text/javascript\">
 document.addEventListener('DOMContentLoaded', function () {

    var ranges = $graphrangedata ,
    averages =  $graphaveragedata ;


 Highcharts.chart('container', {
  chart: {
      zoomType: 'xy',
      backgroundColor: 'transparent',
      plotBackgroundColor: 'transparent',
      plotBorderWidth: 0,
      plotShadow: false,
      events: {
         load: function() {
             var container = this.renderTo;
             container.classList.remove('animate-pulse','bg-gray-200','flex','items-center','justify-center');
             var series = this.series[0],
                 min = series.dataMin,
                 max = series.dataMax,
                 minPoint = series.data.find(function (p) { return p.y === min; }),
                 maxPoint = series.data.find(function (p) { return p.y === max; });

             this.addSeries({
                 type: 'flags',
                 name: 'Max & Min',
                 data: [{
                     x: minPoint.x,
                     y: min,
                     title: 'Min: ' + min + ' $gscale',
                     shape: 'squarepin'
                 }, {
                     x: maxPoint.x,
                     y: max,
                     title: 'Max:' + max + ' $gscale',
                     shape: 'squarepin'
                 }]
             });
         }
       }
  },
    title: {
        text: ' Max, Min & Avg',
        align: 'left'
    },
    subtitle: {
        text: '$what in $scale',
        align: 'left'
    },
    xAxis: {
        type: 'datetime',

      tickInterval: $xscale,
      minTickInterval: $xscale,
      lineWidth: 2,
{$xAxisBounds}

    },

    yAxis: {
        startOnTick: false,
        crosshair: true,
        min:null,
        title: {
            text: '$what ($gscale)'
        }

    },

    tooltip: {
        crosshairs: true,
        shared: true

    },
    plotOptions: {
      columnrange: {
          dataLabels: {
              enabled: true,
              format: '{y}$gscale'
          }
      },
      spline: {
          dataLabels: {
              enabled: false,
              format: '{y}$gscale'
          }
      }
  },
  rangeSelector: {
            selected: 0
        },
  legend: {
     layout: 'vertical',
     align: 'left',
     verticalAlign: 'top',
     x: 100,
     y: 70,
     floating: true,
     backgroundColor: 'transparent',
     borderWidth: 0,
     shadow: false
 },
    series: [{
        name: '$what',
        data: averages,
        type: '$gt',
        zIndex: 1,
        lineWidth: 4,
        tooltip: {
            valueSuffix: ' $gscale'
        },
        marker: {
            fillColor: 'white',
            lineWidth: 1,
            radius: 2
                }
    }, {
        name: '$what Range',
        data: ranges,
        type: 'columnrange',
        lineWidth: 0,
        linkedTo: ':previous',
        fillOpacity: 0.1,
        zIndex: 10,
        tooltip: {
            valueSuffix: ' $gscale'
        },
        marker: {
            enabled: true
        }
      }]

    })

});


</script>

";
}

function avgrangegraph($what, $graphrangedata, $graphaveragedata, $gscale, $scale, $xscale, $xmin = null, $xmax = null)
{

    $xAxisBounds = '';
    if ($xmin !== null) {
        $xAxisBounds .= "      min: $xmin,\n";
    }
    if ($xmax !== null) {
        $xAxisBounds .= "      max: $xmax,\n";
    }

    echo "  <div class=\"container-fluid\">
      <div class=\"chart-frame\">
 <div style=\"height: 75vh;\" id=\"container\" class=\"flex items-center justify-center bg-gray-200 animate-pulse\">Loading graph...</div></div></div>
<script type=\"text/javascript\">
 document.addEventListener('DOMContentLoaded', function () {

    var ranges = $graphrangedata ,
    averages =  $graphaveragedata ;

 Highcharts.chart('container', {
  chart: {
      zoomType: 'xy',
      backgroundColor: 'transparent',
      plotBackgroundColor: 'transparent',
      plotBorderWidth: 0,
      plotShadow: false,
      events: {
         load: function() {
             var container = this.renderTo;
             container.classList.remove('animate-pulse','bg-gray-200','flex','items-center','justify-center');
         }
       }
  },
    title: {
        text: ' Avg Range',
        align: 'left'
    },
    subtitle: {
        text: '$what in $scale',
        align: 'left'
    },
    xAxis: {
        type: 'datetime',

      tickInterval: $xscale,

      minTickInterval: $xscale,

      lineWidth: 2,
{$xAxisBounds}

    },

    yAxis: {
        startOnTick: false,
        crosshair: true,
        min:null,
        title: {
            text: '$what ($gscale)'
        },

    },

    tooltip: {
        crosshairs: true,
        shared: true

    },
    plotOptions: {
      arearange: {
          lineWidth: 1,
          marker: {
              enabled: false
          }
      },

      spline: {

          marker: {
              enabled: false
          }
      }
  },
  rangeSelector: {
            selected: 0
        },
  legend: {
     layout: 'vertical',
     align: 'left',
     verticalAlign: 'top',
     x: 100,
     y: 70,
     floating: true,
     backgroundColor: 'transparent',
     borderWidth: 0,
     shadow: false
 },
    series: [{
        name: '$what',
        data: averages,

        type: 'spline',

        zIndex: 1,
        lineWidth: 1,
        tooltip: {
            valueSuffix: ' $gscale'
        }
    }, {
        name: '$what Range',
        data: ranges,
        type: 'areasplinerange',
        lineWidth: 1,
        linkedTo: ':previous',
        fillOpacity: 0.1,
        zIndex: 0,
        tooltip: {
            valueSuffix: ' $gscale'
        },
        marker: {
            enabled: false
        }
      }]

    })

});


</script>

";
}

function standardgraph($gt, $what, $graphdata, $gscale, $scale, $xmin = null, $xmax = null)
{
    $xAxisBounds = '';
    if ($xmin !== null) {
        $xAxisBounds .= "         min: $xmin,\n";
    }
    if ($xmax !== null) {
        $xAxisBounds .= "         max: $xmax,\n";
    }
    echo "
    <div class=\"container-fluid\">
      <div class=\"chart-frame\">
 <div style=\"height: 75vh;\" id=\"container\" class=\"flex items-center justify-center bg-gray-200 animate-pulse\">Loading graph...</div></div></div>
 <script type='text/javascript'>
 document.addEventListener('DOMContentLoaded', function () {
  if (!window.Highcharts) {
      return;
  }
  const highchartsOptions = Highcharts.getOptions ? Highcharts.getOptions() : {};
  const chartPalette = highchartsOptions.colors || [];
  const isDarkMode = document.documentElement.classList.contains('dark');
  const lineColor = isDarkMode ? '#60a5fa' : '#2563eb';
  const areaTopColor = isDarkMode ? 'rgba(56, 189, 248, 0.38)' : 'rgba(59, 130, 246, 0.32)';
  const areaBaseColor = isDarkMode ? 'rgba(15, 23, 42, 0)' : 'rgba(255, 255, 255, 0)';
  Highcharts.chart('container', {
     chart: {
         type: '$gt',
         zoomType: 'xy',
         backgroundColor: 'transparent',
         plotBackgroundColor: 'transparent',
         plotBorderWidth: 0,
         plotShadow: false,
         spacing: [6, 6, 6, 6],
         events: {
            load: function() {
                var container = this.renderTo;
                container.classList.remove('animate-pulse','bg-gray-200','flex','items-center','justify-center');
                var series = this.series[0],
                    min = series.dataMin,
                    max = series.dataMax,
                    minPoint = series.data.find(function (p) { return p.y === min; }),
                    maxPoint = series.data.find(function (p) { return p.y === max; });

                this.addSeries({
                    type: 'flags',
                    name: 'Max & Min',
                    data: [{
                        x: minPoint.x,
                        y: min,
                        title: 'Min: ' + min + ' $gscale',
                        shape: 'squarepin'
                    }, {
                        x: maxPoint.x,
                        y: max,
                        title: 'Max:' + max + ' $gscale',
                        shape: 'squarepin'
                    }]
                });
            }
        },
     },
     title: {
         text: '$what',
         align: 'left',
         margin: 6
     },
     subtitle: {
         text: 'Time Period : $scale',
         align: 'left',
         margin: 4
     },
     legend: {
         enabled: false
     },
     xAxis: {
         type: 'datetime',

         title: {
             text: 'Date',
             margin: 8,
             reserveSpace: false
         },
{$xAxisBounds}
     },
     yAxis: {
         title: {
             text: '$what ($gscale)',
             align: 'middle',
             rotation: -90,
             y: 0,
             reserveSpace: true
         },
         labels: {
             reserveSpace: false
         }
     },
     tooltip: {
         crosshairs: true,
         shared: true

     },
     plotOptions: {
               areaspline: {
                  color: lineColor,
                   fillColor: {
                       linearGradient: {
                           x1: 0,
                           y1: 0,
                           x2: 0,
                           y2: 1
                       },
                       stops: [
                           [0, areaTopColor],
                           [1, areaBaseColor]
                       ]
                   },
                   marker: {
                       radius: 1
                   },
                   lineWidth: 1,
                   states: {
                       hover: {
                           lineWidth: 1
                       }
                   },
                   threshold: null
               },
               spline: {
                   color: lineColor,
                   fillColor: {
                       linearGradient: {
                           x1: 0,
                           y1: 0,
                           x2: 0,
                           y2: 1
                       },
                       stops: [
                           [0, Highcharts.color(lineColor).setOpacity(0.32).get('rgba')],
                           [1, areaBaseColor]
                       ]
                   },
                   marker: {
                       radius: 0
                   },
                   lineWidth: 1,

                   states: {
                       hover: {
                           lineWidth: 1
                       }
                   },
                   threshold: null
               },


         series: {
           threshold: 0
         }
     },

     series: [{
         name: '$what',
         data:  $graphdata,
         tooltip: {
             valueSuffix: ' $gscale'
         }
 }]
});
});
 </script>
 ";
}

include('footer.php');
