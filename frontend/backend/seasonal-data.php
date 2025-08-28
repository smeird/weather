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
  'mean' => 'AVG'
];
$func = isset($funcMap[$stat]) ? $funcMap[$stat] : 'AVG';

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
