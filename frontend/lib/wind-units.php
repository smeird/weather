<?php

// WeeWX: US=1 (mph), METRIC=16 (km/h), METRICWX=17 (m/s).
function weather_wind_kph($value, int $units): ?float
{
  if ($value === null || !is_numeric($value)) return null;
  $factor = [1 => 1.609344, 16 => 1, 17 => 3.6][$units] ?? null;
  return $factor === null ? null : (float) $value * $factor;
}

// Only call with allow-listed column names, never a raw request parameter.
function weather_wind_sql(string $column, string $unitColumn = 'usUnits'): string
{
  foreach ([$column, $unitColumn] as $identifier) {
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $identifier)) {
      throw new InvalidArgumentException('Invalid wind column');
    }
  }
  return "(CASE $unitColumn WHEN 16 THEN $column WHEN 17 THEN $column * 3.6 WHEN 1 THEN $column * 1.609344 ELSE NULL END)";
}
