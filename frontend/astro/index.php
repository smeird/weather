<?php
include __DIR__ . '/../header.php';
include __DIR__ . '/moon.php';

$singledate = $_GET['DATE'];
$detailcolor = $_GET['DATECOLOR'];
?>


    <title>Smeird Astro Weather</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>document.getElementById("navname").innerHTML = "Wheathampstead Astro";</script>

<style>
a:hover {
    /* REMOVE drop Shadow when hovering only */
    text-decoration: none;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
    shadow: none;
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
  foreach ($json['metcheckData']['forecastLocation']['forecast'] as $key => $value) {
    $hourrag = seeingrag($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex']);
    $html .= "<tr class=\"border-l-4 $hourrag\">";
    $detaildate = $json['metcheckData']['forecastLocation']['forecast'][$key]['utcTime'];
    $nicedate = date('l', strtotime(substr($detaildate, 0, 10))) . ' ' . substr($detaildate, 11, 5);
    if ($date == substr($detaildate, 0, 10)) {
      if ($json['metcheckData']['forecastLocation']['forecast'][$key]['dayOrNight'] == 'N') {
        $html .= '<td class="px-4 py-2 text-left">' . $nicedate . '</td>';
        $html .= centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['totalcloud']);
        $html .= thirtyrag(round(($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex'] + $json['metcheckData']['forecastLocation']['forecast'][$key]['pickeringIndex'] + $json['metcheckData']['forecastLocation']['forecast'][$key]['transIndex']) / 1), 1);
        $html .= tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex']);
        $html .= tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['pickeringIndex']);
        $html .= tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['transIndex']);
        $html .= centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['lowcloud']);
        $html .= centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['medcloud']);
        $html .= centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['highcloud']);
      }
    }
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

function nightview($date, $cloudArray) {
  $segments = array();
  $targetDate = substr($date, 0, 10);

  foreach ($cloudArray as $keydate => $covervalue) {
    if (substr($keydate, 0, 10) === $targetDate) {
      $timestamp = strtotime($keydate);
      if ($timestamp === false) {
        continue;
      }
      $segments[] = array(
        'timestamp' => $timestamp,
        'label' => date('H:i', $timestamp),
        'cloud' => $covervalue,
        'color' => getrag($covervalue)
      );
    }
  }

  if (empty($segments)) {
    return '<p class="text-xs text-gray-500 dark:text-gray-400">No night forecast available.</p>';
  }

  usort($segments, function ($a, $b) {
    return $a['timestamp'] <=> $b['timestamp'];
  });

  $segmentCount = count($segments);
  for ($i = 0; $i < $segmentCount; $i++) {
    $currentTime = $segments[$i]['timestamp'];
    if ($i < $segmentCount - 1) {
      $nextTime = $segments[$i + 1]['timestamp'];
    } else {
      $nextTime = $currentTime + 3600;
    }
    $durationMinutes = max(15, ($nextTime - $currentTime) / 60);
    $segments[$i]['duration'] = $durationMinutes;
  }

  $windows = array();
  $currentWindow = null;
  $clearestSegment = $segments[0];

  foreach ($segments as $segment) {
    if ($segment['cloud'] < $clearestSegment['cloud']) {
      $clearestSegment = $segment;
    }

    if ($segment['color'] === 'green-500') {
      if ($currentWindow === null) {
        $currentWindow = array(
          'start' => $segment['timestamp'],
          'end' => $segment['timestamp'] + ($segment['duration'] * 60)
        );
      } else {
        $currentWindow['end'] = $segment['timestamp'] + ($segment['duration'] * 60);
      }
    } else {
      if ($currentWindow !== null) {
        $windows[] = $currentWindow;
        $currentWindow = null;
      }
    }
  }

  if ($currentWindow !== null) {
    $windows[] = $currentWindow;
  }

  if (!empty($windows)) {
    $formattedWindows = array();
    foreach ($windows as $window) {
      $start = date('H:i', $window['start']);
      $end = date('H:i', $window['end']);
      if ($start === $end) {
        $end = date('H:i', $window['end'] + 3600);
      }
      $formattedWindows[] = $start . ' - ' . $end;
    }
    $summaryText = 'Best shooting windows: ' . implode(', ', $formattedWindows);
  } else {
    $summaryText = 'Best around ' . date('H:i', $clearestSegment['timestamp']) .
      ' (~' . round($clearestSegment['cloud']) . '% cloud)';
  }
  $summaryText = htmlspecialchars($summaryText, ENT_QUOTES, 'UTF-8');

  $timeline = '<div class="space-y-1.5">';
  $timeline .= '<div class="flex h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">';

  foreach ($segments as $segment) {
    $duration = max(1, (int) round($segment['duration']));
    $bgColor = str_replace('-500', '-400', $segment['color']);
    $tooltip = htmlspecialchars($segment['label'] . ' • ' . round($segment['cloud']) . '% cloud', ENT_QUOTES, 'UTF-8');
    $timeline .= '<div class="relative" style="flex-grow: ' . $duration . ';" title="' . $tooltip . '">';
    $timeline .= '<div class="h-full bg-' . $bgColor . '"></div>';
    $timeline .= '<span class="sr-only">' . $tooltip . '</span>';
    $timeline .= '</div>';
  }

  $timeline .= '</div>';

  $startLabel = htmlspecialchars($segments[0]['label'], ENT_QUOTES, 'UTF-8');
  $lastSegment = $segments[$segmentCount - 1];
  $endLabel = htmlspecialchars(
    date('H:i', $lastSegment['timestamp'] + ($lastSegment['duration'] * 60)),
    ENT_QUOTES,
    'UTF-8'
  );

  $timeline .= '<div class="flex justify-between text-[10px] font-medium text-gray-500 dark:text-gray-400">';
  $timeline .= '<span>' . $startLabel . '</span>';
  $timeline .= '<span>' . $endLabel . '</span>';
  $timeline .= '</div>';

  $timeline .= '<p class="text-[11px] text-gray-600 dark:text-gray-300">' . $summaryText . '</p>';
  $timeline .= '</div>';

  return $timeline;
}
    $data = getJson('http://ws1.metcheck.com/ENGINE/v9_0/json.asp?lat=51.81&lon=-0.29&lid=58143&Fc=As');
    $json = json_decode($data, true);

//print_r($json);
$newArray = array();
$cloudArray = array();
    foreach ($json['metcheckData']['forecastLocation']['forecast'] as $key=>$value) {
    if ($json['metcheckData']['forecastLocation']['forecast'][$key]['dayOrNight']=='N') {
        $newdatea=$json['metcheckData']['forecastLocation']['forecast'][$key]['utcTime'];
        $myDateTime = date('Y-m-d l', strtotime(substr($newdatea,0,10)));
      $iterationValue = $json['metcheckData']['forecastLocation']['forecast'][$key]['totalcloud'];
      $descr = $json['metcheckData']['forecastLocation']['forecast'][$key]['iconName'];
      $cloudArray[$newdatea]=$iterationValue;
      $Count=1;
      $dateKey=$myDateTime;
      if(array_key_exists($dateKey, $newArray))
      {
          // If we've already added this date to the new array, add the value
          $newArray[$dateKey]['value'] += $iterationValue;
          $newArray[$dateKey]['count'] += $Count;
          $newArray[$dateKey]['avg'] = $newArray[$dateKey]['value'] / $newArray[$dateKey]['count'] ;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunrise'];
          $newArray[$dateKey]['sunset'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunset'];
      }
      else
      {
          // Otherwise create a new element with datetimeobject as key
          $newArray[$dateKey]['count'] = $Count;
          $newArray[$dateKey]['value'] = $iterationValue;
          //$newArray[$dateKey]['avg'] = $avg ;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunrise'];
          $newArray[$dateKey]['sunset'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunset'];
      }
    }
    }
//echo '<pre>';
//nl2br(print_r($cloudArray));
//echo '</pre>';




if(isset($singledate)){
  $detail=getdetail($singledate,$json);
  echo "
<div class=\"site-workspace mb-4\">
  <header class=\"workspace-header\"><div><span class=\"workspace-eyebrow\">Astronomy detail</span><h1>$singledate</h1><p>Hourly cloud and night-sky conditions for the selected date.</p></div><span class=\"workspace-badge\"><i class=\"fas fa-moon\"></i> Night outlook</span></header>
  <div class=\"workspace-panel p-4 border-l-4 border-$detailcolor\">
    <h2 class=\"text-xl font-semibold mb-4\">$singledate</h2>
    $detail
  </div>
</div>
  ";
}

echo '<div class="site-workspace">

<header class="workspace-header"><div><span class="workspace-eyebrow">Astronomy planner</span><h1>Ten-day night-sky outlook</h1><p>Scan cloud cover, sunset, sunrise and moon timing to find the strongest observing windows.</p></div><span class="workspace-badge"><i class="fas fa-star"></i> Forecast view</span></header>
<div class="grid gap-4 grid-cols-1">';

foreach ($newArray as $keya=>$valuea){
$simple=date('l d', strtotime(substr($keya,0,10)));
$simple2=date('l', strtotime(substr($keya,0,10)));
$graphic=nightview($keya,$cloudArray);
$SS=$valuea['sunset'];
$SR=$valuea['sunrise'];
$cloud=round($valuea['avg'],0);
$color=getrag($cloud);
$wd=$valuea['descr'];
$day=substr($keya,0,10);
$moon=(Moon::calculateMoonTimes(date('m', strtotime(substr($keya,0,10))),date('d', strtotime(substr($keya,0,10))), date('Y', strtotime(substr($keya,0,10))), 51.8, -0.3));
$MR=gmdate("H:i", $moon->moonrise);
$MS=gmdate("H:i", $moon->moonset);
$simpleLabel = htmlspecialchars($simple, ENT_QUOTES, 'UTF-8');
$weekdayLabel = htmlspecialchars($simple2, ENT_QUOTES, 'UTF-8');
$conditionLabel = htmlspecialchars($wd, ENT_QUOTES, 'UTF-8');
$nightLabel = htmlspecialchars('Night ' . $SS . ' → ' . $SR, ENT_QUOTES, 'UTF-8');
$moonLabel = htmlspecialchars('Moon ' . $MS . ' → ' . $MR, ENT_QUOTES, 'UTF-8');
$cloudSummary = htmlspecialchars($cloud . '%', ENT_QUOTES, 'UTF-8');

echo "\n<div class=\"bg-white dark:bg-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700 shadow rounded-xl p-3\">\n  <a href=\"/astro/index.php?DATE=$day&DATECOLOR=$color\" class=\"block space-y-3\">\n    <div class=\"grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center\">\n      <div class=\"space-y-1\">\n        <p class=\"text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400\">$weekdayLabel</p>\n        <p class=\"text-lg font-semibold text-gray-900 dark:text-gray-100\">$simpleLabel</p>\n        <p class=\"text-xs text-gray-600 dark:text-gray-300\">$conditionLabel</p>\n      </div>\n      <div class=\"flex flex-col items-start sm:items-end gap-1\">\n        <p class=\"text-2xl font-bold leading-none text-$color\">$cloudSummary</p>\n        <p class=\"text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400\">Average cloud cover</p>\n        <div class=\"flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400\">\n          <span>$nightLabel</span>\n          <span>$moonLabel</span>\n        </div>\n      </div>\n    </div>\n    <div class=\"grid gap-2 sm:grid-cols-[auto,1fr] sm:items-center\">\n      <div class=\"flex items-center gap-3 text-[11px] font-medium text-gray-600 dark:text-gray-300\">\n        <span>Night timeline</span>\n        <span class=\"flex items-center gap-1 text-gray-500 dark:text-gray-400\">\n          <span class=\"h-2 w-2 rounded-full bg-green-400\"></span>\n          <span>Best for photos</span>\n        </span>\n      </div>\n      <div class=\"sm:col-start-2\">$graphic</div>\n    </div>\n  </a>\n</div>";

}




echo '</div></div>';
