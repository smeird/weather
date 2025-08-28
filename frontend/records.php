<?php
include('header.php');
require_once '../dbconn.php';

$SQLHOT = "SELECT
  ROUND(archive.outTemp, 1) AS temp,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.outTemp DESC
LIMIT 1";

$SQLCOLD = "SELECT
  ROUND(archive.outTemp, 1) AS temp,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.outTemp ASC
LIMIT 1";

$SQLLONGHOT = "SELECT
  COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` > 35;";

$SQLLONGCOLD = "SELECT
  COUNT(DISTINCT DATE(FROM_UNIXTIME(dateTime)))
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` < -5;";

$SQLGUST = "SELECT
  ROUND(archive.windGust, 1) AS gust,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.windGust DESC
LIMIT 1";

$SQLRAINRATE = "SELECT
  ROUND(archive.rainRate, 1) AS rate,
  FROM_UNIXTIME(archive.dateTime, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  weewx.archive
ORDER BY
  archive.rainRate DESC
LIMIT 1";

$resultHot = db_query($SQLHOT);
$hot = mysqli_fetch_assoc($resultHot);
mysqli_free_result($resultHot);

$resultCold = db_query($SQLCOLD);
$cold = mysqli_fetch_assoc($resultCold);
mysqli_free_result($resultCold);

$resultLongHot = db_query($SQLLONGHOT);
$daysOver35 = mysqli_fetch_row($resultLongHot)[0];
mysqli_free_result($resultLongHot);

$resultLongCold = db_query($SQLLONGCOLD);
$daysUnderMinus5 = mysqli_fetch_row($resultLongCold)[0];
mysqli_free_result($resultLongCold);

$resultGust = db_query($SQLGUST);
$gust = mysqli_fetch_assoc($resultGust);
mysqli_free_result($resultGust);

$resultRainRate = db_query($SQLRAINRATE);
$rainRate = mysqli_fetch_assoc($resultRainRate);
mysqli_free_result($resultRainRate);
?>

<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Records</h1>
  </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <a class="block" href="dynamic-graph.php?WHAT=outTemp&SCALE=day&DATE=<?php echo date('Y-m-d', strtotime($hot['dt'])); ?>">
        <div class="bg-white border-l-4 border-red-500 shadow rounded p-4">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-red-500 uppercase mb-1">Hottest Day</div>
              <div class="text-xl font-bold text-gray-800"><?php echo $hot['temp']; ?> &#8451;</div>
              <div class="text-sm text-gray-500"><?php echo $hot['dt']; ?></div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-temperature-high fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </a>

      <a class="block" href="dynamic-graph.php?WHAT=outTemp&SCALE=day&DATE=<?php echo date('Y-m-d', strtotime($cold['dt'])); ?>">
        <div class="bg-white border-l-4 border-blue-500 shadow rounded p-4">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-blue-500 uppercase mb-1">Coldest Day</div>
              <div class="text-xl font-bold text-gray-800"><?php echo $cold['temp']; ?> &#8451;</div>
              <div class="text-sm text-gray-500"><?php echo $cold['dt']; ?></div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-temperature-low fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </a>

    <div class="bg-white border-l-4 border-yellow-500 shadow rounded p-4">
      <div class="flex items-center">
        <div class="flex-grow mr-2">
          <div class="text-xs font-bold text-yellow-500 uppercase mb-1">Days &gt; 35&#8451;</div>
          <div class="text-xl font-bold text-gray-800"><?php echo $daysOver35; ?></div>
        </div>
        <div class="flex-shrink-0">
          <i class="fas fa-sun fa-2x text-gray-300"></i>
        </div>
      </div>
    </div>

    <div class="bg-white border-l-4 border-cyan-500 shadow rounded p-4">
      <div class="flex items-center">
        <div class="flex-grow mr-2">
          <div class="text-xs font-bold text-cyan-500 uppercase mb-1">Days &lt; -5&#8451;</div>
          <div class="text-xl font-bold text-gray-800"><?php echo $daysUnderMinus5; ?></div>
        </div>
        <div class="flex-shrink-0">
          <i class="fas fa-snowflake fa-2x text-gray-300"></i>
        </div>
      </div>
    </div>

      <a class="block" href="dynamic-graph.php?WHAT=windGust&SCALE=day&DATE=<?php echo date('Y-m-d', strtotime($gust['dt'])); ?>">
        <div class="bg-white border-l-4 border-green-500 shadow rounded p-4">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-green-500 uppercase mb-1">Highest Wind Gust</div>
              <div class="text-xl font-bold text-gray-800"><?php echo $gust['gust']; ?> m/s</div>
              <div class="text-sm text-gray-500"><?php echo $gust['dt']; ?></div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-wind fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </a>

      <a class="block" href="dynamic-graph.php?WHAT=rainRate&SCALE=day&DATE=<?php echo date('Y-m-d', strtotime($rainRate['dt'])); ?>">
        <div class="bg-white border-l-4 border-indigo-500 shadow rounded p-4">
          <div class="flex items-center">
            <div class="flex-grow mr-2">
              <div class="text-xs font-bold text-indigo-500 uppercase mb-1">Highest Rain Rate</div>
              <div class="text-xl font-bold text-gray-800"><?php echo $rainRate['rate']; ?> mm/h</div>
              <div class="text-sm text-gray-500"><?php echo $rainRate['dt']; ?></div>
            </div>
            <div class="flex-shrink-0">
              <i class="fas fa-cloud-showers-heavy fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </a>
  </div>
</div>

<?php
mysqli_close($link);
include('footer.php');
?>
