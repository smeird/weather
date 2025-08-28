<?php
include('header.php');
require_once 'dbconn.php';

 function fetchStats($sql) {
   $result = db_query($sql);
   $row = mysqli_fetch_row($result);
   mysqli_free_result($result);
   return $row;
}

$daySql = "SELECT round(max(`archive`.`outTemp`), 1), round(min(`archive`.`outTemp`), 1), round(max(`archive`.`inTemp`), 1), round(min(`archive`.`inTemp`), 1), round(max(`archive`.`inHumidity`), 1), round(min(`archive`.`inHumidity`), 1), round(max(`archive`.`outHumidity`), 1), round(min(`archive`.`outHumidity`), 1), round(max(`archive`.`barometer`), 1), round(min(`archive`.`barometer`), 1), round(max(`archive`.`rain`)-min(`archive`.`rain`), 1) FROM `weewx`.`archive` WHERE from_unixtime(dateTime) >= now() - INTERVAL 1 DAY;";
$weekSql = "SELECT round(max(`archive`.`outTemp`), 1), round(min(`archive`.`outTemp`), 1), round(max(`archive`.`inTemp`), 1), round(min(`archive`.`inTemp`), 1), round(max(`archive`.`inHumidity`), 1), round(min(`archive`.`inHumidity`), 1), round(max(`archive`.`outHumidity`), 1), round(min(`archive`.`outHumidity`), 1), round(max(`archive`.`barometer`), 1), round(min(`archive`.`barometer`), 1), round(max(`archive`.`rain`)-min(`archive`.`rain`), 1) FROM `weewx`.`archive` WHERE from_unixtime(dateTime) >= now() - INTERVAL 7 DAY;";
$monthSql = "SELECT round(max(`archive`.`outTemp`), 1), round(min(`archive`.`outTemp`), 1), round(max(`archive`.`inTemp`), 1), round(min(`archive`.`inTemp`), 1), round(max(`archive`.`inHumidity`), 1), round(min(`archive`.`inHumidity`), 1), round(max(`archive`.`outHumidity`), 1), round(min(`archive`.`outHumidity`), 1), round(max(`archive`.`barometer`), 1), round(min(`archive`.`barometer`), 1), round(max(`archive`.`rain`)-min(`archive`.`rain`), 1) FROM `weewx`.`archive` WHERE from_unixtime(dateTime) >= now() - INTERVAL 1 MONTH;";

 $day = fetchStats($daySql);
 $week = fetchStats($weekSql);
 $month = fetchStats($monthSql);
?>
</head>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-2">
    <h1 class="h3 mb-0 text-gray-800">Max and Min</h1>
  </div>

  <div class="card shadow mb-3">
    <div class="card-body">
      <h4 class="card-title">Max Min last 24hrs</h4>
      <div class="row">
        <div class="col-sm-3">Max Temperature Out <?php echo $day[0]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature Out <?php echo $day[1]; ?> &#8451;</div>
        <div class="col-sm-3">Max Temperature In <?php echo $day[2]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature In <?php echo $day[3]; ?> &#8451;</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Humidity In <?php echo $day[4]; ?> %</div>
        <div class="col-sm-3">Min Humidity In <?php echo $day[5]; ?> %</div>
        <div class="col-sm-3">Max Humidity Out <?php echo $day[6]; ?> %</div>
        <div class="col-sm-3">Min Humidity Out <?php echo $day[7]; ?> %</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Pressure <?php echo $day[8]; ?></div>
        <div class="col-sm-3">Min Pressure <?php echo $day[9]; ?></div>
        <div class="col-sm-3">Total Rain <?php echo $day[10]; ?> mm</div>
      </div>
    </div>
  </div>

  <div class="card shadow mb-3">
    <div class="card-body">
      <h4 class="card-title">Max Min last 7 days</h4>
      <div class="row">
        <div class="col-sm-3">Max Temperature Out <?php echo $week[0]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature Out <?php echo $week[1]; ?> &#8451;</div>
        <div class="col-sm-3">Max Temperature In <?php echo $week[2]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature In <?php echo $week[3]; ?> &#8451;</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Humidity In <?php echo $week[4]; ?> %</div>
        <div class="col-sm-3">Min Humidity In <?php echo $week[5]; ?> %</div>
        <div class="col-sm-3">Max Humidity Out <?php echo $week[6]; ?> %</div>
        <div class="col-sm-3">Min Humidity Out <?php echo $week[7]; ?> %</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Pressure <?php echo $week[8]; ?></div>
        <div class="col-sm-3">Min Pressure <?php echo $week[9]; ?></div>
        <div class="col-sm-3">Total Rain <?php echo $week[10]; ?> mm</div>
      </div>
    </div>
  </div>

  <div class="card shadow mb-3">
    <div class="card-body">
      <h4 class="card-title">Max Min last Month</h4>
      <div class="row">
        <div class="col-sm-3">Max Temperature Out <?php echo $month[0]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature Out <?php echo $month[1]; ?> &#8451;</div>
        <div class="col-sm-3">Max Temperature In <?php echo $month[2]; ?> &#8451;</div>
        <div class="col-sm-3">Min Temperature In <?php echo $month[3]; ?> &#8451;</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Humidity In <?php echo $month[4]; ?> %</div>
        <div class="col-sm-3">Min Humidity In <?php echo $month[5]; ?> %</div>
        <div class="col-sm-3">Max Humidity Out <?php echo $month[6]; ?> %</div>
        <div class="col-sm-3">Min Humidity Out <?php echo $month[7]; ?> %</div>
      </div>
      <div class="row">
        <div class="col-sm-3">Max Pressure <?php echo $month[8]; ?></div>
        <div class="col-sm-3">Min Pressure <?php echo $month[9]; ?></div>
        <div class="col-sm-3">Total Rain <?php echo $month[10]; ?> mm</div>
      </div>
    </div>
  </div>
</div>

<?php mysqli_close($link);
