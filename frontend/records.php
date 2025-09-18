<?php
include('header.php');
require_once '../dbconn.php';

function fetchAssoc(string $sql): array {
  $result = db_query($sql);
  if (!$result) {
    return [];
  }
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row ?: [];
}

function fetchScalar(string $sql) {
  $result = db_query($sql);
  if (!$result) {
    return null;
  }
  $row = mysqli_fetch_row($result);
  mysqli_free_result($result);
  return $row[0] ?? null;
}

function formatNumber($value, int $decimals = 1): string {
  if ($value === null || $value === '') {
    return '—';
  }
  return number_format((float) $value, $decimals);
}

function formatCount($value): string {
  if ($value === null || $value === '') {
    return '—';
  }
  return number_format((int) $value);
}

function formatRecordDate(?string $timestamp): string {
  if (!$timestamp) {
    return '—';
  }
  try {
    $date = new DateTime($timestamp);
  } catch (Exception $exception) {
    return '—';
  }
  return $date->format('j M Y · H:i');
}

function formatRecordMeta(?string $timestamp, string $prefix = 'Logged'): string {
  $formatted = formatRecordDate($timestamp);
  if ($formatted === '—') {
    return 'Date unavailable';
  }
  return $prefix . ' ' . $formatted;
}

function formatIsoDate(?string $timestamp): ?string {
  if (!$timestamp) {
    return null;
  }
  try {
    $date = new DateTime($timestamp);
  } catch (Exception $exception) {
    return null;
  }
  return $date->format('Y-m-d');
}

function recordLink(?string $timestamp, string $metric): ?string {
  $date = formatIsoDate($timestamp);
  if (!$date) {
    return null;
  }
  return sprintf(
    'dynamic-graph.php?WHAT=%s&SCALE=day&DATE=%s',
    rawurlencode($metric),
    rawurlencode($date)
  );
}

$SQLHOT = "SELECT
  ROUND(archive.outTemp, 1) AS temp,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
WHERE
  archive.outTemp IS NOT NULL
ORDER BY
  archive.outTemp DESC
LIMIT 1";

$SQLCOLD = "SELECT
  ROUND(archive.outTemp, 1) AS temp,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
WHERE
  archive.outTemp IS NOT NULL
ORDER BY
  archive.outTemp ASC
LIMIT 1";

$SQLLONGHOT = "SELECT
  COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` > 35;";

$SQLLONGCOLD = "SELECT
  COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` < -5;";

$SQLGUST = "SELECT
  ROUND(archive.windGust, 1) AS gust,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.windGust DESC
LIMIT 1";

$SQLRAINRATE = "SELECT
  ROUND(archive.rainRate, 1) AS rate,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.rainRate DESC
LIMIT 1";

$hot = fetchAssoc($SQLHOT);
$cold = fetchAssoc($SQLCOLD);
$daysOver35 = fetchScalar($SQLLONGHOT);
$daysUnderMinus5 = fetchScalar($SQLLONGCOLD);
$gust = fetchAssoc($SQLGUST);
$rainRate = fetchAssoc($SQLRAINRATE);

$heroSummaryParts = [];
if (!empty($hot)) {
  $heroSummaryParts[] = 'Record warmth peaks at ' . formatNumber($hot['temp'] ?? null) . '°C (' . formatRecordDate($hot['dt'] ?? null) . ')';
}
if (!empty($cold)) {
  $heroSummaryParts[] = 'Record cold sinks to ' . formatNumber($cold['temp'] ?? null) . '°C (' . formatRecordDate($cold['dt'] ?? null) . ')';
}
if (!empty($gust)) {
  $heroSummaryParts[] = 'Peak gusts reached ' . formatNumber($gust['gust'] ?? null) . ' m/s (' . formatRecordDate($gust['dt'] ?? null) . ')';
}
if (!empty($rainRate)) {
  $heroSummaryParts[] = 'Rainfall intensity topped ' . formatNumber($rainRate['rate'] ?? null) . ' mm/h (' . formatRecordDate($rainRate['dt'] ?? null) . ')';
}
$heroSummary = implode(' • ', array_filter($heroSummaryParts));
if ($heroSummary === '') {
  $heroSummary = 'Explore the standout observations captured by the station archive across temperature, wind and rainfall.';
}

$heatColdSummary = sprintf(
  '%s hot · %s cold',
  formatCount($daysOver35),
  formatCount($daysUnderMinus5)
);

$recordCards = [
  [
    'label' => 'Record High Temperature',
    'value' => formatNumber($hot['temp'] ?? null),
    'unit' => '°C',
    'meta' => formatRecordMeta($hot['dt'] ?? null, 'Set'),
    'icon' => 'fas fa-temperature-high',
    'style' => '--accent: 239 68 68; --accent-strong: 220 38 38; --accent-soft: 252 165 165;',
    'href' => recordLink($hot['dt'] ?? null, 'outTemp'),
  ],
  [
    'label' => 'Record Low Temperature',
    'value' => formatNumber($cold['temp'] ?? null),
    'unit' => '°C',
    'meta' => formatRecordMeta($cold['dt'] ?? null, 'Set'),
    'icon' => 'fas fa-temperature-low',
    'style' => '--accent: 59 130 246; --accent-strong: 37 99 235; --accent-soft: 147 197 253;',
    'href' => recordLink($cold['dt'] ?? null, 'outTemp'),
  ],
  [
    'label' => 'Peak Wind Gust',
    'value' => formatNumber($gust['gust'] ?? null),
    'unit' => 'm/s',
    'meta' => formatRecordMeta($gust['dt'] ?? null, 'Logged'),
    'icon' => 'fas fa-wind',
    'style' => '--accent: 14 165 233; --accent-strong: 2 132 199; --accent-soft: 125 211 252;',
    'href' => recordLink($gust['dt'] ?? null, 'windGust'),
  ],
  [
    'label' => 'Maximum Rain Rate',
    'value' => formatNumber($rainRate['rate'] ?? null),
    'unit' => 'mm/h',
    'meta' => formatRecordMeta($rainRate['dt'] ?? null, 'Logged'),
    'icon' => 'fas fa-cloud-showers-heavy',
    'style' => '--accent: 168 85 247; --accent-strong: 126 34 206; --accent-soft: 196 181 253;',
    'href' => recordLink($rainRate['dt'] ?? null, 'rainRate'),
  ],
  [
    'label' => 'Days Above 35°C',
    'value' => formatCount($daysOver35),
    'unit' => 'days',
    'meta' => 'Heat thresholds recorded since the archive began.',
    'icon' => 'fas fa-sun',
    'style' => '--accent: 249 115 22; --accent-strong: 217 119 6; --accent-soft: 253 186 116;',
    'href' => null,
  ],
  [
    'label' => 'Days Below -5°C',
    'value' => formatCount($daysUnderMinus5),
    'unit' => 'days',
    'meta' => 'Frost-filled mornings captured in the record.',
    'icon' => 'fas fa-snowflake',
    'style' => '--accent: 129 140 248; --accent-strong: 99 102 241; --accent-soft: 165 180 252;',
    'href' => null,
  ],
];
?>

<div class="dashboard-shell space-y-10">
  <section class="dashboard-hero">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="hero-chip">Historical Extremes</span>
        <h1 class="text-3xl md:text-4xl font-bold drop-shadow-sm">Records &amp; Extremes</h1>
        <p><?php echo htmlspecialchars($heroSummary, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="status-card status-card-hero">
          <span class="status-dot" aria-hidden="true" style="background: #38bdf8; box-shadow: 0 0 0 6px rgba(56, 189, 248, 0.22); color: #38bdf8;"></span>
          <div class="status-copy">
            <span class="status-label">Extreme Day Counts</span>
            <span class="status-state"><?php echo htmlspecialchars($heatColdSummary, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <span class="status-chip">Archive Totals</span>
        </div>
      </div>
      <div class="hero-stats-grid">
        <div class="insight-card hero-quick-stats">
          <div class="flex items-baseline justify-between">
            <span class="text-xs uppercase tracking-[0.3em] text-slate-600 dark:text-slate-300">Signature Records</span>
            <i class="fas fa-trophy text-slate-400 dark:text-slate-500"></i>
          </div>
          <ul class="insight-list">
            <li class="flex flex-col gap-1">
              <span class="label">Record High</span>
              <div class="flex items-baseline justify-between gap-4">
                <span class="stat-reading">
                  <span><?php echo htmlspecialchars(formatNumber($hot['temp'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="stat-unit">°C</span>
                </span>
                <span class="stat-meta text-right"><?php echo htmlspecialchars(formatRecordMeta($hot['dt'] ?? null, 'On'), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </li>
            <li class="flex flex-col gap-1">
              <span class="label">Record Low</span>
              <div class="flex items-baseline justify-between gap-4">
                <span class="stat-reading">
                  <span><?php echo htmlspecialchars(formatNumber($cold['temp'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="stat-unit">°C</span>
                </span>
                <span class="stat-meta text-right"><?php echo htmlspecialchars(formatRecordMeta($cold['dt'] ?? null, 'On'), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </li>
            <li class="flex flex-col gap-1">
              <span class="label">Peak Gust</span>
              <div class="flex items-baseline justify-between gap-4">
                <span class="stat-reading">
                  <span><?php echo htmlspecialchars(formatNumber($gust['gust'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="stat-unit">m/s</span>
                </span>
                <span class="stat-meta text-right"><?php echo htmlspecialchars(formatRecordMeta($gust['dt'] ?? null, 'On'), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </li>
            <li class="flex flex-col gap-1">
              <span class="label">Rain Rate</span>
              <div class="flex items-baseline justify-between gap-4">
                <span class="stat-reading">
                  <span><?php echo htmlspecialchars(formatNumber($rainRate['rate'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="stat-unit">mm/h</span>
                </span>
                <span class="stat-meta text-right"><?php echo htmlspecialchars(formatRecordMeta($rainRate['dt'] ?? null, 'On'), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </li>
          </ul>
        </div>
        <div class="hero-stat">
          <span class="stat-label">Days Above 35°C</span>
          <div class="stat-value">
            <span><?php echo htmlspecialchars(formatCount($daysOver35), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="stat-unit">days</span>
          </div>
          <span class="stat-meta">Heat thresholds recorded across the archive.</span>
        </div>
        <div class="hero-stat">
          <span class="stat-label">Days Below -5°C</span>
          <div class="stat-value">
            <span><?php echo htmlspecialchars(formatCount($daysUnderMinus5), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="stat-unit">days</span>
          </div>
          <span class="stat-meta">Frost-filled mornings captured in the record.</span>
        </div>
      </div>
    </div>
  </section>

  <section class="metric-section space-y-6">
    <div class="section-header">
      <div class="space-y-2">
        <h2 class="text-xl md:text-2xl font-semibold">Explore each standout day</h2>
        <p>Jump straight to the Highcharts timeline for the moment every record was set or review cumulative totals at a glance.</p>
      </div>
      <span class="section-chip">
        <i class="fas fa-history"></i>
        Archive View
      </span>
    </div>
    <div class="metric-grid grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
      <?php foreach ($recordCards as $card): ?>
        <?php if (!empty($card['href'])): ?>
          <a href="<?php echo htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8'); ?>" class="metric-card" style="<?php echo htmlspecialchars($card['style'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php else: ?>
          <div class="metric-card" style="<?php echo htmlspecialchars($card['style'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
            <div class="flex items-start justify-between gap-3">
              <div>
                <span class="metric-label"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                <div class="metric-value">
                  <span><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php if (!empty($card['unit'])): ?>
                    <span class="stat-unit"><?php echo htmlspecialchars($card['unit'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($card['meta'])): ?>
                  <p class="metric-meta"><?php echo htmlspecialchars($card['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
              </div>
              <div class="flex-shrink-0 text-3xl">
                <i class="<?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
              </div>
            </div>
        <?php if (!empty($card['href'])): ?>
          </a>
        <?php else: ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php
mysqli_close($link);
include('footer.php');
?>
