<?php

 function isIphone($user_agent = NULL)
     {
     if (!isset($user_agent))
         {
         $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']
                 : '';
         }
     return (strpos($user_agent, 'iPhone') !== FALSE);
     }

 if (isIphone())
     {
     header('Location: http://www.smeird.com/iphone.php');
     exit();
     }




 include ('header.php');
 include ('dbconn.php');
 $item   = isset($_GET['item']) ? $_GET['item'] : null;
 $itemmm = isset($_GET['itemmm']) ? $_GET['itemmm'] : null;
 $allowed = ['temp_out','temp_in','hum_in','hum_out','abs_pressure','wind_ave','wind_gust','wind_dir','rain'];
 foreach (['item' => $item, 'itemmm' => $itemmm] as $param => $value) {
     if ($value !== null && !in_array($value, $allowed, true)) {
         http_response_code(400);
         exit('Invalid ' . $param . ' parameter');
     }
 }

 $SQLHOT  = "Select `rawdata`.`temp_out`,`rawdata`.`date` FROM `weather`.`rawdata` WHERE  `rawdata`.`temp_out`=(SELECT MAX(`rawdata`.`temp_out`) FROM `weather`.`rawdata`);";
 $SQLCOLD = "Select `rawdata`.`temp_out`,`rawdata`.`date` FROM `weather`.`rawdata` WHERE  `rawdata`.`temp_out`=(SELECT MIN(`rawdata`.`temp_out`) FROM `weather`.`rawdata`);";

 $SQLLONGHOT  = "select count(*) from (select DATE_FORMAT(date , '%Y-%m-%d')  FROM weather.rawdata where temp_out > 30 group by DATE_FORMAT(date , '%Y-%m-%d')) sub;";
 $SQLLONGCOLD = "select count(*) from (select DATE_FORMAT(date , '%Y-%m-%d')  FROM weather.rawdata where temp_out < 0 group by DATE_FORMAT(date , '%Y-%m-%d')) sub;";


 $SQL = "SELECT
`rawdata`.`ID`,
`rawdata`.`date`,
`rawdata`.`temp_out`,
`rawdata`.`temp_in`,
`rawdata`.`hum_in`,
`rawdata`.`hum_out`,
`rawdata`.`abs_pressure`,
`rawdata`.`wind_ave`,
`rawdata`.`wind_gust`,
`rawdata`.`wind_dir`,
`rawdata`.`rain`
FROM `weather`.`rawdata`
order by date desc limit 1
;";

 $SQL1 = "SELECT * FROM weather.rawdata order by date desc limit 10";

 $SQL2   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 1 DAY;";
 $SQL3   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 7 DAY;";
 $SQL4   = "SELECT
max(`rawdata`.`temp_out`),
min(`rawdata`.`temp_out`),
max(`rawdata`.`temp_in`),
min(`rawdata`.`temp_in`),
max(`rawdata`.`hum_in`),
min(`rawdata`.`hum_in`),
max(`rawdata`.`hum_out`),
min(`rawdata`.`hum_out`),
max(`rawdata`.`abs_pressure`),
min(`rawdata`.`abs_pressure`),
max(`rawdata`.`rain`)-min(`rawdata`.`rain`)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 1 MONTH;";
 $result = mysqli_query($link, $SQL);
 if (!$result) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result)) {

     for ($i = 0; $i <= mysqli_num_fields($result); $i++) {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
         $d11 = $row[11];
         $d12 = $row[12];
         }
     }
 echo "<div id=\"billboard\"><h11>Date of last reading : <h11>$d1";
 echo "</div>";

 $dp   = round(calculateDewPoint($d2, $d5), 2);
 $moon = calculateMoonPhase(time(now));
 $wc   = round(convertTemperature(calculateWindChill(convertTemperature($d2,
                                                                        'c', 'f'),
                                                                        convertSpeed($d8,
                                                                                     'mps',
                                                                                     'mph')),
                                                                                     'f',
                                                                                     'c'),
                                                                                     2);
 $ss   = date_sunset(time(), SUNFUNCS_RET_STRING, 51.752646, -0.325041, 90, 0);
 $sr   = date_sunrise(time(), SUNFUNCS_RET_STRING, 51.752646, -0.325041, 90, 0);
 ?>
<script type="text/javascript">
$(function () {
Highcharts.setOptions({
    chart: {
        style: {
            fontFamily: 'Helvetica',
			fontSize: '10px'
        }
    }
});
    var gaugeOptions = {

        chart: {
            type: 'solidgauge',
			style: {
            fontFamily: 'Helvetica',
			fontSize: '10px'
        }
        },

        title: null,

        pane: {
            center: ['50%', '85%'],
            size: '140%',
            startAngle: -90,
            endAngle: 90,
            background: {
                
                innerRadius: '60%',
                outerRadius: '100%',
                shape: 'arc'
            }
        },

        tooltip: {
            enabled: false
        },

        // the value axis
        yAxis: {
            stops: [
                [0.3, '#0000FF'], // blue
                [0.5, '#DDDF0D'], // yellow
                [0.9, '#FF0000'] // red
            ],
            lineWidth: 0,
            minorTickInterval: null,
            tickPixelInterval: 100,
            tickWidth: 0,
            title: {
                y: -70
            },
            labels: {
                y: 16
            }
        },

        plotOptions: {
            solidgauge: {
                dataLabels: {
                    y: 5,
                    borderWidth: 0,
                    useHTML: true
                }
            }
        }
    };

    // The tmp out
    $('#tmpout').highcharts(Highcharts.merge(gaugeOptions, {
        yAxis: {
            min: -10,
            max: 40,
            title: {
                text: 'Temp Out'
            }
        },

        credits: {
            enabled: false
        },

        series: [{
            name: 'Temp',
            data: [<?php echo $d2; ?>],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                    ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                       '<span style="font-size:12px;color:silver">&#8451</span></div>'
            },
            tooltip: {
                valueSuffix: ' C'
            }
        }]

    }));
// The tmp in
    $('#tmpin').highcharts(Highcharts.merge(gaugeOptions, {
        yAxis: {
            min: -10,
            max: 40,
            title: {
                text: 'Temp In'
            }
        },

        credits: {
            enabled: false
        },

        series: [{
            name: 'Temp',
            data: [<?php echo $d3; ?>],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                    ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                       '<span style="font-size:12px;color:silver">&#8451</span></div>'
            },
            tooltip: {
                valueSuffix: ' C'
            }
        }]

    }));
	 // The Hum Out
    $('#humin').highcharts(Highcharts.merge(gaugeOptions, {
        yAxis: {
            min: 0,
            max: 100,
            title: {
                text: 'Humidity In'
            }
        },
		credits: {
            enabled: false
        },
        series: [{
            name: 'Humidity In',
            data: [<?php echo $d4; ?>],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                    ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y:.1f}</span><br/>' +
                       '<span style="font-size:12px;color:silver">%</span></div>'
            },
            tooltip: {
                valueSuffix: '%'
            }
       }]

    }));
    // The Hum Out
    $('#humout').highcharts(Highcharts.merge(gaugeOptions, {
        yAxis: {
            min: 0,
            max: 100,
            title: {
                text: 'Humidity Out'
            }
        },

        series: [{
            name: 'Humidity Out',
            data: [<?php echo $d5; ?>],
            dataLabels: {
                format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                    ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y:.1f}</span><br/>' +
                       '<span style="font-size:12px;color:silver">%</span></div>'
            },
            tooltip: {
                valueSuffix: '%'
            }
       }]

    }));

 $('#avgwind').highcharts({

              chart: {
            type: 'gauge',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false
        },

        title: {
            text: 'Average Wind Speed'
        },

        pane: {
            startAngle: -150,
            endAngle: 150,
            background: [{
             
                borderWidth: 0,
                outerRadius: '109%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#333'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '107%'
            }, {
                // default background
            }, {
                backgroundColor: '#DDD',
                borderWidth: 0,
                outerRadius: '105%',
                innerRadius: '103%'
            }]
        },

        // the value axis
        yAxis: {
            min: 0,
            max: 8,

            minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 30,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto'
            },
            title: {
                text: 'ms'
            },
            plotBands: [{
                from: 0,
                to: 3,
                color: '#55BF3B' // green
            }, {
                from: 3,
                to: 6,
                color: '#DDDF0D' // yellow
            }, {
                from: 6,
                to: 8,
                color: '#DF5353' // red
            }]
        },

        series: [{
            name: 'Speed',
            data: [<?php echo $d7; ?>],
            tooltip: {
                valueSuffix: ' ms'
            }
        }]

    })
      
 $('#gustwind').highcharts({

              chart: {
            type: 'gauge',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false,
			style: {
            fontFamily: 'Helvetica',
			fontSize: '10px'
        }
        },

        title: {
            text: 'Gust Wind Speed'
        },

        pane: {
            startAngle: -150,
            endAngle: 150,
            background: [{
             
                borderWidth: 0,
                outerRadius: '109%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#333'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '107%'
            }, {
                // default background
            }, {
                backgroundColor: '#DDD',
                borderWidth: 0,
                outerRadius: '105%',
                innerRadius: '103%'
            }]
        },

        // the value axis
        yAxis: {
            min: 0,
            max: 8,

            minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 30,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto'
            },
            title: {
                text: 'ms'
            },
            plotBands: [{
                from: 0,
                to: 3,
                color: '#55BF3B' // green
            }, {
                from: 3,
                to: 6,
                color: '#DDDF0D' // yellow
            }, {
                from: 6,
                to: 8,
                color: '#DF5353' // red
            }]
        },

        series: [{
            name: 'Speed',
            data: [<?php echo $d8; ?>],
            tooltip: {
                valueSuffix: ' ms'
            }
        }]

    })	
	
$('#pres').highcharts({

              chart: {
            type: 'gauge',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false
        },

        title: {
            text: 'Pressure'
        },

        pane: {
            startAngle: -150,
            endAngle: 150,
            background: [{
             
                borderWidth: 0,
                outerRadius: '109%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#333'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '107%'
            }, {
                // default background
            }, {
                backgroundColor: '#DDD',
                borderWidth: 0,
                outerRadius: '105%',
                innerRadius: '103%'
            }]
        },

        // the value axis
        yAxis: {
            min: 950,
            max: 1030,

            minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 30,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto'
            },
            title: {
                text: 'ms'
            }
        },

        series: [{
            name: 'Pressure',
            data: [<?php echo $d6; ?>],
            tooltip: {
                valueSuffix: ' '
            }
        }]

    })	

$('#dir').highcharts({

              chart: {
            type: 'gauge',
            plotBackgroundColor: null,
            plotBackgroundImage: null,
            plotBorderWidth: 0,
            plotShadow: false
        },

        title: {
            text: 'Wind Direction'
        },

        pane: {
            startAngle: 0,
            endAngle: 360,
            background: [{
             
                borderWidth: 0,
                outerRadius: '109%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#333'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '107%'
            }, {
                // default background
            }, {
                backgroundColor: '#DDD',
                borderWidth: 0,
                outerRadius: '105%',
                innerRadius: '103%'
            }]
        },

        // the value axis
        yAxis: {
            min: 0,
            max: 360,

            minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 30,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto'
            },
            title: {
                text: 'Deg'
            }
        },

        series: [{
            name: 'dir',
            data: [<?php echo $d9; ?>],
            tooltip: {
                valueSuffix: ' '
            }
        }]

    })		


	
});

</script>
 
 
<?php 
 echo "
<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table width=100%><tr><td>
						<div class=\"graph\" id=\"tmpout\" style=\"height: 180px;\"></div>
						</td></tr></table>
					</div>
					<div class=\"column\">
							<table width=100%><tr><td>
						<div class=\"graph\" id=\"tmpin\" style=\"height: 180px;\"></div>
						</td></tr></table>
					</div>
					<div class=\"column\">
							<table width=100%><tr><td>
						<div class=\"graph\" id=\"humin\" style=\"height: 180px;\"></div>
						</td></tr></table>
					</div>
					<div class=\"column last\">
							<table width=100%><tr><td>
						<div class=\"graph\" id=\"humout\" style=\"height: 180px;\"></div>
						</td></tr></table>
					</div>
				</div>
	<div id=\"blocks\" class=\"grid4col\">
						<div class=\"column first\">
							<table width=100%><tr><td>
								<div class=\"graph\" id=\"pres\" style=\"height: 250px;\"></div>
						</td></tr></table>
					</div>
					<div class=\"column\">
							<table><tr><td>
								<div class=\"graph\" id=\"avgwind\" style=\"height: 250px;\"></div>
						</td></tr></table>
					</div>
					<div class=\"column\">
							<table><tr><td>
								<div class=\"graph\" id=\"gustwind\" style=\"height: 250px;\"></div>
							</td></tr></table>
					</div>
					<div class=\"column last\">
							<table><tr><td>
								<div class=\"graph\" id=\"dir\" style=\"height: 250px;\"></div>
								</td></tr></table>
					</div>
				</div>
		<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Dew Point</td><td><h10>$dp &#8451</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Moon Phase</td><td><h10> $moon[phase]</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Zodiac</td><td><h10>$moon[zodiac]</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Wind Chill</td><td><h10>$wc &#8451</td></tr></table>

					</div>
				</div>
				<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Sun Rise</td><td><h10>$sr</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Sun Set</td><td><h10>$ss</td></tr></table>

					</div>

				</div>

";

 $result2 = mysqli_query($link, $SQL2);
 if (!$result2) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result2)) {

     for ($i = 0; $i <= mysqli_num_fields($result2); $i++) {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
         $d11 = $row[11];
         }
     }


 echo "<div id=\"billboard\"><h11>Max Min last 24hrs";
 echo "</div>";
 echo "
<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Temperature Out </td><td><h10>$d0 &#8451</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Temperature Out</td><td><h10>$d1 &#8451</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Temperature In</td><td><h10>$d2 &#8451</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Min Temperature In</td><td><h10>$d3 &#8451</td></tr></table>

					</div>
				</div>
	<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Humidity In</td><td><h10>$d4 %</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Humidity In</td><td><h10>$d5 %</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d6 %</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d7 %</td></tr></table>

					</div>
				</div>
				<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Pressure</td><td><h10>$d8</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Presure</td><td><h10>$d9</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Total Rain</td><td><h10>$d10 mm</td></tr></table>

					</div>

				</div>
";

 $result3 = mysqli_query($link, $SQL3);
 if (!$result3) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result3)) {

     for ($i = 0; $i <= mysqli_num_fields($result3); $i++) {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
         $d11 = $row[11];
         }
     }


 echo "<div id=\"billboard\"><h11>Max Min last 7days";
 echo "</div>";
 echo "
<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Temperature Out </td><td><h10>$d0 &#8451</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Temperature Out</td><td><h10>$d1 &#8451</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Temperature In</td><td><h10>$d2 &#8451</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Min Temperature In</td><td><h10>$d3 &#8451</td></tr></table>

					</div>
				</div>
	<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Humidity In</td><td><h10>$d4 %</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Humidity In</td><td><h10>$d5 %</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d6 %</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d7 %</td></tr></table>

					</div>
				</div>
				<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Pressure</td><td><h10>$d8</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Presure</td><td><h10>$d9</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Total Rain</td><td><h10>$d10 mm</td></tr></table>

					</div>

				</div>
";

 $result4 = mysqli_query($link, $SQL4);
 if (!$result4) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result4)) {

     for ($i = 0; $i <= mysqli_num_fields($result4); $i++) {
         $d0  = $row[0];
         $d1  = $row[1];
         $d2  = $row[2];
         $d3  = $row[3];
         $d4  = $row[4];
         $d5  = $row[5];
         $d6  = $row[6];
         $d7  = $row[7];
         $d8  = $row[8];
         $d9  = $row[9];
         $d10 = $row[10];
         $d11 = $row[11];
         }
     }


 echo "<div id=\"billboard\"><h11>Max Min last Month";
 echo "</div>";
 echo "
<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Temperature Out </td><td><h10>$d0 &#8451</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Temperature Out</td><td><h10>$d1 &#8451</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Temperature In</td><td><h10>$d2 &#8451</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Min Temperature In</td><td><h10>$d3 &#8451</td></tr></table>

					</div>
				</div>
	<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Humidity In</td><td><h10>$d4 %</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Humidity In</td><td><h10>$d5 %</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d6 %</td></tr></table>

					</div>
					<div class=\"column last\">
							<table><tr><td><h3>Max Humidity Out</td><td><h10>$d7 %</td></tr></table>

					</div>
				</div>
				<div id=\"blocks\" class=\"grid4col\">
					<div class=\"column first\">
							<table><tr><td><h3>Max Pressure</td><td><h10>$d8</td></tr></table>
							</div>
					<div class=\"column\">
							<table><tr><td><h3>Min Presure</td><td><h10>$d9</td></tr></table>

					</div>
					<div class=\"column\">
							<table><tr><td><h3>Total Rain</td><td><h10>$d10 mm</td></tr></table>

					</div>

				</div>
";




 $result8 = mysqli_query($link, $SQLHOT);
 if (!$result8) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result8)) {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++) {
         $d0 = $row[0];
         $d1 = $row[1];
     }
 }
 echo "<div id=\"billboard\"><h11>Hottest Day of the year  :$d0 &#8451 on the  $d1";
 echo "";
 $result8 = mysqli_query($link, $SQLCOLD);
 if (!$result8) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result8)) {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++) {
         $d0 = $row[0];
         $d1 = $row[1];
     }
 }
 echo "<h11>Coldest Day of the year  :$d0 &#8451 on the  $d1";
 echo "</div>";

 $result8 = mysqli_query($link, $SQLLONGHOT);
 if (!$result8) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result8)) {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++) {
         $d0 = $row[0];
     }
 }
 echo "<div id=\"billboard\"><h11>Number of Days Over 30&#8451 :$d0 days";
 echo "";



 $result8 = mysqli_query($link, $SQLLONGCOLD);
 if (!$result8) {
     die('Invalid query: ' . mysqli_error($link));
 }
 while ($row = mysqli_fetch_row($result8)) {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++) {
         $d0 = $row[0];
     }
 }
 echo "<h11>Number of Days Under 0&#8451  :$d0 days";
 echo "</div>";



 echo "<div id=\"billboard\"><p>Select Graph Data";

 if ($item == "temp_in")
     {
     $s1 = "Selected";
     }
 if ($item == "temp_out")
     {
     $s2 = "Selected";
     }
 if ($item == "hum_in")
     {
     $s3 = "Selected";
     }
 if ($item == "hum_out")
     {
     $s4 = "Selected";
     }
 if ($item == "abs_pressure")
     {
     $s5 = "Selected";
     }
 if ($item == "wind_ave")
     {
     $s6 = "Selected";
     }
 if ($item == "wind_gust")
     {
     $s7 = "Selected";
     }
 if ($item == "wind_dir")
     {
     $s8 = "Selected";
     }
 if ($item == "rain")
     {
     $s9 = "Selected";
     }

 if ($itemmm == "temp_in")
     {
     $ss1 = "Selected";
     }
 if ($itemmm == "temp_out")
     {
     $ss2 = "Selected";
     }
 if ($itemmm == "hum_in")
     {
     $ss3 = "Selected";
     }
 if ($itemmm == "hum_out")
     {
     $ss4 = "Selected";
     }
 if ($itemmm == "abs_pressure")
     {
     $ss5 = "Selected";
     }
 if ($itemmm == "wind_ave")
     {
     $ss6 = "Selected";
     }
 if ($itemmm == "wind_gust")
     {
     $ss7 = "Selected";
     }
 if ($itemmm == "wind_dir")
     {
     $ss8 = "Selected";
     }
 if ($itemmm == "rain")
     {
     $ss9 = "Selected";
     }

 echo "<table width=100% border=0>
<tr><td>Actual and Average data</td><td>Max and Min Data</td></tr>

<tr><td>
<form action=\"/index.php#graph\" method=\"GET\">
<select style=\"width: 200px\" title=\"$title\" name=\"item\">
<option $s2 value=\"temp_out\" title=\"$desc\">Temperature Outside</option>
<option $s1 value=\"temp_in\" title=\"$desc\">Temperature Inside</option>
<option $s3 value=\"hum_in\" title=\"$desc\">Humidity Inside</option>
<option $s4 value=\"hum_out\" title=\"$desc\">Humidity Outside</option>
<option $s5 value=\"abs_pressure\" title=\"$desc\">Pressure</option>
<option $s6 value=\"wind_ave\" title=\"$desc\">Average Windspeed</option>
<option $s7 value=\"wind_gust\" title=\"$desc\">Wind Highest Gust</option>
<option $s8 value=\"wind_dir\" title=\"$desc\">Wind Direction</option>
<option $s9 value=\"rain\" title=\"$desc\">Rain</option>
</select><input class=\"button\" type=\"submit\" value=\"  Select Data  \"></form>
</td>
<td>
<form action=\"/index.php#graph2\" method=\"GET\">
<select style=\"width: 200px\" title=\"$title\" name=\"itemmm\">
<option $ss2 value=\"temp_out\" title=\"$desc\">Temperature Outside</option>
<option $ss1 value=\"temp_in\" title=\"$desc\">Temperature Inside</option>
<option $ss3 value=\"hum_in\" title=\"$desc\">Humidity Inside</option>
<option $ss4 value=\"hum_out\" title=\"$desc\">Humidity Outside</option>
<option $ss5 value=\"abs_pressure\" title=\"$desc\">Pressure</option>
<option $ss6 value=\"wind_ave\" title=\"$desc\">Average Windspeed</option>
<option $ss7 value=\"wind_gust\" title=\"$desc\">Wind Highest Gust</option>
<option $ss8 value=\"wind_dir\" title=\"$desc\">Wind Direction</option>
<option $ss9 value=\"rain\" title=\"$desc\">Rain</option>
</select><input class=\"button\" type=\"submit\" value=\"  Select Data  \"></form>
</td>



<td><a class=button href=windrose.php>WindRose</a></td></tr></table></div>";

if (isset($item))
     {
     echo "<div id=\"billboard\">";
     include ('graph.php');
     echo "</div>";
     }
 if (isset($itemmm))
     {
     echo "<div id=\"billboard\">";
     include ('graph2.php');
     echo "</div>";
     }
 if (!isset($itemmm) and !isset($item))
     {
     echo "<div id=\"billboard\">";
     include ('graph3.php');
     echo "</div>";
     }

 function calculateDewPoint($temperature, $humidity)
     {
     if ($temperature >= 0)
         {
         $a = 7.5;
         $b = 237.3;
         }
     else
         {
         $a = 7.6;
         $b = 240.7;
         }

     // First calculate saturation steam pressure for temperature
     $SSP = 6.1078 * pow(10, ($a * $temperature) / ($b + $temperature));

     // Steam pressure
     $SP = $humidity / 100 * $SSP;

     $v = log($SP / 6.1078, 10);

     return ($b * $v / ($a - $v));
     }

 function calculateWindChill($temperature, $speed)
     {
     // temp in F speed in mph!!!
     return (35.74 + 0.6215 * $temperature - 35.75 * pow($speed, 0.16) + 0.4275 * $temperature
         * pow($speed, 0.16));
     }

 function convertTemperature($temperature, $from, $to)
     {
     if ($temperature == "N/A")
         {
         return $temperature;
         }

    $from = strtolower($from[0]);
    $to   = strtolower($to[0]);

     $result = array(
      "f" => array(
       "f" => $temperature,"c" => ($temperature - 32) / 1.8
      ),
      "c" => array(
       "f" => 1.8 * $temperature + 32,"c" => $temperature
      )
     );

     return $result[$from][$to];
     }

 function convertSpeed($speed, $from, $to)
     {
     $from = strtolower($from);
     $to   = strtolower($to);

     static $factor;
     static $beaufort;
     if (!isset($factor))
         {
         $factor = array(
          "mph" => array(
           "mph" => 1,"kmh" => 1.609344,"kt"  => 0.8689762,"mps" => 0.44704,"fps" => 1.4666667
          ),
          "kmh" => array(
           "mph" => 0.6213712,"kmh" => 1,"kt"  => 0.5399568,"mps" => 0.2777778,"fps" => 0.9113444
          ),
          "kt"  => array(
           "mph" => 1.1507794,"kmh" => 1.852,"kt"  => 1,"mps" => 0.5144444,"fps" => 1.6878099
          ),
          "mps" => array(
           "mph" => 2.2369363,"kmh" => 3.6,"kt"  => 1.9438445,"mps" => 1,"fps" => 3.2808399
          ),
          "fps" => array(
           "mph" => 0.6818182,"kmh" => 1.09728,"kt"  => 0.5924838,"mps" => 0.3048,
           "fps" => 1
          )
         );

         // Beaufort scale, measurements are in knots
         $beaufort = array(
          1,3,6,10,
          16,21,27,33,
          40,47,55,63
         );
         }

     if ($from == "bft")
         {
         return false;
         }
     elseif ($to == "bft")
         {
         $speed = round($speed * $factor[$from]["kt"], 0);
         for ($i = 0; $i < sizeof($beaufort); $i++)
             {
             if ($speed <= $beaufort[$i])
                 {
                 return $i;
                 }
             }
         return sizeof($beaufort);
         }
     else
         {
         return ($speed * $factor[$from][$to]);
         }
     }

 function calculateMoonPhase($date)
     {
     // Date must be timestamp for now
     if (!is_int($date))
         {
         return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_MOONFUNCS_DATE_INVALID,
                                             __FILE__, __LINE__);
         }

     $moon = array();

     $year  = date("Y", $date);
     $month = date("n", $date);
     $day   = date("j", $date);
     $hour  = date("G", $date);
     $min   = date("i", $date);
     $sec   = date("s", $date);

     $age       = 0.0; // Moon's age in days from New Moon
     $distance  = 0.0; // Moon's distance in Earth radii
     $latitude  = 0.0; // Moon's ecliptic latitude in degrees
     $longitude = 0.0; // Moon's ecliptic longitude in degrees
     $phase     = "";  // Moon's phase
     $zodiac    = "";  // Moon's zodiac
     $icon      = "";  // The icon to represent the moon phase

     $YY = 0;
     $MM = 0;
     $DD = 0;
     $HH = 0;
     $A  = 0;
     $B  = 0;
     $JD = 0;
     $IP = 0.0;
     $DP = 0.0;
     $NP = 0.0;
     $RP = 0.0;

     // Calculate Julian Daycount to the second
     if ($month > 2)
         {
         $YY = $year;
         $MM = $month;
         }
     else
         {
         $YY = $year - 1;
         $MM = $month + 12;
         }

     $DD = $day;
     $HH = $hour / 24 + $min / 1440 + $sec / 86400;

     // Check for Gregorian date and adjust JD appropriately
     if (($year * 10000 + $month * 100 + $day) >= 15821015)
         {
         $A = floor($YY / 100);
         $B = 2 - $A + floor($A / 4);
         }

     $JD = floor(365.25 * ($YY + 4716)) + floor(30.6001 * ($MM + 1)) + $DD + $HH
         + $B - 1524.5;

     // Calculate moon's age in days
     $IP  = ($JD - 2451550.1) / 29.530588853;
     if (($IP  = $IP - floor($IP)) < 0) $IP++;
     $age = $IP * 29.530588853;

     switch ($age)
         {
         case ($age < 1.84566):
             $phase = "New";
             break;
         case ($age < 5.53699):
             $phase = "Waxing Crescent";
             break;
         case ($age < 9.22831):
             $phase = "First Quarter";
             break;
         case ($age < 12.91963):
             $phase = "Waxing Gibbous";
             break;
         case ($age < 16.61096):
             $phase = "Full";
             break;
         case ($age < 20.30228):
             $phase = "Waning Gibbous";
             break;
         case ($age < 23.99361):
             $phase = "Last Quarter";
             break;
         case ($age < 27.68493):
             $phase = "Waning Crescent";
             break;
         default:
             $phase = "New";
         }

     // Convert phase to radians
     $IP = $IP * 2 * pi();

     // Calculate moon's distance
     $DP       = ($JD - 2451562.2) / 27.55454988;
     if (($DP       = $DP - floor($DP)) < 0) $DP++;
     $DP       = $DP * 2 * pi();
     $distance = 60.4 - 3.3 * cos($DP) - 0.6 * cos(2 * $IP - $DP) - 0.5 * cos(2 * $IP);

     // Calculate moon's ecliptic latitude
     $NP       = ($JD - 2451565.2) / 27.212220817;
     if (($NP       = $NP - floor($NP)) < 0) $NP++;
     $NP       = $NP * 2 * pi();
     $latitude = 5.1 * sin($NP);

     // Calculate moon's ecliptic longitude
     $RP        = ($JD - 2451555.8) / 27.321582241;
     if (($RP        = $RP - floor($RP)) < 0) $RP++;
     $longitude = 360 * $RP + 6.3 * sin($DP) + 1.3 * sin(2 * $IP - $DP) + 0.7 * sin(2
             * $IP);
     if ($longitude >= 360) $longitude -= 360;

     switch ($longitude)
         {
         case ($longitude < 33.18):
             $zodiac = "Pisces";
             break;
         case ($longitude < 51.16):
             $zodiac = "Aries";
             break;
         case ($longitude < 93.44):
             $zodiac = "Taurus";
             break;
         case ($longitude < 119.48):
             $zodiac = "Gemini";
             break;
         case ($longitude < 135.30):
             $zodiac = "Cancer";
             break;
         case ($longitude < 173.34):
             $zodiac = "Leo";
             break;
         case ($longitude < 224.17):
             $zodiac = "Virgo";
             break;
         case ($longitude < 242.57):
             $zodiac = "Libra";
             break;
         case ($longitude < 271.26):
             $zodiac = "Scorpio";
             break;
         case ($longitude < 302.49):
             $zodiac = "Sagittarius";
             break;
         case ($longitude < 311.72):
             $zodiac = "Capricorn";
             break;
         case ($longitude < 348.58):
             $zodiac = "Aquarius";
             break;
         default:
             $zodiac = "Pisces";
         }

     $moon["age"]       = round($age, 2);
     $moon["distance"]  = round($distance, 2);
     $moon["latitude"]  = round($latitude, 2);
     $moon["longitude"] = round($longitude, 2);
     $moon["zodiac"]    = $zodiac;
     $moon["phase"]     = $phase;
     $moon["icon"]      = (floor($age) - 1) . "";

     return $moon;
     }

?>
