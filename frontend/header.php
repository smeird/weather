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
    #sidebar {
      position: relative;
      overflow: hidden;
      background: linear-gradient(145deg, rgba(255, 255, 255, 0.94), rgba(226, 232, 240, 0.68));
      border: 1px solid rgba(148, 163, 184, 0.2);
      border-radius: 1.75rem;
      box-shadow: 0 32px 70px -40px rgba(30, 64, 175, 0.55);
      backdrop-filter: blur(28px);
      isolation: isolate;
    }
    #sidebar::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.18), transparent 55%),
                  radial-gradient(circle at 85% 10%, rgba(236, 72, 153, 0.12), transparent 55%);
      opacity: 0.8;
      pointer-events: none;
    }
    #sidebar::after {
      content: "";
      position: absolute;
      bottom: -40%;
      left: -35%;
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(56, 189, 248, 0.22), transparent 70%);
      filter: blur(25px);
      opacity: 0.6;
      pointer-events: none;
    }
    html.dark #sidebar {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.78));
      border-color: rgba(71, 85, 105, 0.45);
      box-shadow: 0 35px 80px -45px rgba(14, 165, 233, 0.55);
    }
    html.dark #sidebar::before {
      background: radial-gradient(circle at 12% 18%, rgba(59, 130, 246, 0.24), transparent 60%),
                  radial-gradient(circle at 80% 18%, rgba(129, 140, 248, 0.18), transparent 60%);
    }
    html.dark #sidebar::after {
      background: radial-gradient(circle, rgba(59, 130, 246, 0.3), transparent 70%);
    }
    #navname {
      position: relative;
      display: flex;
      gap: 0.9rem;
      align-items: center;
      padding: 0.25rem 1rem 0.75rem;
      border-radius: 1.2rem;
    }
    #navname::after {
      content: "";
      position: absolute;
      inset: auto 1rem 0;
      height: 1px;
      background: linear-gradient(90deg, rgba(148, 163, 184, 0), rgba(148, 163, 184, 0.35), rgba(148, 163, 184, 0));
    }
    #navname .brand-icon {
      flex-shrink: 0;
      width: 2.75rem;
      height: 2.75rem;
      display: grid;
      place-items: center;
      border-radius: 1rem;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.22), rgba(45, 212, 191, 0.18));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }
    #navname .brand-copy {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
    }
    #navname .brand-title {
      font-weight: 700;
      font-size: 1.02rem;
      letter-spacing: 0.025em;
      background: linear-gradient(90deg, #1d4ed8, #0ea5e9);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    #navname .brand-subtitle {
      font-size: 0.72rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: rgba(15, 23, 42, 0.55);
    }
    html.dark #navname .brand-icon {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.28), rgba(45, 212, 191, 0.25));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    html.dark #navname .brand-subtitle { color: rgba(226, 232, 240, 0.65); }
    #connect {
      margin: 1.25rem 1rem 0;
      border-radius: 1.15rem;
      transition: transform 0.35s ease;
    }
    #connect:hover { transform: translateY(-2px); }
    #connect .status-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.15rem;
      border-radius: 1rem;
      border: 1px solid rgba(148, 163, 184, 0.22);
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.02));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }
    #connect .status-copy {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    #connect .status-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      color: rgba(15, 23, 42, 0.58);
    }
    #connect .status-state {
      font-weight: 600;
      font-size: 0.95rem;
    }
    #connect .status-dot {
      width: 0.85rem;
      height: 0.85rem;
      border-radius: 999px;
      position: relative;
      background: #ef4444;
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }
    #connect .status-dot::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: inherit;
      animation: statusPulse 2.4s infinite;
      background: currentColor;
      opacity: 0.4;
    }
    #connect .status-chip {
      margin-left: auto;
      padding: 0.35rem 0.85rem;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      background: rgba(15, 23, 42, 0.06);
      color: rgba(15, 23, 42, 0.7);
    }
    #connect.status-connected .status-card {
      background: linear-gradient(120deg, rgba(34, 197, 94, 0.18), rgba(59, 130, 246, 0.08));
      border-color: rgba(34, 197, 94, 0.35);
    }
    #connect.status-connected .status-dot {
      background: #22c55e;
      box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
      color: #22c55e;
    }
    #connect.status-connected .status-chip {
      background: rgba(34, 197, 94, 0.12);
      color: #166534;
    }
    #connect.status-reconnecting .status-card {
      background: linear-gradient(120deg, rgba(234, 179, 8, 0.18), rgba(59, 130, 246, 0.05));
      border-color: rgba(234, 179, 8, 0.32);
    }
    #connect.status-reconnecting .status-dot {
      background: #f59e0b;
      box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.16);
      color: #f59e0b;
    }
    #connect.status-reconnecting .status-chip {
      background: rgba(245, 158, 11, 0.16);
      color: #b45309;
    }
    #connect.status-disconnected .status-card {
      background: linear-gradient(120deg, rgba(239, 68, 68, 0.16), rgba(59, 130, 246, 0.04));
      border-color: rgba(239, 68, 68, 0.3);
    }
    #connect.status-disconnected .status-dot {
      background: #ef4444;
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
      color: #ef4444;
    }
    #connect.status-disconnected .status-chip {
      background: rgba(239, 68, 68, 0.18);
      color: #991b1b;
    }
    html.dark #connect .status-card {
      background: linear-gradient(120deg, rgba(148, 163, 184, 0.18), rgba(15, 23, 42, 0.75));
      border-color: rgba(71, 85, 105, 0.45);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    html.dark #connect .status-label { color: rgba(226, 232, 240, 0.62); }
    html.dark #connect .status-chip { background: rgba(148, 163, 184, 0.12); color: rgba(226, 232, 240, 0.82); }
    html.dark #connect.status-connected .status-chip { color: #bbf7d0; }
    html.dark #connect.status-reconnecting .status-chip { color: #fde68a; }
    html.dark #connect.status-disconnected .status-chip { color: #fecaca; }
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
    .submenu.open { max-height: 520px; }
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
    .current-conditions-card {
      position: relative;
      overflow: hidden;
      border-radius: 1.85rem;
      padding: 2.25rem 2.5rem;
      background: linear-gradient(120deg, rgba(59, 130, 246, 0.95), rgba(129, 140, 248, 0.9));
      box-shadow:
        0 28px 60px -38px rgba(30, 64, 175, 0.6),
        0 12px 32px -20px rgba(99, 102, 241, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.28);
      color: #f8fafc;
    }
    .current-conditions-card::before {
      content: "";
      position: absolute;
      inset: -25% 40% 20% -15%;
      background: radial-gradient(circle at top, rgba(255, 255, 255, 0.35), transparent 65%);
      filter: blur(0.5px);
      opacity: 0.75;
      pointer-events: none;
    }
    .current-conditions-card h1 {
      color: #f8fafc;
    }
    .current-conditions-card p {
      color: rgba(248, 250, 252, 0.85);
    }
    .current-conditions-card .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1.75rem;
      border-radius: 9999px;
      background: rgba(15, 23, 42, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: rgba(248, 250, 252, 0.8);
      font-size: 0.75rem;
      letter-spacing: 0.35em;
      text-transform: uppercase;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(18px);
    }
    .current-conditions-card .status-pill i {
      font-size: 1.1rem;
      color: rgba(191, 219, 254, 0.95);
    }
    html.dark .current-conditions-card {
      background: linear-gradient(120deg, rgba(30, 64, 175, 0.88), rgba(76, 29, 149, 0.85));
      border-color: rgba(148, 163, 184, 0.35);
      box-shadow:
        0 28px 60px -38px rgba(30, 64, 175, 0.7),
        0 18px 38px -24px rgba(76, 29, 149, 0.45);
    }
    html.dark .current-conditions-card .status-pill {
      background: rgba(15, 23, 42, 0.45);
      border-color: rgba(148, 163, 184, 0.35);
      color: rgba(226, 232, 240, 0.85);
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
        linear-gradient(160deg,
          rgba(var(--accent-strong), 0.28) 0%,
          rgba(var(--accent), 0.18) 42%,
          rgba(255, 255, 255, 0.48) 100%);
      border: 1px solid rgba(var(--accent-strong), 0.28);
      box-shadow:
        0 26px 55px -34px rgba(var(--accent-strong), 0.55),
        0 14px 32px -24px rgba(var(--accent-soft), 0.45);
      backdrop-filter: blur(18px);
      transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.35s ease, background 0.35s ease;
    }
    .metric-card:focus-visible {
      outline: 3px solid rgba(var(--accent-soft), 0.85);
      outline-offset: 4px;
    }
    html.dark .metric-card:focus-visible { outline-color: rgba(var(--accent-glow), 0.85); }
    .metric-card::before {
      content: "";
      position: absolute;
      inset: -28% -12% 32% -20%;
      background:
        radial-gradient(circle at 18% 22%, rgba(var(--accent-soft), 0.4), transparent 58%),
        radial-gradient(circle at 80% 18%, rgba(var(--accent), 0.22), transparent 70%);
      mix-blend-mode: screen;
      opacity: 0.65;
      pointer-events: none;
      transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .metric-card::after {
      content: "";
      position: absolute;
      inset: 1px;
      border-radius: 1.6rem;
      border: 1px solid rgba(255, 255, 255, 0.45);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
      mix-blend-mode: soft-light;
      pointer-events: none;
      opacity: 0.75;
    }
    .metric-card:hover {
      transform: translateY(-10px);
      box-shadow:
        0 32px 85px -34px rgba(var(--accent-strong), 0.65),
        0 18px 42px -24px rgba(var(--accent-soft), 0.55);
      filter: brightness(1.04) saturate(1.06);
    }
    .metric-card:hover::before {
      opacity: 0.8;
      transform: translate3d(0, -6px, 0) scale(1.04);
    }
    html.dark .metric-card {
      color: #f8fafc;
      background:
        linear-gradient(160deg,
          rgba(var(--accent-strong), 0.32) 0%,
          rgba(var(--accent-soft), 0.2) 45%,
          rgba(2, 6, 23, 0.7) 100%);
      border-color: rgba(var(--accent-soft), 0.4);
      box-shadow:
        0 28px 70px -32px rgba(var(--accent-strong), 0.65),
        0 14px 42px -22px rgba(2, 6, 23, 0.7);
      backdrop-filter: blur(20px);
    }
    html.dark .metric-card::before {
      mix-blend-mode: lighten;
      opacity: 0.6;
    }
    html.dark .metric-card::after {
      border-color: rgba(226, 232, 240, 0.28);
      box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.15);
      mix-blend-mode: screen;
      opacity: 0.65;
    }
    .metric-card .metric-label {
      display: inline-block;
      font-size: 0.7rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      font-weight: 600;

      color: rgba(59, 130, 246, 0.92);
      margin-bottom: 0.75rem;
      text-shadow: 0 12px 26px rgba(59, 130, 246, 0.35);
    }
    html.dark .metric-card .metric-label {
      color: rgba(191, 219, 254, 0.95);
      text-shadow: 0 12px 26px rgba(148, 197, 255, 0.45);

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
      <a id="navname" class="px-4 text-lg font-semibold" href="/">
        <span class="brand-icon">
          <img src="/images/icon.png" class="w-8 h-8" alt="Site icon">
        </span>
        <span class="brand-copy">
          <span class="brand-title">Wheathampstead Weather</span>
          <span class="brand-subtitle">Local conditions in real time</span>
        </span>
      </a>
      <div id="connect" class="status-disconnected" role="status" aria-label="Connection status: disconnected">
        <div class="status-card">
          <span class="status-dot" data-status-dot aria-hidden="true"></span>
          <div class="status-copy">
            <span class="status-label">Station Link</span>
            <span class="status-state" data-status-state aria-live="polite">Disconnected</span>
          </div>
          <span class="status-chip" data-status-chip aria-label="Offline connection">Offline</span>
        </div>
      </div>
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

    <script>
      document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
      });
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
