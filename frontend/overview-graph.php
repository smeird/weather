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
                        fetch('../backend/multidata.php?item=' + encodeURIComponent(name.toLowerCase()))
                          .then(function(response) {
                            if (!response.ok) {
                              throw new Error('Network response was not ok');
                            }
                            return response.json();
                          })
                          .then(function(data) {
                            let typee = name === 'rain' ? 'column' : 'spline';
                            let dashStylee = 'solid';
                            let axis = 0;

                            if (name == 'inHumidity') { dashStylee = 'ShortDashDot'; axis = 3; }
                            if (name == 'outHumidity') { dashStylee = 'ShortDash'; axis = 3; }
                            if (name == 'inTemp') { axis = 0; }
                            if (name == 'outTemp') { dashStylee = 'ShortDot'; axis = 0; }
                            if (name == 'barometer') { dashStylee = 'ShortDashDot'; axis = 2; }
                            if (name == 'rain') { dashStylee = 'solid'; axis = 1; }
                            if (name == 'windspeed') { dashStylee = 'shortDash'; axis = 4; typee = 'spline'; }

                            seriesOptions[i] = {
                                type: typee,
                                name: name,
                                yAxis: axis,
                                dashStyle: dashStylee,
                                data: data,
                                animation: {
                                    duration: 2000
                                }
                            };

                            seriesCounter++;
                            if (seriesCounter == names.length) {
                                createChart();
                            }
                          })
                          .catch(function(error) {
                            console.error('Failed loading ' + name + ':', error);
                            seriesCounter++;
                            if (seriesCounter == names.length) {
                                createChart();
                            }
                          });
                    });


                    // create the chart when all data is loaded
                    function createChart() {
                        var validSeries = seriesOptions.filter(function(s) { return s !== undefined; });

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
                                        }
                                    },
                                    title: {
                                        text: 'Temperature'
                                    },
                                    opposite: false

                                }, {// Secondary yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Rainfall',
                                        type: 'column'
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mm';
                                        }
                                    }

                                }, {// Tertiary yAxis
                                    gridLineWidth: 2,
                                    title: {
                                        text: 'Sea-Level Pressure'
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mb';
                                        }
                                    },
                                    opposite: true
                                }, {// qrt yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Humidity'

                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' %';
                                        }

                                    },
                                    opposite: true
                                }, {// last yAxis
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Wind Speed'
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' mph';
                                        }
                                    },
                                    opposite: true
                                }],
                            series: validSeries
                        });
                    }
                });
        </script>
