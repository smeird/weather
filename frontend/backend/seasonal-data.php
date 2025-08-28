<?php
require_once __DIR__ . '/../../dbconn.php';

header('Content-Type: application/json');

$sql = "
  SELECT
    MONTH(FROM_UNIXTIME(dateTime)) AS month,
    DATE_FORMAT(FROM_UNIXTIME(dateTime), '%b') AS month_name,
    AVG(outTemp) AS avgTemp,
    SUM(rain) AS totalRain
  FROM weewx.archive
  GROUP BY month
  ORDER BY month;
";

$result = db_query($sql);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
  $data[] = [
    'month' => (int) $row['month'],
    'month_name' => $row['month_name'],
    'avgTemp' => round($row['avgTemp'], 1),
    'totalRain' => round($row['totalRain'], 1)
  ];
}

echo json_encode($data);
?>
