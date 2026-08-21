<?php
include __DIR__ . '/../header.php';
include __DIR__ . '/moon.php';
include __DIR__ . '/planner.php';

$singledate = $_GET['DATE'] ?? null;
$detailcolor = $_GET['DATECOLOR'] ?? 'slate-400';
if ($singledate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $singledate)) {
  $singledate = null;
}
if (!in_array($detailcolor, ['red-500', 'yellow-500', 'green-500', 'slate-400'], true)) {
  $detailcolor = 'slate-400';
}
?>


    <title>Smeird Astro Weather</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>document.getElementById("navname").innerHTML = "Wheathampstead Astro";</script>

<style>
  .astro-night-card {
    display: block;
    padding: .85rem;
    border: 1px solid var(--dashboard-border);
    border-radius: .75rem;
    background: #fff;
    color: var(--dashboard-ink);
    box-shadow: var(--dashboard-shadow);
    text-decoration: none;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
  }
  .astro-night-card:hover { border-color: #9eb7d0; box-shadow: 0 10px 24px rgba(16,35,63,.1); text-decoration: none; transform: translateY(-1px); }
  .astro-card-header { display: grid; grid-template-columns: minmax(0,1fr) auto; align-items: center; gap: 1rem; margin-bottom: .72rem; }
  .astro-card-date { display: flex; align-items: baseline; gap: .55rem; }
  .astro-card-date strong { font-family: Roboto, sans-serif; font-size: 1rem; }
  .astro-card-date span { color: #718096; font-size: .62rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
  .astro-card-condition { margin-top: .12rem; color: #64748b; font-size: .68rem; }
  .astro-card-metrics { display: flex; align-items: center; justify-content: flex-end; gap: .9rem; text-align: right; }
  .astro-card-metrics strong { display: block; color: #17263b; font-size: 1.18rem; font-variant-numeric: tabular-nums; }
  .astro-card-metrics small { display: block; color: #7a8798; font-size: .52rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .astro-card-moon { padding-left: .9rem; border-left: 1px solid #dbe3ec; }
  .astro-card-moon strong { font-size: .74rem; }
  .astro-night-plan { padding: .72rem; border: 1px solid #dbe3ec; border-radius: .6rem; background: #f8fafc; }
  .astro-plan-summary { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .65rem; color: #53637a; font-size: .62rem; }
  .astro-plan-summary span { display: inline-flex; align-items: center; gap: .35rem; }
  .astro-plan-summary strong { color: #1d3550; }
  .astro-plan-summary i { color: #3577ad; }
  .astro-track-grid { display: grid; grid-template-columns: 7.4rem minmax(0,1fr); align-items: start; gap: .65rem; }
  .astro-track-labels,
  .astro-track-stack { display: grid; grid-template-rows: repeat(3,1.05rem); gap: .42rem; }
  .astro-track-label { display: flex; align-items: center; gap: .42rem; color: #334155; font-size: .62rem; font-weight: 750; }
  .astro-track-label > i { width: .9rem; color: #5b7693; text-align: center; }
  .astro-track-label span { display: grid; }
  .astro-track-label small { color: #8a96a6; font-size: .48rem; font-weight: 600; }
  .astro-track-bar { position: relative; height: 1.05rem; overflow: hidden; border-radius: .3rem; background: #e5eaf0; box-shadow: inset 0 0 0 1px rgba(100,116,139,.12); }
  .astro-track-segment { position: absolute; top: 0; bottom: 0; border-right: 1px solid rgba(255,255,255,.4); }
  .astro-track-visual { min-width: 0; }
  .astro-track-stack { position: relative; }
  .astro-hour-guides { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
  .astro-hour-guide { position: absolute; top: 0; bottom: 0; width: 1px; background: rgba(255,255,255,.38); box-shadow: 1px 0 rgba(15,35,63,.08); }
  .astro-window-marker { position: absolute; z-index: 3; top: -.16rem; bottom: -.16rem; width: 2px; transform: translateX(-1px); background: #f8fafc; box-shadow: -1px 0 #17466f, 1px 0 #17466f; pointer-events: auto; }
  .astro-sky-good { background: #42a878; }
  .astro-sky-fair { background: #d6a33b; }
  .astro-sky-poor { background: #c96a68; }
  .astro-darkness-civil { background: #a9b9d1; }
  .astro-darkness-nautical { background: #647da6; }
  .astro-darkness-astronomical { background: #354b72; }
  .astro-darkness-dark { background: #14233f; }
  .astro-moon-down { background: #26364e; }
  .astro-moon-up { background: rgba(232,181,48,var(--moon-opacity,.65)); }
  .astro-time-axis { position: relative; height: 1.05rem; color: #7a8798; font-size: .48rem; font-variant-numeric: tabular-nums; }
  .astro-time-axis span { position: absolute; top: .22rem; white-space: nowrap; }
  .astro-time-tick { transform: translateX(-50%); }
  .astro-time-endpoint { color: #627187; font-weight: 700; }
  .astro-time-start { transform: translateX(0); }
  .astro-time-end { transform: translateX(-100%); }
  .astro-plan-legend { display: flex; flex-wrap: wrap; gap: .55rem .8rem; margin-top: .5rem; padding-top: .45rem; border-top: 1px solid #e1e7ee; color: #748196; font-size: .5rem; }
  .astro-plan-legend span { display: inline-flex; align-items: center; gap: .25rem; }
  .astro-key { width: .5rem; height: .5rem; border-radius: .12rem; }
  .astro-key-good { background: #42a878; }
  .astro-key-fair { background: #d6a33b; }
  .astro-key-poor { background: #c96a68; }
  .astro-key-dark { background: #14233f; }
  .astro-key-moon { background: #e8b530; }
  .astro-key-window { width: 2px; height: .62rem; border-radius: 0; background: #f8fafc; box-shadow: -1px 0 #17466f, 1px 0 #17466f; }
  .astro-plan-empty { padding: .8rem; color: #64748b; font-size: .68rem; }
  html.dark .astro-night-card { border-color: #334155; background: #111c2d; color: #e2e8f0; }
  html.dark .astro-card-date strong,
  html.dark .astro-card-metrics strong { color: #e2e8f0; }
  html.dark .astro-card-moon { border-color: #334155; }
  html.dark .astro-night-plan { border-color: #334155; background: #0f172a; }
  html.dark .astro-plan-summary,
  html.dark .astro-track-label { color: #cbd5e1; }
  html.dark .astro-plan-summary strong { color: #e2e8f0; }
  html.dark .astro-plan-legend { border-color: #334155; color: #94a3b8; }
  @media (max-width: 640px) {
    .astro-card-header { grid-template-columns: 1fr; gap: .55rem; }
    .astro-card-metrics { justify-content: flex-start; text-align: left; }
    .astro-track-grid { grid-template-columns: 5.7rem minmax(0,1fr); gap: .42rem; }
    .astro-track-label small { display: none; }
    .astro-plan-summary { align-items: flex-start; flex-direction: column; }
    .astro-plan-legend { display: none; }
    .astro-time-mobile-hidden { display: none; }
    .astro-time-axis { font-size: .44rem; }
  }
</style>





<?php

function getrag($value){
if ($value > 30) {$color="red-500";}
if ($value >= 9 && $value <= 30 ) {$color="yellow-500";}
if ($value < 10 ) {$color="green-500";}
return $color;
}
function centrag($value) {
  if ($value > 30) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-red-500\">$value</td>";
  } elseif ($value >= 9 && $value <= 30) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-yellow-500\">$value</td>";
  } else {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-green-500\">$value</td>";
  }
  return $color;
}
function seeingrag($value){
  if ($value > 6) {
    $color = "border-green-500";
  }
  if ($value <= 6 && $value >= 4) {
    $color = "border-yellow-500";
  }
  if ($value < 4) {
    $color = "border-red-500";
  }
  return $color;
}

function tenrag($value) {
  if ($value > 6) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-green-500 text-green-600\"><span class=\"text-sm\">$value</span></td>";
  } elseif ($value >= 4 && $value <= 6) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-yellow-500\">$value</td>";
  } else {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-red-500\">$value</td>";
  }
  return $color;
}

function thirtyrag($value) {
  if ($value > 18) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-green-500 text-green-600\"><span class=\"text-sm\">$value</span></td>";
  } elseif ($value >= 12 && $value <= 18) {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-yellow-500\">$value</td>";
  } else {
    $color = "<td class=\"px-4 py-2 text-right border-l-4 border-red-500\">$value</td>";
  }
  return $color;
}

function getdetail($date, $json) {
  $html = '<div class="overflow-x-auto"><table class="min-w-full bg-white dark:bg-gray-800 dark:text-gray-100 text-sm"><thead><tr>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold">Date</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Total Cloud</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Combined Index</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Seeing Index</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Pickering Index</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Trans Index</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Low Cloud</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">Medium Cloud</th>' .
    '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-right text-sm uppercase font-semibold">High Cloud</th>' .
    '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">';
  $nightStart = astro_sun_event($date, 'sunset');
  $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
  $nightEnd = astro_sun_event($nextDate, 'sunrise');
  foreach (($json['metcheckData']['forecastLocation']['forecast'] ?? []) as $value) {
    $timestamp = astro_forecast_timestamp($value['utcTime'] ?? '');
    if ($timestamp === null || $nightStart === null || $nightEnd === null || $timestamp < $nightStart || $timestamp >= $nightEnd) {
      continue;
    }
    $hourrag = seeingrag($value['seeingIndex']);
    $nicedate = date('l H:i', $timestamp);
    $html .= "<tr class=\"border-l-4 $hourrag\">";
    $html .= '<td class="px-4 py-2 text-left">' . astro_h($nicedate) . '</td>';
    $html .= centrag($value['totalcloud']);
    $html .= thirtyrag(round(($value['seeingIndex'] + $value['pickeringIndex'] + $value['transIndex']) / 1), 1);
    $html .= tenrag($value['seeingIndex']);
    $html .= tenrag($value['pickeringIndex']);
    $html .= tenrag($value['transIndex']);
    $html .= centrag($value['lowcloud']);
    $html .= centrag($value['medcloud']);
    $html .= centrag($value['highcloud']);
    $html .= '</tr>';
  }
  $html .= '</tbody></table></div><br>' .
    '<p>' .
    'Seeing : This calculation uses the total cloud cover along with turbulence in the atmosphere and low level wind speed to give an index from 0 to 10 where 0 is worst and 10 is best seeing conditions. (experimental)' .
    '</p>' .
    '<p>Transp.: This calculation uses the total amount of water in the atmosphere above your location. It shows the relative humidity in the column of air from 0 to 30,000ft and gives an index from 0 to 10 where 0 is worst and 10 is best seeing conditions.(experimental)' .
    '</p>' .
    '<p>Pickering: This calculation uses the amount of low and mid level turbulence above your location as well as calculating differences in wind speed and temperature at various levels in the atmosphere to show how much distortion the light rays will experience between 0 and 30,000ft and gives an index from 0 to 10 where 0 is worst and 10 is best seeing conditions.(experimental)' .
    '</p>';
  return $html;
}



function getJson($url) {
     // cache files are created like cache/abcdef123456...
     $cacheFile = '/tmp' . DIRECTORY_SEPARATOR . md5($url);

     if (file_exists($cacheFile)) {
         $fh = fopen($cacheFile, 'r');
         $cacheTime = trim(fgets($fh));

         // if data was cached recently, return cached data
         if ($cacheTime > strtotime('-60 minutes')) {
             return fread($fh,filesize($cacheFile));
         }
         // else delete cache file
         fclose($fh);
         unlink($cacheFile);
     }
     $json = file_get_contents($url);
     $fh = fopen($cacheFile, 'w');
     fwrite($fh, time() . "\n");
     fwrite($fh, $json);
     fclose($fh);
     return $json;
 }

    $data = getJson('http://ws1.metcheck.com/ENGINE/v9_0/json.asp?lat=51.81&lon=-0.29&lid=58143&Fc=As');
    $json = json_decode($data, true);
    $forecastRows = $json['metcheckData']['forecastLocation']['forecast'] ?? [];

//print_r($json);
$newArray = array();
    foreach ($forecastRows as $key=>$value) {
    if (($value['dayOrNight'] ?? '') == 'N') {
        $newdatea=$value['utcTime'];
        $myDateTime = date('Y-m-d l', strtotime(substr($newdatea,0,10)));
      $iterationValue = $value['totalcloud'];
      $descr = $value['iconName'];
      $Count=1;
      $dateKey=$myDateTime;
      if(array_key_exists($dateKey, $newArray))
      {
          // If we've already added this date to the new array, add the value
          $newArray[$dateKey]['value'] += $iterationValue;
          $newArray[$dateKey]['count'] += $Count;
          $newArray[$dateKey]['avg'] = $newArray[$dateKey]['value'] / $newArray[$dateKey]['count'] ;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $value['sunrise'];
          $newArray[$dateKey]['sunset'] = $value['sunset'];
      }
      else
      {
          // Otherwise create a new element with datetimeobject as key
          $newArray[$dateKey]['count'] = $Count;
          $newArray[$dateKey]['value'] = $iterationValue;
          $newArray[$dateKey]['avg'] = $iterationValue;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $value['sunrise'];
          $newArray[$dateKey]['sunset'] = $value['sunset'];
      }
    }
    }
$newArray = array_slice($newArray, 0, 10, true);
//echo '<pre>';
//nl2br(print_r($cloudArray));
//echo '</pre>';




if(isset($singledate)){
  $detail=getdetail($singledate,$json);
  $singleDateLabel = astro_h($singledate);
  echo "
<div class=\"site-workspace mb-4\">
  <header class=\"workspace-header\"><div><span class=\"workspace-eyebrow\">Astronomy detail</span><h1>$singleDateLabel</h1><p>Hourly cloud and night-sky conditions for the selected date.</p></div><span class=\"workspace-badge\"><i class=\"fas fa-moon\"></i> Night outlook</span></header>
  <div class=\"workspace-panel p-4 border-l-4 border-$detailcolor\">
    <h2 class=\"text-xl font-semibold mb-4\">$singleDateLabel</h2>
    $detail
  </div>
</div>
  ";
}

echo '<div class="site-workspace">

<header class="workspace-header"><div><span class="workspace-eyebrow">Astronomy planner</span><h1>Ten-day night-sky outlook</h1><p>Scan cloud cover, sunset, sunrise and moon timing to find the strongest observing windows.</p></div><span class="workspace-badge"><i class="fas fa-star"></i> Forecast view</span></header>
<div class="grid gap-4 grid-cols-1">';

foreach ($newArray as $keya=>$valuea){
  $day = substr($keya, 0, 10);
  $simpleLabel = astro_h(date('l j F', strtotime($day)));
  $weekdayLabel = astro_h(date('D', strtotime($day)));
  $conditionLabel = astro_h($valuea['descr']);
  $SS = $valuea['sunset'];
  $SR = $valuea['sunrise'];
  $plan = astro_build_night_plan($day, $SS, $SR, $forecastRows);
  $graphic = astro_render_night_plan($plan);
  $cloud = $plan['average_cloud'] ?? round($valuea['avg'], 0);
  $color = getrag($cloud);
  $cloudSummary = astro_h($cloud . '%');
  $nightLabel = astro_h($SS . ' sunset · ' . $SR . ' sunrise');
  $moonSummary = !empty($plan['available'])
    ? astro_h($plan['moon_phase'] . ' · ' . $plan['moon_illumination'] . '%')
    : 'Moon timing unavailable';
  $dayUrl = rawurlencode($day);

echo "\n<a href=\"/astro/index.php?DATE=$dayUrl&amp;DATECOLOR=$color\" class=\"astro-night-card\">
  <div class=\"astro-card-header\">
    <div>
      <div class=\"astro-card-date\"><span>$weekdayLabel</span><strong>$simpleLabel</strong></div>
      <p class=\"astro-card-condition\">$conditionLabel</p>
    </div>
    <div class=\"astro-card-metrics\">
      <div><strong class=\"text-$color\">$cloudSummary</strong><small>Average cloud</small></div>
      <div class=\"astro-card-moon\"><strong>$moonSummary</strong><small>$nightLabel</small></div>
    </div>
  </div>
  $graphic
</a>";

}




echo '</div></div>';
include __DIR__ . '/../footer.php';
