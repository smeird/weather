<?php
include('header.php');
$now = time();

// Determine the earliest available record in the database for slider range.
$earliest = $now - 31536000; // fallback to one year ago
$res = db_query("SELECT MIN(dateTime) AS min_dt FROM archive");
if ($row = mysqli_fetch_assoc($res)) {
  $earliest = (int) $row['min_dt'];
}
mysqli_free_result($res);
?>
<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Export Data</h1>
  </div>
  <div class="bg-white shadow rounded p-4">
    <p class="mb-4">Download weather observations as a gzipped JSON file.</p>
    <div class="mb-4 range-slider">
      <div class="relative h-2">
        <input id="startRange" type="range" min="<?php echo $earliest; ?>" max="<?php echo $now; ?>" value="<?php echo $earliest; ?>" step="86400" class="absolute top-0 left-0 w-full h-2 appearance-none bg-transparent">
        <input id="endRange" type="range" min="<?php echo $earliest; ?>" max="<?php echo $now; ?>" value="<?php echo $now; ?>" step="86400" class="absolute top-0 left-0 w-full h-2 appearance-none bg-transparent">
      </div>
      <div class="flex justify-between text-sm mt-6">
        <span id="startLabel"></span>
        <span id="endLabel"></span>
      </div>
    </div>
    <a id="downloadLink" href="backend/export-data.php" class="inline-block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"><i class="fas fa-download mr-2"></i>Download</a>
  </div>
</div>
<style>
  .range-slider input[type=range] {
    pointer-events: none;
  }
  .range-slider input[type=range]::-webkit-slider-thumb {
    pointer-events: all;
  }
  .range-slider input[type=range]::-moz-range-thumb {
    pointer-events: all;
  }
</style>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const start = document.getElementById('startRange');
    const end = document.getElementById('endRange');
    const startLabel = document.getElementById('startLabel');
    const endLabel = document.getElementById('endLabel');
    const download = document.getElementById('downloadLink');

    function update() {
      let s = Math.min(parseInt(start.value), parseInt(end.value));
      let e = Math.max(parseInt(start.value), parseInt(end.value));
      start.value = s;
      end.value = e;
      startLabel.textContent = new Date(s * 1000).toISOString().split('T')[0];
      endLabel.textContent = new Date(e * 1000).toISOString().split('T')[0];
      download.href = `backend/export-data.php?start=${s}&end=${e}`;
    }

    start.addEventListener('input', update);
    end.addEventListener('input', update);
    update();
  });
</script>
<?php include('footer.php'); ?>
