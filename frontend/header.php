<?php
require_once __DIR__ . '/../bootstrap.php';

date_default_timezone_set("Europe/London");
setlocale(LC_ALL, 'uk_UA.utf8');

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
  <script src="js/chart-theme.js" defer></script>
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
    }
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Roboto', sans-serif; font-weight: 700; }
    button, .highlight { font-family: 'Source Sans Pro', sans-serif; font-weight: 300; }
    body.theme-mist {
      min-height: 100vh;
      background: radial-gradient(circle at 12% 18%, rgba(56, 189, 248, 0.22), transparent 60%),
                  radial-gradient(circle at 85% 12%, rgba(236, 72, 153, 0.25), transparent 58%),
                  radial-gradient(circle at 50% 100%, rgba(16, 185, 129, 0.2), rgba(241, 245, 249, 0.65));
      color: #0f172a;
      position: relative;
      overflow-x: hidden;
    }
    body.theme-mist::before {
      content: "";
      position: fixed;
      inset: 0;
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.12), rgba(45, 212, 191, 0.05)),
                  radial-gradient(circle at 80% 0%, rgba(14, 116, 144, 0.18), transparent 55%),
                  linear-gradient(200deg, rgba(248, 250, 252, 0.8), rgba(255, 255, 255, 0.5));
      backdrop-filter: blur(40px);
      z-index: 0;
      pointer-events: none;
    }
    html.dark body.theme-mist {
      background: radial-gradient(circle at 20% 18%, rgba(56, 189, 248, 0.12), transparent 65%),
                  radial-gradient(circle at 85% 12%, rgba(236, 72, 153, 0.18), transparent 60%),
                  radial-gradient(circle at 50% 100%, rgba(14, 165, 233, 0.1), rgba(2, 6, 23, 0.94));
      color: #f8fafc;
    }
    html.dark body.theme-mist::before {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(45, 212, 191, 0.05)),
                  radial-gradient(circle at 85% 5%, rgba(14, 165, 233, 0.22), transparent 55%),
                  linear-gradient(200deg, rgba(2, 6, 23, 0.85), rgba(15, 23, 42, 0.75));
    }
    body.theme-mist > * { position: relative; z-index: 1; }
    #sidebar-toggle {
      background: rgba(255, 255, 255, 0.7);
      border: 1px solid var(--surface-border-light);
      backdrop-filter: blur(18px);
      box-shadow: var(--surface-shadow-light);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #sidebar-toggle:hover { transform: translateY(-2px); }
    html.dark #sidebar-toggle {
      background: rgba(15, 23, 42, 0.75);
      border-color: var(--surface-border-dark);
      box-shadow: var(--surface-shadow-dark);
    }
    #sidebar {
      background: linear-gradient(155deg, rgba(255, 255, 255, 0.82), rgba(226, 232, 240, 0.6));
      border: 1px solid var(--surface-border-light);
      border-radius: 1.5rem;
      box-shadow: var(--surface-shadow-light);
      backdrop-filter: blur(24px);
    }
    html.dark #sidebar {
      background: linear-gradient(155deg, rgba(15, 23, 42, 0.88), rgba(30, 41, 59, 0.65));
      border-color: var(--surface-border-dark);
      box-shadow: var(--surface-shadow-dark);
    }
    #navname span { font-weight: 600; letter-spacing: 0.02em; }
    #connect {
      border-radius: 0.75rem;
      padding: 0.5rem 0.75rem;
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.28);
      font-weight: 500;
    }
    #sidebar nav button,
    #sidebar nav a {
      border-radius: 0.85rem;
      transition: background 0.3s ease, color 0.3s ease, transform 0.3s ease;
      border: 1px solid transparent;
      font-weight: 500;
      letter-spacing: 0.01em;
      color: inherit;
    }
    #sidebar nav button span,
    #sidebar nav a {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
    }
    #sidebar nav button:hover,
    #sidebar nav a:hover {
      background: linear-gradient(90deg, rgba(59, 130, 246, 0.14), transparent 75%);
      transform: translateX(4px);
    }
    html.dark #sidebar nav button:hover,
    html.dark #sidebar nav a:hover {
      background: linear-gradient(90deg, rgba(96, 165, 250, 0.22), transparent 70%);
    }
    #sidebar nav .submenu {
      margin-left: 0.5rem;
      border-left: 2px solid rgba(59, 130, 246, 0.15);
      padding-left: 0.75rem;
    }
    html.dark #sidebar nav .submenu {
      border-left-color: rgba(148, 163, 184, 0.2);
    }
    .submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
    .submenu.open { max-height: 500px; }
    #theme-select {
      border-radius: 0.75rem;
      background: rgba(255, 255, 255, 0.6);
      border: 1px solid var(--surface-border-light);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
    }
    html.dark #theme-select {
      background: rgba(15, 23, 42, 0.7);
      border-color: var(--surface-border-dark);
      color: #f1f5f9;
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
    @media (max-width: 768px) {
      .content-wrapper { padding: 2rem 1.5rem; border-radius: 1.5rem; }
    }
    .metric-card {
      position: relative;
      display: block;

      border-radius: 1.65rem;
      padding: 1.75rem;

      --accent: 59 130 246;
      --accent-strong: 37 99 235;
      --accent-soft: 125 211 252;
      --accent-glow: 224 242 254;
      color: #0f172a;
      text-decoration: none;
      cursor: pointer;
      isolation: isolate;
      overflow: hidden;
      background:
        radial-gradient(circle at -10% -10%, rgba(var(--accent-strong), 0.94) 0%, rgba(var(--accent-strong), 0.15) 45%, transparent 65%),
        radial-gradient(circle at 85% -15%, rgba(var(--accent), 0.75) 0%, rgba(var(--accent), 0.12) 55%, transparent 75%),
        linear-gradient(135deg,
          rgba(var(--accent-strong), 0.9) 0%,
          rgba(var(--accent), 0.7) 42%,
          rgba(var(--accent-glow), 0.95) 100%);
      border: 1px solid rgba(var(--accent-strong), 0.42);
      box-shadow:
        0 26px 65px -30px rgba(var(--accent-strong), 0.75),
        0 14px 38px -20px rgba(var(--accent-soft), 0.6);
      transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.35s ease;
    }
    .metric-card:focus-visible {
      outline: 3px solid rgba(var(--accent-soft), 0.85);
      outline-offset: 4px;
    }
    html.dark .metric-card:focus-visible { outline-color: rgba(var(--accent-glow), 0.85); }
    .metric-card::before {
      content: "";
      position: absolute;
      inset: -35% -15% 35% -25%;
      background:

        conic-gradient(from 120deg at 32% 28%, rgba(var(--accent-soft), 0.75) 0%, rgba(var(--accent), 0.15) 48%, transparent 72%),
        radial-gradient(circle at 80% 20%, rgba(var(--accent-glow), 0.85), transparent 58%),
        radial-gradient(circle at 15% 95%, rgba(var(--accent), 0.35), transparent 65%);
      mix-blend-mode: screen;

      opacity: 0.95;
      pointer-events: none;
      transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .metric-card::after {
      content: "";
      position: absolute;
      inset: 1px;

      border-radius: 1.6rem;

      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
      mix-blend-mode: soft-light;
      pointer-events: none;

      opacity: 0.9;
    }
    .metric-card:hover {
      transform: translateY(-10px);
      box-shadow:
        0 36px 95px -34px rgba(var(--accent-strong), 0.8),
        0 18px 48px -24px rgba(var(--accent-soft), 0.7);
      filter: brightness(1.02) saturate(1.05);
    }
    .metric-card:hover::before {
      opacity: 1;
      transform: translate3d(0, -6px, 0) scale(1.04);
    }
    html.dark .metric-card {
      color: #f8fafc;
      background:
        radial-gradient(circle at -10% -20%, rgba(var(--accent-strong), 0.88) 0%, rgba(var(--accent-strong), 0.2) 45%, transparent 68%),
        radial-gradient(circle at 95% -15%, rgba(var(--accent-soft), 0.65) 0%, rgba(var(--accent-soft), 0.18) 55%, transparent 75%),
        linear-gradient(150deg,
          rgba(var(--accent-strong), 0.82) 0%,
          rgba(var(--accent), 0.62) 45%,
          rgba(2, 6, 23, 0.95) 100%);
      border-color: rgba(var(--accent-soft), 0.5);
      box-shadow:
        0 28px 70px -32px rgba(var(--accent-strong), 0.85),
        0 14px 42px -22px rgba(15, 23, 42, 0.6);
    }
    html.dark .metric-card::before {
      mix-blend-mode: lighten;
      opacity: 0.75;
    }
    html.dark .metric-card::after {
      border-color: rgba(226, 232, 240, 0.35);
      box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.18);
      mix-blend-mode: screen;

    }
    .metric-card .metric-label {
      display: inline-block;
      font-size: 0.7rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      font-weight: 600;

      color: rgba(var(--accent-strong), 0.95);
      margin-bottom: 0.75rem;
      text-shadow: 0 12px 26px rgba(var(--accent-strong), 0.55);
    }
    html.dark .metric-card .metric-label {
      color: rgba(var(--accent-glow), 0.95);
      text-shadow: 0 12px 28px rgba(var(--accent-soft), 0.6);

    }
    .metric-card .metric-value {
      font-size: 1.95rem;
      font-weight: 700;

      color: rgba(15, 23, 42, 0.95);
      text-shadow: 0 16px 32px rgba(var(--accent-strong), 0.25);
    }
    html.dark .metric-card .metric-value {
      color: rgba(226, 232, 240, 0.98);
      text-shadow: 0 18px 38px rgba(var(--accent-soft), 0.65);

    }
    .metric-card .metric-meta {
      margin-top: 0.35rem;
      font-size: 0.78rem;

      font-weight: 500;
      color: rgba(var(--accent-strong), 0.78);
    }
    html.dark .metric-card .metric-meta {
      color: rgba(var(--accent-soft), 0.85);
    }
    .metric-card i {
      color: rgba(255, 255, 255, 0.92);
      text-shadow:
        0 20px 35px rgba(var(--accent-strong), 0.48),
        0 8px 18px rgba(var(--accent-soft), 0.35);
    }
    html.dark .metric-card i {
      color: rgba(var(--accent-glow), 0.9);

    }
    .glass-panel {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.75), rgba(241, 245, 249, 0.55));
      border-radius: 1.75rem;
      border: 1px solid var(--surface-border-light);
      box-shadow: var(--surface-shadow-light);
      backdrop-filter: blur(24px);
      overflow: hidden;
    }
    html.dark .glass-panel {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.82), rgba(30, 41, 59, 0.55));
      border-color: var(--surface-border-dark);
      box-shadow: var(--surface-shadow-dark);
    }
    .glass-panel .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.5rem 1.75rem;
      border-bottom: 1px solid rgba(148, 163, 184, 0.2);
      background: linear-gradient(90deg, rgba(59, 130, 246, 0.16), transparent 75%);
    }
    html.dark .glass-panel .panel-header {
      border-bottom-color: rgba(71, 85, 105, 0.35);
      background: linear-gradient(90deg, rgba(96, 165, 250, 0.22), transparent 75%);
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
  <body class="theme-mist text-gray-900 dark:text-gray-100">
  <button id="sidebar-toggle" class="p-2 text-gray-900 dark:text-gray-100 md:hidden fixed top-4 right-4 z-50 rounded-xl" aria-label="Toggle navigation">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>
    <div class="flex min-h-screen">
      <aside id="sidebar" class="text-gray-900 dark:text-gray-100 w-64 space-y-4 py-6 px-4 absolute inset-y-0 left-0 z-40 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out rounded-3xl">
      <a id="navname" class="flex items-center space-x-3 px-4 text-lg font-semibold" href="/">
        <img src="/images/icon.png" class="w-8 h-8" alt="Site icon">
        <span>Wheathampstead Weather</span>
      </a>
      <div id="connect" class="flex items-center px-4 mt-2 text-red-500">
        <i class="fas fa-circle mr-2"></i>Disconnected
      </div>
        <nav class="mt-4 space-y-3">
          <div>
            <button type="button" class="flex items-center justify-between w-full py-2.5 px-4 rounded-xl transition-transform duration-200" data-submenu-toggle="reports-menu" aria-controls="reports-menu">
              <span class="flex items-center"><i class="fas fa-chart-line text-blue-500 mr-2"></i>Reports</span>
              <i class="fas fa-chevron-down"></i>
            </button>
            <div id="reports-menu" class="submenu space-y-1">
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/"><i class="fas fa-home text-blue-500 mr-2"></i>Home <span class="sr-only">(current)</span></a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/extremes.php"><i class="fas fa-chart-line text-blue-500 mr-2"></i>Extremes</a>
                <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/reportrainyeartotals.php"><i class="fas fa-cloud-rain text-blue-500 mr-2"></i>Rain By Year</a>
                <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/reporttempyeartotals.php"><i class="fas fa-temperature-high text-blue-500 mr-2"></i>Temp By Year</a>
                <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/reportwindyeartotals.php"><i class="fas fa-wind text-blue-500 mr-2"></i>Wind By Year</a>
                <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/records.php"><i class="fas fa-book text-blue-500 mr-2"></i>Records</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/windrose.php"><i class="fas fa-compass text-blue-500 mr-2"></i>Wind Rose</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/seasonal.php"><i class="fas fa-calendar text-blue-500 mr-2"></i>Seasonal</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/last-time.php"><i class="fas fa-history text-blue-500 mr-2"></i>Last Time</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/climate-analysis.php"><i class="fas fa-globe text-blue-500 mr-2"></i>Climate Analysis</a>
            </div>
          </div>
          <div>
            <button type="button" class="flex items-center justify-between w-full py-2.5 px-4 rounded-xl transition-transform duration-200" data-submenu-toggle="tools-menu" aria-controls="tools-menu">
              <span class="flex items-center"><i class="fas fa-tools text-blue-500 mr-2"></i>Tools</span>
              <i class="fas fa-chevron-down"></i>
            </button>
            <div id="tools-menu" class="submenu space-y-1">
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/picture.php"><i class="fas fa-camera text-blue-500 mr-2"></i>Webcam</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/export.php"><i class="fas fa-file-export text-blue-500 mr-2"></i>Export Data</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/historical.php"><i class="fas fa-clock text-blue-500 mr-2"></i>Historical Explorer</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="/astro"><i class="fas fa-star text-blue-500 mr-2"></i>Astro</a>
              <div class="px-4 pt-3 pb-4 mt-2">
                <label for="theme-select" class="block text-sm mb-2 font-medium tracking-wide">Theme</label>
                <select id="theme-select" class="w-full py-2.5 px-4 rounded-xl bg-white/70 dark:bg-slate-900/60 text-gray-900 dark:text-gray-100 shadow-inner border border-white/40 dark:border-slate-700/70 focus:outline-none focus:ring-2 focus:ring-sky-300/60">
                  <option value="system">System</option>
                  <option value="light">Light</option>
                  <option value="dark">Dark</option>
                </select>
              </div>
            </div>
          </div>
          <div>
            <button type="button" class="flex items-center justify-between w-full py-2.5 px-4 rounded-xl transition-transform duration-200" data-submenu-toggle="external-menu" aria-controls="external-menu">
              <span class="flex items-center"><i class="fas fa-external-link-alt text-blue-500 mr-2"></i>External</span>
              <i class="fas fa-chevron-down"></i>
            </button>
            <div id="external-menu" class="submenu space-y-1">
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="http://ob.smeird.com"><i class="fas fa-cloud-sun text-blue-500 mr-2"></i>Sky Weather</a>
              <a class="flex items-center w-full py-2.5 px-4 rounded-xl" href="http://power.smeird.com"><i class="fas fa-bolt text-blue-500 mr-2"></i>Power Use</a>
            </div>
          </div>
        </nav>
        <div class="px-4 pb-6">
          <?php include('graph-selector.php'); ?>
        </div>
      </aside>

    <script>
      document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
      });
      document.querySelectorAll('[data-submenu-toggle]').forEach(function(button) {
        var target = document.getElementById(button.getAttribute('data-submenu-toggle'));
        button.addEventListener('click', function() {
          target.classList.toggle('open');
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
