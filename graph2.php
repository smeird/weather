<?php
if(isset($_GET['itemmm'])){$itemmm = $_GET['itemmm'];}

$gt     = "areasplinerange";

if(isset($_GET['FULL'])) {
 include('header.php');
 echo "<a class=\"inline-block bg-blue-500 text-white px-4 py-2 rounded\" href=\"index.php#graph\">Back</a>\n  <div id=\"container2\" class=\"bg-white shadow rounded p-4\" style=\"height: 800px; min-width: 100%\"></div>";
} else {
 echo "<a class=\"inline-block bg-blue-500 text-white px-4 py-2 rounded\" href=graph2.php?FULL=1&itemmm=$itemmm>Click here for Full Screen</a>\n <div id=\"container2\" class=\"bg-white shadow rounded p-4\" style=\"height: 800px; min-width: 100%\"></div>\n ";
}
?>

<script type='text/javascript'>//<![CDATA[
        document.addEventListener('DOMContentLoaded', function() {
            // See source code from the JSONP handler at https://github.com/highslide-software/highcharts.com/blob/master/samples/data/from-sql.php
            fetch('https://www.smeird.com/getgraphdata2.php?itemmm=<?php echo $itemmm; ?>')
              .then(response => response.json())
              .then(function(data) {
                // Add a null value for the end date
                //data = [].concat(data, [[Date.UTC(2012, 11, 10, 19, 59), null, null, null, null]]);
                // create the chart
                window.chart = new Highcharts.StockChart({
                    chart: {
                        renderTo: 'container2',
                        type: '<?php echo $gt; ?>',
                        zoomType: 'x'
                    },
                    navigator: {
                        adaptToUpdatedData: false,
                        series: {
                            data: data

                        }
                    },
                    title: {
                        text: 'Historical Min & Max Data'
                    },
                    subtitle: {
                        text: 'Data:<?php echo $itemmm; ?>'
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
                        selected: 0 // all
                    },
                    xAxis: {
                        events: {
                            afterSetExtremes: afterSetExtremes
                        },

                    },
                    plotOptions: {
                        areasplinerange: {
                            lineWidth: 1,
                            marker: {
                                enabled: false,
                                states: {
                                    hover: {
                                        enabled: true,
                                        radius: 5
                                    }
                                }
                            },
                            shadow: false,
                            states: {
                                hover: {
                                    lineWidth: 0.1
                                }
                            },
                            threshold: null
                        }
                    },
                    series: [{
                            name: '<?php echo $itemmm; ?>',
                            data: data,
                            lineWidth: 1,
                            id: 'primary',
                            dataGrouping: {
                                enabled: false
                            }
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
            fetch('https://www.smeird.com/getgraphdata2.php?start=' + Math.round(e.min) +
                    '&end=' + Math.round(e.max) + '&itemmm=<?php echo $itemmm; ?>')
              .then(response => response.json())
              .then(function(data) {
                chart.series[0].setData(data);
                chart.hideLoading();
              });

        }

//]]>
</script>

    <a name="graph2"></a>
