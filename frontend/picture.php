<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include('header.php');
?>
<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Webcam</h1>
  </div>
  <div class="bg-white shadow rounded p-4">
    <img src="/images/snap.jpeg?<?php echo time(); ?>" alt="Latest webcam snapshot" class="w-full h-auto rounded">
  </div>
</div>
<?php include('footer.php');
