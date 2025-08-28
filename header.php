<?php
@ini_set('zlib.output_compression', 1);
@ini_set('zlib.output_compression_level', 6);
date_default_timezone_set("Europe/London");
setlocale(LC_ALL, 'uk_UA.utf8');

require_once 'dbconn.php';
$sql = "
    SELECT
        round(`archive`.`outTemp`,1) AS `outTemp`,
        (SELECT round(`outTemp`,1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY) - 1 ORDER BY `outTemp` DESC LIMIT 1) AS `maxTemp`,
        (SELECT round(`outTemp`,1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY) - 1 ORDER BY `outTemp` ASC LIMIT 1) AS `minTemp`
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
?>
<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml" xml:lang="en-UK" lang="en-UK">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="refresh" content="3600">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:description" content="Wheathamstead Weather Conditions" />
  <meta id="postdata" property="og:title" content="Weather in Wheathamstead is currently <?php echo $outTemp; ?>°C. The temprature range today was <?php echo $minTemp." : ". $maxTemp; ?>°C." />
  <title> Weather in Wheathamstead is currently <?php echo $outTemp; ?>°C. The temprature range today was <?php echo $minTemp." : ". $maxTemp; ?>°C." </title>
  <meta property="og:type" content="website" />
  <meta property="og:image" content="https://www.smeird.com/snap.jpeg" />
  <meta property="og:url" content="https://www.smeird.com/newgraph.php?WHAT=outTemp&SCALE=day" />
  <meta property="og:image:alt" content="Picture of my Veg Garden" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta name="Keywords" content="Weather" />
  <meta name="Description" content="Personal Weather Site" />
  <link rel="home" href="/" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  <script src="https://kit.fontawesome.com/55c3f37ab0.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&family=Inter&family=Source+Sans+Pro:wght@300&display=swap" rel="stylesheet">
  <script src="https://code.highcharts.com/stock/highstock.js"></script>
  <script src="https://code.highcharts.com/highcharts-more.js"></script>
  <script src="https://code.highcharts.com/modules/boost.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/canvg/3.0.7/umd.min.js"></script>
  <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="theme-color" content="#ffffff">
  <style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Roboto', sans-serif; font-weight: 700; }
    button, .highlight { font-family: 'Source Sans Pro', sans-serif; font-weight: 300; }
  </style>
</head>
  <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <button id="sidebar-toggle" class="p-2 text-gray-900 dark:text-gray-100 md:hidden fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 rounded" aria-label="Toggle navigation">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>
    <div class="flex min-h-screen">
      <aside id="sidebar" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 w-64 space-y-2 py-4 px-2 absolute inset-y-0 left-0 z-40 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
      <a id="navname" class="flex items-center space-x-2 px-4" href="/#">
        <img src="/images/safari-pinned-tab.svg" class="w-8 h-8" alt="">
        <span>Wheathampstead Weather</span>
      </a>
        <nav class="mt-4">
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/">Home <span class="sr-only">(current)</span></a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/extremes.php">Extremes</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/reportrainyeartotals.php">Rain By Year</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/reporttempyeartotals.php">Temp By Year</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/reportwindyeartotals.php">Wind By Year</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/records.php">Records</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/windrose.php">Wind Rose</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/forecast.php">Forecast</a>

          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/picture.php">Webcam</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="/astro">Astro</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="http://ob.smeird.com">Sky Weather</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="http://power.smeird.com">Power Use</a>
          <a class="block py-2.5 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700" href="index.php"><span id="connect">Not Connected</span></a>
        <?php include('test.php'); ?>
      </nav>
    </aside>
    <script>
      document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
      });
    </script>
    <div class="flex-1 flex flex-col">
      <div class="flex-1 p-4">
        <div class="container mx-auto">
