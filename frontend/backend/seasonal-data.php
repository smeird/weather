<?php
require_once __DIR__ . '/../../dbconn.php';

header('Content-Type: application/json');

$years = isset($_GET['years']) ? explode(',', $_GET['years']) : [];
$years = array_filter(array_map('intval', $years));
$where = '';
if (!empty($years)) {
  $where = 'WHERE YEAR(FROM_UNIXTIME(dateTime)) IN (' . implode(',', $years) . ')';
}

$stat = isset($_GET['stat']) ? strtolower($_GET['stat']) : 'avg';
$funcMap = [
  'min' => 'MIN',
  'max' => 'MAX',
  'avg' => 'AVG',
  'mean' => 'AVG',
  'std' => 'STDDEV',
  'median' => null
];
$func = isset($funcMap[$stat]) ? $funcMap[$stat] : 'AVG';
if ($stat === 'median') {
  $sql = "
    SELECT
      YEAR(FROM_UNIXTIME(dateTime)) AS year,
      MONTH(FROM_UNIXTIME(dateTime)) AS month,
      DATE_FORMAT(FROM_UNIXTIME(dateTime), '%b') AS month_name,
      outTemp,
      rain
    FROM weewx.archive
    $where
    ORDER BY year, month;
  ";
  $result = db_query($sql);
  $raw = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $year = $row['year'];
    $month = (int) $row['month'];
    if (!isset($raw[$year])) {
      $raw[$year] = [];
    }
    if (!isset($raw[$year][$month])) {
      $raw[$year][$month] = [
        'month_name' => $row['month_name'],
        'temps' => [],
        'totalRain' => 0
      ];
    }
    $raw[$year][$month]['temps'][] = (float) $row['outTemp'];
    $raw[$year][$month]['totalRain'] += (float) $row['rain'];
  }
  $data = [];
  foreach ($raw as $year => $months) {
    $data[$year] = [];
    foreach ($months as $month => $info) {
      $temps = $info['temps'];
      sort($temps);
      $count = count($temps);
      $mid = (int) ($count / 2);
      $median = $count % 2 ? $temps[$mid] : ($temps[$mid - 1] + $temps[$mid]) / 2;
      $data[$year][] = [
        'month' => $month,
        'month_name' => $info['month_name'],
        'temp' => round($median, 1),
        'totalRain' => round($info['totalRain'], 1)
      ];
    }
  }
  echo json_encode($data);
  exit;
}

$sql = "
  SELECT
    YEAR(FROM_UNIXTIME(dateTime)) AS year,
    MONTH(FROM_UNIXTIME(dateTime)) AS month,
    DATE_FORMAT(FROM_UNIXTIME(dateTime), '%b') AS month_name,
    $func(outTemp) AS temp,
    SUM(rain) AS totalRain
  FROM weewx.archive
  $where
  GROUP BY year, month
  ORDER BY year, month;
";

$result = db_query($sql);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
  $year = $row['year'];
  if (!isset($data[$year])) {
    $data[$year] = [];
  }
  $data[$year][] = [
    'month' => (int) $row['month'],
    'month_name' => $row['month_name'],
    'temp' => round($row['temp'], 1),
    'totalRain' => round($row['totalRain'], 1)
  ];
}

echo json_encode($data);
?>
