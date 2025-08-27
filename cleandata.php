<?php

 include ('header.php');
 echo "start clean up";
//Script to remove bad data from data sets
 $sql0 = "delete from rawdataminmax1h where temp_out_min < -100";
 $sql1 = "delete from rawdataminmax1d where temp_out_min < -100";
 $sql6 = "delete from rawdataminmax1m where temp_out_min < -100";
 $sql2 = "delete from rawdata where temp_out < -100;";
 $sql3 = "delete from rawdata1h where temp_out < -100;";
 $sql4 = "delete from rawdata1d where temp_out < -100;";
 $sql7 = "delete from rawdata1m where temp_out < -100;";
//$sql5="delete FROM weather.rawdata where temp_out = 32.7;";

 require_once 'dbconn.php';
 db_query($sql0);
 db_query($sql1);
 db_query($sql2);
 db_query($sql3);
 db_query($sql4);
 db_query($sql5);
 db_query($sql6);
 db_query($sql7);
 echo "All done.";
?>
