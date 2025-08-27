<?php
if(isset($_GET['FULL'])) {
 include('header.php');
 echo "<div id=\"container3\"></div>";
} else {
 echo "<div id=\"container3\"></div>";
}
?>

        <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var seriesOptions = [],
                        yAxisOptions = [],
                        seriesCounter = 0,
                        names = ['rain', 'outTemp', 'barometer', 'outHumidity', 'windspeed'],
                        colors = Highcharts.getOptions().colors;

                    names.forEach(function(name, i) {

                        fetch('multidata.php?item=' + encodeURIComponent(name.toLowerCase()))
                          .then(response => response.json())
                          .then(function(data) {
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
                                }, {// last yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Wind Speed',
                                        style: {
                                            color: '#BBBBBB'
                                        }
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mph';
                                        },
                                        style: {
                                            color: '#BBBBBB'
                                        }
                                    },
                                    opposite: true
                                }],
                            series: seriesOptions
                        });
                    }
                });
        </script>
