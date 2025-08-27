<?php
date_default_timezone_set("Europe/London");

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
$item     = $_GET['item'];

// Map lowercase inputs to canonical column names
$allowedItems = [
  'outtemp'     => 'outTemp',
  'intemp'      => 'inTemp',
  'outhumidity' => 'outHumidity',
  'inhumidity'  => 'inHumidity',
  'windspeed'   => 'windSpeed',
  'windgust'    => 'windGust',
  'winddir'     => 'windDir',
  'barometer'   => 'barometer',
  'pressure'    => 'pressure',
  'rain'        => 'rain',
  'rainrate'    => 'rainRate',
  'rainn'       => 'rainn',
  'dewpoint'    => 'dewpoint',
  'heatindex'   => 'heatindex',
  'windchill'   => 'windchill',
  'radiation'   => 'radiation',
  'uv'          => 'UV'
];

$itemKey = strtolower($item);
if (!isset($allowedItems[$itemKey])) {
  http_response_code(400);
  exit('Invalid item parameter');
}
$item = $allowedItems[$itemKey];

$callback = $_GET['callback'] ?? null;
$isJsonp = false;
if ($callback !== null) {
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $callback)) {
    http_response_code(400);
    exit('Invalid callback name');
  }
  $isJsonp = true;
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
//require_once('../../configuration.php');
//$conf = new JConfig();
//mysqli_connect($conf->host, $conf->user, $conf->password) or die(mysqli_error($link));
//mysqli_select_db($link,$conf->db) or die(mysqli_error());
// connect to MySQL
 require_once 'dbconn.php';

// set UTC time
db_query("SET time_zone = '+00:00'");

// set some utility variables
 $range     = $end - $start;
 $startTime = gmstrftime('%Y-%m-%d %H:%M:%S', $start / 1000);
 $endTime   = gmstrftime('%Y-%m-%d %H:%M:%S', $end / 1000);

// find the right table
 /* two days range loads minute data
   if ($range < 2 * 24 * 3600 * 1000) {
   $table = 'rawdata';

   // one month range loads hourly data
   } elseif ($range < 15 * 24 * 3600 * 1000) {
   $table = 'rawdata1h';

   // one year range loads daily data
   } elseif ($range < 31 * 24 * 3600 * 1000) {
   $table = 'rawdata1d';

   // one year range loads daily data
   } elseif ($range < 52 * 7 * 24 * 3600 * 1000) {
   $table = 'rawdata1d';
   // greater range loads monthly data
   } else {
   $table = 'rawdata1h';
   }
  */

 $sql_old = "select t.datetime,t.data from (
Select unix_timestamp(date) * 1000 as datetime,$item as data FROM `weather`.`rawdata1d` order by datetime desc limit 14) t
order by t.datetime asc
";
$sql_old2 = "Select unix_timestamp(date) * 1000 as datetime,$item as data FROM `weather`.`rawdata` where date > (NOW() - INTERVAL 1 DAY) order by date asc";
$sql ="SELECT dateTime *1000 AS datetime, round($item,1) AS data FROM weewx.archive WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) AND UNIX_TIMESTAMP(NOW()) ORDER BY dateTime ASC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

 if ($item == "rainn")
     {
     $sql3    = "select $item as data from `weather`.`rawdata1d` order by date desc limit 14,1";
     $stmt2 = mysqli_prepare($link, $sql3);
     mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    $row2 = mysqli_fetch_assoc($result2);
    $data1 = round($row2['data'], 1);
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
        $data   = round($data, 1);
        $data2  = abs(round($data - $data1, 1));
         $rows[] = "[$datetime,$data2]";

        $data1 = round($data, 1);
         }
     }
 else
     {
     $rows = array();
     while ($row  = mysqli_fetch_assoc($result))
         {
         extract($row);
         $rows[] = "[$datetime,$data]";
         }
     }
     mysqli_free_result($result);
     mysqli_stmt_close($stmt);
 // print it
 if ($isJsonp) {
   header('Content-Type: text/javascript');
 } else {
   header('Content-Type: application/json');
 }

 echo "/* console.log(' sql=$sql ,start = $data, end = $end, startTime = $startTime, endTime = $endTime '); */";
 if ($isJsonp) {
   echo $callback . "([\n" . join(",\n", $rows) . "\n]);";
 } else {
   echo "[\n" . join(",\n", $rows) . "\n]";
 }

?>
