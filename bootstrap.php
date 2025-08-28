<?php
$compression = ini_set('zlib.output_compression', '1');
if ($compression === false) {
  error_log('Failed to enable zlib.output_compression');
}

$compressionLevel = ini_set('zlib.output_compression_level', '6');
if ($compressionLevel === false) {
  error_log('Failed to set zlib.output_compression_level');
}
?>
