<?php
include('header.php');
require_once '../dbconn.php';
$allowedWhat = ['rain','inTemp','outTemp','barometer','outHumidity','inHumidity','windSpeed','windGust','windDir','windGustDir'];
$allowedScale = ['hour','day','48','week','month','qtr','6m','year','all'];
$allowedType = ['MINMAX','STANDARD'];

$what = isset($_GET['WHAT']) ? $_GET['WHAT'] : null;
if (!in_array($what, $allowedWhat, true)) {
    http_response_code(400);
    exit('Invalid WHAT parameter');
}
$scale = isset($_GET['SCALE']) ? $_GET['SCALE'] : 'day';
if (!in_array($scale, $allowedScale, true)) {
    http_response_code(400);
    exit('Invalid SCALE parameter');
}
$type = isset($_GET['TYPE']) ? $_GET['TYPE'] : 'STANDARD';
if (!in_array($type, $allowedType, true)) {
    http_response_code(400);
    exit('Invalid TYPE parameter');
}

$date = isset($_GET['DATE']) ? $_GET['DATE'] : null;
if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  http_response_code(400);
  exit('Invalid DATE parameter');
}


switch ($what) {
    case "rain":
        $gt = "column";
        $gscale = "mm";
        $calc = "SUM";
        $units = 10;
        break;
    case "inTemp":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "outTemp":
        $gt = "areaspline";
        $gscale = "°C";
        $units = 1;
        break;
    case "barometer":
        $gt = "areaspline";
        $gscale = "mPh";
        $units = 1;
        break;

    case "outHumidity":
        $gt = "spline";
        $gscale = "%";
        $units = 1;
        break;
    case "inHumidity":
        $gt = "areaspline";
        $gscale = "%";
        $units = 1;
        break;
    case "windSpeed":
        $gt = "spline";
        $gscale = "m/s";
        $units = 1;
        break;
    case "windGust":
        $gt = "spline";
        $gscale = "m/s";
        $units = 1;
        break;
    case "windDir":
        $gt = "scatter";
        $gscale = "deg";
        $units = 1;
        break;
    case "windGustDir":
        $gt = "scatter";
        $gscale = "deg";
        $units = 1;
        break;
    default:
        $gt = "spline";
        $calc = "AVG";
        $units = 1;
}


if ($date) {
  $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP('$date 00:00:00') AND UNIX_TIMESTAMP('$date 23:59:59') ";
  $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
  $xscale = "3600 * 1000";
} else {
  switch ($scale) {
    case "hour":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 2 HOUR) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
      $xscale = "600 * 1000";
      break;
    case "hour":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 12 HOUR) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
      $xscale = "600 * 1000";
      break;
    case "day":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000";
      break;
    case "48":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 2 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000";
      break;
    case "week":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY day(FROM_UNIXTIME(dateTime)),week(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24";
      break;
    case "month":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY day(FROM_UNIXTIME(dateTime)),month(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24";
      break;
    case "qtr":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 3 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY week(FROM_UNIXTIME(dateTime)),month(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24 * 7";
      break;
    case "6m":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY week(FROM_UNIXTIME(dateTime)),year(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24 * 14";
      break;
    case "year":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 YEAR) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY month(FROM_UNIXTIME(dateTime)),year(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24 * 7 * 52 / 12 ";
      break;
    case "all":
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 5 YEAR) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY month(FROM_UNIXTIME(dateTime)),year(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000 * 24 * 7 * 52 / 12";
      break;
    default:
      $scalesql = "WHERE dateTime BETWEEN UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) AND UNIX_TIMESTAMP(NOW()) ";
      $groupby = "GROUP BY hour(FROM_UNIXTIME(dateTime)),day(FROM_UNIXTIME(dateTime))";
      $xscale = "3600 * 1000";
  }
  }

  $scaleLabel = $date ? $date : 'Last ' . $scale;

  switch ($type) {

    case "MINMAX":

        $sql = "select ANY_VALUE(dateTime) * 1000 as datetime, round($calc($what),1) * ? as dataavg, round(MIN($what),1) as datamin, round(MAX($what),1) as datamax FROM weewx.archive $scalesql  $groupby  ORDER BY dateTime ASC";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'd', $units);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rowr = array();
        $rowa = array();
        while ($row  = mysqli_fetch_assoc($result)) {
            extract($row);
            $rowa[] = "[$datetime,$dataavg]";
            $rowr[] = "[$datetime,$datamin,$datamax]";
        }
        $graphaveragedata = "[\n" . join(",\n", $rowa) . "\n]";
        $graphrangedata = "[\n" . join(",\n", $rowr) . "\n]";

        $conditions = [
            "windDir" => "Wind Direction",
            "windSpeed" => "Wind Speed",
            "outTemp" => "Outside Temperature",
            "inTemp" => "Inside Temperature",
            "windGust" => "Highest Wind Gust",
            // Add more conditions as needed
        ];


        if (array_key_exists($what, $conditions)) {
            $what = $conditions[$what];
        }


        minmaxgraph($gt, $what, $graphrangedata, $graphaveragedata, $gscale, $scaleLabel, $xscale);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        break;



    default:
        if ($calc === "SUM") {
            $sql = "SELECT ANY_VALUE(dateTime) * 1000 AS datetime, ifnull(round($calc($what),1),0) * ? AS data FROM weewx.archive $scalesql $groupby ORDER BY dateTime ASC";
        } else {
            $sql = "SELECT dateTime *1000 AS datetime, ifnull(round($what,1),0) * ? AS data FROM weewx.archive $scalesql ORDER BY dateTime ASC";
        }
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'd', $units);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = array();
        while ($row  = mysqli_fetch_assoc($result)) {
            extract($row);
            $rows[] = "[$datetime,$data]";
        }
        $graphdata = "[\n" . join(",\n", $rows) . "\n]";

        $conditions = [
            "windDir" => "Wind Direction",
            "windSpeed" => "Wind Speed",
            "outTemp" => "Outside Temperature",
            "inTemp" => "Inside Temperature",
            "windGust" => "Highest Wind Gust",
            // Add more conditions as needed
        ];


        if (array_key_exists($what, $conditions)) {
            $what = $conditions[$what];
        }
        standardgraph($gt, $what, $graphdata, $gscale, $scaleLabel);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
}



if (array_key_exists($what, $conditions)) {
    $what = $conditions[$what];
}


function minmaxgraph($gt, $what, $graphrangedata, $graphaveragedata, $gscale, $scale, $xscale)
{
   
    echo "  <div class=\"container-fluid\"><br>
      <div class=\"card shadow\">
 <div style=\"height: 75vh;\" id=\"container\"></div></div></div>
<script type=\"text/javascript\">
 document.addEventListener('DOMContentLoaded', function () {

    var ranges = $graphrangedata ,
    averages =  $graphaveragedata ;


 Highcharts.chart('container', {
  chart: {
      zoomType: 'xy',
      events: {
         load: function() {
             var series = this.series[0],
                 yData = series.yData,
                 min = Math.min(...yData),
                 max = Math.max(...yData),
                 x1 = series.points[yData.indexOf(min)].x,
                 x2 = series.points[yData.indexOf(max)].x;

             this.addSeries({
                 type: 'flags',
                 name: 'Max & Min',
                 data: [{
                     x: x1,
                     y: min,
                     title: 'Min: ' + min + ' $gscale',
                      shape: 'squarepin'
                 }, {
                     x: x2,
                     y: max,
                     title: 'Max:' + max + ' $gscale',
                     shape: 'squarepin'
                 }]
             });
         }
       }
  },
    title: {
        text: ' Max, Min & Avg',
        align: 'left'
    },
    subtitle: {
        text: '$what in $scale',
        align: 'left'
    },
    xAxis: {
        type: 'datetime',

      tickInterval: $xscale,
      minTickInterval: 3600 * 1000,
      lineWidth: 2,

    },

    yAxis: {
        startOnTick: false,
        crosshair: true,
        min:null,
        title: {
            text: '$what ($gscale)'
        }

    },

    tooltip: {
        crosshairs: true,
        shared: true

    },
    plotOptions: {
      columnrange: {
          dataLabels: {
              enabled: true,
              format: '{y}$gscale'
          }
      },
      spline: {
          dataLabels: {
              enabled: false,
              format: '{y}$gscale'
          }
      }
  },
  rangeSelector: {
            selected: 0
        },
  legend: {
     layout: 'vertical',
     align: 'left',
     verticalAlign: 'top',
     x: 100,
     y: 70,
     floating: true,
     backgroundColor: Highcharts.defaultOptions.chart.backgroundColor,
     borderWidth: 1
 },
    series: [{
        name: '$what',
        data: averages,
        type: '$gt',
        zIndex: 1,
        lineWidth: 4,
        tooltip: {
            valueSuffix: ' $gscale'
        },
        marker: {
            fillColor: 'white',
            lineWidth: 1,
            radius: 2
                }
    }, {
        name: '$what Range',
        data: ranges,
        type: 'columnrange',
        lineWidth: 0,
        linkedTo: ':previous',
        fillOpacity: 0.1,
        zIndex: 10,
        tooltip: {
            valueSuffix: ' $gscale'
        },
        marker: {
            enabled: true
        }
      }]

    })

});


</script>

";
}

function standardgraph($gt, $what, $graphdata, $gscale, $scale)
{
    echo "
    <div class=\"container-fluid\"><br>
      <div class=\"card shadow\"><div class=\"card-body\">
 <div style=\"height: 75vh;\" id=\"container\"></div></div></div></div>
 <script type='text/javascript'>
 Highcharts.chart('container', {
     chart: {
         type: '$gt',
         zoomType: 'xy',
         events: {
            load: function() {
                var series = this.series[0],
                    yData = series.yData,
                    min = Math.min(...yData),
                    max = Math.max(...yData),
                    x1 = series.points[yData.indexOf(min)].x,
                    x2 = series.points[yData.indexOf(max)].x;

                this.addSeries({
                    type: 'flags',
                    name: 'Max & Min',
                    data: [{
                        x: x1,
                        y: min,
                        title: 'Min: ' + min + ' $gscale',
                         shape: 'squarepin'
                    }, {
                        x: x2,
                        y: max,
                        title: 'Max:' + max + ' $gscale',
                        shape: 'squarepin'
                    }]
                });
            }
        },
     },
     title: {
         text: '$what',
         align: 'left'
     },
     subtitle: {
         text: 'Time Period : $scale',
         align: 'left'
     },
     xAxis: {
         type: 'datetime',

         title: {
             text: 'Date'
         }
     },
     yAxis: {
         title: {
             text: '$what ($gscale)'
         }
     },
     tooltip: {
         crosshairs: true,
         shared: true

     },
     plotOptions: {
               areaspline: {
                   fillColor: {
                       linearGradient: {
                           x1: 0,
                           y1: 0,
                           x2: 0,
                           y2: 1
                       },
                       stops: [
                           [0, Highcharts.getOptions().colors[3]],
                           [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                       ]
                   },
                   marker: {
                       radius: 1
                   },
                   lineWidth: 1,
                   states: {
                       hover: {
                           lineWidth: 1
                       }
                   },
                   threshold: null
               },
               spline: {
                   fillColor: {
                       linearGradient: {
                           x1: 0,
                           y1: 0,
                           x2: 0,
                           y2: 1
                       },
                       stops: [
                           [0, Highcharts.getOptions().colors[2]],
                           [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                       ]
                   },
                   marker: {
                       radius: 0
                   },
                   lineWidth: 1,
                   
                   states: {
                       hover: {
                           lineWidth: 1
                       }
                   },
                   threshold: null
               },


         series: {
           threshold: 0
         }
     },

     series: [{
         name: '$what',
         data:  $graphdata,
         tooltip: {
             valueSuffix: ' $gscale'
         }
 }]
});

 </script>
 ";
}