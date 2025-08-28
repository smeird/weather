<?php
include('header.php');
?>
<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Export Data</h1>
  </div>
  <div class="bg-white shadow rounded p-4">
    <p class="mb-4">Download weather observations as a gzipped JSON file.</p>
    <a href="backend/export-data.php" class="inline-block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"><i class="fas fa-download mr-2"></i>Download</a>
  </div>
</div>
<?php include('footer.php');
