<?php
// PostgreSQL connection shared by the weather application. Production uses
// peer authentication over the local Unix socket as the `weather` OS user.
global $link;

$db_host = getenv('DB_HOST') ?: '/var/run/postgresql';
$db_user = getenv('DB_USER') ?: 'weather';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'weewx';

try {
  $link = new PDO(
    "pgsql:host={$db_host};dbname={$db_name}",
    $db_user,
    $db_pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $error) {
  die('Database connection failed');
}

class DbStatement
{
  public PDOStatement $statement;
  public array $parameters = [];

  public function __construct(PDOStatement $statement)
  {
    $this->statement = $statement;
  }
}

function db_query(string $sql, $conn = null): PDOStatement
{
  global $link;
  return ($conn ?: $link)->query($sql);
}

function db_prepare($conn, string $sql): DbStatement
{
  return new DbStatement($conn->prepare($sql));
}

function db_stmt_bind_param(DbStatement $statement, string $types, &...$values): bool
{
  $statement->parameters = array_values($values);
  return true;
}

function db_stmt_execute(DbStatement $statement): bool
{
  return $statement->statement->execute($statement->parameters);
}

function db_stmt_get_result(DbStatement $statement): PDOStatement
{
  return $statement->statement;
}

function db_stmt_close(DbStatement $statement): bool
{
  $statement->statement->closeCursor();
  return true;
}

function db_fetch_assoc(PDOStatement $result): array|false
{
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row === false) return false;
  $legacyNames = [
    'datetime' => 'dateTime', 'usunits' => 'usUnits',
    'intemp' => 'inTemp', 'outtemp' => 'outTemp',
    'inhumidity' => 'inHumidity', 'outhumidity' => 'outHumidity',
    'windspeed' => 'windSpeed', 'winddir' => 'windDir',
    'windgust' => 'windGust', 'windgustdir' => 'windGustDir',
    'rainrate' => 'rainRate', 'extratemp1' => 'extraTemp1',
    'extratemp2' => 'extraTemp2', 'extratemp3' => 'extraTemp3',
    'mintime' => 'minTime', 'maxtime' => 'maxTime',
    'maxtemp' => 'maxTemp', 'mintemp' => 'minTemp',
    'raintotal' => 'rainTotal', 'totalrain' => 'totalRain',
  ];
  foreach ($legacyNames as $postgresName => $legacyName) {
    if (array_key_exists($postgresName, $row) && !array_key_exists($legacyName, $row)) {
      $row[$legacyName] = $row[$postgresName];
    }
  }
  return $row;
}

function db_fetch_row(PDOStatement $result): array|false
{
  return $result->fetch(PDO::FETCH_NUM);
}

function db_free_result(PDOStatement $result): void
{
  $result->closeCursor();
}

function db_close($conn): void
{
  // PDO closes automatically at request completion.
}
