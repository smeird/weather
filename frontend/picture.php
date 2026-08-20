<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include('header.php');
?>
<div class="site-workspace">
  <header class="workspace-header"><div><span class="workspace-eyebrow">Visual conditions</span><h1>Live garden view</h1><p>A current visual check of cloud, visibility and surface conditions at the station.</p></div><a class="workspace-badge" href="/picture.php"><i class="fas fa-rotate"></i> Refresh image</a></header>
  <div class="workspace-panel">
    <div class="workspace-panel-head"><div><h2>Latest station snapshot</h2><p>Cache disabled · loaded <?php echo date('H:i:s'); ?></p></div><span class="workspace-badge"><i class="fas fa-circle text-green-500"></i> Current</span></div>
    <img src="/images/snap.jpeg?<?php echo time(); ?>" alt="Latest webcam snapshot" class="block w-full h-auto">
  </div>
</div>
<?php include('footer.php');
