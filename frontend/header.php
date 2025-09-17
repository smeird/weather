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
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Roboto', sans-serif; font-weight: 700; }
    button, .highlight { font-family: 'Source Sans Pro', sans-serif; font-weight: 300; }

    :root {
      color-scheme: light dark;
    }

    body {
      background: radial-gradient(circle at 20% 20%, #eef2ff 0%, #e0f2fe 32%, #f8fafc 80%, #fdf4ff 100%);
      background-attachment: fixed;
      transition: background 0.6s ease, color 0.6s ease;
    }

    html.dark body {
      background: radial-gradient(circle at 10% -10%, rgba(56, 189, 248, 0.12) 0%, rgba(15, 23, 42, 0.95) 40%, rgba(2, 6, 23, 1) 100%);
    }

    .background-layers {
      position: fixed;
      inset: 0;
      overflow: hidden;
      pointer-events: none;
      z-index: 0;
    }

    .background-blob {
      position: absolute;
      border-radius: 9999px;
      filter: blur(90px);
      opacity: 0.6;
      transform: translate3d(0, 0, 0);
    }

    .background-blob--one {
      width: 28rem;
      height: 28rem;
      top: -8rem;
      left: -10rem;
      background: radial-gradient(circle at center, rgba(59, 130, 246, 0.45), rgba(59, 130, 246, 0));
    }

    .background-blob--two {
      width: 32rem;
      height: 32rem;
      bottom: -12rem;
      right: -6rem;
      background: radial-gradient(circle at center, rgba(236, 72, 153, 0.35), rgba(236, 72, 153, 0));
    }

    .background-blob--three {
      width: 22rem;
      height: 22rem;
      top: 40%;
      left: 55%;
      background: radial-gradient(circle at center, rgba(34, 211, 238, 0.25), rgba(34, 211, 238, 0));
    }

    .background-grid {
      position: absolute;
      inset: 0;
      background-image: linear-gradient(transparent 0, rgba(148, 163, 184, 0.15) 1px), linear-gradient(90deg, transparent 0, rgba(148, 163, 184, 0.15) 1px);
      background-size: 22px 22px;
      opacity: 0.25;
      mask-image: radial-gradient(circle at top, rgba(0, 0, 0, 0.75), transparent 70%);
    }

    .glass-panel {
      background: linear-gradient(140deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.64));
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: 0 30px 80px -40px rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(26px) saturate(160%);
    }

    html.dark .glass-panel {
      background: linear-gradient(140deg, rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.62));
      border: 1px solid rgba(94, 106, 142, 0.45);
      box-shadow: 0 40px 90px -40px rgba(2, 6, 23, 0.85);
    }

    .glass-card {
      background: linear-gradient(150deg, rgba(255, 255, 255, 0.78), rgba(248, 250, 252, 0.55));
      border: 1px solid rgba(148, 163, 184, 0.3);
      box-shadow: 0 25px 60px -35px rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(18px) saturate(160%);
      transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }

    html.dark .glass-card {
      background: linear-gradient(150deg, rgba(15, 23, 42, 0.78), rgba(30, 41, 59, 0.55));
      border: 1px solid rgba(94, 106, 142, 0.4);
      box-shadow: 0 35px 70px -40px rgba(2, 6, 23, 0.9);
    }

    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 35px 90px -40px rgba(30, 64, 175, 0.45);
      border-color: rgba(59, 130, 246, 0.35);
    }

    .nav-surface {
      border-radius: 1.75rem;
    }

    .nav-group-button {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 0.85rem 1.15rem;
      border-radius: 1rem;
      font-weight: 600;
      font-size: 0.9375rem;
      color: #1e293b;
      transition: background 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
      background: rgba(255, 255, 255, 0.45);
      border: 1px solid rgba(148, 163, 184, 0.4);
    }

    .nav-group-button .nav-group-label {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .nav-group-button .nav-chevron {
      transition: transform 0.3s ease;
    }

    .nav-group-button.open .nav-chevron {
      transform: rotate(180deg);
    }

    .nav-group-button:hover,
    .nav-group-button:focus-visible {
      background: rgba(59, 130, 246, 0.12);
      color: #0f172a;
      box-shadow: 0 18px 40px -24px rgba(37, 99, 235, 0.35);
    }

    html.dark .nav-group-button {
      background: rgba(15, 23, 42, 0.6);
      border-color: rgba(94, 106, 142, 0.45);
      color: #e2e8f0;
    }

    html.dark .nav-group-button:hover,
    html.dark .nav-group-button:focus-visible {
      background: rgba(59, 130, 246, 0.2);
      color: #f8fafc;
      box-shadow: 0 18px 50px -25px rgba(59, 130, 246, 0.35);
    }

    .submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.35s ease-in-out, padding 0.35s ease-in-out;
      margin-left: 0.4rem;
      border-left: 1px solid rgba(148, 163, 184, 0.3);
      padding-left: 0.25rem;
    }

    .submenu.open {
      max-height: 520px;
      padding-left: 0.75rem;
      margin-top: 0.5rem;
    }

    html.dark .submenu {
      border-left-color: rgba(94, 106, 142, 0.35);
    }

    .nav-link {
      display: flex;
      align-items: center;
      width: 100%;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      border-radius: 0.95rem;
      font-weight: 500;
      font-size: 0.9rem;
      color: #1e293b;
      transition: background 0.3s ease, transform 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
      position: relative;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: inherit;
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.18), rgba(14, 165, 233, 0.14));
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: -1;
    }

    .nav-link:hover,
    .nav-link:focus-visible {
      color: #0f172a;
      transform: translateX(4px);
      box-shadow: 0 18px 50px -30px rgba(37, 99, 235, 0.35);
    }

    .nav-link:hover::after,
    .nav-link:focus-visible::after {
      opacity: 1;
    }

    html.dark .nav-link {
      color: #e2e8f0;
    }

    html.dark .nav-link:hover,
    html.dark .nav-link:focus-visible {
      color: #f8fafc;
    }

    html.dark .nav-link::after {
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.25), rgba(56, 189, 248, 0.2));
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.55rem 0.9rem;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 0.85rem;
      letter-spacing: 0.02em;
      background: rgba(255, 255, 255, 0.55);
      border: 1px solid rgba(148, 163, 184, 0.35);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), 0 18px 32px -26px rgba(15, 23, 42, 0.5);
    }

    .status-pill .status-dot {
      width: 0.75rem;
      height: 0.75rem;
      border-radius: 9999px;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
    }

    .status-pill--connected {
      color: #047857;
      background: rgba(16, 185, 129, 0.12);
      border-color: rgba(16, 185, 129, 0.35);
    }

    .status-pill--connected .status-dot {
      background: radial-gradient(circle at center, #34d399, #059669);
      box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }

    .status-pill--reconnecting {
      color: #b45309;
      background: rgba(251, 191, 36, 0.16);
      border-color: rgba(217, 119, 6, 0.35);
    }

    .status-pill--reconnecting .status-dot {
      background: radial-gradient(circle at center, #facc15, #d97706);
      animation: pulse 1.2s ease-in-out infinite;
      box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.18);
    }

    .status-pill--disconnected {
      color: #b91c1c;
      background: rgba(248, 113, 113, 0.18);
      border-color: rgba(220, 38, 38, 0.35);
    }

    .status-pill--disconnected .status-dot {
      background: radial-gradient(circle at center, #f87171, #dc2626);
      box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.2);
    }

    @keyframes pulse {
      0%,
      100% {
        transform: scale(1);
        opacity: 0.9;
      }
      50% {
        transform: scale(1.25);
        opacity: 0.6;
      }
    }

    html.dark .status-pill {
      background: rgba(15, 23, 42, 0.6);
      border-color: rgba(94, 106, 142, 0.45);
      box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.18), 0 18px 40px -26px rgba(15, 23, 42, 0.75);
    }

    .theme-picker {
      width: 100%;
      padding: 0.7rem 1rem;
      border-radius: 1rem;
      border: 1px solid rgba(148, 163, 184, 0.45);
      background: rgba(255, 255, 255, 0.6);
      color: #0f172a;
      font-weight: 500;
      transition: border 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }

    .theme-picker:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.6);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }

    html.dark .theme-picker {
      background: rgba(15, 23, 42, 0.65);
      border-color: rgba(94, 106, 142, 0.45);
      color: #e2e8f0;
    }

    .glass-button {
      background: rgba(255, 255, 255, 0.65);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(148, 163, 184, 0.45);
      border-radius: 1rem;
      padding: 0.6rem 0.7rem;
      box-shadow: 0 20px 40px -25px rgba(15, 23, 42, 0.45);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-button:hover,
    .glass-button:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 25px 55px -30px rgba(37, 99, 235, 0.45);
    }

    html.dark .glass-button {
      background: rgba(15, 23, 42, 0.75);
      border-color: rgba(94, 106, 142, 0.5);
      color: #f8fafc;
    }

    :is(div, section, article).bg-white.rounded,
    :is(div, section, article).bg-white.shadow,
    :is(div, section, article).bg-white.rounded-lg,
    :is(div, section, article).bg-white.rounded-xl {
      background: linear-gradient(150deg, rgba(255, 255, 255, 0.8), rgba(248, 250, 252, 0.6)) !important;
      border: 1px solid rgba(148, 163, 184, 0.28);
      box-shadow: 0 25px 60px -35px rgba(15, 23, 42, 0.4);
      backdrop-filter: blur(18px) saturate(160%);
    }

    html.dark :is(div, section, article).dark\:bg-gray-800.rounded,
    html.dark :is(div, section, article).dark\:bg-gray-800.shadow,
    html.dark :is(div, section, article).dark\:bg-gray-800.rounded-lg,
    html.dark :is(div, section, article).dark\:bg-gray-800.rounded-xl {
      background: linear-gradient(150deg, rgba(15, 23, 42, 0.78), rgba(30, 41, 59, 0.58)) !important;
      border: 1px solid rgba(94, 106, 142, 0.38);
      box-shadow: 0 35px 70px -40px rgba(2, 6, 23, 0.85);
      backdrop-filter: blur(22px) saturate(160%);
    }

    .content-shell {
      position: relative;
      z-index: 10;
    }
  </style>
</head>
  <body class="antialiased text-slate-900 dark:text-slate-100">
  <div class="background-layers" aria-hidden="true">
    <div class="background-grid"></div>
    <div class="background-blob background-blob--one"></div>
    <div class="background-blob background-blob--two"></div>
    <div class="background-blob background-blob--three"></div>
  </div>
  <button id="sidebar-toggle" class="glass-button text-slate-900 dark:text-slate-100 md:hidden fixed top-4 right-4 z-50" aria-label="Toggle navigation" aria-expanded="false">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>
    <div class="flex min-h-screen relative z-10 w-full px-4 sm:px-6 lg:px-12 py-6 md:py-10 md:gap-10">
      <aside id="sidebar" class="glass-panel nav-surface text-slate-900 dark:text-slate-100 w-72 space-y-6 p-6 absolute inset-y-6 left-4 z-40 transform -translate-x-full md:relative md:inset-y-0 md:left-0 md:translate-x-0 md:w-72 md:mr-10 transition-transform duration-300 ease-in-out shadow-2xl max-h-[calc(100vh-3rem)] overflow-y-auto md:max-h-none">
      <a id="navname" class="flex items-center space-x-3 px-3 py-3 rounded-2xl bg-white/60 dark:bg-slate-800/70 border border-white/50 dark:border-slate-700/60 shadow-sm shadow-slate-900/20 transition-transform duration-300 hover:-translate-y-0.5" href="/">
        <img src="/images/icon.png" class="w-9 h-9 rounded-xl shadow-lg shadow-slate-900/20" alt="Site icon">
        <span class="text-base font-semibold tracking-tight">Wheathampstead Weather</span>
      </a>
      <div id="connect" class="status-pill status-pill--disconnected mt-4" role="status" aria-live="polite">
        <span class="status-dot" aria-hidden="true"></span>
        <span class="status-text">Disconnected</span>
      </div>
        <nav class="mt-4 space-y-2">
          <div>
            <button type="button" class="nav-group-button" data-submenu-toggle="reports-menu" aria-controls="reports-menu">
              <span class="nav-group-label"><i class="fas fa-chart-line text-blue-500"></i><span>Reports</span></span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div id="reports-menu" class="submenu space-y-1">
              <a class="nav-link" href="/"><i class="fas fa-home text-blue-500"></i><span>Home <span class="sr-only">(current)</span></span></a>
              <a class="nav-link" href="/extremes.php"><i class="fas fa-chart-line text-blue-500"></i><span>Extremes</span></a>
              <a class="nav-link" href="/reportrainyeartotals.php"><i class="fas fa-cloud-rain text-blue-500"></i><span>Rain By Year</span></a>
              <a class="nav-link" href="/reporttempyeartotals.php"><i class="fas fa-temperature-high text-blue-500"></i><span>Temp By Year</span></a>
              <a class="nav-link" href="/reportwindyeartotals.php"><i class="fas fa-wind text-blue-500"></i><span>Wind By Year</span></a>
              <a class="nav-link" href="/records.php"><i class="fas fa-book text-blue-500"></i><span>Records</span></a>
              <a class="nav-link" href="/windrose.php"><i class="fas fa-compass text-blue-500"></i><span>Wind Rose</span></a>
              <a class="nav-link" href="/seasonal.php"><i class="fas fa-calendar text-blue-500"></i><span>Seasonal</span></a>
              <a class="nav-link" href="/last-time.php"><i class="fas fa-history text-blue-500"></i><span>Last Time</span></a>
              <a class="nav-link" href="/climate-analysis.php"><i class="fas fa-globe text-blue-500"></i><span>Climate Analysis</span></a>
            </div>
          </div>
          <div>
            <button type="button" class="nav-group-button" data-submenu-toggle="tools-menu" aria-controls="tools-menu">
              <span class="nav-group-label"><i class="fas fa-tools text-blue-500"></i><span>Tools</span></span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div id="tools-menu" class="submenu space-y-1">
              <a class="nav-link" href="/picture.php"><i class="fas fa-camera text-blue-500"></i><span>Webcam</span></a>
              <a class="nav-link" href="/export.php"><i class="fas fa-file-export text-blue-500"></i><span>Export Data</span></a>
              <a class="nav-link" href="/historical.php"><i class="fas fa-clock text-blue-500"></i><span>Historical Explorer</span></a>
              <a class="nav-link" href="/astro"><i class="fas fa-star text-blue-500"></i><span>Astro</span></a>
              <?php include('graph-selector.php'); ?>
            </div>
          </div>
          <div>
            <button type="button" class="nav-group-button" data-submenu-toggle="external-menu" aria-controls="external-menu">
              <span class="nav-group-label"><i class="fas fa-external-link-alt text-blue-500"></i><span>External</span></span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div id="external-menu" class="submenu space-y-1">
              <a class="nav-link" href="http://ob.smeird.com"><i class="fas fa-cloud-sun text-blue-500"></i><span>Sky Weather</span></a>
              <a class="nav-link" href="http://power.smeird.com"><i class="fas fa-bolt text-blue-500"></i><span>Power Use</span></a>
            </div>
          </div>
        </nav>
        <div class="px-1">
          <label for="theme-select" class="block text-sm font-semibold tracking-wide text-slate-600 dark:text-slate-300 mb-2">Theme</label>
          <select id="theme-select" class="theme-picker">
            <option value="system">System</option>
            <option value="light">Light</option>
            <option value="dark">Dark</option>
          </select>
        </div>
      </aside>

    <script>
      const sidebarToggle = document.getElementById('sidebar-toggle');
      const sidebar = document.getElementById('sidebar');
      sidebarToggle.addEventListener('click', function() {
        const isHidden = sidebar.classList.toggle('-translate-x-full');
        sidebarToggle.setAttribute('aria-expanded', (!isHidden).toString());
      });
      document.querySelectorAll('[data-submenu-toggle]').forEach(function(button) {
        var target = document.getElementById(button.getAttribute('data-submenu-toggle'));
        button.addEventListener('click', function() {
          target.classList.toggle('open');
          button.classList.toggle('open');
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
    <div class="flex-1 flex flex-col relative z-10">
      <div class="flex-1 py-4 md:py-6">
        <div class="mx-auto w-full max-w-7xl space-y-8 content-shell">
