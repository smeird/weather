<?php
$isFullView = isset($_GET['FULL']);
if($isFullView) {
 include('header.php');
 echo "<main class=\"overview-fullscreen\" aria-label=\"Full-screen weather trends\"><div id=\"container3\" role=\"status\">Loading observations…</div></main>";
} else {
 echo "<div id=\"container3\" role=\"status\">Loading observations…</div>";
}
?>

        <script type="text/javascript">
                // Fetch while deferred chart libraries are still loading.
                var overviewData = fetch('/backend/overview-data.php')
                  .then(function (response) {
                    if (!response.ok) throw new Error('Unable to load observations');
                    return response.json();
                  })
                  .catch(function (error) {
                    console.error('Failed loading overview:', error);
                    return null;
                  });
                document.addEventListener('DOMContentLoaded', function() {
                    var seriesOptions = [];
                    var names = ['rain', 'outTemp', 'barometer', 'outHumidity', 'windspeed', 'dewpoint', 'windchill'];
                    overviewData.then(function (data) {
                      var container = document.getElementById('container3');
                      if (!data || !window.WeatherCharts) {
                        container.textContent = 'Observations could not be loaded. Please reload to try again.';
                        return;
                      }
                      if (!names.some(function (name) { return data[name] && data[name].length; })) {
                        container.textContent = 'No observations available for the last 24 hours.';
                        return;
                      }
                      names.forEach(function (name) {
                        var axes = { rain: 1, barometer: 2, outHumidity: 3, windspeed: 4 };
                        var dashes = { outTemp: 'ShortDot', barometer: 'ShortDashDot', outHumidity: 'ShortDash', windspeed: 'ShortDash', dewpoint: 'Dot', windchill: 'Dash' };
                        seriesOptions.push({
                          type: name === 'rain' ? 'column' : 'spline',
                          name: name,
                          yAxis: axes[name] || 0,
                          dashStyle: dashes[name] || 'Solid',
                          data: data[name],
                          animation: false,
                          pointWidth: name === 'rain' ? 15 : undefined
                        });
                      });
                      container.removeAttribute('role');
                      createChart();
                    });

                    // create the chart when all data is loaded
                    function createChart() {
                        var validSeries = seriesOptions.filter(function(s) { return s !== undefined; });

                        var chart = new WeatherCharts.Chart({
                            chart: {
                                renderTo: 'container3',
                                zoomType: 'xy',
                                animation: false,
                                backgroundColor: 'transparent',
                                plotBackgroundColor: 'transparent',
                                plotBorderWidth: 0,
                                plotShadow: false
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
                                    opposite: true,
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
                                    visible: false,
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
                                    visible: false,
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
                                    visible: false,
                                    gridLineWidth: 1,
                                    title: {
                                        text: 'Wind Speed'
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value + ' kph';
                                        }
                                    },
                                    opposite: true
                                }],
                            series: validSeries
                        });
                    }
                });
        </script>
<?php
if ($isFullView) {
 include('footer.php');
}
?>
