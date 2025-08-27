<?php
 include __DIR__ . '/../includes/header.php';
?>


<!DOCTYPE html>
 <html>
   <head>
   <title></title>
   <meta charset="utf-8" />
   <script type="text/javascript" src="/assets/vxgplayer-1.8.31/vxgplayer-1.8.31.min.js"></script>
   <link href="/assets/vxgplayer-1.8.31/vxgplayer-1.8.31.min.css" rel="stylesheet" />
 </head>
  <body>
    <div  class="vxgplayer"
    id="vxg_media_player1"
    width="640"
    height="480"
    url="rtsp://10.0.179.16:7447/5ee682bb4f0c8f57ed89534c_0"
    nmf-src="/assets/vxgplayer-1.8.31/pnacl/Release/media_player.nmf"
    nmf-path="media_player.nmf"
    useragent-prefix="MMP/3.0"
    latency="10000"
    autohide="2"
    volume="0.7"
    avsync
    autostart
    controls
    mute
    aspect-ratio
    aspect-ratio-mode="1"
    auto-reconnect
    connection-timeout="5000"
    connection-udp="0"
    custom-digital-zoom></div>
 </body>
 </html>
<?php

 include __DIR__ . '/../includes/footer.php';
?>
