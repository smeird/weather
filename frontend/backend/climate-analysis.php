<?php
require_once __DIR__ . '/../../dbconn.php';

/**
 * Calculate basic temperature statistics.
 */
function temperature_stats(int $year) {
  $sql = "SELECT AVG(outTemp) AS mean, MIN(outTemp) AS min, MAX(outTemp) AS max FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year";
  $res = db_query($sql);
  $row = mysqli_fetch_assoc($res);

  $seasonalSql = "SELECT
      AVG(CASE WHEN MONTH(FROM_UNIXTIME(dateTime)) IN (12,1,2) THEN outTemp END) AS winter,
      AVG(CASE WHEN MONTH(FROM_UNIXTIME(dateTime)) IN (3,4,5) THEN outTemp END) AS spring,
      AVG(CASE WHEN MONTH(FROM_UNIXTIME(dateTime)) IN (6,7,8) THEN outTemp END) AS summer,
      AVG(CASE WHEN MONTH(FROM_UNIXTIME(dateTime)) IN (9,10,11) THEN outTemp END) AS autumn
    FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year";
  $seasonal = mysqli_fetch_assoc(db_query($seasonalSql));
  $row['seasonal_averages'] = $seasonal;
  return $row;
}

/**
 * Calculate basic rainfall statistics.
 */
function rainfall_stats(int $year) {
  $sql = "SELECT SUM(daily_rain) AS total,
      SUM(CASE WHEN daily_rain >= 0.1 THEN 1 ELSE 0 END) AS rain_days,
      SUM(CASE WHEN daily_rain >= 1 THEN 1 ELSE 0 END) AS wet_days,
      SUM(CASE WHEN daily_rain >= 10 THEN 1 ELSE 0 END) AS heavy_rain_days,
      MAX(daily_rain) AS max_daily
    FROM (
      SELECT DATE(FROM_UNIXTIME(dateTime)) AS day,
             SUM(rain) AS daily_rain
      FROM archive
      WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year
      GROUP BY day
    ) d";
  $row = mysqli_fetch_assoc(db_query($sql));
  return $row;
}

/**
 * Calculate humidity statistics.
 */
function humidity_stats(int $year) {
  $sql = "SELECT
      AVG(outHumidity) AS mean,
      MIN(outHumidity) AS min,
      MAX(outHumidity) AS max,
      COUNT(DISTINCT CASE WHEN outHumidity > 90 THEN DATE(FROM_UNIXTIME(dateTime)) END) AS days_gt_90,
      COUNT(DISTINCT CASE WHEN outHumidity < 30 THEN DATE(FROM_UNIXTIME(dateTime)) END) AS days_lt_30
    FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year";
  return mysqli_fetch_assoc(db_query($sql));
}

/**
 * Calculate wind statistics.
 */
function wind_stats(int $year) {
  $sql = "SELECT AVG(windSpeed) AS mean_speed,
      MAX(windGust) AS max_gust,
      SUM(CASE WHEN windSpeed < 0.5 THEN 1 ELSE 0 END) / COUNT(*) AS calm_frequency
    FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year";
  $row = mysqli_fetch_assoc(db_query($sql));

  $dirSql = "SELECT windDir, COUNT(*) AS cnt FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year
    GROUP BY windDir ORDER BY cnt DESC LIMIT 1";
  $dir = mysqli_fetch_assoc(db_query($dirSql));
  $row['prevailing_direction'] = $dir ? $dir['windDir'] : null;
  return $row;
}

/**
 * Compute derived indices like heat index and wind chill.
 */
function derived_indices_stats(int $year) {
  $sql = "SELECT AVG(
      -42.379 + 2.04901523*(outTemp*9/5+32) + 10.14333127*outHumidity
      - 0.22475541*(outTemp*9/5+32)*outHumidity
      - 0.00683783*(outTemp*9/5+32)*(outTemp*9/5+32)
      - 0.05481717*outHumidity*outHumidity
      + 0.00122874*(outTemp*9/5+32)*(outTemp*9/5+32)*outHumidity
      + 0.00085282*(outTemp*9/5+32)*outHumidity*outHumidity
      - 0.00000199*(outTemp*9/5+32)*(outTemp*9/5+32)*outHumidity*outHumidity
    ) AS heat_index_f,
    AVG(13.12 + 0.6215*outTemp - 11.37*POW(windSpeed*3.6,0.16) + 0.3965*outTemp*POW(windSpeed*3.6,0.16)) AS wind_chill_c
    FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year";
  $row = mysqli_fetch_assoc(db_query($sql));
  return $row;
}

/**
 * Produce monthly climate summaries for the current year.
 */
function climatological_summaries(int $year) {
  $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(dateTime),'%m') AS month,
      AVG(outTemp) AS mean_temp,
      SUM(rain) AS total_rain
    FROM archive
    WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year
    GROUP BY month
    ORDER BY month";
  $res = db_query($sql);
  $summaries = [];
  while ($row = mysqli_fetch_assoc($res)) {
    $summaries[$row['month']] = ['mean_temp' => $row['mean_temp'], 'total_rain' => $row['total_rain']];
  }
  return $summaries;
}

/**
 * Estimate extreme value statistics.
 */
function extreme_value_stats(int $year) {
  $cntRow = mysqli_fetch_assoc(db_query("SELECT COUNT(*) AS cnt FROM archive WHERE rain IS NOT NULL AND YEAR(FROM_UNIXTIME(dateTime)) = $year"));
  $count = (int) $cntRow['cnt'];
  $rain_p95 = null;
  if ($count > 0) {
    $offset = (int) floor(0.95 * ($count - 1));
    $rainRow = mysqli_fetch_assoc(db_query("SELECT rain FROM archive WHERE rain IS NOT NULL AND YEAR(FROM_UNIXTIME(dateTime)) = $year ORDER BY rain LIMIT 1 OFFSET $offset"));
    $rain_p95 = $rainRow ? (float) $rainRow['rain'] : null;
  }
  $gustRow = mysqli_fetch_assoc(db_query("SELECT MAX(windGust) AS max_gust FROM archive WHERE YEAR(FROM_UNIXTIME(dateTime)) = $year"));
  return ['rain_p95' => $rain_p95, 'max_gust' => $gustRow['max_gust']];
}

/**
 * Gather all climate analysis metrics.
 */
function get_climate_analysis(?int $year = null) {
  $year = $year ?: (int) date('Y');
  return [
    'temperature' => temperature_stats($year),
    'rainfall' => rainfall_stats($year),
    'humidity' => humidity_stats($year),
    'wind' => wind_stats($year),
    'derived_indices' => derived_indices_stats($year),
    'climatological_summaries' => climatological_summaries($year),
    'extreme_value_statistics' => extreme_value_stats($year),
  ];
}

if (php_sapi_name() !== 'cli' && empty(debug_backtrace())) {
  header('Content-Type: application/json');
  $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
  echo json_encode(get_climate_analysis($year));
}
