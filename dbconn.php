<?php
global $link;
 $db_host = getenv('DB_HOST') ?: 'localhost';
 $db_user = getenv('DB_USER') ?: 'webuser';
 $db_pass = getenv('DB_PASSWORD') ?: 'WebUserPass0!';
 $db_name = getenv('DB_NAME') ?: 'weewx';
 $link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
?>
