<?php
include('header.php');
require_once 'dbconn.php';

$SQLHOT = "SELECT
  ROUND(`archive`.`outTemp`, 1) AS temp,
  FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` = (SELECT MAX(`outTemp`) FROM `weewx`.`archive`);";

$SQLCOLD = "SELECT
  ROUND(`archive`.`outTemp`, 1) AS temp,
  FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS dt
FROM
  `weewx`.`archive`
WHERE
  `archive`.`outTemp` = (SELECT MIN(`outTemp`) FROM `weewx`.`archive`);";

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
?>

<div>
  <div class="flex flex-col sm:flex-row items-center justify-between mb-2">
    <h1 class="text-2xl text-gray-800">Records</h1>
  </div>

  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
  </div>
</div>

<?php
mysqli_close($link);
include('footer.php');
?>
