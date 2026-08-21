<?php
include __DIR__ . '/../header.php';
include __DIR__ . '/moon.php';
include __DIR__ . '/planner.php';
include __DIR__ . '/detail.php';

$singledate = $_GET['DATE'] ?? null;
if ($singledate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $singledate)) {
  $singledate = null;
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
  .astro-card-imaging { padding-right: .9rem; border-right: 1px solid #dbe3ec; }
  .astro-card-metrics .astro-card-imaging strong { color: #2f855a; }
  .astro-card-metrics .astro-card-imaging-empty strong { color: #64748b; }
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
  .astro-track-grid-window .astro-track-labels,
  .astro-track-grid-window .astro-track-stack { margin-top: .95rem; }
  .astro-hour-guides { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
  .astro-hour-guide { position: absolute; top: 0; bottom: 0; width: 1px; background: rgba(255,255,255,.38); box-shadow: 1px 0 rgba(15,35,63,.08); }
  .astro-window-marker { position: absolute; z-index: 3; top: -.72rem; bottom: -.16rem; width: 2px; transform: translateX(-1px); background: #f8fafc; box-shadow: -1px 0 #17466f, 1px 0 #17466f; pointer-events: auto; }
  .astro-window-range { position: absolute; z-index: 4; top: -.72rem; height: 0; border-top: 1px solid #17466f; color: #17466f; pointer-events: none; }
  .astro-window-range::before,
  .astro-window-range::after { content: ''; position: absolute; top: -3px; width: 0; height: 0; border-top: 3px solid transparent; border-bottom: 3px solid transparent; }
  .astro-window-range::before { left: 0; border-right: 4px solid #17466f; }
  .astro-window-range::after { right: 0; border-left: 4px solid #17466f; }
  .astro-window-title { position: absolute; left: 50%; top: -.42rem; padding: .04rem .3rem; transform: translateX(-50%); background: #f8fafc; color: #17466f; font-size: .46rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
  .astro-window-title-short { display: none; }
  .astro-sky-good { background: #42a878; }
  .astro-sky-fair { background: #d6a33b; }
  .astro-sky-poor { background: #c96a68; }
  .astro-darkness-civil { background: #a9b9d1; }
  .astro-darkness-nautical { background: #647da6; }
  .astro-darkness-astronomical { background: #354b72; }
  .astro-darkness-dark { background: #14233f; }
  .astro-moon-down { background: #26364e; }
  .astro-moon-up { background: #94a3b8; }
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
  .astro-key-moon { background: #94a3b8; }
  .astro-key-window { width: 2px; height: .62rem; border-radius: 0; background: #f8fafc; box-shadow: -1px 0 #17466f, 1px 0 #17466f; }
  .astro-plan-empty { padding: .8rem; color: #64748b; font-size: .68rem; }
  .astro-detail-dashboard { display: grid; gap: 1rem; }
  .astro-detail-metrics { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .7rem; }
  .astro-detail-metric { position: relative; display: flex; min-width: 0; gap: .7rem; padding: .9rem; overflow: hidden; border: 1px solid #dbe3ec; border-radius: .72rem; background: linear-gradient(145deg, #fff 0%, #f8fafc 100%); box-shadow: 0 5px 14px rgba(16,35,63,.055); }
  .astro-detail-metric::after { content: ''; position: absolute; right: -.9rem; bottom: -1.2rem; width: 4.2rem; height: 4.2rem; border-radius: 50%; background: rgba(53,119,173,.055); }
  .astro-detail-metric-icon { position: relative; z-index: 1; display: grid; flex: 0 0 2rem; width: 2rem; height: 2rem; place-items: center; border-radius: .52rem; background: #e7f0f8; color: #3577ad; font-size: .78rem; }
  .astro-detail-metric > div { position: relative; z-index: 1; min-width: 0; }
  .astro-detail-metric small { display: block; color: #718096; font-size: .52rem; font-weight: 800; letter-spacing: .08em; line-height: 1.2; text-transform: uppercase; }
  .astro-detail-metric strong { display: block; margin-top: .22rem; color: #17263b; font-family: Roboto, sans-serif; font-size: 1.32rem; font-variant-numeric: tabular-nums; line-height: 1.05; white-space: nowrap; }
  .astro-detail-metric p { margin-top: .32rem; overflow: hidden; color: #718096; font-size: .56rem; line-height: 1.3; text-overflow: ellipsis; white-space: nowrap; }
  .astro-detail-metric-positive { border-top: 3px solid #42a878; }
  .astro-detail-metric-positive .astro-detail-metric-icon { background: #e3f4ec; color: #27825b; }
  .astro-detail-metric-night { border-top: 3px solid #354b72; }
  .astro-detail-metric-night .astro-detail-metric-icon { background: #e5e9f1; color: #354b72; }
  .astro-detail-metric-moon { border-top: 3px solid #94a3b8; }
  .astro-detail-metric-moon .astro-detail-metric-icon { background: #eceff3; color: #64748b; }
  .astro-detail-section { padding: 1rem; border: 1px solid #d5dfe9; border-radius: .8rem; background: #fff; box-shadow: var(--dashboard-shadow); }
  .astro-detail-section-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .8rem; }
  .astro-detail-section-head span { display: block; margin-bottom: .16rem; color: #3577ad; font-size: .52rem; font-weight: 850; letter-spacing: .12em; text-transform: uppercase; }
  .astro-detail-section-head h2 { color: #17263b; font-family: Roboto, sans-serif; font-size: 1rem; font-weight: 800; line-height: 1.2; }
  .astro-detail-section-head p { margin-top: .2rem; color: #718096; font-size: .62rem; }
  .astro-detail-section-head > i { display: grid; flex: 0 0 2.25rem; width: 2.25rem; height: 2.25rem; place-items: center; border-radius: .58rem; background: #eef4f9; color: #3577ad; }
  .astro-detail-matrix-section { overflow: hidden; }
  .astro-matrix-scroll { overflow-x: auto; overscroll-behavior-inline: contain; scrollbar-color: #9eb1c5 #e9eef3; scrollbar-width: thin; }
  .astro-detail-matrix { min-width: max(100%, calc(9.7rem + (var(--astro-hours) * 4.35rem))); border: 1px solid #dbe3ec; border-radius: .65rem; overflow: hidden; background: #f8fafc; }
  .astro-matrix-row { display: grid; grid-template-columns: 9.7rem minmax(0, 1fr); border-bottom: 1px solid #e2e8f0; }
  .astro-matrix-row:last-child { border-bottom: 0; }
  .astro-matrix-row:nth-child(n+3):hover { background: #f1f5f9; }
  .astro-matrix-label { position: sticky; left: 0; z-index: 2; display: flex; align-items: center; gap: .58rem; min-width: 0; padding: .46rem .62rem; border-right: 1px solid #dbe3ec; background: #f8fafc; color: #334155; box-shadow: 5px 0 10px rgba(15,35,63,.035); }
  .astro-matrix-label > i { width: .9rem; color: #5b7693; font-size: .64rem; text-align: center; }
  .astro-matrix-label span { min-width: 0; font-size: .61rem; font-weight: 800; line-height: 1.18; }
  .astro-matrix-label small { display: block; margin-top: .1rem; color: #8a96a6; font-size: .47rem; font-weight: 600; }
  .astro-matrix-cells { display: grid; grid-template-columns: repeat(var(--astro-hours), minmax(4.35rem, 1fr)); gap: 1px; background: #dfe6ed; }
  .astro-matrix-cell { display: flex; min-width: 0; min-height: 2.35rem; align-items: baseline; justify-content: center; gap: .12rem; padding: .52rem .2rem; background: #fff; color: #344256; font-variant-numeric: tabular-nums; text-align: center; }
  .astro-matrix-cell strong { font-size: .61rem; font-weight: 800; line-height: 1; white-space: nowrap; }
  .astro-matrix-cell small { color: inherit; font-size: .46rem; font-weight: 700; opacity: .72; }
  .astro-cell-time { align-items: center; background: #edf3f8; color: #344b63; }
  .astro-cell-good { background: #e2f3eb; color: #176844; }
  .astro-cell-fair { background: #fbf1d8; color: #8a5b00; }
  .astro-cell-poor { background: #fae7e6; color: #9b3736; }
  .astro-cell-neutral { background: #f1f5f9; color: #718096; }
  .astro-cell-darkness-civil { background: #dce4ef; color: #526985; }
  .astro-cell-darkness-nautical { background: #8195b4; color: #fff; }
  .astro-cell-darkness-astronomical { background: #425a7f; color: #fff; }
  .astro-cell-darkness-dark { background: #14233f; color: #fff; }
  .astro-cell-moon-up { background: #cbd3dd; color: #344256; }
  .astro-cell-moon-down { background: #26364e; color: #fff; }
  .astro-detail-notes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .7rem; }
  .astro-detail-notes article { display: flex; gap: .7rem; padding: .82rem; border: 1px solid #dbe3ec; border-radius: .68rem; background: #f8fafc; }
  .astro-detail-notes i { display: grid; flex: 0 0 1.8rem; width: 1.8rem; height: 1.8rem; place-items: center; border-radius: .46rem; background: #e7f0f8; color: #3577ad; font-size: .65rem; }
  .astro-detail-notes strong { display: block; color: #27384d; font-size: .65rem; }
  .astro-detail-notes p { margin-top: .18rem; color: #718096; font-size: .54rem; line-height: 1.45; }
  .astro-detail-empty { display: grid; min-height: 13rem; place-items: center; align-content: center; gap: .5rem; padding: 2rem; border: 1px dashed #b8c5d3; border-radius: .8rem; background: #f8fafc; color: #64748b; text-align: center; }
  .astro-detail-empty i { font-size: 1.6rem; color: #8297ad; }
  .astro-detail-empty strong { color: #344256; }
  .astro-detail-empty p { font-size: .65rem; }
  html.dark .astro-night-card { border-color: #334155; background: #111c2d; color: #e2e8f0; }
  html.dark .astro-card-date strong,
  html.dark .astro-card-metrics strong { color: #e2e8f0; }
  html.dark .astro-card-imaging { border-color: #334155; }
  html.dark .astro-card-metrics .astro-card-imaging strong { color: #6ee7b7; }
  html.dark .astro-card-metrics .astro-card-imaging-empty strong { color: #94a3b8; }
  html.dark .astro-card-moon { border-color: #334155; }
  html.dark .astro-night-plan { border-color: #334155; background: #0f172a; }
  html.dark .astro-plan-summary,
  html.dark .astro-track-label { color: #cbd5e1; }
  html.dark .astro-plan-summary strong { color: #e2e8f0; }
  html.dark .astro-plan-legend { border-color: #334155; color: #94a3b8; }
  html.dark .astro-detail-metric { border-color: #334155; background: linear-gradient(145deg, #111c2d 0%, #0f172a 100%); }
  html.dark .astro-detail-metric strong,
  html.dark .astro-detail-section-head h2 { color: #e2e8f0; }
  html.dark .astro-detail-metric-icon,
  html.dark .astro-detail-section-head > i,
  html.dark .astro-detail-notes i { background: #1d3047; color: #7db5e1; }
  html.dark .astro-detail-section { border-color: #334155; background: #111c2d; }
  html.dark .astro-detail-matrix { border-color: #334155; background: #0f172a; }
  html.dark .astro-matrix-row { border-color: #334155; }
  html.dark .astro-matrix-label { border-color: #334155; background: #101b2c; color: #d5deea; }
  html.dark .astro-matrix-cells { background: #26364b; }
  html.dark .astro-cell-time,
  html.dark .astro-cell-neutral { background: #172337; color: #b8c4d3; }
  html.dark .astro-cell-good { background: #164d3a; color: #b7f1d8; }
  html.dark .astro-cell-fair { background: #59471e; color: #fde8a8; }
  html.dark .astro-cell-poor { background: #5a2e32; color: #fecaca; }
  html.dark .astro-detail-notes article { border-color: #334155; background: #0f172a; }
  html.dark .astro-detail-notes strong { color: #dbe5f0; }
  html.dark .astro-detail-empty { border-color: #475569; background: #0f172a; }
  html.dark .astro-detail-empty strong { color: #dbe5f0; }
  @media (max-width: 1100px) {
    .astro-detail-metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  }
  @media (max-width: 640px) {
    .astro-card-header { grid-template-columns: 1fr; gap: .55rem; }
    .astro-card-metrics { display: grid; grid-template-columns: auto auto; justify-content: flex-start; gap: .55rem .9rem; text-align: left; }
    .astro-card-moon { grid-column: 1 / -1; padding: .45rem 0 0; border-top: 1px solid #dbe3ec; border-left: 0; }
    .astro-track-grid { grid-template-columns: 5.7rem minmax(0,1fr); gap: .42rem; }
    .astro-track-label small { display: none; }
    .astro-plan-summary { align-items: flex-start; flex-direction: column; }
    .astro-plan-legend { display: none; }
    .astro-time-mobile-hidden { display: none; }
    .astro-time-axis { font-size: .44rem; }
    .astro-window-title-full { display: none; }
    .astro-window-title-short { display: block; }
    .astro-detail-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; }
    .astro-detail-metric { gap: .48rem; padding: .72rem; }
    .astro-detail-metric-icon { flex-basis: 1.75rem; width: 1.75rem; height: 1.75rem; }
    .astro-detail-metric strong { font-size: 1.08rem; }
    .astro-detail-metric p { white-space: normal; }
    .astro-detail-section { padding: .72rem; }
    .astro-detail-section-head p { max-width: 18rem; }
    .astro-detail-section-head > i { display: none; }
    .astro-detail-notes { grid-template-columns: 1fr; }
    .astro-matrix-row { grid-template-columns: 7.2rem minmax(0, 1fr); }
    .astro-detail-matrix { min-width: max(100%, calc(7.2rem + (var(--astro-hours) * 4rem))); }
    .astro-matrix-cells { grid-template-columns: repeat(var(--astro-hours), minmax(4rem, 1fr)); }
    .astro-matrix-label { padding-inline: .48rem; }
    .astro-matrix-label small { display: none; }
  }
</style>





<?php

function getrag($value){
if ($value > 30) {$color="red-500";}
if ($value >= 9 && $value <= 30 ) {$color="yellow-500";}
if ($value < 10 ) {$color="green-500";}
return $color;
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
  $detail = astro_render_detail_dashboard($singledate, $forecastRows);
  $singleDateLabel = astro_h(date('l j F Y', strtotime($singledate)));
  echo "
<div class=\"site-workspace mb-4\">
  <header class=\"workspace-header\"><div><span class=\"workspace-eyebrow\">Night mission control</span><h1>$singleDateLabel</h1><p>A complete observing plan, from sunset through morning twilight.</p></div><a class=\"workspace-badge\" href=\"/astro/\"><i class=\"fas fa-arrow-left\"></i> Ten-day outlook</a></header>
  $detail
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
  $imagingSummary = astro_h($plan['best_window_duration'] ?? '0h');
  $imagingClass = ($plan['best_window_start'] ?? null) === null
    ? 'astro-card-imaging astro-card-imaging-empty'
    : 'astro-card-imaging';
  $dayUrl = rawurlencode($day);

echo "\n<a href=\"/astro/index.php?DATE=$dayUrl\" class=\"astro-night-card\">
  <div class=\"astro-card-header\">
    <div>
      <div class=\"astro-card-date\"><span>$weekdayLabel</span><strong>$simpleLabel</strong></div>
      <p class=\"astro-card-condition\">$conditionLabel</p>
    </div>
    <div class=\"astro-card-metrics\">
      <div class=\"$imagingClass\"><strong>$imagingSummary</strong><small>Possible imaging</small></div>
      <div><strong class=\"text-$color\">$cloudSummary</strong><small>Average cloud</small></div>
      <div class=\"astro-card-moon\"><strong>$moonSummary</strong><small>$nightLabel</small></div>
    </div>
  </div>
  $graphic
</a>";

}




echo '</div></div>';
include __DIR__ . '/../footer.php';
