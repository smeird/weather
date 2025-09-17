<?php
$what = $_GET['WHAT'] ?? '';
$scale = $_GET['SCALE'] ?? '';
$type = $_GET['TYPE'] ?? '';
?>
<form action="/dynamic-graph.php" method="get" class="space-y-4 px-1 pt-4">
  <div>
    <label for="what" class="block text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-300 mb-2">Data</label>
    <select id="what" name="WHAT" class="w-full rounded-xl border border-slate-200/60 bg-white/70 px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400/60 focus:ring-offset-1 focus:ring-offset-white/60 dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-100 dark:focus:ring-blue-500/60">
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
      <option value="dewpoint">Dew Point</option>
      <option value="windchill">Wind Chill</option>
    </select>
  </div>
  <div>
    <label for="typey" class="block text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-300 mb-2">Graph Type</label>
    <select id="typey" name="TYPE" class="w-full rounded-xl border border-slate-200/60 bg-white/70 px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400/60 focus:ring-offset-1 focus:ring-offset-white/60 dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-100 dark:focus:ring-blue-500/60">
      <option value="STANDARD">Standard</option>
      <option value="MINMAX">Min &amp; Max</option>
      <option value="AVG">Average Range</option>
    </select>
  </div>
  <div>
    <label for="scale" class="block text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-300 mb-2">Time Scale</label>
    <select id="scale" name="SCALE" class="w-full rounded-xl border border-slate-200/60 bg-white/70 px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400/60 focus:ring-offset-1 focus:ring-offset-white/60 dark:border-slate-700/60 dark:bg-slate-900/50 dark:text-slate-100 dark:focus:ring-blue-500/60">
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
  <button type="submit" class="w-full rounded-full bg-gradient-to-r from-emerald-500 via-teal-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-900/30 transition-transform duration-300 hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-400/70">Select</button>
</form>
<script>
const vala = "<?php echo htmlspecialchars($what, ENT_QUOTES); ?>";
const valb = "<?php echo htmlspecialchars($scale, ENT_QUOTES); ?>";
const valc = "<?php echo htmlspecialchars($type, ENT_QUOTES); ?>";
if (vala) document.getElementById('what').value = vala;
if (valb) document.getElementById('scale').value = valb;
if (valc) document.getElementById('typey').value = valc;
</script>
