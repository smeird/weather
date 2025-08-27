<?php

 if (file_exists('/var/fowsr.xml'))
     {
     //fix shit xml file
     $cont  = file_get_contents('/var/fowsr.xml');
     $cont  = str_replace('</ws><ws>', '', $cont);
     $cont  = preg_replace('/[\n\r]+/', '', $cont);
     $cont  = str_replace('<ws></ws>', '', $cont);
     $cont  = str_replace('</ws><ws>', '', $cont);
     $fixed = str_replace('">', '"></wsd>', $cont);

     $xml = simplexml_load_string($fixed);
//$xml = new SimpleXMLElement($fixed);
     //print_r($xml);
     }
 else
     {
     exit('Failed to open test.xml.');
     }

//function stop(){
 $trans = $xml->wsd;
 foreach ($trans as $tran)
     {
     //$tdate = date("Y-m-d", strtotime(substr($trandate, 0, 8)));
     //update db
     //  <wsd date="2012-11-18 22:15:01" delay="0.0" hum_in="66.0" temp_in="19.600" hum_out="87.0" temp_out="3.600" abs_pressure="1003.500" wind_ave="0.0" wind_gust="0.0" wind_dir="90.0" rain="21.300" status="00">

     $SQLc = "(\"$tran[date]\",\"$tran[delay]\",\"$tran[temp_out]\",\"$tran[temp_in]\",\"$tran[hum_out]\",\"$tran[hum_in]\",\"$tran[abs_pressure]\",\"$tran[wind_ave]\",\"$tran[wind_gust]\",\"$tran[wind_dir]\",\"$tran[rain]\")";
     //$SQLc=preg_replace('/[^(\x20-\x7F)]*/','',$SQLc);
     $SQLb = $SQLb . ',' . $SQLc;
     $SQLb = trim($SQLb, ',');
     //echo "\n".$SQLc;
     }
 $SQLa = "INSERT INTO `weather`.`rawdata` 	(`date`,`delay`,`temp_out`,`temp_in`,`hum_out`,`hum_in`,`abs_pressure`,`wind_ave`,`wind_gust`,`wind_dir`,`rain`) VALUES ";
 $SQLd = " ON DUPLICATE KEY UPDATE date = date";
 $SQL  = $SQLa . $SQLb . $SQLd;
//echo $SQL;
// connect to the

 $link = mysqli_connect('localhost', 'root', '92987974');
 if (!$link)
     {
     die('Not connected : ' . mysqli_error());
     }

// make foo the current db
 $db_selected = mysqli_select_db($link,'weather');
 if (!$db_selected)
     {
     die('Can\'t use foo : ' . mysqli_error());
     }

 $result = mysqli_query($link,$SQL);
 if (!$result)
     {
     die('<div id=\"billboard\"> <p>Invalid query: ' . mysqli_error());
     }

 $link2 = mysqli_connect('accounts.smeird.com', 'root', '92987974');
 if (!$link2)
     {
     die('Not connected : ' . mysqli_error());
     }

// make foo the current db
 $db_selected = mysqli_select_db($link,'weather');
 if (!$db_selected)
     {
     die('Can\'t use foo : ' . mysqli_error());
     }

 $result = mysqli_query($link,$SQL);
 if (!$result)
     {
     die('<div id=\"billboard\"> <p>Invalid query: ' . mysqli_error());
     }
?>
