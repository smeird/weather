<?php
date_default_timezone_set("Europe/London");


 $start="";
 $end="";
if(isset($_GET['itemmm'])){$itemmm = $_GET['itemmm'];}
 $callback = $_GET['callback'];
// $min      = $itemmm . '_min';
// $max      = $itemmm . '_max';
 $min      = $itemmm;
$max      = $itemmm;
//echo $min;

 if (!preg_match('/^[a-zA-Z0-9_]+$/', $callback))
     {
     die('Invalid callback name');
     }

 if(isset($_GET['start'])){$start = $_GET['start'];}
 if ($start && !preg_match('/^[0-9]+$/', $start))
     {
     die("Invalid start parameter: $start");
     }

 if(isset($_GET['end'])){$end = $_GET['end'];}
 if ($end && !preg_match('/^[0-9]+$/', $end))
     {
     die("Invalid end parameter: $end");
     }
 if (!$end) $end = time() * 1000;



// connect to MySQL
 include ('dbconn.php');

// set UTC time
 //mysqli_query($link,"SET time_zone = '+00:00'");

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




 $sql    = "
select
		dateTime * 1000 as datetime,
		round(MIN($itemmm),2) as datamin,
		round(MAX($itemmm),2) as datamax
	from $table
	where from_unixtime(dateTime) between '$startTime' and '$endTime'
  GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime)) 
	order by from_unixtime(dateTime)";
//echo $sql;
 $result = mysqli_query($link,$sql) or die(mysqli_error());


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
?>
