<?php
require_once '../../dbconn.php';

$start = isset($_GET['start']) ? (int)$_GET['start'] : time() - 86400;
$end = isset($_GET['end']) ? (int)$_GET['end'] : time();

if ($start > $end) {
  http_response_code(400);
  exit('Invalid time range');
}

$sql = sprintf(
  "SELECT dateTime, outTemp, outHumidity, windSpeed, windGust, windDir, barometer, pressure, rain, rainRate, dewpoint, heatindex, windchill, radiation, UV FROM archive WHERE dateTime BETWEEN %d AND %d ORDER BY dateTime",
  $start,
  $end
);
$result = db_query($sql);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
  $data[] = $row;
}
mysqli_free_result($result);
mysqli_close($link);

$json = json_encode($data);
$gzdata = gzencode($json);

header('Content-Type: application/json');
header('Content-Encoding: gzip');
header('Content-Disposition: attachment; filename="weather-data.json.gz"');
header('Content-Length: ' . strlen($gzdata));

echo $gzdata;
