<?php
require_once __DIR__ . "/wind-units.php";

// Convert the archive's WeeWX unit system to the units shown in the dashboard.
function dashboard_value($value, string $metric, int $units): ?float
{
  if ($value === null || !is_numeric($value)) return null;
  $value = (float) $value;
  if (in_array($metric, ['outtemp', 'dewpoint', 'windchill'], true) && $units === 1) {
    return ($value - 32) / 1.8;
  }
  if (in_array($metric, ['windspeed', 'windgust'], true)) {
    return weather_wind_kph($value, $units);
  }
  if (in_array($metric, ['pressure', 'barometer'], true) && $units === 1) return $value * 33.8638866667;
  if ($metric === 'rain') return $value * ($units === 1 ? 25.4 : 10);
  return $value;
}

function dashboard_snapshot(PDO $connection): array
{
  $zone = new DateTimeZone('Europe/London');
  $today = new DateTimeImmutable('today', $zone);
  $statement = $connection->prepare('
    WITH latest AS (
      SELECT dateTime, usUnits, outTemp, outHumidity, windSpeed, windGust,
             windDir, windGustDir, pressure, dewpoint, windchill
      FROM weewx.archive ORDER BY dateTime DESC LIMIT 1
    ), totals AS (
      SELECT
        MIN(CASE WHEN usUnits = 1 THEN (outTemp - 32) / 1.8 ELSE outTemp END)
          FILTER (WHERE dateTime >= ?) AS low,
        MAX(CASE WHEN usUnits = 1 THEN (outTemp - 32) / 1.8 ELSE outTemp END)
          FILTER (WHERE dateTime >= ?) AS high,
        SUM(rain * CASE WHEN usUnits = 1 THEN 25.4 ELSE 10 END)
          FILTER (WHERE dateTime >= ?) AS dayrain,
        SUM(rain * CASE WHEN usUnits = 1 THEN 25.4 ELSE 10 END) AS monthrain
      FROM weewx.archive WHERE dateTime >= ? AND dateTime < ?
    )
    SELECT latest.*, totals.* FROM latest CROSS JOIN totals
  ');
  $start = $today->getTimestamp();
  $statement->execute([$start, $start, $start,
    $today->modify('first day of this month')->getTimestamp(), $today->modify('+1 day')->getTimestamp()]);
  $row = $statement->fetch(PDO::FETCH_ASSOC);
  if (!$row) return [];
  $units = (int) $row['usunits'];
  $metrics = ['OutTemp' => 'outtemp', 'OutHumidity' => 'outhumidity',
    'Windchill' => 'windchill', 'Dewpoint' => 'dewpoint', 'Barometer' => 'pressure',
    'windSpeed_kph' => 'windspeed', 'windGust_kph' => 'windgust',
    'windDir' => 'winddir', 'windGustDir' => 'windgustdir'];
  $snapshot = ['observedAt' => (int) $row['datetime']];
  foreach ($metrics as $stat => $column) {
    $snapshot[$stat] = dashboard_value($row[$column], $column, $units);
  }
  foreach (['outTempLow' => 'low', 'outTempHigh' => 'high', 'drain' => 'dayrain', 'mrain' => 'monthrain'] as $stat => $column) {
    $snapshot[$stat] = $row[$column] === null ? null : (float) $row[$column];
  }
  return $snapshot;
}

function dashboard_overview(PDO $connection): array
{
  $end = time();
  $statement = $connection->prepare('
    SELECT dateTime, usUnits, rain, outTemp, barometer, outHumidity, windSpeed, dewpoint, windchill
    FROM weewx.archive WHERE dateTime >= ? AND dateTime <= ? ORDER BY dateTime
  ');
  // Integer bounds let PostgreSQL seek directly into the archive primary key.
  $statement->execute([$end - 86400, $end]);
  $metrics = ['outTemp' => 'outtemp', 'barometer' => 'barometer', 'outHumidity' => 'outhumidity',
    'windspeed' => 'windspeed', 'dewpoint' => 'dewpoint', 'windchill' => 'windchill'];
  $series = array_fill_keys(array_merge(['rain'], array_keys($metrics)), []);
  $rain = [];
  while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $timestamp = (int) $row['datetime'];
    $units = (int) $row['usunits'];
    foreach ($metrics as $name => $column) {
      $value = dashboard_value($row[$column], $column, $units);
      $series[$name][] = [$timestamp * 1000, $value === null ? null : round($value, 1)];
    }
    $hour = intdiv($timestamp, 3600) * 3600000;
    if (!array_key_exists($hour, $rain)) $rain[$hour] = null;
    $value = dashboard_value($row['rain'], 'rain', $units);
    if ($value !== null) $rain[$hour] = ($rain[$hour] ?? 0) + $value;
  }
  // Match the existing overview's latest 24 hourly rainfall buckets.
  foreach (array_slice($rain, -24, null, true) as $timestamp => $value) {
    $series['rain'][] = [$timestamp, $value === null ? null : round($value, 2)];
  }
  return $series;
}
