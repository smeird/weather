<?php
 include ('header.php');
 include ('dbconn.php');
 $what = $_GET['WHAT'];}
 $scale = $_GET['SCALE'];}
 $type = $_GET['TYPE'];}


 switch ($what) {
     case "rain":
         $gt = "column";
     case "windDir":
         $gt = "scatter";
     default:
         $gt = "spline";
 }

 switch ($scale) {
     case "day":
     $scalesql = "WHERE FROM_UNIXTIME(dateTime) > (NOW() - INTERVAL 1 DAY) ORDER BY dateTime ASC";
     case "week":
     $scalesql = "WHERE FROM_UNIXTIME(dateTime) > (NOW() - INTERVAL 7 DAY) ORDER BY dateTime ASC";
     case "month":
     $scalesql = "WHERE FROM_UNIXTIME(dateTime) > (NOW() - INTERVAL 1 MONTH) ORDER BY dateTime ASC";
     case "year":
     $scalesql = "WHERE FROM_UNIXTIME(dateTime) > (NOW() - INTERVAL 1 YEAR) ORDER BY dateTime ASC";
     default:
    $scalesql = "WHERE FROM_UNIXTIME(dateTime) > (NOW() - INTERVAL 1 DAY) ORDER BY dateTime ASC";
 }



 $sql ="SELECT dateTime *1000 AS datetime, round($what,2) AS data FROM weewx.archive $scalesql";

 $result = mysql_query($sql) or die(mysql_error());

 $rows = array();
 while ($row  = mysql_fetch_assoc($result))
     {
     extract($row);
     $rows[] = "[$datetime,$data]";
     }

 echo "([\n" . join(",\n", $rows) . "\n]);";
 ?>
