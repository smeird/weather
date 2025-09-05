<?php
require_once 'header.php';
require_once 'backend/climate-analysis.php';

$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$years = [];
$res = db_query("SELECT DISTINCT YEAR(FROM_UNIXTIME(dateTime)) AS year FROM archive ORDER BY year DESC");
while ($row = mysqli_fetch_assoc($res)) {
  $years[] = (int) $row['year'];
}
$data = get_climate_analysis($year);

$descriptions = [
  'mean' => 'Average temperature in °C.',
  'min' => 'Lowest temperature in °C.',
  'max' => 'Highest temperature in °C.',
  'seasonal_averages' => 'Average temperature for each season.',
  'total' => 'Total rainfall in millimetres.',
  'rain_days' => 'Days with at least 0.1 mm of rain.',
  'wet_days' => 'Days with at least 1 mm of rain.',
  'heavy_rain_days' => 'Days with at least 10 mm of rain.',
  'max_daily' => 'Largest single-day rainfall (mm).',
  'days_gt_90' => 'Number of days with humidity above 90%.',
  'days_lt_30' => 'Number of days with humidity below 30%.',
  'mean_speed' => 'Average wind speed in m/s.',
  'max_gust' => 'Strongest wind gust recorded (m/s).',
  'calm_frequency' => 'Fraction of observations with wind &lt;0.5 m/s.',
  'prevailing_direction' => 'Most frequent wind direction (degrees).',
  'heat_index_f' => 'Average heat index in °F.',
  'wind_chill_c' => 'Average wind chill in °C.',
  'climatological_summaries' => 'Monthly mean temperature and total rainfall for the current year.',
  'mean_temp' => 'Average temperature for the month (°C).',
  'total_rain' => 'Total rainfall for the month (mm).',
  'rain_p95' => 'Daily rainfall exceeded only 5% of the time (mm).',
];

function format_value($value) {
  if (is_numeric($value)) {
    return number_format((float) $value, 1);
  }
  if (is_array($value)) {
    $html = '<div class="space-y-1">';
    foreach ($value as $k => $v) {
      $html .= '<div><span class="font-medium">' . ucwords(str_replace('_', ' ', $k)) . ':</span> ';
      $html .= format_value($v) . '</div>';
    }
    $html .= '</div>';
    return $html;
  }
  return $value !== null && $value !== '' ? $value : '-';
}
?>
<div id="loading" class="fixed inset-0 flex items-center justify-center bg-white dark:bg-gray-900 z-50">
  <p>Loading climate analysis... <span id="load-time">0.0</span>s</p>
</div>
<div id="content" class="hidden">
  <h2 class="text-xl font-bold mb-4">Climate Analysis for <?php echo $year; ?></h2>
  <form method="get" class="mb-4 flex items-center space-x-2">
    <label for="year" class="font-medium">Year</label>
    <select id="year" name="year" class="p-2 rounded bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
      <?php foreach ($years as $y): ?>
        <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Go</button>
  </form>
  <div class="grid gap-4 md:grid-cols-2">
    <?php foreach ($data as $section => $metrics): ?>
      <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
        <h3 class="text-lg font-semibold mb-2"><?php echo ucwords(str_replace('_', ' ', $section)); ?></h3>
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="p-2 text-left">Metric</th>
              <th class="p-2 text-left">Value</th>
              <th class="p-2 text-left">Description</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($metrics as $name => $value): ?>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="p-2 font-medium align-top"><?php echo ucwords(str_replace('_', ' ', $name)); ?></td>
                <td class="p-2"><?php echo format_value($value); ?></td>
                <td class="p-2 text-gray-600 dark:text-gray-400"><?php echo $descriptions[$name] ?? '-'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
  const start = performance.now();
  const timerEl = document.getElementById('load-time');
  const interval = setInterval(() => {
    timerEl.textContent = ((performance.now() - start) / 1000).toFixed(1);
  }, 100);
  window.addEventListener('load', () => {
    clearInterval(interval);
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('content').classList.remove('hidden');
  });
</script>
<?php require 'footer.php'; ?>
