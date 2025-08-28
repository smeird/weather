<?php
date_default_timezone_set("Europe/London");


 $start="";
 $end="";
if(isset($_GET['itemmm'])){$itemmm = $_GET['itemmm'];}

$allowedItems = ['outTemp','inTemp','outHumidity','inHumidity','windSpeed','windGust','windDir','barometer','pressure','rain','rainRate','rainn','dewpoint','heatindex','windchill','radiation','UV'];
if (!in_array($itemmm, $allowedItems, true)) {
  http_response_code(400);
  exit('Invalid item parameter');
}

 $callback = $_GET['callback'];
// $min      = $itemmm . '_min';
// $max      = $itemmm . '_max';
 $min      = $itemmm;
$max      = $itemmm;
//echo $min;

 if (!preg_match('/^[a-zA-Z0-9_]+$/', $callback))
     {
     http_response_code(400);
     exit('Invalid callback name');
     }

 if(isset($_GET['start'])){$start = $_GET['start'];}
 if ($start && !preg_match('/^[0-9]+$/', $start))
     {
     http_response_code(400);
     exit("Invalid start parameter: $start");
     }

 if(isset($_GET['end'])){$end = $_GET['end'];}
 if ($end && !preg_match('/^[0-9]+$/', $end))
     {
     http_response_code(400);
     exit("Invalid end parameter: $end");
     }
 if (!$end) $end = time() * 1000;



// connect to MySQL
 require_once '../../dbconn.php';

// set UTC time
//db_query("SET time_zone = '+00:00'");

// set some utility variables
 $range     = $end - $start;
 $startTime = gmstrftime('%Y-%m-%d %H:%M:%S', $start / 1000);
 $endTime   = gmstrftime('%Y-%m-%d %H:%M:%S', $end / 1000);

// find the right table
// two days range loads minute data
 if ($range < 2 * 24 * 3600 * 1000)
     {
     $table = 'archive';

// one month range loads hourly data
     }
 elseif ($range < 31 * 24 * 3600 * 1000)
     {
     $table = 'archive';

// one year range loads daily data
     }
 elseif ($range < 15 * 31 * 24 * 3600 * 1000)
     {
     $table = 'archive';

// greater range loads monthly data
     }
	elseif ($range < 5 * 15 * 31 * 24 * 3600 * 1000)
     {
     $table = 'archive';

// greater range loads monthly data
     }

 else
     {
     $table = 'archive';
     }




$sql    = "select dateTime * 1000 as datetime, round(MIN($itemmm),1) as datamin, round(MAX($itemmm),1) as datamax from $table where dateTime BETWEEN UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(?)  GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime)) order by dateTime";
 $stmt = mysqli_prepare($link, $sql);
 mysqli_stmt_bind_param($stmt, 'ss', $startTime, $endTime);
 mysqli_stmt_execute($stmt);
 $result = mysqli_stmt_get_result($stmt);


 $rows = array();
 while ($row  = mysqli_fetch_assoc($result))
     {
     extract($row);

     $rows[] = "[$datetime,$datamin,$datamax]";
     }

// print it
 header('Content-Type: text/javascript');

echo "/* console.log(' sql=$sql,range= $range ,start = $start, end = $end, startTime = $startTime, endTime = $endTime '); */";
echo $callback . "([\n" . join(",\n", $rows) . "\n]);";
  mysqli_free_result($result);
 mysqli_stmt_close($stmt);

