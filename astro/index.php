<?php
 include ('../header.php');
 include ('moon.php');

 $singledate = $_GET['DATE'];
$detailcolor = $_GET['DATECOLOR'];
?>


    <title>Smeird Astro Weather</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>document.getElementById("navname").innerHTML = "Wheathampstead Astro";</script>

<style>
a:hover {
    /* REMOVE drop Shadow when hovering only */
    text-decoration: none;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
    shadow: none;
}
</style>





<?php

function getrag($value){
if ($value > 30) {$color="red-500";}
if ($value >= 9 && $value <= 30 ) {$color="yellow-500";}
if ($value < 10 ) {$color="green-500";}
return $color;
}
function centrag($value){
if ($value > 30) {$color="<td class=\\\"px-4 py-2 border-l-4 border-red-500\\\">$value</td>";}
if ($value >= 9 && $value <= 30 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-yellow-500\\\">$value</td>";}
if ($value < 10 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-green-500\\\">$value</td>";}
return $color;
}
function seeingrag($value){
  if ($value > 6) {
    $color = "border-green-500";
  }
  if ($value <= 6 && $value >= 4) {
    $color = "border-yellow-500";
  }
  if ($value < 4) {
    $color = "border-red-500";
  }
  return $color;
}

function tenrag($value){
if ($value > 6) {$color="<td class=\\\"px-4 py-2 border-l-4 border-green-500 text-green-600\\\"><span class=text-sm>$value</span></td>";}
if ($value <= 6 && $value >= 4 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-yellow-500\\\">$value</td>";}
if ($value < 4 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-red-500\\\">$value</td>";}
return $color;
}

function thirtyrag($value){
if ($value > 18) {$color="<td class=\\\"px-4 py-2 border-l-4 border-green-500 text-green-600\\\"><span class=text-sm>$value</span></td>";}
if ($value <= 18 && $value >= 4 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-yellow-500\\\">$value</td>";}
if ($value < 12 ) {$color="<td class=\\\"px-4 py-2 border-l-4 border-red-500\\\">$value</td>";}
return $color;
}

function getdetail($date,$json) {
  $html="<div class=\\\"overflow-x-auto\\\"><table class=\\\"min-w-full text-center text-sm divide-y divide-gray-200 border border-gray-300\\\"><thead class=\\\"bg-gray-50 text-sm\\\"><tr>
  <th>Date</th>
  <th>Total Cloud</th>
  <th>Combined Index</th>
  <th>Seeing Index</th>
  <th>Pickering Index</th>
  <th>Trans Index</th>


  <th>Low Cloud</th>
<th>Medium Cloud</th>
<th>High Cloud</th>


</tr></thead><tbody>
  ";
  foreach ($json['metcheckData']['forecastLocation']['forecast'] as $key=>$value) {

    $hourrag=seeingrag($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex']);
    $html.="<tr class=\\\"border-l-4 $hourrag\\\">";
    $detaildate=$json['metcheckData']['forecastLocation']['forecast'][$key]['utcTime'];
    $nicedate = date('l', strtotime(substr($detaildate,0,10))).' '.substr($detaildate,11,5);
  if ($date==substr($detaildate,0,10)) {
      if ($json['metcheckData']['forecastLocation']['forecast'][$key]['dayOrNight']=='N') {





      $html.='<td>'.$nicedate.'</td>';
       $html.=centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['totalcloud']);
       $html.=thirtyrag(round(($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex'] + $json['metcheckData']['forecastLocation']['forecast'][$key]['pickeringIndex'] + $json['metcheckData']['forecastLocation']['forecast'][$key]['transIndex'])/1),1);
       $html.=tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['seeingIndex']);
       $html.=tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['pickeringIndex']);
       $html.=tenrag($json['metcheckData']['forecastLocation']['forecast'][$key]['transIndex']);
       $html.=centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['lowcloud']);
       $html.=centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['medcloud']);
       $html.=centrag($json['metcheckData']['forecastLocation']['forecast'][$key]['highcloud']);


     }
      }
      $html.="</tr>";
    }
    $html.="</tbody></table></div><br>
    <p>
    Seeing : This calculation uses the total cloud cover along with turbulence in the atmosphere and low level wind speed to give an index from 0 to 10 where 0 is worst and 10 is best seeing conditions. (experimental)
    </p>
    <p>Transp.: This calculation uses the total amount of water in the atmosphere above your location. It shows the relative humidity in the column of air from 0 to 30,000ft and gives an index from 0 to 10 where 0 is worst and 10 is best seeing conditions.(experimental)
    </p>
    <p>Pickering: This calculation uses the amount of low and mid level turbulence above your location as well as calculating differences in wind speed and temperature at various levels in the atmosphere to show how much distortion the light rays will experience between 0 and 30,000ft and gives an index from 0 to 10 where 0 is worst and 10 is best seeing conditions.(experimental)
    </p>
    ";
    return $html;
}



function getJson($url) {
     // cache files are created like cache/abcdef123456...
     $cacheFile = '/tmp' . DIRECTORY_SEPARATOR . md5($url);

     if (file_exists($cacheFile)) {
         $fh = fopen($cacheFile, 'r');
         $cacheTime = trim(fgets($fh));

         // if data was cached recently, return cached data
         if ($cacheTime > strtotime('-60 minutes')) {
             return fread($fh,filesize($cacheFile));
         }
         // else delete cache file
         fclose($fh);
         unlink($cacheFile);
     }
     $json = file_get_contents($url);
     $fh = fopen($cacheFile, 'w');
     fwrite($fh, time() . "\n");
     fwrite($fh, $json);
     fclose($fh);
     return $json;
 }

function nightview($date,$cloudArray) {
$html= "<div class=\\\"h-0.5 w-full bg-gray-200\\\">";
foreach ($cloudArray as $keydate=>$covervalue){
  $dayinquestion=substr($keydate,0,10);
  $date=substr($date,0,10);
    if ($dayinquestion==$date) {
      $ragcolor=getrag($covervalue);
        $html.=  "<div class=\\\"h-0.5 w-full bg-$ragcolor\\\"></div>";
        }
      }
$html.= "</div>";
return $html;
}
    $data = getJson('http://ws1.metcheck.com/ENGINE/v9_0/json.asp?lat=51.81&lon=-0.29&lid=58143&Fc=As');
    $json = json_decode($data, true);

//print_r($json);
$newArray = array();
$cloudArray = array();
    foreach ($json['metcheckData']['forecastLocation']['forecast'] as $key=>$value) {
    if ($json['metcheckData']['forecastLocation']['forecast'][$key]['dayOrNight']=='N') {
        $newdatea=$json['metcheckData']['forecastLocation']['forecast'][$key]['utcTime'];
        $myDateTime = date('Y-m-d l', strtotime(substr($newdatea,0,10)));
      $iterationValue = $json['metcheckData']['forecastLocation']['forecast'][$key]['totalcloud'];
      $descr = $json['metcheckData']['forecastLocation']['forecast'][$key]['iconName'];
      $cloudArray[$newdatea]=$iterationValue;
      $Count=1;
      $dateKey=$myDateTime;
      if(array_key_exists($dateKey, $newArray))
      {
          // If we've already added this date to the new array, add the value
          $newArray[$dateKey]['value'] += $iterationValue;
          $newArray[$dateKey]['count'] += $Count;
          $newArray[$dateKey]['avg'] = $newArray[$dateKey]['value'] / $newArray[$dateKey]['count'] ;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunrise'];
          $newArray[$dateKey]['sunset'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunset'];
      }
      else
      {
          // Otherwise create a new element with datetimeobject as key
          $newArray[$dateKey]['count'] = $Count;
          $newArray[$dateKey]['value'] = $iterationValue;
          //$newArray[$dateKey]['avg'] = $avg ;
          $newArray[$dateKey]['descr'] = $descr;
          $newArray[$dateKey]['sunrise'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunrise'];
          $newArray[$dateKey]['sunset'] = $json['metcheckData']['forecastLocation']['forecast'][$key]['sunset'];
      }
    }
    }
//echo '<pre>';
//nl2br(print_r($cloudArray));
//echo '</pre>';

if(isset($singledate)){
$detail=getdetail($singledate,$json);
echo "

<div class=\"container-fluid\">
<h1 class=\"h4 mb-0 text-gray-800\">Detail</h1>

<div class=\"card border-l-4 border-$detailcolor bg-white shadow rounded p-4 mb-4\">
                <!-- Card Header - Dropdown -->
                <div class=\"card-header py-3 d-flex flex-row align-items-center justify-content-between\">
                  <h5 class=\"m-0 fw-bold text-primary\">$singledate</h5>
                  <div class=\"dropdown no-arrow\">
                    <a class=\"dropdown-toggle\" href=\"#\" role=\"button\" id=\"dropdownMenuLink\" data-bs-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                      <i class=\"fas fa-ellipsis-v fa-sm fa-fw text-gray-400\" aria-hidden=\"true\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-end shadow animated--fade-in\" aria-labelledby=\"dropdownMenuLink\">
                      <div class=\"dropdown-header\">Veiws:</div>
                      <a class=\"dropdown-item\" href=\"graph3.php?FULL=1#graph\">Full Screen</a>

                    </div>
                  </div>
                </div>
                <!-- Card Body -->
                <div class=\"card-body\">
                $detail
                </div>
</div></div>
                ";

}


echo '<div class="container-fluid">

<div class="d-sm-flex align-items-center justify-content-between mb-2">
<h1 class="h4 mb-0 text-gray-800">Cloud Forcast for the next 10 days</h1> </div>
<div class="row">';

foreach ($newArray as $keya=>$valuea){
$simple=date('l d', strtotime(substr($keya,0,10)));
$simple2=date('l', strtotime(substr($keya,0,10)));
$graphic=nightview($keya,$cloudArray);
$SS=$valuea['sunset'];
$SR=$valuea['sunrise'];
$cloud=round($valuea['avg'],0);
$color=getrag($cloud);
$wd=$valuea['descr'];
$day=substr($keya,0,10);
$moon=(Moon::calculateMoonTimes(date('m', strtotime(substr($keya,0,10))),date('d', strtotime(substr($keya,0,10))), date('Y', strtotime(substr($keya,0,10))), 51.8, -0.3));
$MR=gmdate("H:i", $moon->moonrise);
$MS=gmdate("H:i", $moon->moonset);
echo "

<div class=\"col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2\">
        <div class=\"card border-l-4 border-$color bg-white shadow rounded p-2\">
            <a href=\"/astro/index.php?DATE=$day&DATECOLOR=$color\">
                  <div class=\"card-body\">
                        <div class=\"row  no-gutters\" style=\"margin-left: -25px;\";>
                              <div class=\"col-1  me-2\">

                                <div class=\"vertical  text-center text-$color text-xs\" style=\"margin-left: 10px; margin-top: 45px;\">$simple</div>

                              </div>

                              <div class=\"col  me-2\">
                                            <div class=\"row no-gutters\">
                                                <div class=\"col me-2\"><div class=\"text-xs fw-bold text-gray-900 text-uppercase mb-1\">$cloud% Cloud<br> $wd</div></div>

                                                <div class=\"col col-sm me-2\">

                                                    <div class=\"row text-end\">
                                                      <div class=\"fw-light small text-gray-900 text-end\">  Night $SS -> $SR</div>
                                                        </div>
                                                        <div class=\"row text-end\">

                                                        <div class=\"fw-light small text-gray-500 text-end\">Moon $MS -> $MR</div>
                                                        </div>

                                                        </div>

                                            </div>


                                            <div class=\"row  no-gutters align-items-center\">
                                            <div class=\"col me-2\">$graphic</div>


                                        </div>
                                </div>

                        </div>
                </div>
          </a>
    </div>
</div>
";

}




?>
</div>
