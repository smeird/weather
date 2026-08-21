<?php

function astro_h($value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function astro_local_datetime(string $date, string $time, int $dayOffset = 0): ?DateTimeImmutable
{
  $timezone = new DateTimeZone('Europe/London');
  $base = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . substr($time, 0, 5), $timezone);
  if (!$base) {
    return null;
  }
  return $dayOffset === 0 ? $base : $base->modify(($dayOffset > 0 ? '+' : '') . $dayOffset . ' day');
}

function astro_sun_event(string $date, string $event): ?int
{
  $timezone = new DateTimeZone('Europe/London');
  $noon = new DateTimeImmutable($date . ' 12:00', $timezone);
  $events = date_sun_info($noon->getTimestamp(), 51.8, -0.3);
  $timestamp = $events[$event] ?? null;
  return is_int($timestamp) ? $timestamp : null;
}

function astro_darkness_segments(string $date, int $start, int $end): array
{
  $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
  $transitions = [
    [astro_sun_event($date, 'civil_twilight_end'), 'nautical'],
    [astro_sun_event($date, 'nautical_twilight_end'), 'astronomical'],
    [astro_sun_event($date, 'astronomical_twilight_end'), 'dark'],
    [astro_sun_event($nextDate, 'astronomical_twilight_begin'), 'astronomical'],
    [astro_sun_event($nextDate, 'nautical_twilight_begin'), 'nautical'],
    [astro_sun_event($nextDate, 'civil_twilight_begin'), 'civil'],
  ];
  $labels = [
    'civil' => 'Civil twilight',
    'nautical' => 'Nautical twilight',
    'astronomical' => 'Astronomical twilight',
    'dark' => 'Astronomical darkness',
  ];

  $events = [];
  foreach ($transitions as [$timestamp, $state]) {
    if ($timestamp !== null && $timestamp > $start && $timestamp < $end) {
      $events[] = ['timestamp' => $timestamp, 'state' => $state];
    }
  }
  usort($events, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

  $segments = [];
  $cursor = $start;
  $state = 'civil';
  foreach ($events as $event) {
    if ($event['timestamp'] > $cursor) {
      $segments[] = [
        'start' => $cursor,
        'end' => $event['timestamp'],
        'state' => $state,
        'label' => $labels[$state],
        'class' => 'astro-darkness-' . $state,
      ];
    }
    $cursor = $event['timestamp'];
    $state = $event['state'];
  }
  if ($cursor < $end) {
    $segments[] = [
      'start' => $cursor,
      'end' => $end,
      'state' => $state,
      'label' => $labels[$state],
      'class' => 'astro-darkness-' . $state,
    ];
  }
  return $segments;
}

function astro_moon_illumination(int $timestamp): array
{
  $cycleDays = 29.53058867;
  $knownNewMoon = strtotime('2000-01-06 18:14 UTC');
  $age = fmod(($timestamp - $knownNewMoon) / 86400, $cycleDays);
  if ($age < 0) {
    $age += $cycleDays;
  }
  $illumination = (1 - cos(2 * M_PI * $age / $cycleDays)) / 2 * 100;
  $phaseNames = [
    [1.84566, 'New moon'],
    [7.38265, 'Waxing crescent'],
    [9.22831, 'First quarter'],
    [14.76529, 'Waxing gibbous'],
    [16.61096, 'Full moon'],
    [22.14794, 'Waning gibbous'],
    [23.99361, 'Last quarter'],
    [27.68493, 'Waning crescent'],
    [$cycleDays, 'New moon'],
  ];
  $phase = 'New moon';
  foreach ($phaseNames as [$limit, $name]) {
    if ($age < $limit) {
      $phase = $name;
      break;
    }
  }
  return ['illumination' => round($illumination), 'phase' => $phase];
}

function astro_moon_events(string $date): array
{
  static $cache = [];
  if (isset($cache[$date])) {
    return $cache[$date];
  }
  $month = (int) substr($date, 5, 2);
  $day = (int) substr($date, 8, 2);
  $year = (int) substr($date, 0, 4);
  $moon = Moon::calculateMoonTimes($month, $day, $year, 51.8, -0.3);
  $sentinel = mktime(0, 0, 0, $month, $day + 1, $year);
  $utc = new DateTimeZone('UTC');
  $events = [];

  foreach (['rise' => $moon->moonrise, 'set' => $moon->moonset] as $type => $rawTimestamp) {
    if ((int) $rawTimestamp === $sentinel) {
      continue;
    }
    $utcEvent = DateTimeImmutable::createFromFormat(
      '!Y-m-d H:i',
      $date . ' ' . date('H:i', (int) $rawTimestamp),
      $utc
    );
    if ($utcEvent) {
      $events[] = ['timestamp' => $utcEvent->getTimestamp(), 'type' => $type];
    }
  }
  return $cache[$date] = $events;
}

function astro_moon_is_up(int $timestamp): bool
{
  $localDate = date('Y-m-d', $timestamp);
  $dates = [
    date('Y-m-d', strtotime($localDate . ' -1 day')),
    $localDate,
    date('Y-m-d', strtotime($localDate . ' +1 day')),
  ];
  $events = [];
  foreach ($dates as $date) {
    $events = array_merge($events, astro_moon_events($date));
  }
  usort($events, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

  $lastEvent = null;
  foreach ($events as $event) {
    if ($event['timestamp'] > $timestamp) {
      break;
    }
    $lastEvent = $event;
  }
  return $lastEvent !== null && $lastEvent['type'] === 'rise';
}

function astro_sample_moon_segments(int $start, int $end, int $illumination): array
{
  $segments = [];
  $cursor = $start;
  while ($cursor < $end) {
    $next = min($end, $cursor + 900);
    $midpoint = (int) (($cursor + $next) / 2);
    $isUp = astro_moon_is_up($midpoint);
    $key = $isUp ? 'up' : 'down';
    $label = $isUp
      ? 'Moon above horizon · ' . $illumination . '% illuminated'
      : 'Moon below horizon';
    $lastIndex = count($segments) - 1;
    if ($lastIndex >= 0 && $segments[$lastIndex]['state'] === $key) {
      $segments[$lastIndex]['end'] = $next;
    } else {
      $segments[] = [
        'start' => $cursor,
        'end' => $next,
        'state' => $key,
        'label' => $label,
        'class' => 'astro-moon-' . $key,
        'style' => $isUp ? '--moon-opacity:' . number_format(0.35 + ($illumination / 100 * 0.55), 2, '.', '') : '',
      ];
    }
    $cursor = $next;
  }
  return $segments;
}

function astro_state_at(array $segments, int $timestamp, string $fallback): string
{
  foreach ($segments as $segment) {
    if ($timestamp >= $segment['start'] && $timestamp < $segment['end']) {
      return $segment['state'];
    }
  }
  return $fallback;
}

function astro_forecast_timestamp(string $value): ?int
{
  if ($value === '') {
    return null;
  }
  try {
    return (new DateTimeImmutable($value, new DateTimeZone('UTC')))->getTimestamp();
  } catch (Exception $exception) {
    return null;
  }
}

function astro_forecast_segments(array $forecast, int $start, int $end, array $darknessSegments, int $illumination): array
{
  $points = [];
  foreach ($forecast as $row) {
    if (($row['dayOrNight'] ?? '') !== 'N' || empty($row['utcTime'])) {
      continue;
    }
    $timestamp = astro_forecast_timestamp((string) $row['utcTime']);
    if ($timestamp === null) {
      continue;
    }
    if ($timestamp === false || $timestamp + 3600 <= $start || $timestamp >= $end) {
      continue;
    }
    $points[] = ['timestamp' => $timestamp, 'row' => $row];
  }
  usort($points, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

  $segments = [];
  $pointCount = count($points);
  for ($index = 0; $index < $pointCount; $index++) {
    $point = $points[$index];
    $segmentStart = max($start, $point['timestamp']);
    $naturalEnd = $index < $pointCount - 1 ? $points[$index + 1]['timestamp'] : $point['timestamp'] + 3600;
    $segmentEnd = min($end, $naturalEnd);
    if ($segmentEnd <= $segmentStart) {
      continue;
    }

    $cloud = max(0, min(100, (float) ($point['row']['totalcloud'] ?? 100)));
    $seeing = max(0, min(10, (float) ($point['row']['seeingIndex'] ?? 0)));
    $hasSeeing = isset($point['row']['seeingIndex']) && is_numeric($point['row']['seeingIndex']);
    $quality = $hasSeeing ? ((100 - $cloud) * 0.78 + $seeing * 10 * 0.22) : (100 - $cloud);
    $state = ($cloud < 15 && (!$hasSeeing || $seeing >= 5.5))
      ? 'good'
      : (($cloud <= 40 && (!$hasSeeing || $seeing >= 4)) ? 'fair' : 'poor');
    $midpoint = (int) (($segmentStart + $segmentEnd) / 2);
    $darkness = astro_state_at($darknessSegments, $midpoint, 'civil');
    $darknessScore = ['civil' => 20, 'nautical' => 45, 'astronomical' => 72, 'dark' => 100][$darkness] ?? 20;
    $moonScore = astro_moon_is_up($midpoint) ? max(0, 100 - $illumination) : 100;
    $score = round($quality * 0.58 + $darknessScore * 0.27 + $moonScore * 0.15);
    $tooltip = date('H:i', $segmentStart) . ' · ' . round($cloud) . '% cloud';
    if ($hasSeeing) {
      $tooltip .= ' · seeing ' . number_format($seeing, 1) . '/10';
    }
    $segments[] = [
      'start' => $segmentStart,
      'end' => $segmentEnd,
      'state' => $state,
      'label' => $tooltip,
      'class' => 'astro-sky-' . $state,
      'cloud' => $cloud,
      'score' => $score,
    ];
  }
  return $segments;
}

function astro_best_window(array $segments): array
{
  if (empty($segments)) {
    return [
      'label' => 'Forecast timing unavailable',
      'start' => null,
      'end' => null,
    ];
  }
  $windows = [];
  $current = null;
  $bestSegment = $segments[0];
  foreach ($segments as $segment) {
    if ($segment['score'] > $bestSegment['score']) {
      $bestSegment = $segment;
    }
    if ($segment['score'] >= 68) {
      if ($current !== null && $segment['start'] <= $current['end'] + 900) {
        $current['end'] = $segment['end'];
        $current['score'] = max($current['score'], $segment['score']);
      } else {
        if ($current !== null) {
          $windows[] = $current;
        }
        $current = ['start' => $segment['start'], 'end' => $segment['end'], 'score' => $segment['score']];
      }
    } elseif ($current !== null) {
      $windows[] = $current;
      $current = null;
    }
  }
  if ($current !== null) {
    $windows[] = $current;
  }

  if (!empty($windows)) {
    usort($windows, function ($a, $b) {
      $durationComparison = ($b['end'] - $b['start']) <=> ($a['end'] - $a['start']);
      return $durationComparison !== 0 ? $durationComparison : $b['score'] <=> $a['score'];
    });
    $best = $windows[0];
    return [
      'label' => date('H:i', $best['start']) . '–' . date('H:i', $best['end']) . ' strongest overlap',
      'start' => $best['start'],
      'end' => $best['end'],
    ];
  }
  return [
    'label' => 'Best near ' . date('H:i', $bestSegment['start']) . ' · conditions remain limited',
    'start' => $bestSegment['start'],
    'end' => $bestSegment['end'],
  ];
}

function astro_build_night_plan(string $date, string $sunset, string $sunrise, array $forecast): array
{
  $startDate = astro_local_datetime($date, $sunset);
  $endDate = astro_local_datetime($date, $sunrise, 1);
  if (!$startDate || !$endDate) {
    return ['available' => false];
  }
  $start = $startDate->getTimestamp();
  $end = $endDate->getTimestamp();
  $moon = astro_moon_illumination((int) (($start + $end) / 2));
  $darkness = astro_darkness_segments($date, $start, $end);
  $moonSegments = astro_sample_moon_segments($start, $end, $moon['illumination']);
  $sky = astro_forecast_segments($forecast, $start, $end, $darkness, $moon['illumination']);
  $bestWindow = astro_best_window($sky);

  $weightedCloud = 0;
  $coveredSeconds = 0;
  foreach ($sky as $segment) {
    $duration = $segment['end'] - $segment['start'];
    $weightedCloud += $segment['cloud'] * $duration;
    $coveredSeconds += $duration;
  }
  $averageCloud = $coveredSeconds > 0 ? round($weightedCloud / $coveredSeconds) : null;
  return [
    'available' => true,
    'start' => $start,
    'end' => $end,
    'sky' => $sky,
    'darkness' => $darkness,
    'moon' => $moonSegments,
    'average_cloud' => $averageCloud,
    'moon_phase' => $moon['phase'],
    'moon_illumination' => $moon['illumination'],
    'best_window' => $bestWindow['label'],
    'best_window_start' => $bestWindow['start'],
    'best_window_end' => $bestWindow['end'],
  ];
}

function astro_render_track(array $segments, int $start, int $end): string
{
  $duration = max(1, $end - $start);
  $html = '<div class="astro-track-bar">';
  foreach ($segments as $segment) {
    $left = max(0, min(100, (($segment['start'] - $start) / $duration) * 100));
    $width = max(0.35, min(100 - $left, (($segment['end'] - $segment['start']) / $duration) * 100));
    $style = 'left:' . number_format($left, 4, '.', '') . '%;width:' . number_format($width, 4, '.', '') . '%;';
    if (!empty($segment['style'])) {
      $style .= $segment['style'] . ';';
    }
    $html .= '<span class="astro-track-segment ' . astro_h($segment['class']) . '" style="' . astro_h($style) . '" title="' . astro_h($segment['label']) . '"><span class="sr-only">' . astro_h($segment['label']) . '</span></span>';
  }
  return $html . '</div>';
}

function astro_render_time_guides(array $plan): string
{
  $duration = max(1, $plan['end'] - $plan['start']);
  $firstHour = (int) (ceil($plan['start'] / 3600) * 3600);
  $html = '<div class="astro-hour-guides" aria-hidden="true">';
  for ($tick = $firstHour; $tick < $plan['end']; $tick += 3600) {
    $position = (($tick - $plan['start']) / $duration) * 100;
    $html .= '<i class="astro-hour-guide" style="left:' . number_format($position, 4, '.', '') . '%"></i>';
  }
  return $html . '</div>';
}

function astro_render_window_markers(array $plan): string
{
  if ($plan['best_window_start'] === null || $plan['best_window_end'] === null) {
    return '';
  }
  $duration = max(1, $plan['end'] - $plan['start']);
  $startPosition = (($plan['best_window_start'] - $plan['start']) / $duration) * 100;
  $endPosition = (($plan['best_window_end'] - $plan['start']) / $duration) * 100;
  $startLabel = 'Strongest overlap starts ' . date('H:i', $plan['best_window_start']);
  $endLabel = 'Strongest overlap ends ' . date('H:i', $plan['best_window_end']);

  return '<span class="astro-window-marker" style="left:' . number_format($startPosition, 4, '.', '') . '%" title="' . astro_h($startLabel) . '"><span class="sr-only">' . astro_h($startLabel) . '</span></span>' .
    '<span class="astro-window-marker" style="left:' . number_format($endPosition, 4, '.', '') . '%" title="' . astro_h($endLabel) . '"><span class="sr-only">' . astro_h($endLabel) . '</span></span>';
}

function astro_render_time_axis(array $plan): string
{
  $duration = max(1, $plan['end'] - $plan['start']);
  $firstHour = (int) (ceil($plan['start'] / 3600) * 3600);
  $html = '<div class="astro-time-axis">';
  $html .= '<span class="astro-time-endpoint astro-time-start" style="left:0">' . astro_h(date('H:i', $plan['start'])) . '</span>';
  for ($tick = $firstHour; $tick < $plan['end']; $tick += 3600) {
    $position = (($tick - $plan['start']) / $duration) * 100;
    $mobileClass = ((int) date('G', $tick) % 2 === 0) ? '' : ' astro-time-mobile-hidden';
    $html .= '<span class="astro-time-tick' . $mobileClass . '" style="left:' . number_format($position, 4, '.', '') . '%">' . astro_h(date('H:i', $tick)) . '</span>';
  }
  $html .= '<span class="astro-time-endpoint astro-time-end" style="left:100%">' . astro_h(date('H:i', $plan['end'])) . '</span>';
  return $html . '</div>';
}

function astro_render_night_plan(array $plan): string
{
  if (empty($plan['available'])) {
    return '<p class="astro-plan-empty">Night timing unavailable.</p>';
  }
  $moonText = $plan['moon_phase'] . ' · ' . $plan['moon_illumination'] . '% illuminated';
  $html = '<div class="astro-night-plan">';
  $html .= '<div class="astro-plan-summary"><span><i class="fas fa-camera"></i><strong>Best imaging window</strong> ' . astro_h($plan['best_window']) . '</span><span><i class="fas fa-moon"></i>' . astro_h($moonText) . '</span></div>';
  $html .= '<div class="astro-track-grid">';
  $html .= '<div class="astro-track-labels">';
  $html .= '<div class="astro-track-label"><i class="fas fa-eye"></i><span>Sky / seeing<small>Cloud and stability</small></span></div>';
  $html .= '<div class="astro-track-label"><i class="fas fa-circle-half-stroke"></i><span>Darkness<small>Twilight levels</small></span></div>';
  $html .= '<div class="astro-track-label"><i class="fas fa-moon"></i><span>Moon<small>Above or below</small></span></div>';
  $html .= '</div>';
  $html .= '<div class="astro-track-visual"><div class="astro-track-stack">';
  $html .= astro_render_track($plan['sky'], $plan['start'], $plan['end']);
  $html .= astro_render_track($plan['darkness'], $plan['start'], $plan['end']);
  $html .= astro_render_track($plan['moon'], $plan['start'], $plan['end']);
  $html .= astro_render_time_guides($plan) . astro_render_window_markers($plan);
  $html .= '</div>' . astro_render_time_axis($plan) . '</div>';
  $html .= '</div>';
  $html .= '<div class="astro-plan-legend"><span><i class="astro-key astro-key-good"></i>Good</span><span><i class="astro-key astro-key-fair"></i>Mixed</span><span><i class="astro-key astro-key-poor"></i>Poor</span><span><i class="astro-key astro-key-dark"></i>Full darkness</span><span><i class="astro-key astro-key-moon"></i>Moon above</span><span><i class="astro-key astro-key-window"></i>Strongest window bounds</span></div>';
  return $html . '</div>';
}
