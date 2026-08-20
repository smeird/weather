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
  <meta property="og:description" content="Wheathampstead live weather conditions" />
  <meta id="postdata" property="og:title" content="Weather in Wheathampstead is currently <?php echo $outTemp; ?>°C. Today's temperature range is <?php echo $minTemp."–". $maxTemp; ?>°C, with <?php echo $rainTotal; ?> mm of rain." />
  <title>Wheathampstead Weather · <?php echo $outTemp; ?>°C · Today <?php echo $minTemp."–". $maxTemp; ?>°C</title>
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
  --hero-gradient: linear-gradient(to right, #1e293b 0%, #334155 35%, #3b82f6 100%);
  --hero-foreground: #0f172a;
}

body[data-weather="clear-night"] {
  --hero-gradient: linear-gradient(to right, #0B1B3B 0%, #1A2A6C 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="storm"] {
  --hero-gradient: linear-gradient(to right, #394B59 0%, #101820 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="heavy-rain"] {
  --hero-gradient: linear-gradient(to right, #4B6B8C 0%, #1F3549 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="rain"] {
  --hero-gradient: linear-gradient(to right, #6D90B9 0%, #2F4F6F 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="snow"] {
  --hero-gradient: linear-gradient(to right, #E6F2FF 0%, #BFD8F6 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="fog"] {
  --hero-gradient: linear-gradient(to right, #D9E2EC 0%, #A3B3C2 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="windy"] {
  --hero-gradient: linear-gradient(to right, #B3E5FC 0%, #5EB3E5 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="hot"] {
  --hero-gradient: linear-gradient(to right, #FDB36B 0%, #E85D04 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="cold"] {
  --hero-gradient: linear-gradient(to right, #B6D0F5 0%, #3A6EA5 100%);
  --hero-foreground: #FFFFFF;
}

body[data-weather="overcast"] {
  --hero-gradient: linear-gradient(to right, #C9D1D9 0%, #8A96A3 100%);
  --hero-foreground: #0A0A0A;
}

body[data-weather="stale"] {
  --hero-gradient: linear-gradient(to right, #ECECEC 0%, #BBBBBB 100%);
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
  <link href="/assets/tailwind.css?v=<?php echo asset_version('assets/tailwind.css'); ?>" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js" defer></script>
  <script src="https://kit.fontawesome.com/55c3f37ab0.js" crossorigin="anonymous" defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&family=Inter&family=Source+Sans+Pro:wght@300&display=swap" rel="stylesheet">
  <script src="https://code.highcharts.com/stock/highstock.js" defer></script>
  <script src="https://code.highcharts.com/highcharts-more.js" defer></script>
  <script src="https://code.highcharts.com/modules/columnrange.js" defer></script>
  <script src="https://code.highcharts.com/modules/exporting.js" defer></script>
  <script src="https://code.highcharts.com/modules/accessibility.js" defer></script>
  <script src="https://code.highcharts.com/themes/adaptive.js" defer></script>
  <script>
    window.SMEIRD = window.SMEIRD || {};
    window.SMEIRD.brokerUrl = window.SMEIRD.brokerUrl || 'wss://mqtt.smeird.com:8083/mqtt';
    if (typeof window.SMEIRD.loopTopic === 'undefined') {
      window.SMEIRD.loopTopic = 'weather/loop';
    }
  </script>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js" defer></script>
  <?php if (!empty($heroGradientScript)) { ?>
    <script data-inline-asset="hero-gradient" type="application/javascript">
      (function () {
        var source = <?php echo json_encode($heroGradientScript, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        if (!source) return;

        function appendHeroGradientScript() {
          var existing = document.querySelector('script[data-inline-asset="hero-gradient-runtime"]');
          if (existing) return existing;

          var script = document.createElement('script');
          script.type = 'text/javascript';
          script.text = source;
          script.setAttribute('data-inline-asset', 'hero-gradient-runtime');
          document.head.appendChild(script);
          return script;
        }

        function waitForMqtt() {
          return new Promise(function (resolve, reject) {
            var CHECK_INTERVAL = 200;
            var MAX_ATTEMPTS = 50;
            var attempts = 0;
            var done = false;
            var intervalId = null;
            var mqttScript = document.querySelector('script[src="https://unpkg.com/mqtt/dist/mqtt.min.js"]');

            function cleanup() {
              if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
              }
              if (mqttScript) {
                mqttScript.removeEventListener('load', onLoad);
                mqttScript.removeEventListener('error', onError);
              }
            }

            function finish(handler, value) {
              if (done) return;
              done = true;
              cleanup();
              handler(value);
            }

            function hasMqtt() {
              return typeof window !== 'undefined' && typeof window.mqtt !== 'undefined';
            }

            function attemptResolve() {
              if (hasMqtt()) {
                finish(resolve, window.mqtt);
                return true;
              }
              return false;
            }

            function onLoad() {
              // Allow the browser a moment to expose the global before rechecking.
              setTimeout(attemptResolve, 0);
            }

            function onError() {
              finish(reject, new Error('Failed to load MQTT library.'));
            }

            intervalId = setInterval(function () {
              if (done) return;
              attempts += 1;
              if (attemptResolve()) return;
              if (attempts >= MAX_ATTEMPTS) {
                finish(reject, new Error('MQTT library did not become available in time.'));
              }
            }, CHECK_INTERVAL);

            if (mqttScript) {
              mqttScript.addEventListener('load', onLoad);
              mqttScript.addEventListener('error', onError);
            }

            attemptResolve();
          });
        }

        function start() {
          waitForMqtt()
            .then(function () {
              appendHeroGradientScript();
            })
            .catch(function (error) {
              console.warn('Skipping hero gradient script because mqtt library is unavailable.', error);
            });
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', start, { once: true });
        } else {
          start();
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
      color-scheme: light;
      --surface-border-light: rgba(255, 255, 255, 0.45);
      --surface-border-dark: rgba(148, 163, 184, 0.25);
      --surface-shadow-light: 0 24px 48px -30px rgba(37, 99, 235, 0.18);
      --surface-shadow-dark: 0 22px 48px -28px rgba(8, 47, 73, 0.55);
      --chart-surface-light: rgba(255, 255, 255, 0.72);
      --chart-surface-dark: rgba(15, 23, 42, 0.74);
      --chart-plot-light: rgba(255, 255, 255, 0.5);
      --chart-plot-dark: rgba(15, 23, 42, 0.6);
      --chart-grid-light: rgba(148, 163, 184, 0.2);
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
      padding: 0.75rem;
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
    @media (max-width: 640px) {
      .chart-frame {
        padding: 0.5rem 0.25rem;
      }
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
      background: #f7f9fc;
      color: #0f172a;
      position: relative;
      overflow-x: hidden;
    }
    body.theme-mist::before {
      content: "";
      position: fixed;
      inset: 0;
      background:
        linear-gradient(transparent 0 0),
        radial-gradient(circle at 12% 12%, rgba(37, 99, 235, 0.08), transparent 45%),
        radial-gradient(circle at 85% 14%, rgba(94, 234, 212, 0.1), transparent 45%);
      opacity: 1;
      pointer-events: none;
      mix-blend-mode: normal;
      z-index: 0;
    }
    body.theme-mist::after {
      content: "";
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(to right, rgba(15, 23, 42, 0.06) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(15, 23, 42, 0.06) 1px, transparent 1px);
      background-size: 32px 32px;
      opacity: 0.2;
      pointer-events: none;
      z-index: 0;
    }
    html.dark body.theme-mist {
      background: linear-gradient(to right, #111827 0%, #1f2937 32%, #2563eb 100%);
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
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: 0 6px 18px -12px rgba(15, 23, 42, 0.2);
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    #sidebar-toggle:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 18px -12px rgba(37, 99, 235, 0.25);
      background: #f8fafc;
    }
    body.sidebar-open {
      overflow: hidden;
    }
    #sidebar {
      overflow: hidden;
      background: #f8fafc;
      border: 1px solid rgba(148, 163, 184, 0.3);
      border-radius: 0.75rem;
      box-shadow: 0 16px 36px -28px rgba(15, 23, 42, 0.2);
      isolation: isolate;
    }
    #sidebar::before,
    #sidebar::after {
      display: none;
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
      gap: 0.75rem;
      align-items: center;
      padding: 0.35rem 0.85rem 0.55rem;
      border-radius: 0.65rem;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.25);
    }
    html.dark #navname {
      background: rgba(15, 23, 42, 0.65);
      border-color: rgba(148, 163, 184, 0.35);
    }
    #navname .brand-icon {
      flex-shrink: 0;
      width: 2.4rem;
      height: 2.4rem;
      display: grid;
      place-items: center;
      border-radius: 0.55rem;
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
      display: inline-block;
      font-weight: 700;
      font-size: 0.98rem;
      letter-spacing: 0.02em;
      color: #0f172a;
    }
    html.dark #navname .brand-title {
      background: linear-gradient(90deg, #38bdf8, #a5b4fc);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      -webkit-text-fill-color: transparent;
    }
    #navname .brand-subtitle {
      font-size: 0.6rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: rgba(15, 23, 42, 0.58);
    }
    html.dark #navname .brand-subtitle { color: rgba(226, 232, 240, 0.68); }
    #connect {
      margin: 1rem 1rem 0;
    }
    .status-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.85rem 1rem;
      border-radius: 0.75rem;
      border: 1px solid rgba(148, 163, 184, 0.2);
      background: rgba(255, 255, 255, 0.86);
      backdrop-filter: blur(12px);
      box-shadow: 0 18px 40px -32px rgba(37, 99, 235, 0.18);
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
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.2em;
      color: rgba(15, 23, 42, 0.65);
    }
    html.dark .status-card .status-label { color: rgba(226, 232, 240, 0.65); }
    .status-card .status-state {
      font-weight: 600;
      font-size: 0.88rem;
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
      padding: 0.32rem 0.8rem;
      border-radius: 999px;
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      background: rgba(59, 130, 246, 0.12);
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
      gap: 0.7rem;
      padding: 0.2rem 0.35rem 0.2rem 0.5rem;
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
      gap: 0.75rem;
      width: 100%;
      padding: 0.65rem 0.85rem;
      border-radius: 0.65rem;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.25);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
      color: inherit;
    }
    html.dark #sidebar nav .nav-tile {
      background: rgba(15, 23, 42, 0.82);
      border-color: rgba(71, 85, 105, 0.45);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }
    #sidebar nav .nav-tile:hover {
      transform: translateX(4px);
      box-shadow: 0 12px 22px -18px rgba(37, 99, 235, 0.25);
      border-color: rgba(59, 130, 246, 0.35);
      background: #f8fafc;
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
      border-radius: 0.8rem;
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
      font-size: 0.88rem;
      letter-spacing: 0.02em;
    }
    #sidebar nav .nav-group-toggle .nav-subtitle {
      font-size: 0.62rem;
      letter-spacing: 0.14em;
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
      gap: 0.75rem;
      font-size: 0.88rem;
    }
    #sidebar nav .nav-link .nav-title { letter-spacing: 0.01em; }
    #sidebar nav .nav-icon {
      flex-shrink: 0;
      width: 2.2rem;
      height: 2.2rem;
      border-radius: 0.5rem;
      display: grid;
      place-items: center;
      background: rgba(37, 99, 235, 0.08);
      color: #1d4ed8;
      box-shadow: none;
    }
    #sidebar nav .nav-icon i { font-size: 0.9rem; }
    html.dark #sidebar nav .nav-icon {
      background: linear-gradient(135deg, rgba(96, 165, 250, 0.22), rgba(14, 116, 144, 0.22));
      color: #38bdf8;
      box-shadow: none;
    }
    #sidebar nav .submenu {
      margin: 0.4rem 0 0;
      margin-left: 0.3rem;
      padding-left: 0.7rem;
      border-left: 1px solid rgba(59, 130, 246, 0.2);
      display: grid;
      gap: 0.4rem;
    }
    html.dark #sidebar nav .submenu {
      border-left-color: rgba(148, 163, 184, 0.3);
    }
    #sidebar nav .submenu .nav-link {
      padding: 0.6rem 0.8rem;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.2);
      border-radius: 0.6rem;
    }
    html.dark #sidebar nav .submenu .nav-link {
      background: rgba(15, 23, 42, 0.78);
      border-color: rgba(71, 85, 105, 0.4);
    }
    #sidebar nav .submenu .nav-link:hover {
      transform: translateX(6px);
      box-shadow: 0 10px 20px -16px rgba(37, 99, 235, 0.25);
      background: #f8fafc;
    }
    html.dark #sidebar nav .submenu .nav-link:hover {
      background: linear-gradient(120deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.85));
      box-shadow: 0 18px 36px -28px rgba(56, 189, 248, 0.5);

    }
    #sidebar nav .submenu .nav-icon {
      width: 2rem;
      height: 2rem;
      border-radius: 0.45rem;
    }

    #sidebar nav .chevron {
      display: grid;
      place-items: center;
      width: 2rem;
      height: 2rem;
      border-radius: 0.45rem;
      background: rgba(37, 99, 235, 0.12);
      color: #2563eb;
      transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
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
      border-radius: 0.55rem;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.3);
      box-shadow: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
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
      background: rgba(248, 250, 252, 0.88);
      border-radius: 0.75rem;
      padding: 0.6rem 0.6rem 0.6rem;
      border: 1px solid rgba(148, 163, 184, 0.25);
      box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.2);
    }
    html.dark .content-wrapper {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.6));
      border-color: var(--surface-border-dark);
      box-shadow: 0 35px 80px -45px rgba(30, 64, 175, 0.45);
    }

    .current-conditions-section {
      position: relative;
      overflow: hidden;
      border-radius: 1rem;
      padding: clamp(2rem, 3vw, 2.8rem);
      background: #0f172a;
      border: 1px solid rgba(148, 163, 184, 0.4);
      box-shadow: 0 26px 60px -42px rgba(15, 23, 42, 0.7);
      color: #e2e8f0;
    }
    .current-conditions-section::before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        linear-gradient(120deg, rgba(59, 130, 246, 0.35), rgba(15, 23, 42, 0.1)),
        radial-gradient(circle at 85% 15%, rgba(56, 189, 248, 0.35), transparent 55%);
      opacity: 0.9;
      pointer-events: none;
    }
    .current-conditions-section::after { display: none; }
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
      color: rgba(226, 232, 240, 0.78);
      max-width: 38rem;

    }
    .current-conditions-card .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 1.2rem;
      border-radius: 9999px;

      background: rgba(15, 23, 42, 0.5);
      border: 1px solid rgba(148, 163, 184, 0.4);
      color: rgba(226, 232, 240, 0.85);

      font-size: 0.68rem;
      letter-spacing: 0.28em;
      text-transform: uppercase;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(18px);
    }
    .current-conditions-card .status-pill i {
      font-size: 0.95rem;
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
      margin-top: clamp(1.6rem, 3vw, 2.4rem);
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
      .content-wrapper { padding: 0.9rem 0.75rem 1.2rem; border-radius: 0.75rem; }
      .current-conditions-section { padding: 1.6rem; }
    }
    .metric-card {
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 0.65rem;
      padding: 1.1rem;
      border-radius: 0.75rem;
      text-decoration: none;
      color: #1f2937;
      overflow: hidden;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.3);
      box-shadow: none;
      transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }
    .metric-card:focus-visible {
      outline: 3px solid rgba(224, 242, 254, 0.75);
      outline-offset: 4px;
    }
    html.dark .metric-card:focus-visible { outline-color: rgba(191, 219, 254, 0.85); }
    .metric-card::before { display: none; }
    .metric-card::after { display: none; }
    .metric-card:hover {
      transform: translateY(-3px);
      border-color: rgba(59, 130, 246, 0.35);
      background: #f8fafc;
    }
    .metric-card:hover::before {
      opacity: 0.8;
      transform: translate3d(0, -6px, 0) scale(1.04);
    }
    html.dark .metric-card {
      background:
        linear-gradient(155deg, rgba(var(--accent-soft), 0.14), rgba(15, 23, 42, 0.9)),
        linear-gradient(170deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.92));
      border-color: rgba(148, 163, 184, 0.22);
      color: rgba(226, 232, 240, 0.95);
    }
    html.dark .metric-card::after {
      border-color: rgba(148, 163, 184, 0.22);
    }
    .metric-card .metric-label {
      font-size: 0.65rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      font-weight: 600;
      color: #475569;
    }
    html.dark .metric-card .metric-label {
      color: rgba(148, 163, 184, 0.85);
    }
    .metric-card .metric-value {
      font-size: 1.7rem;
      font-weight: 700;
      line-height: 1.1;
      display: flex;
      align-items: baseline;
      gap: 0.3rem;
      color: #111827;
      text-shadow: none;
    }
    html.dark .metric-card .metric-value {
      color: rgba(241, 245, 249, 0.98);
      text-shadow: none;
    }
    .metric-card .metric-value .stat-unit {
      font-size: 0.82rem;
      font-weight: 500;
      opacity: 0.85;
    }
    .metric-card .metric-meta {
      font-size: 0.72rem;
      font-weight: 500;
      color: #334155;
    }
    html.dark .metric-card .metric-meta {
      color: #94a3b8;
    }
    .metric-card i {
      font-size: 1.45rem;
      color: #0f172a;
    }
    html.dark .metric-card i {
      color: rgba(241, 245, 249, 0.92);
    }
    .dashboard-shell {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }
    .dashboard-hero {
      position: relative;
      padding: clamp(1.6rem, 3vw, 2.4rem);
      border-radius: 0.95rem;
      border: 1px solid rgba(148, 163, 184, 0.35);
      background:
        linear-gradient(120deg, rgba(15, 23, 42, 0.95), rgba(30, 58, 138, 0.95)),
        linear-gradient(90deg, rgba(59, 130, 246, 0.2), rgba(14, 165, 233, 0.1));
      box-shadow: 0 24px 60px -42px rgba(15, 23, 42, 0.7);
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
      inset: 0;
      background:
        radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.35), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(56, 189, 248, 0.3), transparent 55%),
        linear-gradient(to bottom, rgba(15, 23, 42, 0.4), transparent 60%);
      opacity: 0.9;
      pointer-events: none;
      mix-blend-mode: normal;
    }
    html.dark .dashboard-hero {
      border-color: rgba(148, 163, 184, 0.35);
      background:
        linear-gradient(to right, rgba(37, 99, 235, 0.32), rgba(2, 132, 199, 0.2)),
        linear-gradient(to right, rgba(15, 23, 42, 0.78), rgba(2, 6, 23, 0.68)),
        var(--hero-gradient, linear-gradient(to right, #1e293b 0%, #334155 35%, #3b82f6 100%));
      box-shadow: 0 44px 120px -62px rgba(2, 6, 23, 0.85);
    }
    html.dark .dashboard-hero::before {
      opacity: 0.78;
      background:
        radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.35), transparent 65%),
        radial-gradient(circle at 78% 18%, rgba(14, 165, 233, 0.3), transparent 70%),
        linear-gradient(to bottom, rgba(37, 99, 235, 0.28), rgba(15, 23, 42, 0));
    }
    .hero-grid {
      position: relative;
      display: grid;
      gap: 1.5rem;
      grid-template-columns: minmax(0, 1fr);
      align-items: start;
    }
    @media (min-width: 768px) {
      .hero-grid {
        grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
        align-items: stretch;
      }
    }
    @media (min-width: 1024px) {
      .hero-grid {
        grid-template-columns: minmax(0, 1fr) minmax(520px, 1fr);
      }
      .hero-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    .hero-copy {
      display: flex;
      flex-direction: column;
      gap: 0.9rem;
      max-width: 700px;
    }
    .hero-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.85rem;
      border-radius: 999px;
      font-size: 0.65rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      background: rgba(15, 23, 42, 0.6);
      color: rgba(226, 232, 240, 0.9);
      border: 1px solid rgba(148, 163, 184, 0.4);
    }
    .hero-copy h1 {
      font-size: clamp(1.9rem, 4.5vw, 2.6rem);
      letter-spacing: 0.015em;
    }
    .hero-copy p {
      max-width: 520px;
      font-size: 0.92rem;
      line-height: 1.6;
      color: var(--hero-ink-soft);
    }
    .status-card-hero {
      margin-top: 0.35rem;
      padding: 1rem 1.25rem;
      border-radius: 0.75rem;
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: 0 18px 48px -38px rgba(15, 23, 42, 0.5);
      color: rgba(226, 232, 240, 0.92);
    }
    .status-card-hero .status-label {
      color: var(--hero-ink-muted);
    }
    .status-card-hero .status-chip {
      background: var(--hero-chip-bg);
      color: var(--hero-ink-strong);
    }
    .hero-stats-grid {
      min-width: 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 0.75rem;
      align-items: start;
      align-content: start;
      justify-items: stretch;
      width: 100%;
    }
    .hero-stats-grid > .hero-quick-stats:only-child {
      align-self: start;
      width: 100%;
      grid-column: 1 / -1;
    }
    .hero-stat {
      position: relative;
      padding: 0.95rem 1.15rem;
      border-radius: 0.7rem;
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: 0 18px 50px -40px rgba(15, 23, 42, 0.5);
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    .hero-stat .stat-label {
      font-size: 0.62rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--hero-ink-muted);
    }
    .hero-stat .stat-value {
      font-size: 1.6rem;
      font-weight: 700;
      display: flex;
      align-items: baseline;
      gap: 0.35rem;
      color: #f8fafc;
      text-shadow: 0 12px 32px rgba(15, 23, 42, 0.45);
    }
    .hero-stat .stat-value .stat-unit {
      font-size: 0.8rem;
      font-weight: 500;
      opacity: 0.85;
      color: rgba(241, 245, 249, 0.85);
    }
    .hero-stat .stat-meta {
      font-size: 0.75rem;
      color: var(--hero-ink-muted);
    }
    .hero-stats-grid .insight-card {
      height: 100%;
    }
    .hero-temp-gauge .temp-gauge {
      margin-top: 0.75rem;
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
    }
    .hero-temp-gauge .temp-gauge-track {
      position: relative;
      height: 0.75rem;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.45);
      border: 1px solid rgba(148, 163, 184, 0.4);
      overflow: visible;
    }
    .hero-temp-gauge .temp-gauge-range {
      position: absolute;
      inset: 0;
      border-radius: inherit;
      background: linear-gradient(90deg, rgba(56, 189, 248, 0.85), rgba(249, 115, 22, 0.85));
    }
    .hero-temp-gauge .temp-gauge-marker {
      position: absolute;
      top: 50%;
      transform: translate(-50%, -50%);
      transition: left 0.4s ease;
    }
    .hero-temp-gauge .temp-gauge-marker.is-hidden {
      opacity: 0;
      pointer-events: none;
    }
    .hero-temp-gauge .temp-gauge-value {
      display: inline-flex;
      align-items: baseline;
      gap: 0.25rem;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.85);
      border: 1px solid rgba(148, 163, 184, 0.45);
      font-size: 0.7rem;
      font-weight: 600;
      color: rgba(248, 250, 252, 0.95);
      transform: translateY(-1.4rem);
      box-shadow: 0 12px 30px -18px rgba(15, 23, 42, 0.6);
      white-space: nowrap;
    }
    .hero-temp-gauge .temp-gauge-labels {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      font-size: 0.75rem;
      color: #1f2937;
    }
    .hero-temp-gauge .temp-gauge-label {
      display: inline-flex;
      align-items: baseline;
      gap: 0.3rem;
    }
    .hero-temp-gauge .temp-gauge-caption {
      font-size: 0.6rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #475569;
    }
    .hero-temp-gauge .temp-gauge-meta {
      font-size: 0.72rem;
      color: #475569;
    }
    .dashboard-hero [data-stat],
    .dashboard-hero .stat-reading,
    .dashboard-hero .stat-value {
      color: #f8fafc;
      text-shadow: 0 12px 32px rgba(15, 23, 42, 0.55);
    }
    .dashboard-hero .stat-reading .stat-unit,
    .dashboard-hero .stat-unit,
    .dashboard-hero .metric-meta,
    .dashboard-hero .stat-meta,
    .dashboard-hero .label {
      color: rgba(241, 245, 249, 0.82);
    }
    .dashboard-hero .temp-gauge-value,
    .dashboard-hero .temp-gauge-value [data-stat],
    .dashboard-hero .temp-gauge-value .stat-unit {
      color: rgba(248, 250, 252, 0.98);
      text-shadow: 0 12px 32px rgba(15, 23, 42, 0.55);
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
      color: inherit;
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
      gap: 1rem;
    }
    .section-header h2 {
      font-size: 1.1rem;
      letter-spacing: 0.05em;
    }
    .section-header p {
      max-width: 520px;
      color: rgba(15, 23, 42, 0.68);
      font-size: 0.88rem;
      line-height: 1.6;
    }
    html.dark .section-header p { color: rgba(226, 232, 240, 0.7); }
    .section-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      font-size: 0.65rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      background: #ffffff;
      border: 1px solid rgba(148, 163, 184, 0.3);
      color: rgba(15, 23, 42, 0.78);
    }
    html.dark .section-chip {
      background: rgba(15, 23, 42, 0.65);
      border-color: rgba(148, 163, 184, 0.32);
      color: rgba(226, 232, 240, 0.75);
    }
    .panels-grid {
      display: grid;
      gap: 1rem;
    }
    @media (min-width: 1280px) {
      .panels-grid {
        grid-template-columns: 2fr 1fr;
        align-items: stretch;
      }
    }
    .insight-card {
      border-radius: 0.85rem;
      border: 1px solid rgba(148, 163, 184, 0.28);
      background: #ffffff;
      box-shadow: 0 16px 40px -30px rgba(15, 23, 42, 0.2);
      padding: 1.1rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    html.dark .insight-card {
      background: rgba(15, 23, 42, 0.72);
      border-color: rgba(148, 163, 184, 0.32);
      box-shadow: 0 40px 96px -54px rgba(2, 6, 23, 0.85);
    }
    html.dark .hero-temp-gauge .temp-gauge-meta {
      color: rgba(226, 232, 240, 0.78);
    }
    html.dark .hero-temp-gauge .temp-gauge-labels {
      color: rgba(241, 245, 249, 0.85);
    }
    html.dark .hero-temp-gauge .temp-gauge-caption {
      color: rgba(241, 245, 249, 0.65);
    }
    .insight-list {
      display: grid;
      gap: 0.6rem;
    }
    .insight-list li {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      font-size: 0.85rem;
      color: #6b7280;
    }
    html.dark .insight-list li { color: #6b7280; }
    .insight-list .label {
      text-transform: uppercase;
      letter-spacing: 0.16em;
      font-size: 0.62rem;
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
      background: #ffffff;
      border-radius: 0.9rem;
      border: 1px solid rgba(148, 163, 184, 0.28);
      box-shadow: 0 18px 50px -36px rgba(15, 23, 42, 0.2);
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
      padding: 1.1rem 1.4rem;
      border-bottom: 1px solid rgba(148, 163, 184, 0.28);
      background: #f1f5f9;
    }
    html.dark .glass-panel .panel-header {
      border-bottom-color: rgba(71, 85, 105, 0.35);
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.25), transparent 70%);
    }
    .glass-panel .panel-title {
      font-size: 0.98rem;
      font-weight: 700;
      color: #1e293b;
      letter-spacing: 0.04em;
    }
    html.dark .glass-panel .panel-title { color: #e2e8f0; }
    .glass-panel .panel-body { padding: 1.2rem; }
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
      border-radius: 0.75rem;
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
    /* Scientific density overrides */
    :root {
      --science-surface: #f4f6f8;
      --science-surface-alt: #ffffff;
      --science-surface-muted: #eef1f4;
      --science-border: #d1d5db;
      --science-border-strong: #9ca3af;
      --science-ink: #111827;
      --science-ink-muted: #4b5563;
      --science-grid: rgba(15, 23, 42, 0.08);
      --science-accent: #1d4ed8;
      --science-accent-soft: rgba(37, 99, 235, 0.12);
    }
    body.theme-mist {
      background: var(--science-surface);
      color: var(--science-ink);
      font-size: 13px;
      line-height: 1.45;
    }
    body.theme-mist::before {
      background:
        linear-gradient(to right, var(--science-grid) 1px, transparent 1px),
        linear-gradient(to bottom, var(--science-grid) 1px, transparent 1px);
      background-size: 32px 32px;
      opacity: 0.4;
      mix-blend-mode: normal;
    }
    body.theme-mist::after { display: none; }
    html.dark body.theme-mist {
      background: #0b0f14;
      color: #e5e7eb;
    }
    html.dark body.theme-mist::before {
      background:
        linear-gradient(to right, rgba(148, 163, 184, 0.2) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(148, 163, 184, 0.2) 1px, transparent 1px);
      opacity: 0.25;
    }
    #sidebar-toggle {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      box-shadow: none;
      background: var(--science-surface-alt);
    }
    #sidebar-toggle:hover {
      transform: none;
      box-shadow: none;
      background: var(--science-surface-muted);
    }
    #sidebar {
      background: var(--science-surface-alt);
      border: 1px solid var(--science-border);
      border-radius: 0.2rem;
      box-shadow: none;
    }
    html.dark #sidebar {
      background: #111827;
      border-color: rgba(148, 163, 184, 0.4);
      box-shadow: none;
    }
    #navname {
      border-radius: 0.2rem;
      background: var(--science-surface-alt);
      border: 1px solid var(--science-border);
      padding: 0.35rem 0.6rem;
    }
    html.dark #navname {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    #navname .brand-icon { display: none; }
    #navname .brand-title {
      font-size: 0.9rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    #navname .brand-subtitle {
      font-size: 0.55rem;
      letter-spacing: 0.3em;
    }
    #sidebar nav {
      gap: 0.4rem;
    }
    #sidebar nav .nav-tile {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      padding: 0.4rem 0.6rem;
      font-size: 0.75rem;
      box-shadow: none;
    }
    #sidebar nav .nav-tile:hover {
      transform: none;
      box-shadow: none;
      border-color: var(--science-border-strong);
      background: var(--science-surface-muted);
    }
    html.dark #sidebar nav .nav-tile {
      background: #111827;
      border-color: rgba(148, 163, 184, 0.4);
    }
    #sidebar nav .nav-icon {
      width: 1.6rem;
      height: 1.6rem;
      border-radius: 0.2rem;
      background: var(--science-accent-soft);
      color: var(--science-accent);
    }
    html.dark #sidebar nav .nav-icon {
      background: rgba(148, 163, 184, 0.25);
      color: #e5e7eb;
    }
    #sidebar nav .chevron {
      width: 1.6rem;
      height: 1.6rem;
      border-radius: 0.2rem;
      background: var(--science-accent-soft);
      color: var(--science-accent);
    }
    .submenu {
      margin-left: 0.2rem;
      padding-left: 0.4rem;
      border-left: 1px solid var(--science-border);
    }
    #theme-select {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      font-size: 0.75rem;
    }
    html.dark #theme-select {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    .content-wrapper {
      background: var(--science-surface-alt);
      border-radius: 0.2rem;
      padding: 0.75rem;
      border: 1px solid var(--science-border);
      box-shadow: none;
    }
    html.dark .content-wrapper {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
      box-shadow: none;
    }
    .dashboard-shell {
      gap: 1.25rem;
    }
    .dashboard-shell > * + * {
      margin-top: 1.25rem !important;
    }
    .dashboard-hero {
      padding: 1rem;
      border-radius: 0.25rem;
      border: 1px solid var(--science-border);
      background:
        linear-gradient(120deg, rgba(37, 99, 235, 0.08), rgba(125, 211, 252, 0.12)),
        var(--science-surface-alt);
      box-shadow: none;
      color: var(--science-ink);
    }
    .dashboard-hero::before { display: none; }
    html.dark .dashboard-hero {
      background: #111827;
      border-color: rgba(148, 163, 184, 0.4);
      color: #e5e7eb;
      box-shadow: none;
    }
    .hero-chip {
      border-radius: 0.2rem;
      padding: 0.2rem 0.5rem;
      letter-spacing: 0.2em;
      background: var(--science-surface-muted);
      border: 1px solid var(--science-border);
      color: var(--science-ink);
    }
    .hero-copy h1 {
      font-size: 1.4rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .hero-copy p {
      font-size: 0.78rem;
      color: var(--science-ink-muted);
    }
    .status-card,
    .status-card-hero {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      box-shadow: none;
      padding: 0.5rem 0.75rem;
    }
    html.dark .status-card,
    html.dark .status-card-hero {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    .status-card .status-chip {
      border-radius: 0.2rem;
      background: var(--science-surface-muted);
      color: var(--science-ink);
      font-size: 0.6rem;
      padding: 0.2rem 0.45rem;
    }
    .hero-stats-grid,
    .metric-grid {
      gap: 0.75rem !important;
    }
    .hero-stat,
    .insight-card {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      box-shadow: none;
      padding: 0.6rem 0.75rem;
    }
    html.dark .hero-stat,
    html.dark .insight-card {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    .section-header {
      border-bottom: 1px solid var(--science-border);
      padding-bottom: 0.35rem;
      align-items: flex-end;
    }
    .section-header h2 {
      font-size: 1rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }
    .section-header p {
      font-size: 0.75rem;
      color: var(--science-ink-muted);
    }
    .section-chip {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-muted);
      color: var(--science-ink);
      padding: 0.2rem 0.45rem;
      font-size: 0.6rem;
    }
    .metric-card {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      box-shadow: none;
      padding: 0.6rem 0.75rem;
      gap: 0.4rem;
    }
    .metric-card:hover {
      transform: none;
      border-color: var(--science-accent);
      background: var(--science-surface-muted);
    }
    html.dark .metric-card {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    .metric-card .metric-label {
      font-size: 0.6rem;
      letter-spacing: 0.24em;
    }
    .metric-card .metric-value,
    .stat-reading,
    .hero-stat .stat-value,
    .insight-list [data-stat] {
      font-feature-settings: "tnum" 1, "lnum" 1;
      font-variant-numeric: tabular-nums lining-nums;
      letter-spacing: 0.02em;
    }
    .metric-card .metric-value {
      font-size: 1.2rem;
    }
    .metric-card .metric-meta,
    .insight-list .label,
    .hero-stat .stat-meta {
      font-size: 0.7rem;
      color: var(--science-ink-muted);
    }
    .insight-list {
      gap: 0.4rem;
    }
    .insight-list li {
      padding: 0.25rem 0;
    }
    .chart-frame {
      border: 1px solid var(--science-border);
      background: var(--science-surface-alt);
      border-radius: 0.2rem;
      padding: 0.5rem;
    }
    html.dark .chart-frame {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    table.min-w-full {
      border-radius: 0.2rem;
      box-shadow: none;
      font-size: 0.75rem;
      background: var(--science-surface-alt);
      border: 1px solid var(--science-border);
    }
    table.min-w-full thead {
      background: var(--science-surface-muted);
    }
    table.min-w-full tbody tr:hover {
      background: rgba(15, 23, 42, 0.04);
    }
    html.dark table.min-w-full {
      background: #0f172a;
      border-color: rgba(148, 163, 184, 0.4);
    }
    html.dark table.min-w-full thead {
      background: rgba(148, 163, 184, 0.18);
    }
    .content-wrapper .bg-white,
    .content-wrapper .bg-gray-800 {
      border-radius: 0.2rem;
      border: 1px solid var(--science-border);
      box-shadow: none;
    }
    .content-wrapper .shadow {
      box-shadow: none !important;
    }
    .content-wrapper .rounded {
      border-radius: 0.2rem !important;
    }
    .content-wrapper .p-4 {
      padding: 0.75rem !important;
    }
    .content-wrapper .text-2xl {
      font-size: 1.1rem !important;
    }
    .content-wrapper .text-xl {
      font-size: 0.95rem !important;
    }
    .content-wrapper .text-sm {
      font-size: 0.75rem !important;
    }
    .content-wrapper select,
    .content-wrapper input,
    .content-wrapper button,
    .content-wrapper .btn {
      border-radius: 0.2rem !important;
      font-size: 0.75rem !important;
    }
    .status-card-hero {
      color: var(--science-ink);
    }
    .status-card-hero .status-label {
      color: var(--science-ink-muted);
    }
    .hero-stat .stat-value {
      color: var(--science-ink);
      text-shadow: none;
    }
    .hero-stat .stat-unit,
    .hero-stat .stat-meta {
      color: var(--science-ink-muted);
    }
    .dashboard-hero [data-stat],
    .dashboard-hero .stat-reading,
    .dashboard-hero .stat-value {
      color: var(--science-ink);
      text-shadow: none;
    }
    .dashboard-hero .stat-reading .stat-unit,
    .dashboard-hero .stat-unit,
    .dashboard-hero .metric-meta,
    .dashboard-hero .stat-meta,
    .dashboard-hero .label {
      color: var(--science-ink-muted);
    }

    /* Dashboard visual system: dense, calm and operational. */
    :root {
      --dashboard-bg: #edf1f5;
      --dashboard-panel: #ffffff;
      --dashboard-panel-muted: #f6f8fa;
      --dashboard-border: #d7dee7;
      --dashboard-ink: #152033;
      --dashboard-muted: #667085;
      --dashboard-navy: #10233f;
      --dashboard-blue: #2563eb;
      --dashboard-cyan: #0891b2;
      --dashboard-shadow: 0 8px 24px rgba(16, 35, 63, 0.08);
    }
    body.theme-mist {
      background: var(--dashboard-bg);
      color: var(--dashboard-ink);
      font-size: 14px;
    }
    body.theme-mist::before,
    body.theme-mist::after { display: none; }
    #sidebar {
      width: 17.5rem !important;
      background: var(--dashboard-navy);
      color: #e8eef7;
      border: 0;
      border-radius: 0;
      box-shadow: 8px 0 30px rgba(16, 35, 63, 0.12);
    }
    #navname,
    html.dark #navname {
      background: transparent;
      border: 0;
      border-bottom: 1px solid rgba(255,255,255,.12);
      border-radius: 0;
      padding: .5rem .25rem 1rem;
    }
    #navname .brand-title,
    html.dark #navname .brand-title {
      color: #fff;
      -webkit-text-fill-color: currentColor;
      font-size: .92rem;
      letter-spacing: .08em;
    }
    #navname .brand-subtitle,
    html.dark #navname .brand-subtitle { color: #9fb0c7; }
    #navname .brand-mark {
      display: none;
      place-items: center;
      width: 2.35rem;
      height: 2.35rem;
      border-radius: .65rem;
      color: #fff;
      background: linear-gradient(145deg, #2563eb, #0891b2);
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .08em;
    }
    #sidebar nav .nav-tile,
    html.dark #sidebar nav .nav-tile {
      color: #dce6f2;
      background: transparent;
      border-color: transparent;
      border-radius: .5rem;
      padding: .52rem .6rem;
    }
    #sidebar nav .nav-tile:hover,
    html.dark #sidebar nav .nav-tile:hover {
      color: #fff;
      background: rgba(255,255,255,.08);
      border-color: rgba(255,255,255,.08);
    }
    #sidebar nav .nav-tile[aria-current="page"] {
      color: #fff;
      background: rgba(74,144,226,.2);
      border-color: rgba(141,199,255,.22);
      box-shadow: inset 3px 0 0 #60a5fa;
    }
    #sidebar nav .nav-icon,
    html.dark #sidebar nav .nav-icon,
    #sidebar nav .chevron {
      color: #8dc7ff;
      background: rgba(74, 144, 226, .15);
      border-radius: .4rem;
    }
    #sidebar nav .nav-group-toggle .nav-subtitle,
    html.dark #sidebar nav .nav-group-toggle .nav-subtitle { color: #8397b1; }
    #sidebar nav .submenu { border-left-color: rgba(141,199,255,.25); }
    #sidebar nav .submenu .nav-link,
    html.dark #sidebar nav .submenu .nav-link { background: rgba(255,255,255,.025); }
    #theme-select {
      background: #173052;
      color: #eef5ff;
      border-color: rgba(255,255,255,.15);
    }
    .content-wrapper,
    html.dark .content-wrapper {
      background: transparent;
      border: 0;
      border-radius: 0;
      padding: clamp(.85rem, 1.6vw, 1.5rem);
      box-shadow: none;
    }
    .dashboard-shell { gap: 1rem; }
    .dashboard-shell > * + * { margin-top: 0 !important; }
    .dashboard-hero {
      order: 1;
      padding: 1.2rem;
      border-radius: .85rem;
      border: 1px solid #c8d3e1;
      background: linear-gradient(110deg, #f9fbfd 0%, #eef5fc 58%, #e3f2f7 100%);
      box-shadow: var(--dashboard-shadow);
    }
    .hero-grid { gap: 1rem; }
    .hero-copy { gap: .65rem; }
    .hero-chip {
      width: fit-content;
      color: #1d4f7a;
      background: #dcecf8;
      border-color: #bfd8ea;
      border-radius: 999px;
      font-weight: 700;
    }
    .hero-copy h1 {
      color: var(--dashboard-ink);
      font-size: clamp(1.6rem, 3vw, 2.25rem);
      letter-spacing: -.025em;
      text-transform: none;
    }
    .hero-copy p { color: var(--dashboard-muted); max-width: 44rem; font-size: .82rem; }
    .status-card,
    .status-card-hero,
    html.dark .status-card,
    html.dark .status-card-hero {
      width: fit-content;
      min-width: 12rem;
      background: rgba(255,255,255,.78);
      border-color: var(--dashboard-border);
      border-radius: .55rem;
      color: var(--dashboard-ink);
      padding: .5rem .7rem;
    }
    .hero-stats-grid { gap: .65rem; }
    .hero-stat,
    .insight-card,
    html.dark .hero-stat,
    html.dark .insight-card {
      background: rgba(255,255,255,.9);
      border: 1px solid var(--dashboard-border);
      border-radius: .65rem;
      box-shadow: none;
      padding: .8rem;
    }
    .insight-list { gap: .28rem; }
    .insight-list li { padding: .2rem 0; border-bottom: 1px solid #edf1f5; }
    .insight-list li:last-child { border-bottom: 0; }
    .dashboard-hero [data-stat],
    .dashboard-hero .stat-reading,
    .dashboard-hero .stat-value { color: var(--dashboard-ink); text-shadow: none; }
    .dashboard-hero .stat-reading .stat-unit,
    .dashboard-hero .stat-unit,
    .dashboard-hero .label { color: var(--dashboard-muted); }
    .metric-section { order: 2; }
    .panels-grid { order: 3; gap: 1rem; }
    .section-header {
      padding: .75rem .1rem .55rem;
      border-bottom: 1px solid var(--dashboard-border);
    }
    .section-header h2 { color: var(--dashboard-ink); font-size: 1rem; }
    .section-header p { color: var(--dashboard-muted); font-size: .75rem; }
    .section-chip { background: #e8f1fb; border-color: #c8dcef; color: #245b8d; border-radius: 999px; }
    .metric-grid {
      display: grid !important;
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: .65rem !important;
    }
    .metric-card,
    html.dark .metric-card {
      min-height: 7.2rem;
      padding: .75rem .8rem;
      border: 1px solid var(--dashboard-border);
      border-top: 3px solid rgb(var(--accent));
      border-radius: .65rem;
      background: var(--dashboard-panel);
      color: var(--dashboard-ink);
      box-shadow: 0 3px 12px rgba(16,35,63,.045);
    }
    .metric-card:hover {
      transform: translateY(-2px);
      background: #fff;
      border-color: rgb(var(--accent));
      box-shadow: 0 8px 20px rgba(16,35,63,.1);
    }
    .metric-card .metric-label { color: #58677c; letter-spacing: .14em; }
    .metric-card .metric-value,
    html.dark .metric-card .metric-value { color: var(--dashboard-ink); font-size: 1.55rem; }
    .metric-card .metric-meta,
    html.dark .metric-card .metric-meta { color: var(--dashboard-muted); line-height: 1.35; }
    .metric-card i,
    html.dark .metric-card i { color: rgb(var(--accent-strong)); opacity: .9; }
    .glass-panel,
    html.dark .glass-panel {
      background: var(--dashboard-panel);
      border: 1px solid var(--dashboard-border);
      border-radius: .75rem;
      box-shadow: var(--dashboard-shadow);
      overflow: hidden;
    }
    .panel-header {
      min-height: 3rem;
      padding: .7rem 1rem;
      background: var(--dashboard-panel-muted);
      border-bottom: 1px solid var(--dashboard-border);
    }
    .panel-body { padding: .85rem; }
    .panel-title { color: var(--dashboard-ink); font-size: .85rem; letter-spacing: .05em; text-transform: uppercase; }
    .btn-modern { border-radius: .4rem; background: var(--dashboard-navy); color: #fff; border: 0; }
    .dashboard-content { padding: .75rem !important; min-width: 0; }
    #container3 { min-height: 280px; height: 32vh; max-height: 360px; }
    .mobile-bottom-nav { display: none; }
    @media (min-width: 760px) {
      .metric-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
    }
    @media (min-width: 768px) {
      #sidebar {
        position: sticky !important;
        top: 0;
        align-self: flex-start;
        width: 4.65rem !important;
        min-width: 4.65rem;
        flex: none;
        height: 100vh;
        padding: 1rem .6rem;
        overflow-x: hidden;
        overflow-y: auto;
        clip-path: inset(0);
        contain: paint;
        isolation: isolate;
        transition: width .2s cubic-bezier(.22,.8,.3,1);
        z-index: 50;
      }
      #sidebar:hover,
      #sidebar:focus-within {
        width: 17.5rem !important;
        margin-right: -12.85rem;
        box-shadow: none;
      }
      #navname { justify-content: center; min-height: 3.7rem; }
      #navname .brand-mark { display: grid; }
      #navname .brand-copy,
      #sidebar .nav-text,
      #sidebar .nav-title,
      #sidebar .nav-subtitle,
      #sidebar .chevron,
      #sidebar > .px-4 {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        white-space: nowrap;
        transition: opacity .08s linear, visibility 0s linear .08s;
      }
      #sidebar:hover #navname { justify-content: flex-start; }
      #sidebar:hover #navname .brand-mark,
      #sidebar:focus-within #navname .brand-mark { display: none; }
      #sidebar:hover #navname .brand-copy,
      #sidebar:focus-within #navname .brand-copy,
      #sidebar:hover .nav-text,
      #sidebar:focus-within .nav-text,
      #sidebar:hover .nav-title,
      #sidebar:focus-within .nav-title,
      #sidebar:hover .nav-subtitle,
      #sidebar:focus-within .nav-subtitle,
      #sidebar:hover .chevron,
      #sidebar:focus-within .chevron,
      #sidebar:hover > .px-4,
      #sidebar:focus-within > .px-4 {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transition-delay: .06s, 0s;
      }
      #sidebar nav { padding: 0; }
      #sidebar nav .nav-tile { justify-content: center; min-height: 2.75rem; overflow: hidden; }
      #sidebar:hover nav .nav-tile,
      #sidebar:focus-within nav .nav-tile { justify-content: space-between; }
      #sidebar nav .nav-tile > .flex { gap: 0; }
      #sidebar:hover nav .nav-tile > .flex,
      #sidebar:focus-within nav .nav-tile > .flex { gap: .75rem; }
      #sidebar .submenu { display: none; }
      #sidebar:hover .submenu:not(.hidden),
      #sidebar:focus-within .submenu:not(.hidden) { display: grid; }
      #sidebar .chart-studio-wrap { display: none; }
      #sidebar:hover .chart-studio-wrap,
      #sidebar:focus-within .chart-studio-wrap { display: block; }
      .dashboard-content { padding: .8rem 1rem !important; }
      .dashboard-hero { padding: .8rem 1rem; }
      .hero-copy h1 { font-size: 1.55rem; }
      .hero-copy p { line-height: 1.35; }
      .metric-section .section-header { padding-top: .3rem; }
      .metric-card { min-height: 5.6rem; }
      .metric-card .metric-meta { display: none; }
    }
    @media (min-width: 1180px) {
      .metric-grid { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
      .panels-grid { grid-template-columns: minmax(0, 2.25fr) minmax(17rem, .75fr); }
    }
    @media (max-width: 767px) {
      body { padding-bottom: 4.75rem; }
      #sidebar {
        width: min(88vw, 19rem) !important;
        bottom: 4.35rem !important;
        height: auto;
        max-height: calc(100vh - 4.35rem);
        padding-bottom: 1rem;
      }
      .dashboard-hero { padding: .85rem; }
      .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
      .metric-card { min-height: 5.7rem; padding: .65rem; }
      .metric-card .metric-meta { display: none; }
      .metric-card .metric-value { font-size: 1.3rem; }
      .dashboard-content { padding: .55rem !important; }
      .mobile-bottom-nav {
        position: fixed;
        z-index: 55;
        left: .55rem;
        right: .55rem;
        bottom: .55rem;
        height: 3.75rem;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: stretch;
        padding: .3rem;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: .9rem;
        background: rgba(16,35,63,.97);
        box-shadow: 0 14px 35px rgba(16,35,63,.3);
        backdrop-filter: blur(16px);
      }
      .mobile-bottom-nav a,
      .mobile-bottom-nav button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .18rem;
        min-width: 0;
        color: #b9c9dc;
        border: 0;
        border-radius: .6rem;
        background: transparent;
        font: inherit;
        font-size: .61rem !important;
        font-weight: 600;
        letter-spacing: .03em;
      }
      .mobile-bottom-nav #sidebar-toggle {
        position: static;
        padding: 0;
        color: #b9c9dc;
        background: transparent;
        border: 0;
        box-shadow: none;
        transform: none;
      }
      .mobile-bottom-nav #sidebar-toggle:hover,
      .mobile-bottom-nav #sidebar-toggle:focus-visible {
        color: #fff;
        background: rgba(255,255,255,.09);
      }
      .mobile-bottom-nav a:hover,
      .mobile-bottom-nav button:hover,
      .mobile-bottom-nav a:focus-visible,
      .mobile-bottom-nav button:focus-visible {
        color: #fff;
        background: rgba(255,255,255,.09);
        outline: none;
      }
      .mobile-bottom-nav i { color: #8dc7ff; font-size: 1rem; }
      #container3 { height: 300px; min-height: 300px; }
    }
    @media (max-width: 390px) {
      .metric-grid { grid-template-columns: 1fr !important; }
    }

    /* Current-conditions instrument panel */
    .hero-command-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: .85rem;
    }
    .hero-command-header h1 {
      margin-top: .42rem;
      color: var(--dashboard-ink);
      font-size: clamp(1.25rem, 2.2vw, 1.8rem);
      letter-spacing: -.025em;
    }
    .hero-command-header p { margin-top: .15rem; color: var(--dashboard-muted); font-size: .76rem; }
    .hero-instruments {
      display: grid;
      grid-template-columns: minmax(10rem, .8fr) minmax(22rem, 1.8fr) minmax(14rem, 1fr);
      gap: .7rem;
    }
    .hero-instruments article {
      position: relative;
      min-width: 0;
      min-height: 8.5rem;
      padding: .85rem;
      border: 1px solid var(--dashboard-border);
      border-radius: .75rem;
      overflow: hidden;
    }
    .instrument-label {
      display: inline-flex;
      align-items: center;
      gap: .38rem;
      color: #607087;
      font-size: .6rem;
      font-weight: 700;
      letter-spacing: .13em;
      text-transform: uppercase;
    }
    .instrument-label i { color: #3280c7; }
    .current-instrument {
      color: #fff;
      background:
        radial-gradient(circle at 85% 15%, rgba(56,189,248,.34), transparent 42%),
        linear-gradient(145deg, #10233f, #153b62);
      border-color: #214d78 !important;
    }
    .current-instrument .instrument-label { color: #b8d5ec; }
    .current-instrument .instrument-label i { color: #79c9ff; }
    .current-reading { display: flex; align-items: flex-start; margin-top: .25rem; line-height: 1; }
    .current-reading > [data-stat] { color: #fff; font-size: clamp(2.65rem, 4.5vw, 4rem); font-weight: 750; letter-spacing: -.07em; }
    .current-unit { margin: .42rem 0 0 .32rem; color: #9fd8ff; font-size: 1rem; font-weight: 700; }
    .current-support { display: flex; flex-wrap: wrap; gap: .45rem 1rem; color: #b8c9dc; font-size: .68rem; }
    .current-support strong,
    .current-support [data-stat] { color: #fff; font-variant-numeric: tabular-nums; }
    .range-instrument { background: linear-gradient(155deg, #fff, #f4f8fc); }
    .instrument-heading { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .range-spread { color: #26364d; font-size: .72rem; font-weight: 750; font-variant-numeric: tabular-nums; }
    .temperature-spectrum { position: relative; height: 3.2rem; margin: .72rem .55rem .15rem; }
    .spectrum-track {
      position: absolute;
      left: 0;
      right: 0;
      top: 1.4rem;
      height: .72rem;
      border-radius: 999px;
      background: linear-gradient(90deg, #2f9bd1 0%, #5dc6c3 28%, #f2ca52 62%, #ed7c43 82%, #d94a48 100%);
      box-shadow: inset 0 1px 2px rgba(15,23,42,.18), 0 0 0 4px rgba(148,163,184,.1);
    }
    .spectrum-marker { position: absolute; top: .2rem; transform: translateX(-50%); transition: left .45s cubic-bezier(.2,.8,.2,1); }
    .spectrum-marker.is-hidden { opacity: 0; }
    .spectrum-pin { display: block; width: 3px; height: 2.3rem; margin: .52rem auto 0; border-radius: 3px; background: #14243b; box-shadow: 0 0 0 3px rgba(255,255,255,.85); }
    .spectrum-value { position: absolute; left: 50%; top: -.35rem; transform: translateX(-50%); padding: .18rem .42rem; border-radius: .38rem; background: #14243b; color: #fff; font-size: .65rem; font-weight: 800; white-space: nowrap; }
    .spectrum-value [data-stat] { color: #fff; }
    .spectrum-labels { display: grid; grid-template-columns: 1fr auto 1fr; align-items: end; gap: .5rem; color: #26364d; }
    .spectrum-labels > span { display: flex; flex-direction: column; }
    .spectrum-labels > span:last-child { text-align: right; align-items: flex-end; }
    .spectrum-labels small { color: #7b8798; font-size: .55rem; letter-spacing: .12em; text-transform: uppercase; }
    .spectrum-labels strong { font-size: .8rem; font-variant-numeric: tabular-nums; }
    .spectrum-midpoint { color: #8a95a5; font-size: .58rem; text-align: center; }
    .summary-instrument { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; background: #f8fafc; }
    .summary-cell { display: flex; flex-direction: column; justify-content: space-between; gap: .25rem; padding: .42rem .52rem; border-radius: .55rem; background: #fff; border: 1px solid #e3e9f0; }
    .summary-cell strong { color: #17263b; font-size: 1.15rem; font-variant-numeric: tabular-nums; }
    .summary-cell strong small { color: #748196; font-size: .62rem; font-weight: 600; }
    .summary-cell-wide { grid-column: 1 / -1; flex-direction: row; align-items: center; }
    .summary-direction { margin-left: auto; color: #748196; font-size: .62rem; }

    /* Focused graph launcher shown when the navigation rail expands. */
    .chart-studio-wrap { padding: .1rem .1rem 1rem !important; }
    .chart-studio { padding: .75rem; border: 1px solid rgba(141,199,255,.18); border-radius: .7rem; background: rgba(4,17,33,.34); }
    .chart-studio-header { display: flex; align-items: center; gap: .55rem; margin-bottom: .65rem; color: #fff; }
    .chart-studio-header > span:last-child { display: flex; flex-direction: column; }
    .chart-studio-header strong { font-size: .74rem; letter-spacing: .04em; }
    .chart-studio-header small { color: #8fa5bd; font-size: .58rem; }
    .chart-studio-icon { display: grid; place-items: center; width: 1.9rem; height: 1.9rem; border-radius: .48rem; color: #8dc7ff; background: rgba(74,144,226,.16); }
    .chart-studio form { display: grid; gap: .65rem; }
    .studio-field { display: grid; gap: .25rem; }
    .studio-field > span,
    .studio-range legend,
    .sidebar-theme-control label { color: #91a6bd; font-size: .56rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .chart-studio select,
    .sidebar-theme-control select {
      width: 100%;
      padding: .46rem .5rem;
      border: 1px solid rgba(141,199,255,.18);
      border-radius: .45rem;
      color: #eef6ff;
      background: #16304f;
      font-size: .68rem;
    }
    .studio-range-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: .24rem; margin-top: .28rem; }
    .studio-range-grid label { cursor: pointer; }
    .studio-range-grid input { position: absolute; opacity: 0; pointer-events: none; }
    .studio-range-grid span { display: grid; place-items: center; min-height: 1.65rem; border: 1px solid rgba(141,199,255,.14); border-radius: .38rem; color: #aebdd0; background: rgba(255,255,255,.035); font-size: .57rem; font-weight: 700; }
    .studio-range-grid input:checked + span { color: #fff; background: #2563eb; border-color: #4b83f3; }
    .studio-range-grid input:focus-visible + span { outline: 2px solid #8dc7ff; outline-offset: 1px; }
    .studio-footer { display: grid; grid-template-columns: 1fr auto; align-items: end; gap: .45rem; }
    .chart-studio button { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; height: 2rem; padding: 0 .65rem; border: 0; border-radius: .45rem; color: #fff; background: linear-gradient(135deg, #2563eb, #087f9b); font-size: .62rem; font-weight: 750; }
    .sidebar-theme-control { display: grid; gap: .3rem; padding: .6rem .5rem; }
    @media (max-width: 1050px) {
      .hero-instruments { grid-template-columns: .8fr 1.5fr; }
      .summary-instrument { grid-column: 1 / -1; grid-template-columns: repeat(3, 1fr); }
      .summary-cell-wide { grid-column: auto; }
    }
    @media (max-width: 720px) {
      .hero-command-header { align-items: center; }
      .hero-command-header p { display: none; }
      .hero-instruments { grid-template-columns: 1fr; }
      .hero-instruments article { min-height: auto; }
      .summary-instrument { grid-column: auto; }
    }

    /* Trend workspace */
    .trend-workspace { display: grid; gap: .8rem; min-width: 0; }
    .trend-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; padding: .2rem .15rem; }
    .trend-eyebrow { color: #2670ae; font-size: .6rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
    .trend-header h1 { margin-top: .2rem; color: var(--dashboard-ink); font-size: clamp(1.45rem, 2.5vw, 2rem); letter-spacing: -.03em; }
    .trend-header p { margin-top: .12rem; color: var(--dashboard-muted); font-size: .74rem; }
    .trend-header-actions { display: flex; align-items: center; gap: .5rem; }
    .trend-quality,
    .trend-icon-button { display: inline-flex; align-items: center; gap: .38rem; min-height: 2rem; padding: 0 .65rem; border: 1px solid var(--dashboard-border); border-radius: .5rem; color: #53637a; background: #fff; font-size: .64rem; font-weight: 700; }
    .trend-quality i { color: #22c55e; font-size: .42rem; }
    .trend-quality.is-stale i { color: #f59e0b; }
    .trend-icon-button:hover { color: #1d4ed8; border-color: #a9c5e4; }
    .trend-toolbar { display: grid; grid-template-columns: minmax(10rem, 1.25fr) minmax(24rem, 3fr) minmax(8rem, .9fr) minmax(9rem, 1fr) minmax(8.5rem, .85fr) auto; align-items: end; gap: .55rem; padding: .72rem; border: 1px solid var(--dashboard-border); border-radius: .72rem; background: var(--dashboard-panel); box-shadow: 0 4px 16px rgba(16,35,63,.05); }
    .trend-control { display: grid; gap: .25rem; min-width: 0; }
    .trend-control > span,
    .trend-segments legend { color: #748196; font-size: .55rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .trend-control select,
    .trend-control input { width: 100%; height: 2.18rem; padding: 0 .55rem; border: 1px solid #d7dee7; border-radius: .46rem; color: #25354a; background: #f8fafc; font-size: .7rem; }
    .trend-segments > div { display: grid; grid-template-columns: repeat(10, minmax(0,1fr)); gap: .2rem; }
    .trend-segments label { cursor: pointer; }
    .trend-segments input { position: absolute; opacity: 0; pointer-events: none; }
    .trend-segments span { display: grid; place-items: center; height: 2.18rem; border: 1px solid #d7dee7; border-radius: .42rem; color: #617187; background: #f8fafc; font-size: .58rem; font-weight: 800; }
    .trend-segments input:checked + span { color: #fff; background: #173f69; border-color: #173f69; }
    .trend-segments input:focus-visible + span { outline: 2px solid #60a5fa; outline-offset: 1px; }
    .trend-apply { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; height: 2.18rem; padding: 0 .8rem; border: 0; border-radius: .46rem; color: #fff; background: linear-gradient(135deg,#1d4ed8,#087f9b); font-size: .66rem; font-weight: 800; }
    .trend-summary { display: grid; grid-template-columns: repeat(6, minmax(0,1fr)); gap: .5rem; }
    .trend-summary > div { display: grid; grid-template-columns: 1fr auto; align-items: baseline; gap: .08rem .3rem; min-width: 0; padding: .62rem .72rem; border: 1px solid var(--dashboard-border); border-radius: .62rem; background: #fff; }
    .trend-summary span { grid-column: 1 / -1; color: #748196; font-size: .55rem; font-weight: 750; letter-spacing: .1em; text-transform: uppercase; }
    .trend-summary strong { color: #17263b; font-size: 1.1rem; font-variant-numeric: tabular-nums; }
    .trend-summary small { color: #7b8798; font-size: .58rem; }
    .trend-chart-panel { min-width: 0; border: 1px solid var(--dashboard-border); border-radius: .75rem; background: #fff; box-shadow: var(--dashboard-shadow); overflow: hidden; }
    .trend-chart-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 3.25rem; padding: .65rem .85rem; border-bottom: 1px solid var(--dashboard-border); background: #f7f9fb; }
    .trend-chart-heading h2 { color: #1d2d43; font-size: .82rem; }
    .trend-chart-heading p { margin-top: .12rem; color: #7a8798; font-size: .6rem; }
    .trend-date-nav { display: inline-flex; gap: .2rem; }
    .trend-date-nav button { display: grid; place-items: center; width: 2rem; height: 2rem; border: 1px solid #d4dce6; border-radius: .42rem; color: #53637a; background: #fff; }
    .trend-chart { width: 100%; height: clamp(360px, 58vh, 660px); padding: .15rem; color: #748196; }
    .garden-panel-body { display: grid; gap: .65rem; padding: .7rem !important; }
    .garden-view { position: relative; display: block; min-height: 12rem; border-radius: .6rem; overflow: hidden; background: #dbe4ec; }
    .garden-view::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(7,20,35,.08), transparent 45%, rgba(7,20,35,.55)); }
    .garden-view img { width: 100%; height: 100%; min-height: 12rem; object-fit: cover; transition: transform .35s ease; }
    .garden-view:hover img { transform: scale(1.025); }
    .garden-live-badge,
    .garden-open { position: absolute; z-index: 2; display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .5rem; border-radius: .42rem; color: #fff; background: rgba(10,26,45,.78); backdrop-filter: blur(8px); font-size: .57rem; font-weight: 750; }
    .garden-live-badge { left: .55rem; top: .55rem; }
    .garden-live-badge i { color: #4ade80; font-size: .4rem; }
    .garden-open { right: .55rem; bottom: .55rem; }
    .garden-conditions { display: grid; grid-template-columns: repeat(3,1fr); gap: .35rem; }
    .garden-conditions > span { display: flex; flex-direction: column; gap: .08rem; padding: .45rem .5rem; border: 1px solid var(--dashboard-border); border-radius: .48rem; background: #f8fafc; }
    .garden-conditions small { color: #7a8798; font-size: .5rem; letter-spacing: .08em; text-transform: uppercase; }
    .garden-conditions strong { color: #23344a; font-size: .72rem; font-variant-numeric: tabular-nums; }
    @media (max-width: 1180px) {
      .trend-toolbar { grid-template-columns: 1fr 2.5fr 1fr 1fr; }
      .trend-date, .trend-apply { grid-row: 2; }
      .trend-summary { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 760px) {
      .trend-header { align-items: flex-start; }
      .trend-header p { display: none; }
      .trend-quality span { display: none; }
      .trend-icon-button span { display: none; }
      .trend-toolbar { grid-template-columns: 1fr 1fr; }
      .trend-sensor, .trend-segments { grid-column: 1 / -1; }
      .trend-segments > div { grid-template-columns: repeat(5, 1fr); }
      .trend-date, .trend-apply { grid-row: auto; }
      .trend-summary { grid-template-columns: repeat(3, 1fr); gap: .35rem; }
      .trend-summary > div { padding: .5rem; }
      .trend-summary strong { font-size: .95rem; }
      .trend-chart { height: min(54vh, 470px); min-height: 340px; }
    }
    @media (max-width: 420px) {
      .trend-toolbar { grid-template-columns: 1fr; }
      .trend-sensor, .trend-segments { grid-column: auto; }
      .trend-summary { grid-template-columns: repeat(2, 1fr); }
    }

    /* Annual report workspaces */
    .annual-workspace { display: grid; gap: .8rem; min-width: 0; }
    .annual-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; }
    .annual-eyebrow { color: #2670ae; font-size: .58rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
    .annual-header h1 { margin-top: .2rem; color: var(--dashboard-ink); font-size: clamp(1.45rem,2.5vw,2rem); letter-spacing: -.03em; }
    .annual-header p { margin-top: .15rem; color: var(--dashboard-muted); font-size: .72rem; }
    .annual-switcher { display: inline-flex; gap: .25rem; padding: .25rem; border: 1px solid var(--dashboard-border); border-radius: .6rem; background: #fff; }
    .annual-switcher a { display: inline-flex; align-items: center; gap: .3rem; min-height: 2rem; padding: 0 .65rem; border-radius: .42rem; color: #617187; font-size: .62rem; font-weight: 750; }
    .annual-switcher a[aria-current="page"] { color: #fff; background: #173f69; }
    .annual-kpis { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .5rem; }
    .annual-kpi { position: relative; padding: .68rem .75rem; border: 1px solid var(--dashboard-border); border-radius: .62rem; background: #fff; overflow: hidden; }
    .annual-kpi::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 3px; background: var(--kpi-accent,#2563eb); }
    .annual-kpi span { display: block; color: #748196; font-size: .54rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .annual-kpi strong { display: inline-block; margin-top: .18rem; color: #17263b; font-size: 1.25rem; font-variant-numeric: tabular-nums; }
    .annual-kpi small { margin-left: .25rem; color: #7c899b; font-size: .58rem; }
    .annual-chart-panel,
    .annual-table-panel { min-width: 0; border: 1px solid var(--dashboard-border); border-radius: .72rem; background: #fff; box-shadow: 0 5px 18px rgba(16,35,63,.055); overflow: hidden; }
    .annual-panel-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 3rem; padding: .6rem .8rem; border-bottom: 1px solid var(--dashboard-border); background: #f7f9fb; }
    .annual-panel-header h2 { color: #1d2d43; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; }
    .annual-panel-header p { color: #7b8798; font-size: .58rem; }
    .annual-chart { width: 100%; height: clamp(310px,43vh,480px); }
    .annual-table-scroll { max-height: 47vh; overflow: auto; }
    .annual-table { width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0; color: #35445a; font-size: .65rem; font-variant-numeric: tabular-nums; }
    .annual-table th,
    .annual-table td { padding: .46rem .58rem; border-right: 1px solid #edf1f5; border-bottom: 1px solid #e5eaf0; text-align: right; white-space: nowrap; }
    .annual-table thead th { position: sticky; top: 0; z-index: 3; color: #53637a; background: #eef3f7; font-size: .55rem; letter-spacing: .06em; text-transform: uppercase; }
    .annual-table .month-cell { position: sticky; left: 0; z-index: 2; min-width: 7rem; text-align: left; color: #25354a; font-weight: 750; background: #fff; }
    .annual-table thead .month-cell { z-index: 4; background: #e8eef4; }
    .annual-table tbody tr:hover td { background: #f8fafc; }
    .annual-table tbody tr:hover .month-cell { background: #f2f6f9; }
    .annual-table .is-high { color: #c2413a; font-weight: 750; background: #fff7f5; }
    .annual-table .is-low { color: #2563a6; font-weight: 750; background: #f2f8fd; }
    .annual-table .total-row td { position: sticky; bottom: 0; z-index: 2; color: #17263b; font-weight: 800; background: #eaf0f5; }
    .annual-table .total-row .month-cell { z-index: 3; }
    @media (max-width: 760px) {
      .annual-header { align-items: flex-start; flex-direction: column; }
      .annual-switcher { width: 100%; }
      .annual-switcher a { flex: 1; justify-content: center; }
      .annual-kpis { grid-template-columns: repeat(2,1fr); }
      .annual-chart { height: 350px; }
      .annual-table-scroll { max-height: 54vh; }
    }
    /* Shared information workspace */
    .site-workspace { display: grid; gap: .75rem; min-width: 0; }
    .workspace-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; }
    .workspace-eyebrow { color: #2670ae; font-size: .58rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
    .workspace-header h1 { margin-top: .18rem; color: var(--dashboard-ink); font-size: clamp(1.4rem,2.4vw,1.95rem); letter-spacing: -.035em; }
    .workspace-header p { max-width: 46rem; margin-top: .12rem; color: var(--dashboard-muted); font-size: .7rem; }
    .workspace-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .42rem .65rem; border: 1px solid #cbd9e6; border-radius: 999px; color: #36536e; background: #f8fbfd; font-size: .6rem; font-weight: 750; white-space: nowrap; }
    .workspace-toolbar { display: flex; flex-wrap: wrap; align-items: end; gap: .55rem; padding: .65rem .7rem; border: 1px solid var(--dashboard-border); border-radius: .68rem; background: #f7f9fb; }
    .workspace-field { display: grid; gap: .2rem; min-width: 9rem; }
    .workspace-field label { color: #68778b; font-size: .53rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
    .workspace-field select, .workspace-field input { min-height: 2.15rem; padding: .35rem .55rem; border: 1px solid #cbd5df; border-radius: .45rem; color: #25364b; background: #fff; font-size: .68rem; }
    .workspace-action { min-height: 2.15rem; padding: 0 .85rem; border-radius: .45rem; color: #fff; background: #173f69; font-size: .64rem; font-weight: 800; }
    .workspace-kpis { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .45rem; }
    .workspace-kpi { padding: .58rem .68rem; border: 1px solid var(--dashboard-border); border-radius: .58rem; background: #fff; }
    .workspace-kpi span { display: block; color: #78869a; font-size: .52rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .workspace-kpi strong { display: inline-block; margin-top: .12rem; color: #17263b; font-size: 1.15rem; font-variant-numeric: tabular-nums; }
    .workspace-kpi small { margin-left: .2rem; color: #7b899a; font-size: .57rem; }
    .workspace-panel { min-width: 0; border: 1px solid var(--dashboard-border); border-radius: .7rem; background: #fff; box-shadow: 0 5px 18px rgba(16,35,63,.05); overflow: hidden; }
    .workspace-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .58rem .72rem; border-bottom: 1px solid var(--dashboard-border); background: #f7f9fb; }
    .workspace-panel-head h2 { color: #23344a; font-size: .72rem; font-weight: 800; letter-spacing: .055em; text-transform: uppercase; }
    .workspace-panel-head p { color: #7b8798; font-size: .57rem; }
    .workspace-chart { height: clamp(320px,48vh,520px); }
    .workspace-table-scroll { max-height: 48vh; overflow: auto; }
    .workspace-table { width: 100%; border-collapse: collapse; color: #35445a; font-size: .65rem; font-variant-numeric: tabular-nums; }
    .workspace-table th, .workspace-table td { padding: .46rem .58rem; border-bottom: 1px solid #e7ebf0; text-align: left; }
    .workspace-table th { position: sticky; top: 0; z-index: 2; color: #596980; background: #eef3f7; font-size: .54rem; letter-spacing: .07em; text-transform: uppercase; }
    .workspace-table tbody tr:hover { background: #f8fafc; }
    .workspace-empty { display: grid; place-items: center; min-height: 18rem; padding: 2rem; color: #758398; text-align: center; background: linear-gradient(145deg,#fff,#f5f8fa); }
    @media (max-width: 760px) {
      .workspace-header { align-items: flex-start; flex-direction: column; }
      .workspace-kpis { grid-template-columns: repeat(2,1fr); }
      .workspace-toolbar > * { flex: 1 1 9rem; }
      .workspace-chart { height: 360px; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: 0.01ms !important; animation-duration: 0.01ms !important; }
    }
  </style>
</head>
  <body class="theme-mist text-gray-900 dark:text-gray-100" data-weather="stale">
    <div class="flex min-h-screen">

      <aside id="sidebar" role="navigation" aria-label="Primary" class="text-gray-900 dark:text-gray-100 w-full md:w-[20.24rem] space-y-4 py-6 px-4 fixed inset-y-0 left-0 z-40 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out rounded-none md:rounded-3xl overflow-y-auto md:overflow-visible max-h-screen md:max-h-none">

      <a id="navname" class="px-4 text-lg font-semibold" href="/">
        <span class="brand-mark" aria-hidden="true">WX</span>
        <span class="brand-copy">
          <span class="brand-title">Wheathampstead Weather</span>
          <span class="brand-subtitle">Conditions in real time</span>
        </span>
      </a>
      <nav class="mt-5">
          <a class="nav-tile nav-link nav-direct" href="/">
            <span class="nav-icon"><i class="fas fa-gauge-high" aria-hidden="true"></i></span>
            <span class="nav-title">Dashboard <span class="sr-only">(current)</span></span>
          </a>
          <div>
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="explore-menu" aria-controls="explore-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                <span class="nav-text">
                  <span class="nav-title">Explore data</span>
                  <span class="nav-subtitle">Trends &amp; history</span>
                </span>
              </span>
              <span class="chevron" aria-hidden="true" data-chevron>
                <i class="fas fa-chevron-down"></i>
              </span>
            </button>
            <div id="explore-menu" class="submenu" aria-hidden="true">
              <a class="nav-tile nav-link" href="/dynamic-graph.php?WHAT=outTemp&amp;SCALE=day">
                <span class="nav-icon"><i class="fas fa-wave-square" aria-hidden="true"></i></span>
                <span class="nav-title">Live Trends</span>
              </a>
              <a class="nav-tile nav-link" href="/extremes.php">
                <span class="nav-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                <span class="nav-title">Extremes</span>
              </a>
              <a class="nav-tile nav-link" href="/historical.php">
                <span class="nav-icon"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></span>
                <span class="nav-title">Historical Explorer</span>
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
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="annual-menu" aria-controls="annual-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                <span class="nav-text"><span class="nav-title">Annual reports</span><span class="nav-subtitle">Year-on-year views</span></span>
              </span>
              <span class="chevron" aria-hidden="true" data-chevron><i class="fas fa-chevron-down"></i></span>
            </button>
            <div id="annual-menu" class="submenu" aria-hidden="true">
              <a class="nav-tile nav-link" href="/reporttempyeartotals.php"><span class="nav-icon"><i class="fas fa-temperature-half"></i></span><span class="nav-title">Temperature by year</span></a>
              <a class="nav-tile nav-link" href="/reportrainyeartotals.php"><span class="nav-icon"><i class="fas fa-cloud-rain"></i></span><span class="nav-title">Rain by year</span></a>
              <a class="nav-tile nav-link" href="/reportwindyeartotals.php"><span class="nav-icon"><i class="fas fa-wind"></i></span><span class="nav-title">Wind by year</span></a>
            </div>
          </div>
          <div>
            <button type="button" class="nav-tile nav-group-toggle" data-submenu-toggle="tools-menu" aria-controls="tools-menu" aria-expanded="false">
              <span class="flex items-center gap-3">
                <span class="nav-icon"><i class="fas fa-tools" aria-hidden="true"></i></span>
                <span class="nav-text">
                  <span class="nav-title">Tools &amp; views</span>
                  <span class="nav-subtitle">Utilities &amp; export</span>
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
              <a class="nav-tile nav-link" href="/astro">
                <span class="nav-icon"><i class="fas fa-star" aria-hidden="true"></i></span>
                <span class="nav-title">Astro</span>
              </a>
              <div class="sidebar-theme-control">
                <label for="theme-select">Appearance</label>
                <select id="theme-select">
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
                  <span class="nav-title">Connected systems</span>
                  <span class="nav-subtitle">Related dashboards</span>
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
        <div class="px-4 pb-6 chart-studio-wrap">
          <?php include('graph-selector.php'); ?>
        </div>
      </aside>
      <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 md:hidden" aria-hidden="true"></div>
      <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/" aria-label="Dashboard"><i class="fas fa-gauge-high" aria-hidden="true"></i><span>Dashboard</span></a>
        <a href="/dynamic-graph.php?WHAT=outTemp&SCALE=day" aria-label="Trends"><i class="fas fa-chart-line" aria-hidden="true"></i><span>Trends</span></a>
        <a href="/records.php" aria-label="Records"><i class="fas fa-table-list" aria-hidden="true"></i><span>Records</span></a>
        <button id="sidebar-toggle" type="button" aria-label="Open full menu" aria-controls="sidebar" aria-expanded="false"><i class="fas fa-th-large" aria-hidden="true"></i><span>Menu</span></button>
      </nav>

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

        sidebar.addEventListener('mouseleave', function() {
          if (breakpoint.matches && sidebar.contains(document.activeElement)) {
            document.activeElement.blur();
          }
        });

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
      (function () {
        var currentPath = window.location.pathname.replace(/\/$/, '') || '/';
        document.querySelectorAll('#sidebar .nav-tile').forEach(function (item) {
          var labelNode = item.querySelector('.nav-title');
          if (labelNode) item.setAttribute('title', labelNode.textContent.trim());
          if (item.tagName !== 'A') return;
          var itemPath = new URL(item.href, window.location.origin).pathname.replace(/\/$/, '') || '/';
          if (itemPath === currentPath) {
            item.setAttribute('aria-current', 'page');
            var submenu = item.closest('.submenu');
            if (submenu) {
              var group = document.querySelector('[aria-controls="' + submenu.id + '"]');
              if (group) group.setAttribute('aria-current', 'page');
            }
          }
        });
      })();
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
      <div class="flex-1 p-4 md:p-8 lg:p-10 dashboard-content">
        <div class="w-full content-wrapper">
