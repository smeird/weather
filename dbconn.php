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

/**
 * Execute a SQL query using the shared connection.
 *
 * @param string $sql      The SQL statement to run.
 * @param mysqli|null $conn Optional connection; defaults to global link.
 *
 * @return mysqli_result
 */
function db_query(string $sql, $conn = null)
{
  global $link;
  $handle = $conn ?: $link;
  $result = mysqli_query($handle, $sql);
  if (! $result) {
    die('Query Error: ' . mysqli_error($handle) . ' SQL: ' . $sql);
  }
  return $result;
}
?>
