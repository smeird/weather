<?php
$what = isset($_GET['WHAT']) ? $_GET['WHAT'] : null;
$scale = isset($_GET['SCALE']) ? $_GET['SCALE'] : null;
$type = isset($_GET['TYPE']) ? $_GET['TYPE'] : null;
?>

<form action="/newgraph.php" method="get">
  <div class="form-inline">
    <select id="what" name=WHAT class="browser-default custom-select">
      <option value="outTemp">Outside Temperature</option>
      <option value="outHumidity">Out Side Humidity</option>
      <option value="windSpeed">Wind Speed</option>
      <option value="windDir">Wind Direction</option>
      <option value="windGust">Wind Gust Speed</option>
      <option value="windGustDir">Wind Gust Direction</option>
      <option value="barometer">Barometer</option>
      <option value="rain">Rain</option>
      <option value="rainRate">Rain Rate</option>
      <option value="dewpoint">Dew Point</option>
      <option value="windchill">Wind chill</option>
      <option value="consBatteryVoltage">Console Battery Voltage</option>
      <option value="inTemp">Inside Temperature</option>
      <option value="inHumidity">Inside Humidity</option>
    </select>

    <select id="typey" name=TYPE class="custom-select">
      <option value="Standard">Standard</option>
      <option value="MINMAX">Min & Max</option>
    </select>

    <select id="scale" name=SCALE class="browser-default custom-select">
      <option value="hour">Hour</option>
      <option value="12hour">12 Hour</option>
      <option value="Day">Day</option>
      <option value="48">48hrs</option>
      <option value="week">Week</option>
      <option value="month">Month</option>
      <option value="qtr">Qtr</option>
      <option value="6m">6 M</option>
      <option value="year">Year</option>
      <option value="all">ALL</option>
    </select>

    <button class="form-inline btn btn-outline-success" type="submit">Select</button>

  </div>
</form>
<script type='text/javascript'>
        const vala = "<?php echo htmlspecialchars($what, ENT_QUOTES); ?>";
        const valb = "<?php echo htmlspecialchars($scale, ENT_QUOTES); ?>";
        const valc = "<?php echo htmlspecialchars($type, ENT_QUOTES); ?>";
        
        if (vala) {
            document.querySelector('#what [value="' + vala + '"]').selected = true;
        }

        if (valb) {
            document.querySelector('#scale [value="' + valb + '"]').selected = true;
        }

        if (valc) {
            document.querySelector('#typey [value="' + valc + '"]').selected = true;
        }
    </script>