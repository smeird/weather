<?php
$what = $_GET['WHAT'] ?? '';
$scale = $_GET['SCALE'] ?? '';
$type = $_GET['TYPE'] ?? '';
?>
<form action="/dynamic-graph.php" method="get" class="max-w-sm mx-auto space-y-3">
  <div>
    <label for="what" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Data</label>
    <select id="what" name="WHAT" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      <option value="outTemp">Outside Temperature</option>
      <option value="outHumidity">Outside Humidity</option>
      <option value="windSpeed">Wind Speed</option>
      <option value="windDir">Wind Direction</option>
      <option value="windGust">Wind Gust Speed</option>
      <option value="windGustDir">Wind Gust Direction</option>
      <option value="barometer">Barometer</option>
      <option value="rain">Rain</option>
      <option value="inTemp">Inside Temperature</option>
      <option value="inHumidity">Inside Humidity</option>
    </select>
  </div>
  <div>
    <label for="typey" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Graph Type</label>
    <select id="typey" name="TYPE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      <option value="STANDARD">Standard</option>
      <option value="MINMAX">Min &amp; Max</option>
    </select>
  </div>
  <div>
    <label for="scale" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time Scale</label>
    <select id="scale" name="SCALE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      <option value="hour">Hour</option>
      <option value="day">Day</option>
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
