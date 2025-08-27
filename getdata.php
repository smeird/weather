<?php
// Ingest new weather station data from an XML file and insert it into the
// MySQL database, replicating to a remote server as needed.

if (file_exists('/var/fowsr.xml')) {
  // Clean up the poorly formatted XML file
  $cont = file_get_contents('/var/fowsr.xml');
  $cont = str_replace('</ws><ws>', '', $cont);
  $cont = preg_replace('/[\n\r]+/', '', $cont);
  $cont = str_replace('<ws></ws>', '', $cont);
  $cont = str_replace('</ws><ws>', '', $cont);
  $fixed = str_replace('">', '"></wsd>', $cont);

  $xml = simplexml_load_string($fixed);
} else {
  exit('Failed to open /var/fowsr.xml.');
}

// Build value tuples for each record
$trans = $xml->wsd;
$values = array();
foreach ($trans as $tran) {
  $values[] = "(\"$tran[date]\",\"$tran[delay]\",\"$tran[temp_out]\",\"$tran[temp_in]\",\"$tran[hum_out]\",\"$tran[hum_in]\",\"$tran[abs_pressure]\",\"$tran[wind_ave]\",\"$tran[wind_gust]\",\"$tran[wind_dir]\",\"$tran[rain]\")";
}

$SQLa = "INSERT INTO `weather`.`rawdata` (`date`,`delay`,`temp_out`,`temp_in`,`hum_out`,`hum_in`,`abs_pressure`,`wind_ave`,`wind_gust`,`wind_dir`,`rain`) VALUES ";
$SQLd = " ON DUPLICATE KEY UPDATE date = date";
$SQL = $SQLa . join(',', $values) . $SQLd;

// Connect to the local database
require_once 'dbconn.php';
db_query($SQL);

// Replicate to a remote database
$link2 = mysqli_connect('accounts.smeird.com', 'root', '92987974', 'weather');
if (! $link2) {
  die('Not connected : ' . mysqli_connect_error());
}

db_query($SQL, $link2);
?>

