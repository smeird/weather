<?php
 include ('header.php');
 include ('dbconn.php');


echo " <div class=\"container\">  <h1 class=\"display-4\">Records</h1>
";
 $SQLHOT  = "SELECT 
 ROUND(`archive`.`outTemp`, 1) AS 'Max Temperature',
 FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS 'Date and Time'
FROM
 `weewx`.`archive`
WHERE
 `archive`.`outTemp` = (SELECT MAX(`outTemp`) FROM `weewx`.`archive`);
";
 $SQLCOLD = "SELECT 
 ROUND(`archive`.`outTemp`, 1) AS 'Min Temperature',
 FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS 'Date and Time'
FROM
 `weewx`.`archive`
WHERE
 `archive`.`outTemp` = (SELECT MIN(`outTemp`) FROM `weewx`.`archive`);
";



 $SQLLONGHOT  = "SELECT 
 COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM 
 `weewx`.`archive`
WHERE 
 `archive`.`outTemp` > 35
;
";
 $SQLLONGCOLD = "SELECT 
 COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM 
 `weewx`.`archive`
WHERE 
 `archive`.`outTemp` < -5
;
";
 
 
$result8 = mysqli_query($link,$SQLHOT);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error().$SQLHOT);
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         $d1 = $row[1];
         }
     }
 echo "<div class=\"card mb-3\"><div class=\"card-header\">Records</div><div class=\"card-body\"><p class=\"card-text\"><li>Hottest Day on record  : $d0 &#8451 on the  $d1";
 echo "";
 $result8 = mysqli_query($link,$SQLCOLD);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         $d1 = $row[1];
         }
     }
 echo "<li>Coldest Day on record  : $d0 &#8451 on the  $d1";


 $result8 = mysqli_query($link,$SQLLONGHOT);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         }
     }
 echo "<li>Number of Days Over 35&#8451 : $d0 days";




 $result8 = mysqli_query($link,$SQLLONGCOLD);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         }
     }
 echo "<li>Number of Days Under -5&#8451  :$d0 days</p></div>";
 echo "</div>";
 mysqli_free_result($result8);
?>