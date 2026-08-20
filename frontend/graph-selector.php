<?php
$what = $_GET['WHAT'] ?? 'outTemp';
$scale = $_GET['SCALE'] ?? 'day';
$type = $_GET['TYPE'] ?? 'STANDARD';
$scales = ['hour' => '1H', 'day' => '24H', '48' => '48H', 'week' => '7D', 'month' => '1M', 'qtr' => '3M', '6m' => '6M', 'year' => '1Y', 'all' => 'All'];
?>
<section class="chart-studio" aria-labelledby="chart-studio-title">
  <div class="chart-studio-header">
    <span class="chart-studio-icon"><i class="fas fa-chart-area" aria-hidden="true"></i></span>
    <span><strong id="chart-studio-title">Chart studio</strong><small>Build a focused trend view</small></span>
  </div>
  <form action="/dynamic-graph.php" method="get">
    <label class="studio-field"><span>Sensor</span>
      <select name="WHAT">
        <option value="outTemp">Outside temperature</option><option value="outHumidity">Outside humidity</option>
        <option value="windSpeed">Wind speed</option><option value="windDir">Wind direction</option>
        <option value="windGust">Wind gust</option><option value="windGustDir">Gust direction</option>
        <option value="barometer">Barometer</option><option value="rain">Rain</option>
        <option value="inTemp">Inside temperature</option><option value="inHumidity">Inside humidity</option>
        <option value="dewpoint">Dew point</option><option value="windchill">Wind chill</option>
      </select>
    </label>
    <fieldset class="studio-range"><legend>Range</legend><div class="studio-range-grid">
      <?php foreach ($scales as $value => $label) { ?>
        <label><input type="radio" name="SCALE" value="<?php echo htmlspecialchars($value, ENT_QUOTES); ?>"<?php echo $scale === $value ? ' checked' : ''; ?>><span><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span></label>
      <?php } ?>
    </div></fieldset>
    <div class="studio-footer">
      <label class="studio-field studio-mode"><span>View</span><select name="TYPE"><option value="STANDARD">Standard</option><option value="MINMAX">Min / max</option><option value="AVG">Average range</option></select></label>
      <button type="submit"><span>Open chart</span><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
    </div>
  </form>
</section>
<script>
(function () {
  var sensor = document.querySelector('.chart-studio select[name="WHAT"]');
  var mode = document.querySelector('.chart-studio select[name="TYPE"]');
  if (sensor) sensor.value = <?php echo json_encode($what); ?>;
  if (mode) mode.value = <?php echo json_encode($type); ?>;
})();
</script>
