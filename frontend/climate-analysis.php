<?php
require_once 'header.php';
require_once 'backend/climate-analysis.php';
$data = get_climate_analysis();

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
<div id="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
  <p>Loading climate analysis... <span id="load-time">0.0</span>s</p>
</div>
<div id="content" class="hidden">
  <h2 class="text-xl font-bold mb-4">Climate Analysis</h2>
  <div class="grid gap-4 md:grid-cols-2">
    <?php foreach ($data as $section => $metrics): ?>
      <div class="bg-white shadow rounded p-4">
        <h3 class="text-lg font-semibold mb-2"><?php echo ucwords(str_replace('_', ' ', $section)); ?></h3>
        <table class="min-w-full text-sm">
          <tbody>
            <?php foreach ($metrics as $name => $value): ?>
              <tr class="border-b">
                <td class="p-2 font-medium align-top"><?php echo ucwords(str_replace('_', ' ', $name)); ?></td>
                <td class="p-2"><?php echo format_value($value); ?></td>
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
