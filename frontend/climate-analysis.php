<?php
require_once 'header.php';
require_once 'backend/climate-analysis.php';
$data = get_climate_analysis();
?>
<div class="bg-white shadow rounded p-4">
  <h2 class="text-xl font-bold mb-4">Climate Analysis</h2>
  <?php foreach ($data as $section => $metrics): ?>
    <h3 class="text-lg font-semibold mt-4"><?php echo ucwords(str_replace('_', ' ', $section)); ?></h3>
    <table class="min-w-full text-sm">
      <tbody>
        <?php foreach ($metrics as $name => $value): ?>
          <tr class="border-b">
            <td class="p-2 font-medium"><?php echo ucwords(str_replace('_', ' ', $name)); ?></td>
            <td class="p-2">
              <?php echo is_array($value) ? json_encode($value) : $value; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
</div>
<?php require 'footer.php'; ?>
