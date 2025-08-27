<?php
// Establish a reusable MySQL connection using environment variables
// or the default development values.
global $link;

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'webuser';
$db_pass = getenv('DB_PASSWORD') ?: 'WebUserPass0!';
$db_name = getenv('DB_NAME') ?: 'weewx';

// Create the connection and stop execution if it fails
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (! $link) {
  die('Connect Error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error());
}
?>
