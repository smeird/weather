<?php
// Generate wind rose data summarizing the frequency of wind speeds
// within different ranges for each direction.

$sql = "SELECT wind_dir,
    COUNT(CASE WHEN wind_ave>=0 AND wind_ave <=2 THEN 1 END) AS A,
    COUNT(CASE WHEN wind_ave>=2 AND wind_ave <=4 THEN 1 END) AS B,
    COUNT(CASE WHEN wind_ave>=4 AND wind_ave <=6 THEN 1 END) AS C,
    COUNT(CASE WHEN wind_ave>=6 AND wind_ave <=8 THEN 1 END) AS D
  FROM rawdata
  WHERE date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
  GROUP BY wind_dir";

 require_once 'dbconn.php';
 $result = db_query($sql);

$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
  extract($row);
  $rows[] = "[$wind_dir,$A,$B,$C,$D]";
}

// Output the data as a JavaScript callback
header('Content-Type: text/javascript');

echo "/* console.log(' sql=$sql '); */\n";
echo $callback . "([\n" . join(",\n", $rows) . "\n]);";
