<?php
 include ('header.php');

echo " <div class=\"container\"><div class=\"page-header\">
  <h1>Smeirds Weather Station - St Albans</h1>
</div>";



 include ('dbconn.php');




$title="Select item";
$desc="";

 if(isset($_GET['item'])){$item   = $_GET['item'];}
 if(isset($_GET['itemmm'])){$itemmm = $_GET['itemmm'];}

 $SQLHOT  = "Select `rawdata`.`temp_out`,`rawdata`.`date` FROM `weather`.`rawdata` WHERE  `rawdata`.`temp_out`=(SELECT MAX(`rawdata`.`temp_out`) FROM `weather`.`rawdata`) limit 1;";
 $SQLCOLD = "Select `rawdata`.`temp_out`,`rawdata`.`date` FROM `weather`.`rawdata` WHERE  `rawdata`.`temp_out`=(SELECT MIN(`rawdata`.`temp_out`) FROM `weather`.`rawdata`) limit 1;";

 $SQLLONGHOT  = "SELECT
    COUNT(*)
FROM
    weather.rawdataminmax1d
WHERE
    temp_out_max > 30";
 $SQLLONGCOLD = "select count(*)  FROM weather.rawdataminmax1d where temp_out_min < 0 ";


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
order by ID desc limit 1
;";

$SQL1 = "SELECT * FROM weather.rawdata order by date desc limit 10";

 $SQL22   = "SELECT
round(max(`rawdata`.`temp_out`), 1),
round(min(`rawdata`.`temp_out`), 1),
round(max(`rawdata`.`temp_in`), 1),
round(min(`rawdata`.`temp_in`), 1),
round(max(`rawdata`.`hum_in`), 1),
round(min(`rawdata`.`hum_in`), 1),
round(max(`rawdata`.`hum_out`), 1),
round(min(`rawdata`.`hum_out`), 1),
round(max(`rawdata`.`abs_pressure`), 1),
round(min(`rawdata`.`abs_pressure`), 1),
round(max(`rawdata`.`rain`)-min(`rawdata`.`rain`), 1)
FROM `weather`.`rawdata` WHERE date >= now() - INTERVAL 1 DAY;";
 $SQL3   = "SELECT
round(max(`rawdata`.`temp_out`), 1),
round(min(`rawdata`.`temp_out`), 1),
round(max(`rawdata`.`temp_in`), 1),
round(min(`rawdata`.`temp_in`), 1),
round(max(`rawdata`.`hum_in`), 1),
round(min(`rawdata`.`hum_in`), 1),
round(max(`rawdata`.`hum_out`), 1),
round(min(`rawdata`.`hum_out`), 1),
round(max(`rawdata`.`abs_pressure`), 1),
round(min(`rawdata`.`abs_pressure`), 1),
round(max(`rawdata`.`rain`)-min(`rawdata`.`rain`), 1)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 7 DAY;";
 $SQL4   = "SELECT
round(max(`rawdata`.`temp_out`), 1),
round(min(`rawdata`.`temp_out`), 1),
round(max(`rawdata`.`temp_in`), 1),
round(min(`rawdata`.`temp_in`), 1),
round(max(`rawdata`.`hum_in`), 1),
round(min(`rawdata`.`hum_in`), 1),
round(max(`rawdata`.`hum_out`), 1),
round(min(`rawdata`.`hum_out`), 1),
round(max(`rawdata`.`abs_pressure`), 1),
round(min(`rawdata`.`abs_pressure`), 1),
round(max(`rawdata`.`rain`)-min(`rawdata`.`rain`), 1)
FROM `weather`.`rawdata`WHERE date >= now() - INTERVAL 1 MONTH;";

 $result = mysqli_query($link,$SQL);
 if (!$result)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result))
     {

     for ($i = 0; $i <= mysqli_num_fields($result); $i++)
         {
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
         //$d11 = $row[11];
         //$d12 = $row[12];
         }
     }
     $since=humantiming(strtotime($d1));
 echo "<span>Time since last reading : $since</span>

 ";


$dp   = round(calculateDewPoint($d2, $d5), 2);
 $moon = calculateMoonPhase(time('now'));
 $wc   = round(convertTemperature(calculateWindChill(convertTemperature($d2, 'c', 'f'),convertSpeed($d8,'mps','mph')),'f','c'),2);

 $ss   = date_sunset(time(), SUNFUNCS_RET_STRING, 51.752646, -0.325041, 90, 0);
 $sr   = date_sunrise(time(), SUNFUNCS_RET_STRING, 51.752646, -0.325041, 90, 0);
 ?>

<script type="text/javascript">
$(function () {
Highcharts.setOptions({
    chart: {
        style: {
            fontFamily: 'Helvetica',
			fontSize: '12px'
        }
    }
});
    var gaugeOptions = {

        chart: {
            type: 'solidgauge'
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
            	series: {
            	animation: {
                    duration: 2000
                }
                },
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
                text: 'Temp Out',
				style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '12px'
        }
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
credits: {
            enabled: false
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
            text: 'Average Wind Speed',
			style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '12px'
        }
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
credits: {
            enabled: false
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
            plotShadow: false
        },

        title: {
            text: 'Gust Wind Speed',
			style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '12px'
        }
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
credits: {
            enabled: false
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
            text: 'Pressure',
			style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '12px'
        }
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
credits: {
            enabled: false
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
            text: 'Wind Direction',
			style: {
            fontFamily: 'HelveticaNeue-Light,Helvetica',
			fontSize: '12px'
        }
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
credits: {
            enabled: false
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

<div class=\"card mb-3\">

  <div class=\"card-body\">


<div  class=\"row\">
					<div class=\"col-sm-3\">

						<a href=\"/graph.php?FULL=1&item=temp_out\"><div  id=\"tmpout\" style=\"height: 180px;\"></div></a>

					</div>
					<div class=\"col-sm-3\">

						<a href=\"/graph.php?FULL=1&item=temp_in\"><div  id=\"tmpin\" style=\"height: 180px;\"></div></a>

					</div>
					<div class=\"col-sm-3\">

						<a href=\"/graph.php?FULL=1&item=hum_in\"><div  id=\"humin\" style=\"height: 180px;\"></div></a>

					</div>
					<div class=\"col-sm-3\">

						<a href=\"/graph.php?FULL=1&item=hum_out\"><div  id=\"humout\" style=\"height: 180px;\"></div></a>

					</div>
				</div>
	<div  class=\"row\">
						<div class=\"col-sm-3\">

								<div  id=\"pres\" style=\"height: 250px;\"></div>

					</div>
					<div class=\"col-sm-3\">

								<div  id=\"avgwind\" style=\"height: 250px;\"></div>

					</div>
					<div class=\"col-sm-3\">

								<div  id=\"gustwind\" style=\"height: 250px;\"></div>

					</div>
					<div class=\"col-sm-3\">

								<div  id=\"dir\" style=\"height: 250px;\"></div>

					</div>
				</div>
		<div  class=\"row\">
					<div class=\"col-sm-3\">
							Dew Point $dp &#8451
							</div>
					<div class=\"col-sm-3\">
							Moon Phase $moon[phase]

					</div>
					<div class=\"col-sm-3\">
							Zodiac $moon[zodiac]

					</div>
					<div class=\"col-sm-3\">
							Wind Chill $wc &#8451

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Sun Rise $sr
							</div>
					<div class=\"col-sm-3\">
							Sun Set $ss

					</div>

				</div>
			</div>
</div>

";


 $result = mysqli_query($link,$SQL22);
 if (!$result)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result))
     {

     for ($i = 0; $i <= mysqli_num_fields($result); $i++)
         {
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

         }
     }


 echo "<div class=\"card mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Max Min last 24hrs</h4>
<p class=\"card-text\">
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div>
</p>
				</div>
				</div></div>

";

 $result3 = mysqli_query($link,$SQL3);
 if (!$result3)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result3))
     {

     for ($i = 0; $i <= mysqli_num_fields($result3); $i++)
         {
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
         //$d11 = $row[11];
         }
     }


 echo "<div class=\"card mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Min Max Last 7 Days</h4>
   <p class=\"card-text\">
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div>
</p>
				</div></div></div>


";

 $result4 = mysqli_query($link,$SQL4);
 if (!$result4)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result4))
     {

     for ($i = 0; $i <= mysqli_num_fields($result4); $i++)
         {
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
        // $d11 = $row[11];
         }
     }


 echo "<div class=\"card mb-3\">
  <div class=\"card-body\">
   <h4 class=\"card-title\">Min Max Last Month</h4>
<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Temperature Out $d0 &#8451
							</div>
					<div class=\"col-sm-3\">
							Min Temperature Out $d1 &#8451

					</div>
					<div class=\"col-sm-3\">
							Max Temperature In $d2 &#8451

					</div>
					<div class=\"col-sm-3\">
							Min Temperature In $d3 &#8451

					</div>
				</div>
	<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Humidity In $d4 %
							</div>
					<div class=\"col-sm-3\">
							Min Humidity In $d5 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d6 %

					</div>
					<div class=\"col-sm-3\">
							Max Humidity Out $d7 %

					</div>
				</div>
				<div  class=\"row\">
					<div class=\"col-sm-3\">
							Max Pressure $d8
							</div>
					<div class=\"col-sm-3\">
							Min Presure $d9

					</div>
					<div class=\"col-sm-3\">
							Total Rain $d10 mm

					</div></div>
					</div>
</div>


";




 $result8 = mysqli_query($link,$SQLHOT);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         $d1 = $row[1];
         }
     }
 echo "<div class=\"card mb-3\"><div class=\"card-header\">Records</div><div class=\"card-body\"><p class=\"card-text\"><li>Hottest Day on record  : $d0 &#8451 on the  $d1";
 echo "";
 $result8 = mysqli_query($link,$SQLCOLD);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         $d1 = $row[1];
         }
     }
 echo "<li>Coldest Day on record  : $d0 &#8451 on the  $d1";


 $result8 = mysqli_query($link,$SQLLONGHOT);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         }
     }
 echo "<li>Number of Days Over 30&#8451 : $d0 days";




 $result8 = mysqli_query($link,$SQLLONGCOLD);
 if (!$result8)
     {
     die('Invalid query: ' . mysqli_error());
     }
 while ($row = mysqli_fetch_row($result8))
     {

     for ($i = 0; $i <= mysqli_num_fields($result8); $i++)
         {
         $d0 = $row[0];
         }
     }
 echo "<li>Number of Days Under 0&#8451  :$d0 days</p></div>";
 echo "</div>";



 echo "<div class=\"card mb-3\"><div class=\"card-header\">Select Graph Data</div><div class=\"card-body\">";

 if(isset($item)){
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
}

 if(isset($itemmm)){
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
	 }
$s1="";
$s2="";
$s3="";
$s4="";
$s5="";
$s6="";
$s7="";
$s8="";
$s9="";
$ss1="";
$ss2="";
$ss3="";
$ss4="";
$ss5="";
$ss6="";
$ss7="";
$ss8="";
$ss9="";
 echo "
 <div  class=\"row\">

<div class=\"col-sm-4\">
<div class=\"card mb-3\">
<div class=\"card-header\">Actual and Average data </div>
<div class=\"card-body\">
<form class=\"form-inline\" action=\"/index.php#graph\" method=\"GET\">
<div class=\"form-group\">
<select class=\"form-control\" style=\"width: 200px\" title=\"$title\" name=\"item\">
<option $s2 value=\"temp_out\" title=\"$desc\">Temperature Outside</option>
<option $s1 value=\"temp_in\" title=\"$desc\">Temperature Inside</option>
<option $s3 value=\"hum_in\" title=\"$desc\">Humidity Inside</option>
<option $s4 value=\"hum_out\" title=\"$desc\">Humidity Outside</option>
<option $s5 value=\"abs_pressure\" title=\"$desc\">Pressure</option>
<option $s6 value=\"wind_ave\" title=\"$desc\">Average Windspeed</option>
<option $s7 value=\"wind_gust\" title=\"$desc\">Wind Highest Gust</option>
<option $s8 value=\"wind_dir\" title=\"$desc\">Wind Direction</option>
<option $s9 value=\"rain\" title=\"$desc\">Rain</option>
</select>
</div>
<div class=\"form-group\">
<input class=\"btn btn-primary\" type=\"submit\" value=\"Select\"></form>
</div>
</div>
</div>
</div>




<div class=\"col-sm-4\">
<div class=\"card mb-3\">
<div class=\"card-header\">Max and Min Data</div>
<div class=\"card-body\">

<form class=\"form-inline\" action=\"/index.php#graph2\" method=\"GET\">
<div class=\"form-group\">
<select class=\"form-control\" style=\"width: 200px\" title=\"$title\" name=\"itemmm\">
<option $ss2 value=\"temp_out\" title=\"$desc\">Temperature Outside</option>
<option $ss1 value=\"temp_in\" title=\"$desc\">Temperature Inside</option>
<option $ss3 value=\"hum_in\" title=\"$desc\">Humidity Inside</option>
<option $ss4 value=\"hum_out\" title=\"$desc\">Humidity Outside</option>
<option $ss5 value=\"abs_pressure\" title=\"$desc\">Pressure</option>
<option $ss6 value=\"wind_ave\" title=\"$desc\">Average Windspeed</option>
<option $ss7 value=\"wind_gust\" title=\"$desc\">Wind Highest Gust</option>
<option $ss8 value=\"wind_dir\" title=\"$desc\">Wind Direction</option>
<option $ss9 value=\"rain\" title=\"$desc\">Rain</option>
</select>
</div>
<div class=\"form-group\">
<input class=\"btn btn-primary\" type=\"submit\" value=\"Select\"></form>
</div>
</div>
</div>
</div>



<div class=\"col-sm-4\">
<div class=\"card mb-3\">
<div class=\"card-header\">Other</div>
<div class=\"card-body\">
<a class=\"btn btn-primary\" href=windrose.php>WindRose</a></div>
</div>
</div>
</div>


";

if (isset($item))
     {

     include ('graph.php');
     echo "</div>";
     }
 if (isset($itemmm))
     {

     include ('graph2.php');
     echo "</div>";
     }
 if (!isset($itemmm) and !isset($item))
     {

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

     $from = strtolower($from{0});
     $to   = strtolower($to{0});

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
function humanTiming ($time){

    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
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
	//include ('forcast.php');

 include ('footer.php');
?>
