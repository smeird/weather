<?php
require_once __DIR__ . '/../bootstrap.php';

date_default_timezone_set("Europe/London");
setlocale(LC_ALL, 'uk_UA.utf8');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

if (!function_exists('asset_version')) {
  function asset_version(string $path): string {
    $normalized = ltrim($path, '/');
    $candidates = [
      __DIR__ . '/' . $normalized,
      dirname(__DIR__) . '/' . $normalized,
    ];

    foreach ($candidates as $candidate) {
      if (is_file($candidate)) {
        return (string) filemtime($candidate);
      }
    }

    return (string) time();
  }
}

require_once __DIR__ . '/../dbconn.php';
$sql = "
    SELECT
        round(`archive`.`outTemp`,1) AS `outTemp`,
        (SELECT round(`outTemp`,1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY) - 1 ORDER BY `outTemp` DESC LIMIT 1) AS `maxTemp`,
        (SELECT round(`outTemp`,1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY) - 1 ORDER BY `outTemp` ASC LIMIT 1) AS `minTemp`,
        (SELECT round(sum(`rain`),1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY) - 1) AS `rainTotal`
    FROM
        `weewx`.`archive`
    ORDER BY
        `archive`.`dateTime` DESC
    LIMIT 1;
";
 $result = db_query($sql);

// Fetch the result row as an associative array
$row = mysqli_fetch_assoc($result);

// Now you can access the `outTemp` value like this
$outTemp = $row['outTemp'];
// And the highest temperature for today
$maxTemp = $row['maxTemp'];
$minTemp = $row['minTemp'];
$rainTotal = round($row['rainTotal'] * 10, 1);
?>
<!DOCTYPE html>
<html lang="en-UK">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="refresh" content="3600">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:description" content="Wheathamstead Weather Conditions" />
  <meta id="postdata" property="og:title" content="Weather in Wheathamstead is currently <?php echo $outTemp; ?>°C. The temprature range today was <?php echo $minTemp." : ". $maxTemp; ?>°C. Total rain today is <?php echo $rainTotal; ?> mm." />
  <title> Weather in Wheathamstead is currently <?php echo $outTemp; ?>°C. The temprature range today was <?php echo $minTemp." : ". $maxTemp; ?>°C. Total rain today is <?php echo $rainTotal; ?> mm. </title>
  <meta property="og:type" content="website" />
  <meta property="og:image" content="https://www.smeird.com/images/snap.jpeg" />
  <meta property="og:url" content="https://www.smeird.com/dynamic-graph.php?WHAT=outTemp&SCALE=day" />
  <meta property="og:image:alt" content="Picture of my Veg Garden" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta name="Keywords" content="Weather" />
  <meta name="Description" content="Personal Weather Site" />
  <link rel="home" href="/" />
  <?php
    $heroGradientAsset = 'assets/hero-gradient.css';
    $heroGradientContent = null;
    $heroGradientCandidates = [
      __DIR__ . '/' . $heroGradientAsset,
      dirname(__DIR__) . '/' . $heroGradientAsset,
    ];

    foreach ($heroGradientCandidates as $candidate) {
      if (is_file($candidate)) {
        $heroGradientContent = file_get_contents($candidate);
        break;
      }
    }

    if ($heroGradientContent === false || $heroGradientContent === null) {
      $heroGradientContent = <<<'CSS'
/* Default gradient ensures desktop browsers render a smooth blend even before
   the MQTT-driven weather theme sets a specific state. */
:root {
  --hero-gradient: linear-gradient(128deg, #020617 0%, #0f172a 35%, #1d4ed8 100%);
  --hero-foreground: #0f172a;
}

body[data-weather="clear-night"] {
  --hero-gradient: linear-gradient(128deg, #0B1B3B 0%, #1A2A6C 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="storm"] {
  --hero-gradient: linear-gradient(128deg, #394B59 0%, #101820 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="heavy-rain"] {
  --hero-gradient: linear-gradient(128deg, #4B6B8C 0%, #1F3549 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="rain"] {
  --hero-gradient: linear-gradient(128deg, #6D90B9 0%, #2F4F6F 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="snow"] {
  --hero-gradient: linear-gradient(128deg, #E6F2FF 0%, #BFD8F6 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="fog"] {
  --hero-gradient: linear-gradient(128deg, #D9E2EC 0%, #A3B3C2 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="windy"] {
  --hero-gradient: linear-gradient(128deg, #B3E5FC 0%, #5EB3E5 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="hot"] {
  --hero-gradient: linear-gradient(128deg, #FDB36B 0%, #E85D04 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="cold"] {
  --hero-gradient: linear-gradient(128deg, #B6D0F5 0%, #3A6EA5 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="overcast"] {
  --hero-gradient: linear-gradient(128deg, #C9D1D9 0%, #8A96A3 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="stale"] {
  --hero-gradient: linear-gradient(128deg, #ECECEC 0%, #BBBBBB 100%);
  --hero-foreground: #222222;
}
CSS;
    }

    if (!empty($heroGradientContent)) {
      echo "  <style data-inline-asset=\"hero-gradient\">\n";
      echo rtrim($heroGradientContent);
      echo "\n  </style>\n";
    }


    $heroGradientScriptAsset = 'assets/hero-gradient.js';
    $heroGradientScript = null;
    $heroGradientScriptCandidates = [
      __DIR__ . '/' . $heroGradientScriptAsset,
      dirname(__DIR__) . '/' . $heroGradientScriptAsset,
    ];

    foreach ($heroGradientScriptCandidates as $candidate) {
      if (is_file($candidate)) {
        $heroGradientScript = file_get_contents($candidate);
        break;
      }
    }

    if ($heroGradientScript === false) {
      $heroGradientScript = null;
    }

  ?>
  <script src="https://cdn.tailwindcss.com" defer></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js" defer></script>
  <script src="https://kit.fontawesome.com/55c3f37ab0.js" crossorigin="anonymous" defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&family=Inter&family=Source+Sans+Pro:wght@300&display=swap" rel="stylesheet">
  <script src="https://code.highcharts.com/stock/highstock.js" defer></script>
  <script src="https://code.highcharts.com/highcharts-more.js" defer></script>
  <script src="https://code.highcharts.com/modules/columnrange.js" defer></script>
  <script src="https://code.highcharts.com/modules/exporting.js" defer></script>
  <script src="https://code.highcharts.com/themes/adaptive.js" defer></script>
  <script>
    window.SMEIRD = window.SMEIRD || {};
    window.SMEIRD.brokerUrl = window.SMEIRD.brokerUrl || 'wss://mqtt.smeird.com:8083/mqtt';
  </script>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js" defer></script>
  <?php if (!empty($heroGradientScript)) { ?>
    <script data-inline-asset="hero-gradient" type="application/javascript">
      (function () {
        var source = <?php echo json_encode($heroGradientScript, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        function inject() {
          if (!source) return;
          if (typeof mqtt === 'undefined') {
            console.warn('Skipping hero gradient script because mqtt library is unavailable.');
            return;
          }
          var script = document.createElement('script');
          script.type = 'text/javascript';
          script.text = source;
          document.head.appendChild(script);
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', inject, { once: true });
        } else {
          inject();
        }
      })();
    </script>
  <?php } else { ?>
    <script data-inline-asset="hero-gradient-fallback">
      document.addEventListener('DOMContentLoaded', function () {
        if (!document.body) return;
        if (!document.body.hasAttribute('data-weather')) {
          document.body.setAttribute('data-weather', 'overcast');
        }
      });
    </script>
  <?php } ?>
  <script defer>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.Highcharts && Highcharts.theme) {
        Highcharts.setOptions(Highcharts.theme);
      }
      if (window.Highcharts) {
        Highcharts.setOptions({
          time: { useUTC: false },
          tooltip: { fixed: true, shared: true, xDateFormat: '%e %b %Y %H:%M' }
        });
      }
    });
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/canvg/3.0.7/umd.min.js" defer></script>
  <script src="js/chart-theme.js?v=<?php echo asset_version('js/chart-theme.js'); ?>" defer></script>
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
  <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="theme-color" content="#ffffff">
  <style>
    :root {
      color-scheme: light dark;
      --surface-border-light: rgba(255, 255, 255, 0.45);
      --surface-border-dark: rgba(148, 163, 184, 0.25);
      --surface-shadow-light: 0 22px 48px -28px rgba(15, 23, 42, 0.45);
      --surface-shadow-dark: 0 22px 48px -28px rgba(8, 47, 73, 0.55);
      --chart-surface-light: rgba(255, 255, 255, 0.42);
      --chart-surface-dark: rgba(15, 23, 42, 0.74);
      --chart-plot-light: rgba(255, 255, 255, 0.22);
      --chart-plot-dark: rgba(15, 23, 42, 0.6);
      --chart-grid-light: rgba(148, 163, 184, 0.28);
      --chart-grid-dark: rgba(59, 130, 246, 0.35);
      --chart-tooltip-border-light: rgba(59, 130, 246, 0.45);
      --chart-tooltip-border-dark: rgba(56, 189, 248, 0.45);
      --chart-marker-fill-light: rgba(255, 255, 255, 0.85);
      --chart-marker-fill-dark: rgba(15, 23, 42, 0.85);
      --chart-area-opacity: 0.45;
      --chart-palette-light: #1d4ed8, #2563eb, #0ea5e9, #38bdf8, #60a5fa, #a855f7;
      --chart-palette-dark: #38bdf8, #60a5fa, #22d3ee, #f472b6, #facc15, #f97316;
    }
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Roboto', sans-serif; font-weight: 700; }
    button, .highlight { font-family: 'Source Sans Pro', sans-serif; font-weight: 300; }
    .chart-frame {
      background: transparent;
      border: none;
      box-shadow: none;
      padding: 1rem;
    }
    .chart-frame .highcharts-container,
    .chart-frame .highcharts-root,
    .chart-frame .highcharts-background,
    .chart-frame .highcharts-plot-background {
      background: transparent !important;
      fill: transparent !important;
    }
    .chart-frame .highcharts-plot-border,
    .chart-frame .highcharts-plot-border-line {
      stroke: transparent !important;
    }
    .highcharts-background,
    .highcharts-plot-background {
      fill: transparent !important;
    }
    .highcharts-plot-border,
    .highcharts-plot-border-line {
      stroke: transparent !important;
    }
    body.theme-mist {
      min-height: 100vh;
      background: linear-gradient(128deg, #020617 0%, #0f172a 35%, #1d4ed8 100%);
      color: #0f172a;
      position: relative;
      overflow-x: hidden;
    }
    body.theme-mist::before {
      content: "";
      position: fixed;
      inset: 0;
      background:
        radial-gradient(circle at 12% 18%, rgba(59, 130, 246, 0.35), transparent 58%),
        radial-gradient(circle at 82% 10%, rgba(14, 165, 233, 0.28), transparent 60%),
        linear-gradient(120deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0));
      backdrop-filter: blur(48px);
      z-index: 0;
      pointer-events: none;
      mix-blend-mode: screen;
      opacity: 0.9;
    }
    html.dark body.theme-mist {
      background: linear-gradient(135deg, #020617 0%, #0b1120 32%, #1e3a8a 100%);
      color: #e2e8f0;
    }
    html.dark body.theme-mist::before {
      background:
        radial-gradient(circle at 18% 80%, rgba(14, 165, 233, 0.28), transparent 60%),
        radial-gradient(circle at 78% 12%, rgba(56, 189, 248, 0.3), transparent 65%),
        linear-gradient(140deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0));
      mix-blend-mode: lighten;
      opacity: 0.85;
    }
    body.theme-mist > * { position: relative; z-index: 1; }
    #sidebar-toggle {
      background: rgba(255, 255, 255, 0.78);
      border: 1px solid var(--surface-border-light);
      backdrop-filter: blur(18px);
      box-shadow: var(--surface-shadow-light);
      transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }
    #sidebar-toggle:hover {
      transform: translateY(-2px) scale(1.03);
      box-shadow: 0 20px 38px -22px rgba(59, 130, 246, 0.45);
      background: rgba(255, 255, 255, 0.92);
    }
    html.dark #sidebar-toggle {
      background: rgba(15, 23, 42, 0.85);
      border-color: var(--surface-border-dark);
      box-shadow: var(--surface-shadow-dark);
    }
    html.dark #sidebar-toggle:hover {
      box-shadow: 0 22px 40px -24px rgba(56, 189, 248, 0.55);
      background: rgba(15, 23, 42, 0.92);
    }
    body.sidebar-open {
      overflow: hidden;
    }
    #sidebar {
      overflow: hidden;
      background: linear-gradient(160deg, rgba(255, 255, 255, 0.28), rgba(148, 163, 184, 0.12));
      border: 1px solid rgba(255, 255, 255, 0.35);
      border-radius: 2rem;
      box-shadow: 0 40px 90px -48px rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(28px);
      isolation: isolate;
    }
    #sidebar::before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 12% 22%, rgba(59, 130, 246, 0.32), transparent 60%),
        radial-gradient(circle at 88% 12%, rgba(236, 72, 153, 0.2), transparent 60%);
      opacity: 0.9;
      pointer-events: none;
    }
    #sidebar::after {
      content: "";
      position: absolute;
      inset: 40% -45% -35% -45%;
      background: radial-gradient(circle, rgba(56, 189, 248, 0.3), transparent 75%);
      filter: blur(32px);
      opacity: 0.55;
      pointer-events: none;
    }
    html.dark #sidebar {
      background: linear-gradient(160deg, rgba(15, 23, 42, 0.9), rgba(8, 47, 73, 0.7));
      border-color: rgba(71, 85, 105, 0.5);
      box-shadow: 0 45px 96px -50px rgba(14, 165, 233, 0.55);
    }
    html.dark #sidebar::before {
      background:
        radial-gradient(circle at 18% 18%, rgba(59, 130, 246, 0.32), transparent 60%),
        radial-gradient(circle at 82% 18%, rgba(14, 165, 233, 0.28), transparent 62%);
    }
    html.dark #sidebar::after {
      background: radial-gradient(circle, rgba(59, 130, 246, 0.38), transparent 70%);
    }
    #navname {
      position: relative;
      display: flex;
      gap: 1rem;
      align-items: center;
      padding: 0.6rem 1.4rem 1rem;
      border-radius: 1.6rem;
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(20px);
    }
    html.dark #navname {
      background: rgba(15, 23, 42, 0.65);
      border-color: rgba(148, 163, 184, 0.35);
    }
    #navname .brand-icon {
      flex-shrink: 0;
      width: 2.9rem;
      height: 2.9rem;
      display: grid;
      place-items: center;
      border-radius: 1.1rem;
      background: linear-gradient(140deg, rgba(59, 130, 246, 0.38), rgba(14, 165, 233, 0.22));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    html.dark #navname .brand-icon {
      background: linear-gradient(140deg, rgba(96, 165, 250, 0.35), rgba(14, 165, 233, 0.3));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }
    #navname .brand-copy {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    #navname .brand-title {
      font-weight: 700;
      font-size: 1.08rem;
      letter-spacing: 0.03em;
      background: linear-gradient(90deg, rgba(15, 23, 42, 0.9), rgba(59, 130, 246, 0.85));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    html.dark #navname .brand-title {
      background: linear-gradient(90deg, #38bdf8, #a5b4fc);
    }
    #navname .brand-subtitle {
      font-size: 0.68rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: rgba(15, 23, 42, 0.58);
    }
    html.dark #navname .brand-subtitle { color: rgba(226, 232, 240, 0.68); }
    #connect {
      margin: 1.5rem 1.5rem 0;
    }
    .status-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.05rem 1.35rem;
      border-radius: 1.5rem;
      border: 1px solid rgba(255, 255, 255, 0.28);
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(20px);
      box-shadow: 0 30px 80px -46px rgba(15, 23, 42, 0.65);
      transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    html.dark .status-card {
      background: rgba(15, 23, 42, 0.7);
      border-color: rgba(148, 163, 184, 0.32);
      box-shadow: 0 34px 85px -48px rgba(2, 6, 23, 0.82);
    }
    .status-card .status-copy {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .status-card .status-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.2em;
      color: rgba(15, 23, 42, 0.65);
    }
    html.dark .status-card .status-label { color: rgba(226, 232, 240, 0.65); }
    .status-card .status-state {
      font-weight: 600;
      font-size: 0.95rem;
    }
    .status-card .status-dot {
      width: 0.85rem;
      height: 0.85rem;
      border-radius: 999px;
      background: #f87171;
      box-shadow: 0 0 0 6px rgba(248, 113, 113, 0.16);
      position: relative;
    }
    .status-card .status-dot::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: inherit;
      animation: statusPulse 2.4s infinite;
      background: currentColor;
      opacity: 0.45;
    }
    .status-card .status-chip {
      margin-left: auto;
      padding: 0.4rem 0.95rem;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      background: rgba(15, 23, 42, 0.05);
      color: rgba(15, 23, 42, 0.75);
    }
    html.dark .status-card .status-chip {
      background: rgba(148, 163, 184, 0.15);
      color: rgba(226, 232, 240, 0.8);
    }
    [data-status-container] {
      transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    [data-status-container]:hover {
      transform: translateY(-2px);
    }
    [data-status-container].status-connected {
      border-color: rgba(134, 239, 172, 0.45);
      box-shadow: 0 32px 80px -46px rgba(34, 197, 94, 0.5);
    }
    [data-status-container].status-connected .status-dot {
      background: #4ade80;
      box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.22);
      color: #4ade80;
    }
    [data-status-container].status-connected .status-chip {
      background: rgba(34, 197, 94, 0.16);
      color: #166534;
    }
    [data-status-container].status-reconnecting {
      border-color: rgba(251, 191, 36, 0.45);
      box-shadow: 0 32px 80px -46px rgba(250, 204, 21, 0.45);
    }
    [data-status-container].status-reconnecting .status-dot {
      background: #facc15;
      box-shadow: 0 0 0 6px rgba(250, 204, 21, 0.22);
      color: #facc15;
    }
    [data-status-container].status-reconnecting .status-chip {
      background: rgba(250, 204, 21, 0.2);
      color: #92400e;
    }
    [data-status-container].status-disconnected {
      border-color: rgba(248, 113, 113, 0.4);
      box-shadow: 0 32px 80px -46px rgba(248, 113, 113, 0.45);
    }
    [data-status-container].status-disconnected .status-chip {
      background: rgba(248, 113, 113, 0.18);
      color: #7f1d1d;
    }
    @keyframes statusPulse {
      0% { transform: scale(1); opacity: 0.6; }
      70% { transform: scale(1.85); opacity: 0; }
      100% { transform: scale(1.85); opacity: 0; }
    }
    #sidebar nav {
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 1.1rem;
      padding: 0.25rem 0.5rem 0.25rem 0.75rem;
    }
    #sidebar nav::before {
      content: "";
      position: absolute;
      inset: 0.5rem 0.85rem auto;
      height: 1px;
      background: linear-gradient(90deg, rgba(148, 163, 184, 0), rgba(148, 163, 184, 0.25), rgba(148, 163, 184, 0));
    }
    #sidebar nav .nav-tile {
      position: relative;
      display: flex;
      align-items: center;
      gap: 1rem;
      width: 100%;
      padding: 0.9rem 1rem;
      border-radius: 1.2rem;
      background: rgba(255, 255, 255, 0.58);
      border: 1px solid rgba(148, 163, 184, 0.16);
      backdrop-filter: blur(18px);
      transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease, background 0.35s ease;
      color: inherit;
    }
    html.dark #sidebar nav .nav-tile {
      background: rgba(15, 23, 42, 0.82);
      border-color: rgba(71, 85, 105, 0.45);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }
    #sidebar nav .nav-tile:hover {
      transform: translateX(6px) scale(1.01);
      box-shadow: 0 20px 40px -28px rgba(14, 165, 233, 0.55);
      border-color: rgba(59, 130, 246, 0.32);
      background: linear-gradient(120deg, rgba(255, 255, 255, 0.92), rgba(224, 242, 254, 0.65));
    }
    html.dark #sidebar nav .nav-tile:hover {
      background: linear-gradient(120deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.82));
      border-color: rgba(94, 234, 212, 0.32);
      box-shadow: 0 22px 45px -32px rgba(56, 189, 248, 0.62);
    }
    #sidebar nav .nav-tile:focus-visible {
      outline: none;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.28);
      border-color: rgba(59, 130, 246, 0.38);
    }
    html.dark #sidebar nav .nav-tile:focus-visible {
      box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.38);
      border-color: rgba(96, 165, 250, 0.48);
    }
    #sidebar nav .nav-group-toggle {
      justify-content: space-between;
      border-radius: 1.25rem;
      font-weight: 600;
      cursor: pointer;
    }
    #sidebar nav .nav-group-toggle .nav-text {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
      align-items: flex-start;
    }
    #sidebar nav .nav-group-toggle .nav-title {
      font-size: 0.95rem;
      letter-spacing: 0.015em;
    }
    #sidebar nav .nav-group-toggle .nav-subtitle {
      font-size: 0.7rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(15, 23, 42, 0.5);
    }
    html.dark #sidebar nav .nav-group-toggle .nav-subtitle { color: rgba(226, 232, 240, 0.58); }
    #sidebar nav .nav-group-toggle.open {
      border-color: rgba(59, 130, 246, 0.35);
      box-shadow: 0 18px 30px -24px rgba(59, 130, 246, 0.4);
    }
    html.dark #sidebar nav .nav-group-toggle.open {
      border-color: rgba(56, 189, 248, 0.35);
      box-shadow: 0 20px 38px -26px rgba(56, 189, 248, 0.48);
    }
    #sidebar nav .nav-link {
      text-decoration: none;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 1rem;
      font-size: 0.95rem;
    }
    #sidebar nav .nav-link .nav-title { letter-spacing: 0.01em; }
    #sidebar nav .nav-icon {
      flex-shrink: 0;
      width: 2.65rem;
      height: 2.65rem;
      border-radius: 1rem;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(14, 165, 233, 0.1));
      color: #1d4ed8;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }
    #sidebar nav .nav-icon i { font-size: 1rem; }
    html.dark #sidebar nav .nav-icon {
      background: linear-gradient(135deg, rgba(96, 165, 250, 0.22), rgba(14, 116, 144, 0.22));
      color: #38bdf8;
      box-shadow: none;
    }
    #sidebar nav .submenu {
      margin: 0.6rem 0 0;
      margin-left: 0.35rem;
      padding-left: 0.85rem;
      border-left: 1px solid rgba(59, 130, 246, 0.2);
      display: grid;
      gap: 0.55rem;
    }
    html.dark #sidebar nav .submenu {
      border-left-color: rgba(148, 163, 184, 0.3);
    }
    #sidebar nav .submenu .nav-link {
      padding: 0.75rem 1rem;
      background: rgba(255, 255, 255, 0.5);
      border: 1px solid rgba(148, 163, 184, 0.14);
      border-radius: 1.1rem;
    }
    html.dark #sidebar nav .submenu .nav-link {
      background: rgba(15, 23, 42, 0.78);
      border-color: rgba(71, 85, 105, 0.4);
    }
    #sidebar nav .submenu .nav-link:hover {
      transform: translateX(8px);
      box-shadow: 0 16px 32px -26px rgba(59, 130, 246, 0.42);
      background: linear-gradient(120deg, rgba(255, 255, 255, 0.95), rgba(224, 242, 254, 0.75));
    }
    html.dark #sidebar nav .submenu .nav-link:hover {
      background: linear-gradient(120deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.85));
      box-shadow: 0 18px 36px -28px rgba(56, 189, 248, 0.5);

    }
    #sidebar nav .submenu .nav-icon {
      width: 2.25rem;
      height: 2.25rem;
      border-radius: 0.9rem;
    }

    #sidebar nav .chevron {
      display: grid;
      place-items: center;
      width: 2.3rem;
      height: 2.3rem;
      border-radius: 0.9rem;
      background: rgba(59, 130, 246, 0.14);
      color: #2563eb;
      transition: transform 0.35s ease, background 0.35s ease, color 0.35s ease;
    }
    #sidebar nav .nav-group-toggle:hover .chevron,
    #sidebar nav .nav-group-toggle.open .chevron {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.28), rgba(14, 165, 233, 0.18));
      color: #0f172a;
    }
    html.dark #sidebar nav .chevron {
      background: rgba(96, 165, 250, 0.2);
      color: #60a5fa;
    }
    html.dark #sidebar nav .nav-group-toggle:hover .chevron,
    html.dark #sidebar nav .nav-group-toggle.open .chevron {
      background: linear-gradient(135deg, rgba(96, 165, 250, 0.32), rgba(14, 165, 233, 0.26));
      color: #f8fafc;
    }
    #sidebar nav .nav-group-toggle.open .chevron { transform: rotate(180deg); }
    .submenu { max-height: 0; overflow: hidden; transition: max-height 0.35s ease-in-out; }

    .submenu.open { max-height: 1200px; }

    #theme-select {
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.65);
      border: 1px solid rgba(148, 163, 184, 0.18);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    #theme-select:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.45);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
    html.dark #theme-select {
      background: rgba(15, 23, 42, 0.7);
      border-color: rgba(71, 85, 105, 0.55);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
      color: #f1f5f9;
    }
    html.dark #theme-select:focus {
      border-color: rgba(96, 165, 250, 0.55);
      box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2);
    }
    .content-wrapper {
      backdrop-filter: blur(22px);
      background: linear-gradient(145deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.4));
      border-radius: 2rem;
      padding: 2.5rem 2rem;
      border: 1px solid var(--surface-border-light);
      box-shadow: 0 35px 80px -45px rgba(30, 64, 175, 0.35);
    }
    html.dark .content-wrapper {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.6));
      border-color: var(--surface-border-dark);
      box-shadow: 0 35px 80px -45px rgba(30, 64, 175, 0.45);
    }

    .current-conditions-section {
      position: relative;
      overflow: hidden;
      border-radius: 2.5rem;
      padding: clamp(2.5rem, 4vw, 3.25rem);
      background: linear-gradient(120deg, #0b173d 0%, #1e3a8a 40%, #1d4ed8 75%, #38bdf8 100%);
      border: 1px solid rgba(255, 255, 255, 0.28);
      box-shadow: 0 42px 95px -50px rgba(15, 23, 42, 0.7);
      color: #e0f2fe;
    }
    .current-conditions-section::before {
      content: "";
      position: absolute;
      inset: -25% 42% 45% -18%;
      background:
        radial-gradient(circle at 18% 12%, rgba(191, 219, 254, 0.65), transparent 65%),
        radial-gradient(circle at 75% -15%, rgba(125, 211, 252, 0.55), transparent 70%);
      opacity: 0.85;
      mix-blend-mode: screen;
      pointer-events: none;
    }
    .current-conditions-section::after {
      content: "";
      position: absolute;
      inset: 55% -35% -25% 38%;
      background: radial-gradient(circle at 40% 40%, rgba(56, 189, 248, 0.4), transparent 70%);
      opacity: 0.7;
      mix-blend-mode: screen;
      pointer-events: none;
    }
    html.dark .current-conditions-section {
      background: linear-gradient(125deg, #020617 0%, #0b173d 28%, #1e3a8a 65%, #312e81 100%);
      border-color: rgba(148, 163, 184, 0.35);
      box-shadow: 0 42px 95px -50px rgba(8, 47, 73, 0.75);
      color: #e2e8f0;
    }
    html.dark .current-conditions-section::before {
      background:
        radial-gradient(circle at 15% 5%, rgba(191, 219, 254, 0.55), transparent 65%),
        radial-gradient(circle at 85% 10%, rgba(96, 165, 250, 0.5), transparent 70%);
    }
    html.dark .current-conditions-section::after {
      background: radial-gradient(circle at 40% 35%, rgba(37, 99, 235, 0.4), transparent 70%);
    }
    .current-conditions-card {
      position: relative;
      z-index: 2;
      border-radius: 0;
      padding: 0;
      background: transparent;
      box-shadow: none;
      border: none;
      color: inherit;
    }
    .current-conditions-card::before { display: none; }
    .current-conditions-card h1 {
      color: #f8fafc;
      text-shadow: 0 30px 65px rgba(2, 6, 23, 0.65);
    }
    .current-conditions-card p {
      color: rgba(224, 242, 254, 0.85);
      max-width: 38rem;

    }
    .current-conditions-card .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1.75rem;
      border-radius: 9999px;

      background: rgba(15, 23, 42, 0.3);
      border: 1px solid rgba(191, 219, 254, 0.45);
      color: rgba(224, 242, 254, 0.85);

      font-size: 0.75rem;
      letter-spacing: 0.35em;
      text-transform: uppercase;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(18px);
    }
    .current-conditions-card .status-pill i {
      font-size: 1.1rem;
      color: rgba(191, 219, 254, 0.95);

      text-shadow: 0 20px 40px rgba(2, 6, 23, 0.55);
    }
    html.dark .current-conditions-card .status-pill {
      background: rgba(2, 6, 23, 0.45);
      border-color: rgba(148, 163, 184, 0.4);
      color: rgba(226, 232, 240, 0.85);
    }
    html.dark .current-conditions-card .status-pill i {
      color: rgba(224, 242, 254, 0.95);
    }
    .current-conditions-metrics {
      position: relative;
      z-index: 2;
      margin-top: clamp(2.25rem, 4vw, 3.25rem);
    }

    @media (max-width: 768px) {
      #sidebar {
        width: 100vw;
        max-width: 100vw;
        border-radius: 0;
        border: none;
        box-shadow: none;
      }
      body.sidebar-open #sidebar {
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 40px 90px -48px rgba(15, 23, 42, 0.55);
      }
      html.dark body.sidebar-open #sidebar {
        border-color: rgba(71, 85, 105, 0.5);
        box-shadow: 0 45px 96px -50px rgba(14, 165, 233, 0.55);
      }
      #sidebar::before,
      #sidebar::after {
        display: none;
      }
      body.sidebar-open #sidebar::before,
      body.sidebar-open #sidebar::after {
        display: block;
      }
      .content-wrapper { padding: 2rem 1.5rem; border-radius: 1.5rem; }
      .current-conditions-section { padding: 2rem; }
    }
    .metric-card {
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 0.8rem;
      padding: 1.5rem;
      border-radius: 1.6rem;
      text-decoration: none;
      color: #f8fafc;
      overflow: hidden;
      background:
        linear-gradient(155deg, rgba(var(--accent), 0.28), rgba(var(--accent-strong), 0.24)),
        linear-gradient(170deg, rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85));
      border: 1px solid rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(22px);
      box-shadow: 0 34px 90px -48px rgba(var(--accent), 0.55);
      transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.35s ease;
    }
    .metric-card:focus-visible {
      outline: 3px solid rgba(224, 242, 254, 0.75);
      outline-offset: 4px;
    }
    html.dark .metric-card:focus-visible { outline-color: rgba(191, 219, 254, 0.85); }
    .metric-card::before {
      content: "";
      position: absolute;
      inset: -35% -10% 55% -25%;
      background:
        radial-gradient(circle at 12% 25%, rgba(255, 255, 255, 0.55), transparent 60%),
        radial-gradient(circle at 80% 15%, rgba(var(--accent-soft), 0.55), transparent 70%);
      opacity: 0.85;
      pointer-events: none;
      transition: opacity 0.35s ease, transform 0.35s ease;
      mix-blend-mode: screen;
    }
    .metric-card::after {
      content: "";
      position: absolute;
      inset: 1px;
      border-radius: 1.55rem;
      border: 1px solid rgba(255, 255, 255, 0.28);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
      pointer-events: none;
      opacity: 0.6;
    }
    .metric-card:hover {
      transform: translateY(-12px);
      box-shadow:
        0 38px 96px -48px rgba(var(--accent), 0.65),
        0 20px 56px -30px rgba(15, 23, 42, 0.65);
      filter: brightness(1.05) saturate(1.1);
    }
    .metric-card:hover::before {
      opacity: 0.95;
      transform: translate3d(0, -10px, 0) scale(1.05);
    }
    html.dark .metric-card {
      box-shadow:
        0 38px 100px -52px rgba(var(--accent), 0.55),
        0 24px 60px -30px rgba(2, 6, 23, 0.85);
    }
    html.dark .metric-card::after {
      border-color: rgba(148, 163, 184, 0.35);
    }
    .metric-card .metric-label {
      font-size: 0.72rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      font-weight: 600;
      color: rgba(100, 116, 139, 0.95);
    }
    html.dark .metric-card .metric-label {
      color: rgba(148, 163, 184, 0.9);
    }
    .metric-card .metric-value {
      font-size: 2rem;
      font-weight: 700;
      line-height: 1.1;
      display: flex;
      align-items: baseline;
      gap: 0.3rem;
    }
    .metric-card .metric-value .stat-unit {
      font-size: 0.9rem;
      font-weight: 500;
      opacity: 0.85;
    }
    .metric-card .metric-meta {
      font-size: 0.8rem;
      font-weight: 500;
      color: #6b7280;
    }
    html.dark .metric-card .metric-meta {
      color: #6b7280;
    }
    .metric-card i {
      font-size: 1.75rem;
      color: rgba(248, 250, 252, 0.9);
    }
    .dashboard-shell {
      display: flex;
      flex-direction: column;
      gap: 3rem;
    }
    .dashboard-hero {
      position: relative;
      padding: clamp(2rem, 4vw, 3rem);
      border-radius: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.3);
      background:
        linear-gradient(to bottom right, rgba(59, 130, 246, 0.35), rgba(14, 165, 233, 0.15)),
        linear-gradient(to bottom right, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.05)),
        var(--hero-gradient, linear-gradient(128deg, #020617 0%, #0f172a 35%, #1d4ed8 100%));
      box-shadow: 0 40px 110px -58px rgba(15, 23, 42, 0.7);
      backdrop-filter: blur(28px);
      overflow: hidden;
      color: var(--hero-foreground, #0f172a);
      --hero-ink-strong: var(--hero-foreground, #0f172a);
      --hero-ink-soft: rgba(15, 23, 42, 0.72);
      --hero-ink-muted: rgba(15, 23, 42, 0.6);
      --hero-chip-bg: rgba(255, 255, 255, 0.22);
      --hero-chip-border: rgba(255, 255, 255, 0.4);
      --hero-card-bg: rgba(255, 255, 255, 0.2);
      --hero-card-border: rgba(255, 255, 255, 0.35);
    }

    @supports (color: color-mix(in srgb, #000 50%, transparent)) {
      .dashboard-hero {
        --hero-ink-soft: color-mix(in srgb, var(--hero-ink-strong) 72%, transparent);
        --hero-ink-muted: color-mix(in srgb, var(--hero-ink-strong) 58%, transparent);
        --hero-chip-bg: color-mix(in srgb, var(--hero-ink-strong) 16%, transparent);
        --hero-chip-border: color-mix(in srgb, var(--hero-ink-strong) 32%, transparent);
        --hero-card-bg: color-mix(in srgb, var(--hero-ink-strong) 12%, transparent);
        --hero-card-border: color-mix(in srgb, var(--hero-ink-strong) 28%, transparent);
      }
    }
    .dashboard-hero::before {
      content: "";
      position: absolute;
      inset: -30% -10% 50% -15%;
      background:
        radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.55), transparent 60%),
        radial-gradient(circle at 80% 20%, rgba(191, 219, 254, 0.55), transparent 70%);
      opacity: 0.85;
      pointer-events: none;
      mix-blend-mode: screen;
    }
    html.dark .dashboard-hero {
      border-color: rgba(148, 163, 184, 0.35);
      background:
        linear-gradient(to bottom right, rgba(37, 99, 235, 0.32), rgba(2, 132, 199, 0.2)),
        linear-gradient(to bottom right, rgba(15, 23, 42, 0.78), rgba(2, 6, 23, 0.68)),
        var(--hero-gradient, linear-gradient(128deg, #020617 0%, #0f172a 35%, #1d4ed8 100%));
      box-shadow: 0 44px 120px -62px rgba(2, 6, 23, 0.85);
    }
    html.dark .dashboard-hero::before {
      opacity: 0.75;
      background:
        radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.35), transparent 65%),
        radial-gradient(circle at 78% 18%, rgba(14, 165, 233, 0.3), transparent 70%);
    }
    .hero-grid {
      position: relative;
      display: grid;
      gap: 2.2rem;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      align-items: start;
    }
    .hero-copy {
      grid-column: span 12 / span 12;
      display: flex;
      flex-direction: column;
      gap: 1.3rem;
      max-width: 700px;
    }
    @media (min-width: 768px) {
      .hero-copy { grid-column: span 7 / span 7; }
      .hero-stats-grid { grid-column: span 5 / span 5; }
    }
    .hero-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.55rem 1.1rem;
      border-radius: 999px;
      font-size: 0.75rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      background: var(--hero-chip-bg);
      color: var(--hero-ink-strong);
      border: 1px solid var(--hero-chip-border);
      backdrop-filter: blur(16px);
    }
    .hero-copy h1 {
      font-size: clamp(2.4rem, 5vw, 3.25rem);
      letter-spacing: 0.01em;
    }
    .hero-copy p {
      max-width: 520px;
      font-size: 1rem;
      line-height: 1.7;
      color: var(--hero-ink-soft);
    }
    .status-card-hero {
      margin-top: 0.5rem;
      padding: 1.25rem 1.65rem;
      border-radius: 1.6rem;
      background: var(--hero-card-bg);
      border: 1px solid var(--hero-card-border);
      box-shadow: 0 36px 90px -50px rgba(15, 23, 42, 0.65);
      color: var(--hero-ink-strong);
    }
    .status-card-hero .status-label {
      color: var(--hero-ink-muted);
    }
    .status-card-hero .status-chip {
      background: var(--hero-chip-bg);
      color: var(--hero-ink-strong);
    }
    .hero-stats-grid {
      grid-column: span 12 / span 12;
      min-width: 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      align-items: start;
      align-content: start;
    }
    .hero-stats-grid > .hero-quick-stats:only-child {
      align-self: start;
      justify-self: end;
      width: min(100%, 340px);
    }
    .hero-stat {
      position: relative;
      padding: 1.2rem 1.4rem;
      border-radius: 1.5rem;
      background: var(--hero-card-bg);
      border: 1px solid var(--hero-card-border);
      box-shadow: 0 30px 80px -50px rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
    }
    .hero-stat .stat-label {
      font-size: 0.72rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--hero-ink-muted);
    }
    .hero-stat .stat-value {
      font-size: 1.9rem;
      font-weight: 700;
      display: flex;
      align-items: baseline;
      gap: 0.35rem;
      color: var(--hero-ink-strong);
    }
    .hero-stat .stat-value .stat-unit {
      font-size: 0.9rem;
      font-weight: 500;
      opacity: 0.85;
      color: var(--hero-ink-soft);
    }
    .hero-stat .stat-meta {
      font-size: 0.85rem;
      color: var(--hero-ink-muted);
    }
    .hero-stats-grid .insight-card {
      height: 100%;
    }
    .dashboard-hero [data-stat] {
      color: var(--hero-ink-strong);
    }
    .dashboard-hero .stat-reading,
    .dashboard-hero .stat-value {
      color: var(--hero-ink-strong);
    }
    .dashboard-hero .stat-reading .stat-unit,
    .dashboard-hero .stat-unit,
    .dashboard-hero .metric-meta,
    .dashboard-hero .stat-meta,
    .dashboard-hero .label {
      color: var(--hero-ink-soft);
    }
    .stat-reading {
      display: inline-flex;
      align-items: baseline;
      gap: 0.35rem;
      font-weight: 600;
    }
    .stat-unit {
      color: #6b7280;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .stat-value [data-stat],
    .metric-value [data-stat],
    .insight-list [data-stat],
    .stat-reading [data-stat] {
      color: #000;
    }
    .metric-meta,
    .stat-meta {
      color: #6b7280;
    }
    .section-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
    }
    .section-header h2 {
      font-size: 1.35rem;
      letter-spacing: 0.04em;
    }
    .section-header p {
      max-width: 520px;
      color: rgba(15, 23, 42, 0.68);
      font-size: 0.95rem;
      line-height: 1.7;
    }
    html.dark .section-header p { color: rgba(226, 232, 240, 0.7); }
    .section-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1.25rem;
      border-radius: 999px;
      font-size: 0.75rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      background: rgba(255, 255, 255, 0.22);
      border: 1px solid rgba(255, 255, 255, 0.32);
      color: rgba(15, 23, 42, 0.72);
    }
    html.dark .section-chip {
      background: rgba(15, 23, 42, 0.65);
      border-color: rgba(148, 163, 184, 0.32);
      color: rgba(226, 232, 240, 0.75);
    }
    .panels-grid {
      display: grid;
      gap: 1.5rem;
    }
    @media (min-width: 1280px) {
      .panels-grid {
        grid-template-columns: 2fr 1fr;
        align-items: stretch;
      }
    }
    .insight-card {
      border-radius: 1.75rem;
      border: 1px solid rgba(255, 255, 255, 0.28);
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(24px);
      box-shadow: 0 36px 90px -50px rgba(15, 23, 42, 0.6);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    html.dark .insight-card {
      background: rgba(15, 23, 42, 0.72);
      border-color: rgba(148, 163, 184, 0.32);
      box-shadow: 0 40px 96px -54px rgba(2, 6, 23, 0.85);
    }
    .insight-list {
      display: grid;
      gap: 0.85rem;
    }
    .insight-list li {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      font-size: 0.95rem;
      color: #6b7280;
    }
    html.dark .insight-list li { color: #6b7280; }
    .insight-list .label {
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 0.72rem;
      color: #6b7280;
      font-weight: 600;
    }
    html.dark .insight-list .label { color: #6b7280; }
    .insight-list .stat-unit {
      color: #6b7280;
    }
    html.dark .insight-list .stat-unit { color: #6b7280; }
    html.dark .metric-meta,
    html.dark .stat-meta {
      color: #6b7280;
    }
    html.dark .stat-unit {
      color: #6b7280;
    }
    .glass-panel {
      background: linear-gradient(150deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.06));
      border-radius: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 36px 110px -62px rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(28px);
      overflow: hidden;
    }
    html.dark .glass-panel {
      background: linear-gradient(150deg, rgba(15, 23, 42, 0.78), rgba(2, 6, 23, 0.6));
      border-color: rgba(148, 163, 184, 0.35);
      box-shadow: 0 44px 120px -66px rgba(2, 6, 23, 0.85);
    }
    .glass-panel .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.6rem 1.85rem;
      border-bottom: 1px solid rgba(148, 163, 184, 0.22);
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.18), transparent 70%);
    }
    html.dark .glass-panel .panel-header {
      border-bottom-color: rgba(71, 85, 105, 0.35);
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.25), transparent 70%);
    }
    .glass-panel .panel-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1e293b;
      letter-spacing: 0.04em;
    }
    html.dark .glass-panel .panel-title { color: #e2e8f0; }
    .glass-panel .panel-body { padding: 1.75rem; }
    .btn-modern {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: linear-gradient(135deg, rgba(37, 99, 235, 1), rgba(14, 165, 233, 0.9));
      color: #fff;
      font-weight: 600;
      border-radius: 9999px;
      padding: 0.55rem 1.5rem;
      box-shadow: 0 14px 30px -15px rgba(37, 99, 235, 0.9);
      transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
    }
    .btn-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 38px -18px rgba(14, 165, 233, 0.85);
      filter: brightness(1.03);
    }
    html.dark .btn-modern { box-shadow: 0 18px 38px -18px rgba(56, 189, 248, 0.7); }
    table.min-w-full {
      border-radius: 1rem;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.65);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    html.dark table.min-w-full {
      background: rgba(15, 23, 42, 0.7);
      border-color: rgba(148, 163, 184, 0.22);
    }
    table.min-w-full thead {
      background: linear-gradient(90deg, rgba(59, 130, 246, 0.18), rgba(125, 211, 252, 0.1));
    }
    html.dark table.min-w-full thead {
      background: linear-gradient(90deg, rgba(96, 165, 250, 0.22), rgba(56, 189, 248, 0.12));
    }
    table.min-w-full tbody tr {
      transition: background 0.2s ease;
    }
    table.min-w-full tbody tr:hover {
      background: rgba(59, 130, 246, 0.08);
    }
    html.dark table.min-w-full tbody tr:hover {
      background: rgba(148, 163, 184, 0.14);
    }
    table.min-w-full tbody td,
    table.min-w-full thead th {
      border-color: rgba(148, 163, 184, 0.2);
    }
    html.dark table.min-w-full tbody td,
    html.dark table.min-w-full thead th {
      border-color: rgba(71, 85, 105, 0.35);
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
    }
  </style>
</head>
  <body class="theme-mist text-gray-900 dark:text-gray-100" data-weather="stale">
  <button id="sidebar-toggle" type="button" class="p-2 text-gray-900 dark:text-gray-100 md:hidden fixed top-4 right-4 z-50 rounded-xl" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>
    <div class="flex min-h-screen">

      <aside id="sidebar" role="navigation" aria-label="Primary" class="text-gray-900 dark:text-gray-100 w-full md:w-[20.24rem] space-y-4 py-6 px-4 fixed inset-y-0 left-0 z-40 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out rounded-none md:rounded-3xl overflow-y-auto md:overflow-visible max-h-screen md:max-h-none">

      <a id="navname" class="px-4 text-lg font-semibold" href="/">
        <span class="brand-copy">
          <span class="brand-title">Wheathampstead Weather</span>
          <span class="brand-subtitle">Local conditions in real time</span>
        </span>
      </a>
      <nav class="mt-5">
          <div>
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="reports-menu" aria-controls="reports-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                <span class="nav-text">
                  <span class="nav-title">Reports</span>
                  <span class="nav-subtitle">Insights &amp; trends</span>
                </span>
              </span>
              <span class="chevron" aria-hidden="true" data-chevron>
                <i class="fas fa-chevron-down"></i>
              </span>
            </button>
            <div id="reports-menu" class="submenu" aria-hidden="true">
              <a class="nav-tile nav-link" href="/">
                <span class="nav-icon"><i class="fas fa-home" aria-hidden="true"></i></span>
                <span class="nav-title">Home <span class="sr-only">(current)</span></span>
              </a>
              <a class="nav-tile nav-link" href="/extremes.php">
                <span class="nav-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                <span class="nav-title">Extremes</span>
              </a>
                <a class="nav-tile nav-link" href="/reportrainyeartotals.php">
                  <span class="nav-icon"><i class="fas fa-cloud-rain" aria-hidden="true"></i></span>
                  <span class="nav-title">Rain By Year</span>
                </a>
                <a class="nav-tile nav-link" href="/reporttempyeartotals.php">
                  <span class="nav-icon"><i class="fas fa-temperature-high" aria-hidden="true"></i></span>
                  <span class="nav-title">Temp By Year</span>
                </a>
                <a class="nav-tile nav-link" href="/reportwindyeartotals.php">
                  <span class="nav-icon"><i class="fas fa-wind" aria-hidden="true"></i></span>
                  <span class="nav-title">Wind By Year</span>
                </a>
                <a class="nav-tile nav-link" href="/records.php">
                  <span class="nav-icon"><i class="fas fa-book" aria-hidden="true"></i></span>
                  <span class="nav-title">Records</span>
                </a>
              <a class="nav-tile nav-link" href="/windrose.php">
                <span class="nav-icon"><i class="fas fa-compass" aria-hidden="true"></i></span>
                <span class="nav-title">Wind Rose</span>
              </a>
              <a class="nav-tile nav-link" href="/seasonal.php">
                <span class="nav-icon"><i class="fas fa-calendar" aria-hidden="true"></i></span>
                <span class="nav-title">Seasonal</span>
              </a>
              <a class="nav-tile nav-link" href="/last-time.php">
                <span class="nav-icon"><i class="fas fa-history" aria-hidden="true"></i></span>
                <span class="nav-title">Last Time</span>
              </a>
              <a class="nav-tile nav-link" href="/climate-analysis.php">
                <span class="nav-icon"><i class="fas fa-globe" aria-hidden="true"></i></span>
                <span class="nav-title">Climate Analysis</span>
              </a>
            </div>
          </div>
          <div>
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="tools-menu" aria-controls="tools-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-tools" aria-hidden="true"></i></span>
                <span class="nav-text">
                  <span class="nav-title">Tools</span>
                  <span class="nav-subtitle">Utilities &amp; exports</span>
                </span>
              </span>
              <span class="chevron" aria-hidden="true" data-chevron>
                <i class="fas fa-chevron-down"></i>
              </span>
            </button>
            <div id="tools-menu" class="submenu" aria-hidden="true">
              <a class="nav-tile nav-link" href="/picture.php">
                <span class="nav-icon"><i class="fas fa-camera" aria-hidden="true"></i></span>
                <span class="nav-title">Webcam</span>
              </a>
              <a class="nav-tile nav-link" href="/export.php">
                <span class="nav-icon"><i class="fas fa-file-export" aria-hidden="true"></i></span>
                <span class="nav-title">Export Data</span>
              </a>
              <a class="nav-tile nav-link" href="/historical.php">
                <span class="nav-icon"><i class="fas fa-clock" aria-hidden="true"></i></span>
                <span class="nav-title">Historical Explorer</span>
              </a>
              <a class="nav-tile nav-link" href="/astro">
                <span class="nav-icon"><i class="fas fa-star" aria-hidden="true"></i></span>
                <span class="nav-title">Astro</span>
              </a>
              <div class="px-4 pt-4 pb-5 mt-4 rounded-2xl border border-white/40 dark:border-slate-800/60 bg-white/60 dark:bg-slate-900/60 shadow-sm backdrop-blur">
                <label for="theme-select" class="block text-sm mb-2 font-medium tracking-wide">Theme</label>
                <select id="theme-select" class="w-full py-2.5 px-4 rounded-xl bg-white/70 dark:bg-slate-900/60 text-gray-900 dark:text-gray-100 shadow-inner border border-white/40 dark:border-slate-700/70">
                  <option value="system">System</option>
                  <option value="light">Light</option>
                  <option value="dark">Dark</option>
                </select>
              </div>
            </div>
          </div>
          <div>
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="external-menu" aria-controls="external-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-external-link-alt" aria-hidden="true"></i></span>
                <span class="nav-text">
                  <span class="nav-title">External</span>
                  <span class="nav-subtitle">Related resources</span>
                </span>
              </span>
              <span class="chevron" aria-hidden="true" data-chevron>
                <i class="fas fa-chevron-down"></i>
              </span>
            </button>
            <div id="external-menu" class="submenu" aria-hidden="true">
              <a class="nav-tile nav-link" href="http://ob.smeird.com">
                <span class="nav-icon"><i class="fas fa-cloud-sun" aria-hidden="true"></i></span>
                <span class="nav-title">Sky Weather</span>
              </a>
              <a class="nav-tile nav-link" href="http://power.smeird.com">
                <span class="nav-icon"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                <span class="nav-title">Power Use</span>
              </a>
            </div>
          </div>
        </nav>
        <div class="px-4 pb-6">
          <?php include('graph-selector.php'); ?>
        </div>
      </aside>
      <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 md:hidden" aria-hidden="true"></div>

    <script>
      (function() {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar || !toggleButton) { return; }

        const hiddenClass = '-translate-x-full';
        const visibleClass = 'translate-x-0';
        const breakpoint = window.matchMedia('(min-width: 768px)');

        const hideBackdrop = function() {
          if (!backdrop) { return; }
          backdrop.classList.remove('opacity-100');
          backdrop.classList.add('opacity-0', 'pointer-events-none');
        };

        const showBackdrop = function() {
          if (!backdrop) { return; }
          backdrop.classList.add('opacity-100');
          backdrop.classList.remove('opacity-0', 'pointer-events-none');
        };

        const openSidebar = function() {
          sidebar.classList.remove(hiddenClass);
          sidebar.classList.add(visibleClass);
          sidebar.setAttribute('aria-hidden', 'false');
          toggleButton.setAttribute('aria-expanded', 'true');
          document.body.classList.add('sidebar-open');
          showBackdrop();
        };

        const closeSidebar = function() {
          sidebar.classList.add(hiddenClass);
          sidebar.classList.remove(visibleClass);
          sidebar.setAttribute('aria-hidden', 'true');
          toggleButton.setAttribute('aria-expanded', 'false');
          document.body.classList.remove('sidebar-open');
          hideBackdrop();
        };

        const syncToBreakpoint = function(state) {
          if (state.matches) {
            sidebar.classList.remove(hiddenClass, visibleClass);
            sidebar.removeAttribute('aria-hidden');
            toggleButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('sidebar-open');
            hideBackdrop();
          } else {
            closeSidebar();
          }
        };

        const handleToggle = function() {
          if (breakpoint.matches) { return; }
          if (sidebar.classList.contains(visibleClass)) {
            closeSidebar();
          } else {
            openSidebar();
          }
        };

        toggleButton.addEventListener('click', handleToggle);

        if (backdrop) {
          backdrop.addEventListener('click', function() {
            if (!breakpoint.matches) {
              closeSidebar();
            }
          });
        }

        document.addEventListener('keydown', function(event) {
          if (event.key === 'Escape' && sidebar.classList.contains(visibleClass) && !breakpoint.matches) {
            closeSidebar();
            toggleButton.focus();
          }
        });

        sidebar.querySelectorAll('a.nav-link').forEach(function(link) {
          link.addEventListener('click', function() {
            if (!breakpoint.matches) {
              closeSidebar();
            }
          });
        });

        const onChange = function(event) {
          syncToBreakpoint(event);
        };

        if (typeof breakpoint.addEventListener === 'function') {
          breakpoint.addEventListener('change', onChange);
        } else if (typeof breakpoint.addListener === 'function') {
          breakpoint.addListener(onChange);
        }

        syncToBreakpoint(breakpoint);
      })();
      document.querySelectorAll('[data-submenu-toggle]').forEach(function(button) {
        var target = document.getElementById(button.getAttribute('data-submenu-toggle'));
        if (!target) { return; }

        target.setAttribute('aria-hidden', 'true');

        button.addEventListener('click', function() {
          var isOpen = target.classList.toggle('open');
          button.classList.toggle('open', isOpen);
          button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
          target.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        });
      });
      (function() {
        const themeSelect = document.getElementById('theme-select');
        function applyTheme(theme) {
          if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
          } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
          } else {
            localStorage.removeItem('theme');
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
              document.documentElement.classList.add('dark');
            } else {
              document.documentElement.classList.remove('dark');
            }
          }
        }
        const storedTheme = localStorage.getItem('theme') || 'system';
        applyTheme(storedTheme);
        if (themeSelect) {
          themeSelect.value = storedTheme;
          themeSelect.addEventListener('change', function() {
            applyTheme(this.value);
          });
        }
      })();
    </script>
    <div class="flex-1 flex flex-col">
      <div class="flex-1 p-4 md:p-8 lg:p-10">
        <div class="container mx-auto max-w-7xl content-wrapper">
