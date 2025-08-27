<!DOCTYPE html>
<html>
    <head>
        <link rel="apple-touch-icon" href="icon.png" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
        <title>Smeird Weather</title>
        <link rel="stylesheet" href="../../iui/iui.css" type="text/css" />
        <link rel="stylesheet" href="../../iui/t/default/default-theme.css"  type="text/css"/>
        <script type="application/x-javascript" src="../../iui/iui.js"></script>
    </head>

    <body>
        <div class="toolbar">
            <h1 id="pageTitle"></h1>
            <a id="backButton" class="button" href="#"></a>
        </div>
        <ul id="nav" title="Select" selected="true">
            <li><a href="#current">Current</a></li>
            <li><a href="#last24">Last 24 Hrs</a></li>
            <li><a href="#lastw">Last Week</a></li>
            <li><a href="#lastm">Last Month</a></li>
        </ul>



        <?php
         include ('dbconn.php');

         $SQL    = "SELECT
`rawdata`.`ID`,
`rawdata`.`date`,
`rawdata`.`temp_out`,
`rawdata`.`temp_in`,
`rawdata`.`hum_out`,
`rawdata`.`hum_in`,
`rawdata`.`abs_pressure`,
`rawdata`.`wind_ave`,
`rawdata`.`wind_gust`,
`rawdata`.`wind_dir`,
`rawdata`.`rain`
FROM `weather`.`rawdata`
order by date desc limit 1
;";
         $result = mysqli_query($link,$SQL);
         if (!$result)
             {
             die('Invalid query: ' . mysqli_error());
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
                 $d11 = $row[11];
                 $d12 = $row[12];
                 }
             }
         $dp     = round(calculateDewPoint($d2, $d4), 2);
         $moon   = calculateMoonPhase(time(now));
         $wc     = round(convertTemperature(calculateWindChill(convertTemperature($d2,
                                                                                  'c',
                                                                                  'f'),
                                                                                  convertSpeed($d8,
                                                                                               'mps',
                                                                                               'mph')),
                                                                                               'f',
                                                                                               'c'),
                                                                                               2);
         $ss     = date_sunset(time(), SUNFUNCS_RET_STRING, 51.752646,
                               -0.325041, 90, 0);
         $sr     = date_sunrise(time(), SUNFUNCS_RET_STRING, 51.752646,
                                -0.325041, 90, 0);
         echo "
<div id=\"current\" title=\"Weather\" class=\"panel\">

	
			<div class=\"row\">
			<table width=95%><tr><td>Date</td> <td align=right>$d1</td></tr></table>
			</div>
		
			<div class=\"row\">
				<table width=95%><tr><td>Temperature Out</td> <td align=right>$d2 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Temperature Inside </td> <td align=right>$d3 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Humidity Out </td> <td align=right>$d4 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Humidity In</td> <td align=right>$d5 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Absolute Pressure</td> <td align=right>$d6</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Wind Average Speed</td> <td align=right>$d7 ms</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Wind Max Gust</td> <td align=right>$d8 ms</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Wind Direction</td> <td align=right>$d9 &#176</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Rain</td> <td align=right>$d10 mm</td></tr></table>
			</div>
					<div class=\"row\">
				<table width=95%><tr><td>Moon Phase</td> <td align=right>$moon[phase]</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Wind Chill</td> <td align=right>$wc &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Dew Point</td> <td align=right>$dp &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Sun Rise</td> <td align=right>$sr</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Sun Set</td> <td align=right>$ss</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td><a href=http://www.smeird.com/graph3.php target=_blank>Graph</a></td></tr></table>
			</div>
		
		</div>


";
// max min stuff
         $SQL2   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 1 DAY;";
         $result = mysqli_query($link,$SQL2);
         if (!$result)
             {
             die('Invalid query: ' . mysqli_error());
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
                 $d11 = $row[11];
                 $d12 = $row[12];
                 }
             }
         echo "
<div id=\"last24\" title=\"Last 24hrs\" class=\"panel\">



	
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature Out</td> <td align=right>$d0 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature Out </td> <td align=right>$d1 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature In</td> <td align=right>$d2 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature In </td> <td align=right>$d3 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity Out </td> <td align=right>$d4 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity Out </td> <td align=right>$d5 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity In</td> <td align=right>$d6 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity In</td> <td align=right>$d7 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Max Absolute Pressure</td> <td align=right>$d8</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Absolute Pressure</td> <td align=right>$d9</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Rain</td> <td align=right>$d10 mm</td></tr></table>
			</div>

		
		</div>


";
         $SQL3   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 7 DAY;";
         $result = mysqli_query($link,$SQL3);
         if (!$result)
             {
             die('Invalid query: ' . mysqli_error());
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
                 $d11 = $row[11];
                 $d12 = $row[12];
                 }
             }
         echo "
<div id=\"lastw\" title=\"Last Week\" class=\"panel\">



	
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature Out</td> <td align=right>$d0 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature Out </td> <td align=right>$d1 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature In</td> <td align=right>$d2 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature In </td> <td align=right>$d3 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity Out </td> <td align=right>$d4 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity Out </td> <td align=right>$d5 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity In</td> <td align=right>$d6 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity In</td> <td align=right>$d7 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Max Absolute Pressure</td> <td align=right>$d8</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Absolute Pressure</td> <td align=right>$d9</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Rain</td> <td align=right>$d10 mm</td></tr></table>
			</div>

	
		</div>

";

         $SQL4   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 1 MONTH;";
         $result = mysqli_query($link,$SQL4);
         if (!$result)
             {
             die('Invalid query: ' . mysqli_error());
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
                 $d11 = $row[11];
                 $d12 = $row[12];
                 }
             }
         echo "
<div id=\"lastm\" title=\"Last Month\" class=\"panel\">



	
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature Out</td> <td align=right>$d0 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature Out </td> <td align=right>$d1 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Temperature In</td> <td align=right>$d2 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Temperature In </td> <td align=right>$d3 &#8451</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity Out </td> <td align=right>$d4 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity Out </td> <td align=right>$d5 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Max Humidity In</td> <td align=right>$d6 %</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Humidity In</td> <td align=right>$d7 %</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Max Absolute Pressure</td> <td align=right>$d8</td></tr></table>
			</div>
			<div class=\"row\">
				<table width=95%><tr><td>Min Absolute Pressure</td> <td align=right>$d9</td></tr></table>
			</div>

			<div class=\"row\">
				<table width=95%><tr><td>Rain</td> <td align=right>$d10 mm</td></tr></table>
			</div>

	
		</div>

";

         function calculateDewPoint($temperature, $humidity)
             {
             if ($temperature >= 0)
                 {
                 $a = 7.5;
                 $b = 237.3;
                 }
             else
                 {
                 $a = 7.6;
                 $b = 240.7;
                 }

             // First calculate saturation steam pressure for temperature
             $SSP = 6.1078 * pow(10, ($a * $temperature) / ($b + $temperature));

             // Steam pressure
             $SP = $humidity / 100 * $SSP;

             $v = log($SP / 6.1078, 10);

             return ($b * $v / ($a - $v));
             }

         function calculateWindChill($temperature, $speed)
             {
             // temp in F speed in mph!!!
             return (35.74 + 0.6215 * $temperature - 35.75 * pow($speed, 0.16) + 0.4275
                 * $temperature * pow($speed, 0.16));
             }

         function convertTemperature($temperature, $from, $to)
             {
             if ($temperature == "N/A")
                 {
                 return $temperature;
                 }

             $from = strtolower($from{0});
             $to   = strtolower($to{0});

             $result = array(
              "f" => array(
               "f" => $temperature,"c" => ($temperature - 32) / 1.8
              ),
              "c" => array(
               "f" => 1.8 * $temperature + 32,"c" => $temperature
              )
             );

             return $result[$from][$to];
             }

         function convertSpeed($speed, $from, $to)
             {
             $from = strtolower($from);
             $to   = strtolower($to);

             static $factor;
             static $beaufort;
             if (!isset($factor))
                 {
                 $factor = array(
                  "mph" => array(
                   "mph" => 1,"kmh" => 1.609344,"kt"  => 0.8689762,"mps" => 0.44704,
                   "fps" => 1.4666667
                  ),
                  "kmh" => array(
                   "mph" => 0.6213712,"kmh" => 1,"kt"  => 0.5399568,"mps" => 0.2777778,
                   "fps" => 0.9113444
                  ),
                  "kt"  => array(
                   "mph" => 1.1507794,"kmh" => 1.852,"kt"  => 1,"mps" => 0.5144444,
                   "fps" => 1.6878099
                  ),
                  "mps" => array(
                   "mph" => 2.2369363,"kmh" => 3.6,"kt"  => 1.9438445,"mps" => 1,
                   "fps" => 3.2408399
                  ),
                  "fps" => array(
                   "mph" => 0.6818182,"kmh" => 1.09724,"kt"  => 0.5924838,"mps" => 0.3048,
                   "fps" => 1
                  )
                 );

                 // Beaufort scale, measurements are in knots
                 $beaufort = array(
                  1,3,6,10,
                  16,21,27,33,
                  40,47,55,63
                 );
                 }

             if ($from == "bft")
                 {
                 return false;
                 }
             elseif ($to == "bft")
                 {
                 $speed = round($speed * $factor[$from]["kt"], 0);
                 for ($i = 0; $i < sizeof($beaufort); $i++)
                     {
                     if ($speed <= $beaufort[$i])
                         {
                         return $i;
                         }
                     }
                 return sizeof($beaufort);
                 }
             else
                 {
                 return ($speed * $factor[$from][$to]);
                 }
             }

         function calculateMoonPhase($date)
             {
             // Date must be timestamp for now
             if (!is_int($date))
                 {
                 return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_MOONFUNCS_DATE_INVALID,
                                                     __FILE__, __LINE__);
                 }

             $moon = array();

             $year  = date("Y", $date);
             $month = date("n", $date);
             $day   = date("j", $date);
             $hour  = date("G", $date);
             $min   = date("i", $date);
             $sec   = date("s", $date);

             $age       = 0.0; // Moon's age in days from New Moon
             $distance  = 0.0; // Moon's distance in Earth radii
             $latitude  = 0.0; // Moon's ecliptic latitude in degrees
             $longitude = 0.0; // Moon's ecliptic longitude in degrees
             $phase     = "";  // Moon's phase
             $zodiac    = "";  // Moon's zodiac
             $icon      = "";  // The icon to represent the moon phase

             $YY = 0;
             $MM = 0;
             $DD = 0;
             $HH = 0;
             $A  = 0;
             $B  = 0;
             $JD = 0;
             $IP = 0.0;
             $DP = 0.0;
             $NP = 0.0;
             $RP = 0.0;

             // Calculate Julian Daycount to the second
             if ($month > 2)
                 {
                 $YY = $year;
                 $MM = $month;
                 }
             else
                 {
                 $YY = $year - 1;
                 $MM = $month + 12;
                 }

             $DD = $day;
             $HH = $hour / 24 + $min / 1440 + $sec / 86400;

             // Check for Gregorian date and adjust JD appropriately
             if (($year * 10000 + $month * 100 + $day) >= 15821015)
                 {
                 $A = floor($YY / 100);
                 $B = 2 - $A + floor($A / 4);
                 }

             $JD = floor(365.25 * ($YY + 4716)) + floor(30.6001 * ($MM + 1)) + $DD
                 + $HH + $B - 1524.5;

             // Calculate moon's age in days
             $IP  = ($JD - 2451550.1) / 29.530588853;
             if (($IP  = $IP - floor($IP)) < 0) $IP++;
             $age = $IP * 29.530588853;

             switch ($age)
                 {
                 case ($age < 1.84566):
                     $phase = "New";
                     break;
                 case ($age < 5.53699):
                     $phase = "Waxing Crescent";
                     break;
                 case ($age < 9.22431):
                     $phase = "First Quarter";
                     break;
                 case ($age < 12.91963):
                     $phase = "Waxing Gibbous";
                     break;
                 case ($age < 16.61096):
                     $phase = "Full";
                     break;
                 case ($age < 20.30224):
                     $phase = "Waning Gibbous";
                     break;
                 case ($age < 23.99361):
                     $phase = "Last Quarter";
                     break;
                 case ($age < 27.68493):
                     $phase = "Waning Crescent";
                     break;
                 default:
                     $phase = "New";
                 }

             // Convert phase to radians
             $IP = $IP * 2 * pi();

             // Calculate moon's distance
             $DP       = ($JD - 2451562.2) / 27.55454988;
             if (($DP       = $DP - floor($DP)) < 0) $DP++;
             $DP       = $DP * 2 * pi();
             $distance = 60.4 - 3.3 * cos($DP) - 0.6 * cos(2 * $IP - $DP) - 0.5 * cos(2
                     * $IP);

             // Calculate moon's ecliptic latitude
             $NP       = ($JD - 2451565.2) / 27.212220817;
             if (($NP       = $NP - floor($NP)) < 0) $NP++;
             $NP       = $NP * 2 * pi();
             $latitude = 5.1 * sin($NP);

             // Calculate moon's ecliptic longitude
             $RP        = ($JD - 2451555.8) / 27.321582241;
             if (($RP        = $RP - floor($RP)) < 0) $RP++;
             $longitude = 360 * $RP + 6.3 * sin($DP) + 1.3 * sin(2 * $IP - $DP) + 0.7
                 * sin(2 * $IP);
             if ($longitude >= 360) $longitude -= 360;

             switch ($longitude)
                 {
                 case ($longitude < 33.18):
                     $zodiac = "Pisces";
                     break;
                 case ($longitude < 51.16):
                     $zodiac = "Aries";
                     break;
                 case ($longitude < 93.44):
                     $zodiac = "Taurus";
                     break;
                 case ($longitude < 119.48):
                     $zodiac = "Gemini";
                     break;
                 case ($longitude < 135.30):
                     $zodiac = "Cancer";
                     break;
                 case ($longitude < 173.34):
                     $zodiac = "Leo";
                     break;
                 case ($longitude < 224.17):
                     $zodiac = "Virgo";
                     break;
                 case ($longitude < 242.57):
                     $zodiac = "Libra";
                     break;
                 case ($longitude < 271.26):
                     $zodiac = "Scorpio";
                     break;
                 case ($longitude < 302.49):
                     $zodiac = "Sagittarius";
                     break;
                 case ($longitude < 311.72):
                     $zodiac = "Capricorn";
                     break;
                 case ($longitude < 348.58):
                     $zodiac = "Aquarius";
                     break;
                 default:
                     $zodiac = "Pisces";
                 }

             $moon["age"]       = round($age, 2);
             $moon["distance"]  = round($distance, 2);
             $moon["latitude"]  = round($latitude, 2);
             $moon["longitude"] = round($longitude, 2);
             $moon["zodiac"]    = $zodiac;
             $moon["phase"]     = $phase;
             $moon["icon"]      = (floor($age) - 1) . "";

             return $moon;
             }
        ?>
    </body>
</html>