<?php
require_once '../../dbconn.php';

date_default_timezone_set('Europe/London');

$timezone = new DateTimeZone('Europe/London');
$start = new DateTimeImmutable('today', $timezone);
$end = $start->modify('+1 day');

$startTimestamp = $start->getTimestamp();
$endTimestamp = $end->getTimestamp();

$sql = 'SELECT MAX(outTemp) AS high, MIN(outTemp) AS low FROM archive WHERE dateTime >= ? AND dateTime < ?';
$stmt = mysqli_prepare($link, $sql);
if (! $stmt) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Unable to prepare statement']);
  exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $startTimestamp, $endTimestamp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = $result ? mysqli_fetch_assoc($result) : null;

$high = null;
$low = null;
if ($row) {
  if ($row['high'] !== null) {
    $high = round((float)$row['high'], 1);
  }
  if ($row['low'] !== null) {
    $low = round((float)$row['low'], 1);
  }
}

if ($result) {
  mysqli_free_result($result);
}
mysqli_stmt_close($stmt);

header('Content-Type: application/json');
echo json_encode([
  'high' => $high,
  'low' => $low,
]);
