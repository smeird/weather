<?php

if(isset($_GET['FULL'])) {
echo "

<script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"></script>

<script src=\"https://code.highcharts.com/stock/highstock.js\"></script>

<script src=\"https://code.highcharts.com/highcharts-more.js\"></script>
<script src=\"https://code.highcharts.com/modules/boost.js\"></script>
<script src=\"https://code.highcharts.com/modules/data.js\"></script>
<script src=\"https://code.highcharts.com/modules/exporting.js\"></script>
<script src=\"https://code.highcharts.com/modules/solid-gauge.js\"></script>


<script src=\"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js\"></script>

<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css\">
<script src=\"https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js\"></script>




<div id=\"container3\"></div>


"; } else {
echo "
<div  id=\"container3\"></div>



 ";

}

?>




        <script type="text/javascript">
                $(function() {
                    var seriesOptions = [],
                            yAxisOptions = [],
                            seriesCounter = 0,
                            names = ['rain', 'outTemp', 'barometer', 'outHumidity', 'windspeed'],
                            colors = Highcharts.getOptions().colors;

                    $.each(names, function(i, name) {

                        $.getJSON('https://www.smeird.com/multidata.php?item=' + name.toLowerCase() + '&callback=?', function(data) {
                            colorr = '#89A54E';
                            if (name == 'rain') {
                                typee = 'column';
                            } else {
                                typee = 'spline';
                            }


                            if (name == 'inHumidity') {
                                dashStylee = 'ShortDashDot', colorr = '#DDA54E', axis = 3;
                            }
                            if (name == 'outHumidity') {
                                dashStylee = 'ShortDash', colorr = '#DDA54E', axis = 3;
                            }
                            if (name == 'inTemp') {
                                axis = 0;
                            }
                            if (name == 'outTemp') {
                                dashStylee = 'ShortDot', colorr = '#ccA54E', axis = 0;
                            }
                            if (name == 'barometer') {
                                dashStylee = 'ShortDashDot', colorr = '#AA4643', axis = 2;
                            }
                            if (name == 'rain') {
                                dashStylee = 'solid', axis = 1, colorr = '#4572A7';
                            }
                            if (name == 'windspeed') {
                                dashStylee = 'shortDash', colorr = '#bbbbbb', axis = 4,typee = 'spline';
                            }


                            seriesOptions[i] = {
                                type: typee,
                                name: name,
                                yAxis: axis,
                                color: colorr,
                                dashStyle: dashStylee,
                                data: data,
                                animation: {
                                    duration: 2000
                                }
                            };

                            // As we're loading the data asynchronously, we don't know what order it will arrive. So
                            // we keep a counter and create the chart when all the data is loaded.
                            seriesCounter++;

                            if (seriesCounter == names.length) {
                                createChart();
                            }
                        });
                    });



                    // create the chart when all data is loaded
                    function createChart() {

                        chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'container3',
                                zoomType: 'xy'
                            },
                            title: {
                                text: ''
                            },
                            tooltip: {
                                shared: true,
                                useHTML: true,
                                crosshairs: {
                                    width: 2,
                                    color: 'gray',
                                    dashStyle: 'shortdot'
                                }
                            },
                            xAxis: {
                                type: 'datetime'

                            },
                            yAxis: [{// Primary yAxis
                                    labels: {
                                        formatter: function() {
                                            return this.value + 'C';
                                        },
                                        style: {
                                            color: '#89A54E'
                                        }
                                    },
                                    title: {
                                        text: 'Temperature',
                                        style: {
                                            color: '#89A54E'
                                        }
                                    },
                                    opposite: false

                                }, {// Secondary yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Rainfall',
                                        type: 'column',
                                        style: {
                                            color: '#4572A7'
                                        }
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mm';
                                        },
                                        style: {
                                            color: '#4572A7'
                                        }
                                    }

                                }, {// Tertiary yAxis
                                    gridLineWidth: 2,
                                    title: {
                                        text: 'Sea-Level Pressure',
                                        style: {
                                            color: '#AA4643'
                                        }
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mb';
                                        },
                                        style: {
                                            color: '#AA4643'
                                        }
                                    },
                                    opposite: true
                                }, {// qrt yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Humidity',
                                        style: {
                                            color: '#DDA54E'
                                        }

                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' %';
                                        },
                                        style: {
                                            color: '#DDA54E'
                                        }

                                    },
                                    opposite: true
                                }, {// 5 yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Windspeed',
                                        style: {
                                            color: '#CC00AA'
                                        }

                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' km/h';
                                        },
                                        style: {
                                            color: '#CC00AA'
                                        }

                                    },
                                    opposite: true
                                }



                            ],
                            plotOptions: {
                                spline: {
                                    lineWidth: 1,
                                    states: {
                                        hover: {
                                            lineWidth: 2
                                        }
                                    },
                                    marker: {
                                        enabled: false,
                                        states: {
                                            hover: {
                                                enabled: true,
                                                symbol: 'circle',
                                                radius: 1,
                                                lineWidth: 1
                                            }
                                        }
                                    },
                                },
                                scatter: {
                                  marker: {
                                    radius: 2,
                                    symbol: 'circle'
                                  }
                                },
                                column: {
                                    pointWidth: 5,
                                    pointPadding: 0,
                                    borderRadius: 0,
                                    lineWidth: 1,
                                }
                            },
                            series: seriesOptions
                        });
                    }

                });
                canvg(document.getElementById('canvas'), chart.getSVG())

        var canvas = document.getElementById("canvas");
        var img = canvas.toDataURL("image/png");

        document.write('<img src="'+img+'"/>');        
        </script>

<a name="graph3"></a>
<a class=btn href=index.php?item=$item#graph>Back</a>
