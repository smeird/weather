<?php
//include ('header.php');
if(isset($_GET['itemmm'])){$itemmm = $_GET['itemmm'];}

 $gt     = "areasplinerange";
//if ($itemmm=="wind_ave") {$gt="column";} else {$gt="areasplinerange";}
//if ($itemmm=="hum_in") {$gt="columnrange";}
//if ($itemmm=="wind_gust") {$gt="column";}
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


<a class=btn href=index.php?item=$item#graph>Back</a>
  <div class=\"card\" id=\"container2\" style=\"height: 800px; min-width: 100%\"></div></div>

"; } else {
echo "<a class=\"btn\" href=graph.php?FULL=1&item=$item>Click here for Full Screen</a>
 <div class=\"card\" id=\"container2\" style=\"height: 800px; min-width: 100%\"></div>
 ";
}

?>





<script type='text/javascript'>//<![CDATA[

        $(function() {

            // See source code from the JSONP handler at https://github.com/highslide-software/highcharts.com/blob/master/samples/data/from-sql.php
            $.getJSON('https://www.smeird.com/getgraphdata2.php?callback=?' + '&itemmm=<?php echo $itemmm; ?>', function(data) {

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
                            color: '#FF0000',
                            negativeColor: '#0088FF',
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
            $.getJSON('https://www.smeird.com/getgraphdata2.php?start=' + Math.round(e.min) +
                    '&end=' + Math.round(e.max) + '&callback=?' + '&itemmm=<?php echo $itemmm; ?>', function(data) {

                chart.series[0].setData(data);
                chart.hideLoading();
            });

        }

//]]>

</script>






    <a name="graph2"></a>
