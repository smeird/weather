<?php
 if(isset($_GET['item'])){$item   = $_GET['item'];} else {http_response_code(400); exit('Missing item parameter');}
 $allowedItems = ['rain','wind_ave','windDir','windSpeed','outTemp','inTemp','windGust','outHumidity','inHumidity','barometer','pressure','rainn','dewpoint','windchill'];
 if (!in_array($item, $allowedItems, true)) {
   http_response_code(400);
   exit('Invalid item parameter');
 }
 require_once '../dbconn.php';

 if ($item == "wind_ave")
     {
     $gt = "column";
     }
 else
     {
     $gt = "spline";
     }
 if ($item == "rain")
     {
     $gt = "column";
     }
 if ($item == "windDir")
     {
     $gt = "scatter";
     }

 if(isset($_GET['FULL'])) {
 include ('header.php');
 echo "<a class=\"inline-block bg-blue-500 text-white px-4 py-2 rounded\" href=\"index.php?item=$item#graph\">Back</a>
  <div id=\"largecontainer\" class=\"bg-white dark:bg-gray-800 dark:text-gray-100 shadow rounded p-4\" style=\"height: 100%; min-width: 100%\"></div>";
 } else {
 echo "<p><a class=\"inline-block bg-blue-500 text-white px-4 py-2 rounded\" href=metric-graph.php?FULL=1&item=$item>Click here to open the graph in a seperate page</a></p><div><hr>
 <div id=\"largecontainer\" class=\"bg-white dark:bg-gray-800 dark:text-gray-100 shadow rounded p-4\" style=\"height: 100%;\"></div>
 ";
}


$SQLHOT  = "SELECT round(MAX(`archive`.`$item`),1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CONCAT(YEAR(CURRENT_DATE()),'-01-01')) AND UNIX_TIMESTAMP(CONCAT(YEAR(CURRENT_DATE()),'-12-31 23:59:59')) limit 1";
$SQLCOLD = "SELECT round(MIN(`archive`.`$item`),1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(CONCAT(YEAR(CURRENT_DATE()),'-01-01')) AND UNIX_TIMESTAMP(CONCAT(YEAR(CURRENT_DATE()),'-12-31 23:59:59')) limit 1";
$SQLHOTM  = "SELECT round(MAX(`archive`.`$item`),1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')) AND UNIX_TIMESTAMP(LAST_DAY(CURRENT_DATE()) + INTERVAL 1 DAY) - 1 limit 1";
$SQLCOLDM = "SELECT round(MIN(`archive`.`$item`),1) FROM `weewx`.`archive` WHERE dateTime BETWEEN UNIX_TIMESTAMP(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')) AND UNIX_TIMESTAMP(LAST_DAY(CURRENT_DATE()) + INTERVAL 1 DAY) - 1 limit 1";


 function goget($link, $SQL) {
 $stmt = mysqli_prepare($link, $SQL);
 if (!$stmt) {
     http_response_code(500);
     exit('Invalid query');
 }
 mysqli_stmt_execute($stmt);
 $result8 = mysqli_stmt_get_result($stmt);
 $row = mysqli_fetch_row($result8);
 $d0 = $row[0];
 mysqli_free_result($result8);
 mysqli_stmt_close($stmt);
 return $d0;
 }
 $hot=goget($link, $SQLHOT);
 $cold=goget($link, $SQLCOLD);
  $hotm=goget($link, $SQLHOTM);
 $coldm=goget($link, $SQLCOLDM);

?>

<script type='text/javascript'>//<![CDATA[

        document.addEventListener('DOMContentLoaded', function() {


            fetch('backend/metric-data.php?item=<?php echo $item; ?>')

              .then(response => response.json())
              .then(function(data) {

                // Add a null value for the end dateTime
               // data = [].concat(data, [[dateTime.UTC(2011, 11, 10, 19, 59), null, null, null, null]]);

                // create the chart
                window.chart = new Highcharts.stockChart({
                    chart: {
                        renderTo: 'largecontainer',
                        type: '<?php echo $gt; ?>',
                        zoomType: 'x'
                    },
                    navigator: {
                        adaptToUpdateTimedData: false,
                        series: {
                            data: data

                        }
                    },
                    title: {
                        text: '<?php echo $item; ?>'
                    },
                    subtitle: {
                        text: 'Data:<?php echo $gt; ?>'
                    },
                    rangeSelector: {
                        buttons: [{
                                type: 'day',
                                count: 1,
                                text: '1d'
                            }, {
                                type: 'week',
                                count: 1,
                                text: '1w'
                            }, {
                                type: 'week',
                                count: 2,
                                text: '2w'
                            }, {
                                type: 'month',
                                count: 1,
                                text: '1m'
                            }, {
                                type: 'month',
                                count: 2,
                                text: '2m'
                            }, {
                                type: 'month',
                                count: 6,
                                text: '6m'
                            }, {
                                type: 'year',
                                count: 1,
                                text: '1y'
                            }, {
                                type: 'all',
                                text: 'All'
                            }],
                        inputEnabled: true, // it supports only days
                        selected: 1 // all
                    },
                    xAxis: {
                        type: 'datetime',
						events: {
                            afterSetExtremes: afterSetExtremes
                        },
						dateTimeLabelFormats: {
                day:'%e. %b %Y',
				week: '%e. %b %Y',
				month: '%b \'%y',
				year: '%Y'
            }
                        //minRange: 3600 * 1000 // one hour
                    },
                    plotOptions: {
                        spline: {
                            lineWidth: 1,
                            marker: {
                                enabled: false,
                                states: {
                                    hover: {
                                        enabled: true,
                                        radius: 1
                                    }
                                }
                            },
                            shadow: false,
                            threshold: null
                        }
                    },
                    legend: {
                        enabled: true,
                        borderWidth: 0
                    },
                    tooltip: {
                crosshairs: true
            },
                    yAxis : {
                title : {
                    text : '<?php echo $item; ?>'
                },
                plotLines : [{
                    value : <?php echo $cold; ?>,
                    dashStyle : 'longdash',
                    width : 1,
                    label : {
                        text : 'Low of Year: <?php echo $cold; ?>',
                        style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '8px'
        }
                    }
                }, {
                    value : <?php echo $hot; ?>,
                    dashStyle : 'longdash',
                    width : 1,
                    label : {
                        text : 'High of Year: <?php echo $hot; ?>',
                        style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '8px'
        }
                    }
                },{
                    value : <?php echo $coldm; ?>,
                    dashStyle : 'shortdash',
                    width : 1,
                    label : {
                        text : 'Low of Month: <?php echo $coldm; ?>',
                        align: 'right',
                        style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '8px'
        }
                    }
                }, {
                    value : <?php echo $hotm; ?>,
                    dashStyle : 'shortdash',
                    width : 1,
                    label : {
                        text : 'High of Month: <?php echo $hotm; ?>',
                        align: 'right',
                        style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '8px'
        }
                    }
                }]
            },


                    series: [{
                            name: '<?php echo $item; ?>',
                            data: data,
                            id: 'primary',
                            lineWidth: 1
                        }]
                });
            });
        });


        /**
         * Load new data depending on the selected min and max
         */
        function afterSetExtremes(e) {

            var url,
                    currentExtremes = this.getExtremes(),
                    range = e.max - e.min;

            chart.showLoading('Getting correct data from server...');

            fetch('backend/metric-data.php?start=' + Math.round(e.min) +
                '&end=' + Math.round(e.max) + '&item=<?php echo $item; ?>')

              .then(response => response.json())
              .then(function(data) {
                chart.series[0].setData(data);
                chart.hideLoading();
              });

        }

//]]>

</script>

    <a name="graph"></a>
