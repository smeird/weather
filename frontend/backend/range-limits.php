<?php
require_once '../../dbconn.php';

$sql = 'SELECT MIN(dateTime) AS minTime, MAX(dateTime) AS maxTime FROM archive';
$result = db_query($sql);
$row = mysqli_fetch_assoc($result);
mysqli_free_result($result);

$range = [
  'min' => ((int)$row['minTime']) * 1000,
  'max' => ((int)$row['maxTime']) * 1000
];

header('Content-Type: application/json');
echo json_encode($range);
