<?php
 include ('header.php');
 include ('dbconn.php');


echo " <div class=\"container\">

    <h1 class=\"display-1\">Max and Min</h1>

";

 $SQL22   = "SELECT
round(max(`archive`.`outTemp`), 1),
round(min(`archive`.`outTemp`), 1),
round(max(`archive`.`inTemp`), 1),
round(min(`archive`.`inTemp`), 1),
round(max(`archive`.`inHumidity`), 1),
round(min(`archive`.`inHumidity`), 1),
round(max(`archive`.`outHumidity`), 1),
round(min(`archive`.`outHumidity`), 1),
round(max(`archive`.`barometer`), 1),
round(min(`archive`.`barometer`), 1),
round(max(`archive`.`rain`)-min(`archive`.`rain`), 1)
FROM `weewx`.`archive` WHERE from_unixtime(dateTime) >= now() - INTERVAL 1 DAY;";
 $SQL3   = "SELECT
round(max(`archive`.`outTemp`), 1),
round(min(`archive`.`outTemp`), 1),
round(max(`archive`.`inTemp`), 1),
round(min(`archive`.`inTemp`), 1),
round(max(`archive`.`inHumidity`), 1),
round(min(`archive`.`inHumidity`), 1),
round(max(`archive`.`outHumidity`), 1),
round(min(`archive`.`outHumidity`), 1),
round(max(`archive`.`barometer`), 1),
round(min(`archive`.`barometer`), 1),
round(max(`archive`.`rain`)-min(`archive`.`rain`), 1)
FROM `weewx`.`archive`WHERE from_unixtime(dateTime) >= now() - INTERVAL 7 DAY;";
 $SQL4   = "SELECT
round(max(`archive`.`outTemp`), 1),
round(min(`archive`.`outTemp`), 1),
round(max(`archive`.`inTemp`), 1),
round(min(`archive`.`inTemp`), 1),
round(max(`archive`.`inHumidity`), 1),
round(min(`archive`.`inHumidity`), 1),
round(max(`archive`.`outHumidity`), 1),
round(min(`archive`.`outHumidity`), 1),
round(max(`archive`.`barometer`), 1),
round(min(`archive`.`barometer`), 1),
round(max(`archive`.`rain`)-min(`archive`.`rain`), 1)
FROM `weewx`.`archive`WHERE from_unixtime(dateTime) >= now() - INTERVAL 1 MONTH;";



$result = mysqli_query($link,$SQL22);
 if (!$result)
     {
     die('Invalid query: ' . mysqli_error().$SQL22);
     }
 while ($row = mysqli_fetch_row($result))
     {

     for ($i = 0; $i <= mysqli_num_fields($result); $i++)
         {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];

         }
     }


 echo "$<div class=\"card shadow mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Max Min last 24hrs</h4>
<p class=\"card-text\">
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div>
</p>
				</div>
				</div></div>

";

 $result3 = mysqli_query($link,$SQL3);
 if (!$result3)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result3))
     {

     for ($i = 0; $i <= mysqli_num_fields($result3); $i++)
         {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
         //$d11 = $row[11];
         }
     }


 echo "<div class=\"card  shadow mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Min Max Last 7 Days</h4>
   <p class=\"card-text\">
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div>
</p>
				</div></div></div>


";

 $result4 = mysqli_query($link,$SQL4);
 if (!$result4)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result4))
     {

     for ($i = 0; $i <= mysqli_num_fields($result4); $i++)
         {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
        // $d11 = $row[11];
         }
     }


 echo "<div class=\"card  shadow mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Min Max Last Month</h4>
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div></div>
					</div>
</div>


";

 mysqli_free_result($result);
?>
