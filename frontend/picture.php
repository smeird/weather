<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include('header.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="/js/garden-image.js?v=<?php echo asset_version('js/garden-image.js'); ?>" defer></script>
<div class="site-workspace">
  <header class="workspace-header"><div><span class="workspace-eyebrow">Visual conditions</span><h1>Live garden view</h1><p>A current visual check of cloud, visibility and surface conditions at the station.</p></div><a class="workspace-badge" href="/picture.php"><i class="fas fa-rotate"></i> Refresh image</a></header>
  <div class="workspace-panel">
    <div class="workspace-panel-head"><div><h2>Latest station snapshot</h2><p>Delivered live over MQTT</p></div><span class="workspace-badge" data-garden-camera-status data-state="waiting"><i class="fas fa-circle text-green-500"></i> <span data-garden-camera-label>Waiting for feed</span></span></div>
    <img data-garden-image src="/images/snap.jpeg?<?php echo time(); ?>" alt="Latest webcam snapshot" class="block w-full h-auto">
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.SmeirdGardenImage) {
    window.SmeirdGardenImage.connect({ topic: 'weather/vegimage' });
  }
});
</script>
<?php include('footer.php');
