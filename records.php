<?php
include('header.php');
include('dbconn.php');

$SQLHOT = "SELECT
 ROUND(`archive`.`outTemp`, 1) AS 'Max Temperature',
 FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS 'Date and Time'
FROM
 `weewx`.`archive`
WHERE
 `archive`.`outTemp` = (SELECT MAX(`outTemp`) FROM `weewx`.`archive`);";

$SQLCOLD = "SELECT
 ROUND(`archive`.`outTemp`, 1) AS 'Min Temperature',
 FROM_UNIXTIME(`archive`.`dateTime`, '%Y-%m-%d %H:%i:%s') AS 'Date and Time'
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
 $hot = mysqli_fetch_row($resultHot);
 mysqli_free_result($resultHot);

 $resultCold = db_query($SQLCOLD);
 $cold = mysqli_fetch_row($resultCold);
 mysqli_free_result($resultCold);

 $resultLongHot = db_query($SQLLONGHOT);
 $daysOver35 = mysqli_fetch_row($resultLongHot)[0];
 mysqli_free_result($resultLongHot);

 $resultLongCold = db_query($SQLLONGCOLD);
 $daysUnderMinus5 = mysqli_fetch_row($resultLongCold)[0];
 mysqli_free_result($resultLongCold);
?>
</head>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-2">
    <h1 class="h3 mb-0 text-gray-800">Records</h1>
  </div>

  <div class="card mb-3">
    <div class="card-header">Records</div>
    <div class="card-body">
      <p class="card-text">
        <li>Hottest Day on record  : <?php echo $hot[0]; ?> &#8451; on the <?php echo $hot[1]; ?></li>
        <li>Coldest Day on record  : <?php echo $cold[0]; ?> &#8451; on the <?php echo $cold[1]; ?></li>
        <li>Number of Days Over 35&#8451 : <?php echo $daysOver35; ?> days</li>
        <li>Number of Days Under -5&#8451  : <?php echo $daysUnderMinus5; ?> days</li>
      </p>
    </div>
  </div>
</div>

<?php mysqli_close($link); ?>

