<?php

function astro_detail_duration_for_state(array $segments, string $state): int
{
  $seconds = 0;
  foreach ($segments as $segment) {
    if (($segment['state'] ?? '') === $state) {
      $seconds += max(0, (int) $segment['end'] - (int) $segment['start']);
    }
  }
  return $seconds;
}

function astro_detail_quality_class($value, bool $higherIsBetter = true): string
{
  if (!is_numeric($value)) {
    return 'astro-cell-neutral';
  }
  $number = (float) $value;
  if ($higherIsBetter) {
    return $number > 6 ? 'astro-cell-good' : ($number >= 4 ? 'astro-cell-fair' : 'astro-cell-poor');
  }
  return $number < 10 ? 'astro-cell-good' : ($number <= 30 ? 'astro-cell-fair' : 'astro-cell-poor');
}

function astro_detail_metric(string $icon, string $label, string $value, string $context, string $tone = 'neutral'): string
{
  $allowedTones = ['neutral', 'positive', 'night', 'moon'];
  if (!in_array($tone, $allowedTones, true)) {
    $tone = 'neutral';
  }
  return '<article class="astro-detail-metric astro-detail-metric-' . $tone . '">' .
    '<span class="astro-detail-metric-icon"><i class="' . astro_h($icon) . '"></i></span>' .
    '<div><small>' . astro_h($label) . '</small><strong>' . astro_h($value) . '</strong><p>' . astro_h($context) . '</p></div>' .
    '</article>';
}

function astro_detail_matrix_row(string $icon, string $label, string $description, array $cells): string
{
  $html = '<div class="astro-matrix-row"><div class="astro-matrix-label"><i class="' . astro_h($icon) . '"></i><span>' . astro_h($label) . '<small>' . astro_h($description) . '</small></span></div><div class="astro-matrix-cells">';
  foreach ($cells as $cell) {
    $class = $cell['class'] ?? 'astro-cell-neutral';
    $html .= '<span class="astro-matrix-cell ' . astro_h($class) . '" title="' . astro_h($cell['title'] ?? '') . '"><strong>' . astro_h($cell['value'] ?? '—') . '</strong>';
    if (!empty($cell['unit'])) {
      $html .= '<small>' . astro_h($cell['unit']) . '</small>';
    }
    $html .= '</span>';
  }
  return $html . '</div></div>';
}

function astro_detail_sky_cell(array $segments, int $timestamp): array
{
  foreach ($segments as $segment) {
    if ($timestamp >= $segment['start'] && $timestamp < $segment['end']) {
      $state = $segment['state'] ?? 'poor';
      return [
        'value' => $state === 'good' ? 'Good' : ($state === 'fair' ? 'Mixed' : 'Poor'),
        'class' => 'astro-cell-' . ($state === 'fair' ? 'fair' : ($state === 'good' ? 'good' : 'poor')),
        'title' => $segment['label'] ?? '',
      ];
    }
  }
  return ['value' => '—', 'class' => 'astro-cell-neutral', 'title' => 'Forecast unavailable'];
}

function astro_render_detail_dashboard(string $date, array $forecast): string
{
  $selectedRows = [];
  $sunset = null;
  $sunrise = null;
  foreach ($forecast as $row) {
    if (substr((string) ($row['utcTime'] ?? ''), 0, 10) === $date) {
      $sunset = $sunset ?? ($row['sunset'] ?? null);
      $sunrise = $sunrise ?? ($row['sunrise'] ?? null);
    }
  }

  if (!$sunset || !$sunrise) {
    $sunsetTimestamp = astro_sun_event($date, 'sunset');
    $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
    $sunriseTimestamp = astro_sun_event($nextDate, 'sunrise');
    $sunset = $sunsetTimestamp === null ? null : date('H:i', $sunsetTimestamp);
    $sunrise = $sunriseTimestamp === null ? null : date('H:i', $sunriseTimestamp);
  }

  if (!$sunset || !$sunrise) {
    return '<div class="astro-detail-empty"><i class="fas fa-cloud-moon"></i><strong>Night detail is unavailable</strong><p>Sunset and sunrise timing could not be calculated for this date.</p></div>';
  }

  $plan = astro_build_night_plan($date, (string) $sunset, (string) $sunrise, $forecast);
  if (empty($plan['available'])) {
    return '<div class="astro-detail-empty"><i class="fas fa-cloud-moon"></i><strong>Night detail is unavailable</strong><p>The observing window could not be built for this date.</p></div>';
  }

  foreach ($forecast as $row) {
    $timestamp = astro_forecast_timestamp((string) ($row['utcTime'] ?? ''));
    if ($timestamp === null || $timestamp < $plan['start'] || $timestamp >= $plan['end']) {
      continue;
    }
    $selectedRows[] = ['timestamp' => $timestamp, 'row' => $row];
  }
  usort($selectedRows, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

  if (empty($selectedRows)) {
    return '<div class="astro-detail-empty"><i class="fas fa-cloud-moon"></i><strong>Hourly forecast unavailable</strong><p>The night timings are known, but no hourly condition data was returned.</p></div>';
  }

  $darkSeconds = astro_detail_duration_for_state($plan['darkness'], 'dark');
  $moonFreeSeconds = astro_detail_duration_for_state($plan['moon'], 'down');
  $cloudValues = [];
  $seeingValues = [];
  $transparencyValues = [];
  foreach ($selectedRows as $point) {
    $row = $point['row'];
    if (isset($row['totalcloud']) && is_numeric($row['totalcloud'])) {
      $cloudValues[] = ['value' => (float) $row['totalcloud'], 'timestamp' => $point['timestamp']];
    }
    if (isset($row['seeingIndex']) && is_numeric($row['seeingIndex'])) {
      $seeingValues[] = (float) $row['seeingIndex'];
    }
    if (isset($row['transIndex']) && is_numeric($row['transIndex'])) {
      $transparencyValues[] = (float) $row['transIndex'];
    }
  }
  usort($cloudValues, fn($a, $b) => $a['value'] <=> $b['value']);
  $clearest = $cloudValues[0] ?? null;
  $peakSeeing = empty($seeingValues) ? null : max($seeingValues);
  $averageTransparency = empty($transparencyValues) ? null : array_sum($transparencyValues) / count($transparencyValues);

  $bestContext = ($plan['best_window_start'] ?? null) !== null
    ? date('H:i', $plan['best_window_start']) . '–' . date('H:i', $plan['best_window_end']) . ' strict overlap'
    : 'No strict three-way overlap';
  $metrics = astro_detail_metric('fas fa-camera', 'Possible imaging', $plan['best_window_duration'] ?? '0h', $bestContext, 'positive');
  $metrics .= astro_detail_metric('fas fa-adjust', 'Full darkness', astro_format_duration(0, $darkSeconds), 'Astronomical darkness', 'night');
  $metrics .= astro_detail_metric('fas fa-moon', 'Moon-free sky', astro_format_duration(0, $moonFreeSeconds), ($plan['moon_phase'] ?? 'Moon') . ' · ' . ($plan['moon_illumination'] ?? 0) . '% lit', 'moon');
  $metrics .= astro_detail_metric('fas fa-cloud', 'Clearest hour', $clearest === null ? '—' : round($clearest['value']) . '%', $clearest === null ? 'Cloud data unavailable' : 'At ' . date('H:i', $clearest['timestamp']), 'positive');
  $metrics .= astro_detail_metric('fas fa-eye', 'Peak seeing', $peakSeeing === null ? '—' : number_format($peakSeeing, 1) . '/10', 'Best atmospheric stability');
  $metrics .= astro_detail_metric('fas fa-star', 'Avg transparency', $averageTransparency === null ? '—' : number_format($averageTransparency, 1) . '/10', 'Mean column clarity');

  $matrix = [
    'time' => [], 'sky' => [], 'cloud' => [], 'seeing' => [], 'transparency' => [],
    'pickering' => [], 'low' => [], 'mid' => [], 'high' => [], 'darkness' => [], 'moon' => [],
  ];
  $darknessLabels = [
    'civil' => 'Civil', 'nautical' => 'Nautical',
    'astronomical' => 'Astro', 'dark' => 'Dark',
  ];
  foreach ($selectedRows as $point) {
    $timestamp = $point['timestamp'];
    $midpoint = min($plan['end'] - 1, $timestamp + 1800);
    $row = $point['row'];
    $matrix['time'][] = ['value' => date('H:i', $timestamp), 'class' => 'astro-cell-time', 'title' => date('l j F, H:i', $timestamp)];
    $matrix['sky'][] = astro_detail_sky_cell($plan['sky'], $midpoint);
    foreach ([
      'cloud' => ['key' => 'totalcloud', 'unit' => '%'],
      'low' => ['key' => 'lowcloud', 'unit' => '%'],
      'mid' => ['key' => 'medcloud', 'unit' => '%'],
      'high' => ['key' => 'highcloud', 'unit' => '%'],
    ] as $matrixKey => $field) {
      $value = $row[$field['key']] ?? null;
      $matrix[$matrixKey][] = [
        'value' => is_numeric($value) ? (string) round((float) $value) : '—',
        'unit' => is_numeric($value) ? $field['unit'] : '',
        'class' => astro_detail_quality_class($value, false),
        'title' => is_numeric($value) ? round((float) $value) . '% cloud' : 'Unavailable',
      ];
    }
    foreach (['seeing' => 'seeingIndex', 'transparency' => 'transIndex', 'pickering' => 'pickeringIndex'] as $matrixKey => $field) {
      $value = $row[$field] ?? null;
      $matrix[$matrixKey][] = [
        'value' => is_numeric($value) ? number_format((float) $value, 1) : '—',
        'unit' => is_numeric($value) ? '/10' : '',
        'class' => astro_detail_quality_class($value),
        'title' => is_numeric($value) ? number_format((float) $value, 1) . ' out of 10' : 'Unavailable',
      ];
    }
    $darkness = astro_state_at($plan['darkness'], $midpoint, 'civil');
    $matrix['darkness'][] = [
      'value' => $darknessLabels[$darkness] ?? ucfirst($darkness),
      'class' => 'astro-cell-darkness-' . $darkness,
      'title' => $darknessLabels[$darkness] ?? ucfirst($darkness),
    ];
    $moon = astro_state_at($plan['moon'], $midpoint, 'down');
    $matrix['moon'][] = [
      'value' => $moon === 'up' ? 'Up' : 'Down',
      'class' => 'astro-cell-moon-' . $moon,
      'title' => $moon === 'up' ? 'Moon above horizon' : 'Moon below horizon',
    ];
  }

  $matrixHtml = astro_detail_matrix_row('far fa-clock', 'Local time', 'Hourly forecast', $matrix['time']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-camera', 'Imaging quality', 'Cloud + seeing', $matrix['sky']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-cloud', 'Total cloud', 'Lower is better', $matrix['cloud']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-eye', 'Seeing', 'Higher is better', $matrix['seeing']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-star', 'Transparency', 'Higher is better', $matrix['transparency']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-wave-square', 'Pickering', 'Higher is better', $matrix['pickering']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-cloud', 'Low cloud', 'Lower is better', $matrix['low']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-cloud', 'Mid cloud', 'Lower is better', $matrix['mid']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-cloud', 'High cloud', 'Lower is better', $matrix['high']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-adjust', 'Darkness', 'Twilight stage', $matrix['darkness']);
  $matrixHtml .= astro_detail_matrix_row('fas fa-moon', 'Moon', 'Horizon position', $matrix['moon']);

  $timeline = astro_render_night_plan($plan);
  return '<div class="astro-detail-dashboard">' .
    '<div class="astro-detail-metrics">' . $metrics . '</div>' .
    '<section class="astro-detail-section"><div class="astro-detail-section-head"><div><span>Night at a glance</span><h2>Observing timeline</h2><p>See exactly where usable sky, full darkness and a moon-free horizon overlap.</p></div><i class="fas fa-binoculars"></i></div>' . $timeline . '</section>' .
    '<section class="astro-detail-section astro-detail-matrix-section"><div class="astro-detail-section-head"><div><span>Hour by hour</span><h2>Condition matrix</h2><p>Exact values stay visible; stronger imaging conditions are shown in green.</p></div><i class="fas fa-th"></i></div>' .
    '<div class="astro-matrix-scroll" style="--astro-hours:' . count($selectedRows) . '"><div class="astro-detail-matrix">' . $matrixHtml . '</div></div></section>' .
    '<div class="astro-detail-notes">' .
      '<article><i class="fas fa-eye"></i><div><strong>Seeing</strong><p>Atmospheric steadiness, using cloud, turbulence and low-level wind. Higher values mean sharper potential detail.</p></div></article>' .
      '<article><i class="fas fa-star"></i><div><strong>Transparency</strong><p>Clarity through the full air column, influenced by atmospheric moisture. Higher values favour faint targets.</p></div></article>' .
      '<article><i class="fas fa-wave-square"></i><div><strong>Pickering</strong><p>Expected light distortion from turbulence, wind and temperature changes aloft. Higher values mean steadier stars.</p></div></article>' .
    '</div></div>';
}
