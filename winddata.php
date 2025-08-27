<?php

 $sql = "SELECT wind_dir,
     COUNT(CASE WHEN wind_ave>=0 AND wind_ave <=2 then wind_ave ELSE NULL end) AS 'A',
	COUNT(CASE WHEN wind_ave>=2 AND wind_ave <=4 then wind_ave ELSE NULL end) AS 'B',
	COUNT(CASE WHEN wind_ave>=4 AND wind_ave <=6 then wind_ave ELSE NULL end) AS 'C',
	COUNT(CASE WHEN wind_ave>=6 AND wind_ave <=8 then wind_ave ELSE NULL end) AS 'D'
   FROM
  rawdata
group by wind_dir";


 include ('dbconn.php');
 $result = mysqli_query($link,$sql) or die(mysqli_error());

 $rows = array();
 while ($row  = mysqli_fetch_assoc($result))
     {
     extract($row);
     $rows[] = "[$wind_dir,$A,$B,$C,$D]";
     }

// print it
 header('Content-Type: text/javascript');

 echo "/* console.log(' sql=$sql '); */\n";
 echo $callback . "([\n" . join(",\n", $rows) . "\n]);";
?>
