
<?php
  include ('header.php');
 include ('dbconn.php');
 
 if(isset($_POST['DATE'])){$daterange  = $_POST['DATE'];}
 if(isset($_POST['DATEEND'])){$daterange2 = $_POST['DATEEND'];}

 echo " <div class=\"container\"><legend>Windrose</legend><div class=\"card mb-3\"><h4 class=\"card-header\">
Current Status
</h4>
<div class=card-body>
<p>Select TimeScales </p>";
 echo "<form action=\"/windrose.php\" method=\"POST\">";
 echo selecttag('SELECT	EXTRACT(YEAR_MONTH FROM date) AS daterange FROM	rawdata GROUP BY	 EXTRACT(YEAR_MONTH FROM date)	order by EXTRACT(YEAR_MONTH FROM date) desc','DATE');
 echo "From --> To ";
 echo selecttag('SELECT	EXTRACT(YEAR_MONTH FROM date) AS daterange FROM	rawdata GROUP BY	 EXTRACT(YEAR_MONTH FROM date)	order by EXTRACT(YEAR_MONTH FROM date) desc','DATEEND');
 echo "<input class=\"btn\" type=\"submit\" value=\"  Select Date  \"></form>";
 
?>
</div></div>
<div class="card mb-3">
<div id="container2"></div>


<script type="text/javascript">
$(function() {

            // Parse the data from an inline table using the Highcharts Data plugin
            $('#container2').highcharts({
                data: {
                    table: 'freqq',
                    startRow: 0,
                    endRow: 16,
                    endColumn: 9
                },
                chart: {
                    polar: true,
                    type: 'column'
                },
                title: {
                    text: 'Wind rose for st Albans'
                },
                subtitle: {
                    text: 'Source: Smeird'
                },
                pane: {
                    size: '95%'
                },
                legend: {
                    reversed: true,
                    align: 'center',
                    verticalAlign: 'bottom',
                    y: 00,
                    layout: 'horizontal'
                },
                xAxis: {
                    tickmarkPlacement: 'on'
                },
                yAxis: {
                    min: 0,
                    endOnTick: false,
                    showLastLabel: true,
                    title: {
                        text: 'Count'
                    },
                    labels: {
                        formatter: function() {
                            return this.value + ' count';
                        }
                    }
                },
                tooltip: {
                    valueSuffix: ' count',
                    followPointer: true
                },
                plotOptions: {
                    series: {
                        stacking: 'normal',
                        shadow: true,
                        groupPadding: 0,
                        pointPlacement: 'on'
                    }
                }
            });
        });
</script>


<?php

 $sql = "SELECT wind_dir,
   	COUNT(CASE WHEN wind_ave>=2 AND wind_ave <=2.5 then wind_ave ELSE NULL end) AS 'E',
	COUNT(CASE WHEN wind_ave>=2.5 AND wind_ave <=3 then wind_ave ELSE NULL end) AS 'F',
	COUNT(CASE WHEN wind_ave>=3 AND wind_ave <=3.5 then wind_ave ELSE NULL end) AS 'G',
	COUNT(CASE WHEN wind_ave>=3.5 AND wind_ave <=14 then wind_ave ELSE NULL end) AS 'H',
	COUNT(CASE WHEN wind_ave>=1.5 AND wind_ave <=2 then wind_ave ELSE NULL end) AS 'D',
	COUNT(CASE WHEN wind_ave>=1 AND wind_ave <=1.5 then wind_ave ELSE NULL end) AS 'C',
	COUNT(CASE WHEN wind_ave>=0.5 AND wind_ave <=1 then wind_ave ELSE NULL end) AS 'B',
	COUNT(CASE WHEN wind_ave>=0 AND wind_ave <=0.5 then wind_ave ELSE NULL end) AS 'A'
   FROM
  rawdata

group by wind_dir";




 if (isset($daterange))
     {
	  $sql1 = "SELECT wind_dir,
   	COUNT(CASE WHEN wind_ave>=2 AND wind_ave <=2.5 then wind_ave ELSE NULL end) AS 'E',
	COUNT(CASE WHEN wind_ave>=2.5 AND wind_ave <=3 then wind_ave ELSE NULL end) AS 'F',
	COUNT(CASE WHEN wind_ave>=3 AND wind_ave <=3.5 then wind_ave ELSE NULL end) AS 'G',
	COUNT(CASE WHEN wind_ave>=3.5 AND wind_ave <=14 then wind_ave ELSE NULL end) AS 'H',
	COUNT(CASE WHEN wind_ave>=1.5 AND wind_ave <=2 then wind_ave ELSE NULL end) AS 'D',
	COUNT(CASE WHEN wind_ave>=1 AND wind_ave <=1.5 then wind_ave ELSE NULL end) AS 'C',
	COUNT(CASE WHEN wind_ave>=0.5 AND wind_ave <=1 then wind_ave ELSE NULL end) AS 'B',
	COUNT(CASE WHEN wind_ave>=0 AND wind_ave <=0.5 then wind_ave ELSE NULL end) AS 'A'
   FROM
  rawdata
  where
  EXTRACT(YEAR_MONTH FROM date) BETWEEN $daterange AND $daterange2
group by wind_dir";
     $sql = $sql1;
     }
 include ('dbconn.php');
 $result = mysqli_query($link,$sql) or die(mysqli_error());
 echo "</div><div class=\"card mb-3\">
 <table  id=freqq class=\"table table-hover table-sm table-responsive-sm\">";
 echo "<thead class=\"thead-inverse\"><tr>
 <th>Direction</th>
 <th >3.5-4ms</th>
 <th >3-3.5ms</th>
 <th >2.5-3ms</th>
 <th >2-2.5ms</th>
 <th >1.5-2ms</th>
 <th >1-1.5ms</th>
 <th >0.5-1ms</th>
 <th >0-0.5ms</th>
 </tr></thead><tbody>";
 
 $rows   = array();
 while ($row    = mysqli_fetch_assoc($result))
     {
     extract($row);
     if ($wind_dir == 0)
         {
         $wind_dir = "N";
         }
     if ($wind_dir == 22.5)
         {
         $wind_dir = "NNE";
         }
     if ($wind_dir == 45)
         {
         $wind_dir = "NE";
         }
     if ($wind_dir == 67.5)
         {
         $wind_dir = "ENE";
         }
     if ($wind_dir == 90)
         {
         $wind_dir = "E";
         }
     if ($wind_dir == 112.5)
         {
         $wind_dir = "ESE";
         }
     if ($wind_dir == 135)
         {
         $wind_dir = "SE";
         }
     if ($wind_dir == 157.5)
         {
         $wind_dir = "SSE";
         }
     if ($wind_dir == 180)
         {
         $wind_dir = "S";
         }
     if ($wind_dir == 202.5)
         {
         $wind_dir = "SSW";
         }
     if ($wind_dir == 225)
         {
         $wind_dir = "SW";
         }
     if ($wind_dir == 247.5)
         {
         $wind_dir = "WSW";
         }
     if ($wind_dir == 270)
         {
         $wind_dir = "W";
         }
     if ($wind_dir == 292.5)
         {
         $wind_dir = "WNW";
         }
     if ($wind_dir == 315)
         {
         $wind_dir = "NW";
         }
     if ($wind_dir == 337.5)
         {
         $wind_dir = "NNW";
         }


     echo "<tr>
        <td class=dir>$wind_dir</td>
        <td class=data>$H</td>
        <td class=data>$G</td>
        <td class=data>$F</td>
        <td class=data>$E</td>
        <td class=data>$D</td>
        <td class=data>$C</td>
        <td class=data>$B</td>
        <td class=data>$A</td>
        </tr>";
     }

 echo "</tbody></table></div>";

 function selecttag($SQL, $name)
     {
     {
         include ('dbconn.php');
         //connect to a db
$title="Select date";
         $resultg = mysqli_query($link,$SQL);
         if (!$resultg)
             {
             die('Invalid query: ' . mysqli_error());
             } $html = "<select class=input-form style=\"width: 200px\" title=\"$title\" name=\"$name\">";
         while ($row  = mysqli_fetch_row($resultg))
             {
             for ($i = 0; $i <= mysqli_num_fields($resultg); $i++)
                 {
                 $id   = $row[0];
                // $name = $row[1];
                // $desc = $row[2];
                 }
             $html.="<option value=\"$id\" title=\"$id\">$id</option> ";
             }
         //echo $selected;
         //close the connection
     }
     $html.="</select>";
     return $html;
     mysqli_free_result($resultg);
     }
mysqli_free_result($result);
?>
