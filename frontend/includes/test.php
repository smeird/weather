<?php
$what = $_GET['WHAT'] ?? '';
$scale = $_GET['SCALE'] ?? '';
$type = $_GET['TYPE'] ?? '';
?>
<form action="/pages/newgraph.php" method="get" class="space-y-3">
  <div>
    <label for="what" class="block text-sm font-medium text-blue-900">Data</label>
    <select id="what" name="WHAT" class="mt-1 block w-full rounded border-blue-300 bg-white p-2 text-blue-900 focus:border-blue-500 focus:ring focus:ring-blue-500">
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
  </div>
  <div>
    <label for="typey" class="block text-sm font-medium text-blue-900">Graph Type</label>
    <select id="typey" name="TYPE" class="mt-1 block w-full rounded border-blue-300 bg-white p-2 text-blue-900 focus:border-blue-500 focus:ring focus:ring-blue-500">
      <option value="Standard">Standard</option>
      <option value="MINMAX">Min &amp; Max</option>
    </select>
  </div>
  <div>
    <label for="scale" class="block text-sm font-medium text-blue-900">Time Scale</label>
    <select id="scale" name="SCALE" class="mt-1 block w-full rounded border-blue-300 bg-white p-2 text-blue-900 focus:border-blue-500 focus:ring focus:ring-blue-500">
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
  </div>
  <button type="submit" class="w-full rounded border border-green-700 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-700 hover:text-white">Select</button>
</form>
<script>
const vala = "<?php echo htmlspecialchars($what, ENT_QUOTES); ?>";
const valb = "<?php echo htmlspecialchars($scale, ENT_QUOTES); ?>";
const valc = "<?php echo htmlspecialchars($type, ENT_QUOTES); ?>";
if (vala) document.getElementById('what').value = vala;
if (valb) document.getElementById('scale').value = valb;
if (valc) document.getElementById('typey').value = valc;
</script>

