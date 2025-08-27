<?php


@ini_set('zlib.output_compression_level', 0);
date_default_timezone_set("Europe/London");
setlocale(LC_ALL, 'uk_UA.utf8');

include('dbconn.php');
$sql = "
    SELECT 
        round(`archive`.`outTemp`,2) AS `outTemp`,
        (SELECT round(`outTemp`,2) FROM `weewx`.`archive` WHERE DATE(FROM_UNIXTIME(`dateTime`)) = CURDATE() ORDER BY `outTemp` DESC LIMIT 1) AS `maxTemp`,
        (SELECT round(`outTemp`,2) FROM `weewx`.`archive` WHERE DATE(FROM_UNIXTIME(`dateTime`)) = CURDATE() ORDER BY `outTemp` ASC LIMIT 1) AS `minTemp`
    FROM 
        `weewx`.`archive` 
    ORDER BY 
        `archive`.`dateTime` DESC 
    LIMIT 1;
";
$result = mysqli_query($link,$sql) or die(mysqli_connect_error());

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
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <meta property="og:description" content="Wheathamstead Weather Conditions" />
  <meta id=postdata property="og:title" content="Weather in Wheathamstead is currently <?php echo $outTemp; ?>°C. The temprature range today was <?php echo $minTemp." : ". $maxTemp; ?>°C." />
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



  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/base.css">
  <script src="https://kit.fontawesome.com/55c3f37ab0.js" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/stock/highstock.js"></script>

  <script src="https://code.highcharts.com/highcharts-more.js"></script>
  <script src="https://code.highcharts.com/modules/boost.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/canvg/3.0.7/umd.min.js"></script>



  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="theme-color" content="#ffffff">






  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a id="navname" class="navbar-brand" href="/#">
      <img src="/safari-pinned-tab.svg" width="30" height="30" alt="">
      Wheathampstead Weather</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/maxmin.php">Max and Mins</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/reportrainyeartotals.php">Rain By Year</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/reporttempyeartotals.php">Temp By Year</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/records.php">Records</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/astro">Astro</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="http://ob.smeird.com">Sky Weather</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="http://power.smeird.com">Power Use</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php"><span id=connect>Not Connected</span></a>
        </li>

      </ul>
      <?php include('test.php'); ?>
    </div>

  </nav>
  <meta charset="utf-8">
</head>

<body>
