<?php



 /**
  * This file loads content from four different data tables depending on the required time range.
  * The stockquotes table containts 1.7 million data points. Since we are loading OHLC data and
  * MySQL has no concept of first and last in a data group, we have extracted groups by hours, days
  * and months into separate tables. If we were to load a line series with average data, we wouldn't
  * have to do this.
  *
  * @param callback {String} The name of the JSONP callback to pad the JSON within
  * @param start {Integer} The starting point in JS time
  * @param end {Integer} The ending point in JS time
  */
// get the parameters
 $start="";
 $end="";

if(isset($_GET['item'])){$item = $_GET['item'];}

$allowedItems = ['outTemp','inTemp','outHumidity','inHumidity','windSpeed','windGust','windDir','barometer','pressure','rain','rainRate','rainn','dewpoint','heatindex','windchill','radiation','UV'];
if (!in_array($item, $allowedItems, true)) {
  http_response_code(400);
  exit('Invalid item parameter');
}

 $callback = $_GET['callback'];
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
 if (!$start) $start = ((time() - 3*52*7*60*60*24) * 1000) ;
 if (!$end) $end = time() * 1000;



// connect to MySQL
//require_once('../../configuration.php');
//$conf = new JConfig();
//mysqli_connect($conf->host, $conf->user, $conf->password) or die(mysql_error());
//mysqli_select_db($link,$conf->db) or die(mysqli_error());
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
}

 else
     {
     $table = "archive";
     }



 $sql = "SELECT dateTime * 1000 AS datey, round($item,2) AS data FROM $table WHERE from_unixtime(dateTime) BETWEEN ? AND ? ORDER BY datey";
 $stmt = mysqli_prepare($link, $sql);
 mysqli_stmt_bind_param($stmt, 'ss', $startTime, $endTime);
 mysqli_stmt_execute($stmt);
 $result = mysqli_stmt_get_result($stmt);

 if ($item == "rainn") {
     mysqli_free_result($result);
     mysqli_stmt_close($stmt);
     $sql2 = "SELECT unix_timestamp(t1.dateTime) * 1000 as datetime, IFNULL((t1.rain - t2.rain),0) as data FROM archive t1 LEFT OUTER JOIN archive t2 ON t2.dateTime = (SELECT MAX(dateTime) FROM archive WHERE dateTime < t1.dateTime) WHERE t1.dateTime BETWEEN ? AND ? ORDER BY t1.dateTime limit 0, 5000";
     $stmt = mysqli_prepare($link, $sql2);
     mysqli_stmt_bind_param($stmt, 'ss', $startTime, $endTime);
     mysqli_stmt_execute($stmt);
     $result = mysqli_stmt_get_result($stmt);

     $sql3 = "select $item as data from $table where dateTime between ? and ? order by dateTime limit 1";
     $stmt2 = mysqli_prepare($link, $sql3);
     mysqli_stmt_bind_param($stmt2, 'ss', $startTime, $endTime);
     mysqli_stmt_execute($stmt2);
     $result2 = mysqli_stmt_get_result($stmt2);
     $dataRow = mysqli_fetch_row($result2);
     $data1 = round($dataRow[0], 2);
     mysqli_free_result($result2);
     mysqli_stmt_close($stmt2);
 }


 if ($item == "rainn")
     {
     $rows = array();

     while ($row = mysqli_fetch_assoc($result))
         {
         extract($row);
         // add deductions
         $data = round($data, 3);

         $data2 = round($data - $data1, 3);

         $rows[] = "[$datey,$data2]";

         $data1 = round($data, 2);
         }
     }
 else
     {

     $rows = array();
     while ($row  = mysqli_fetch_assoc($result))
         {
         extract($row);
         $rows[] = "[$datey,$data]";
         }
     }
// print it
 header('Content-Type: text/javascript');

 echo "/* console.log(' range=$sql ,table=$table ,range= $range ,start = $start, end = $end, startTime = $startTime, endTime = $endTime '); */";
 echo $callback . "([\n" . join(",\n", $rows) . "\n]);";

  mysqli_free_result($result);
 mysqli_stmt_close($stmt);
?>
