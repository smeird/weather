<?php
include('header.php');
require_once '../dbconn.php';

$sql = "SELECT YEAR(FROM_UNIXTIME(dateTime)) AS year, MONTH(FROM_UNIXTIME(dateTime)) AS month,
               ROUND(AVG(outTemp),1) AS avg_temp, ROUND(MAX(outTemp),1) AS max_temp, ROUND(MIN(outTemp),1) AS min_temp
        FROM archive WHERE outTemp IS NOT NULL GROUP BY year, month ORDER BY year, month";
$result = db_query($sql);
$data = $years = [];
$recordHigh = null; $recordLow = null; $sum = 0; $count = 0;
$monthlyHigh = $monthlyLow = [];
while ($row = mysqli_fetch_assoc($result)) {
  $year = (int)$row['year']; $month = (int)$row['month'];
  $data[$year][$month] = ['avg'=>(float)$row['avg_temp'],'max'=>(float)$row['max_temp'],'min'=>(float)$row['min_temp']];
  $years[$year] = true; $sum += (float)$row['avg_temp']; $count++;
  $recordHigh = $recordHigh === null ? (float)$row['max_temp'] : max($recordHigh, (float)$row['max_temp']);
  $recordLow = $recordLow === null ? (float)$row['min_temp'] : min($recordLow, (float)$row['min_temp']);
  $monthlyHigh[$month] = isset($monthlyHigh[$month]) ? max($monthlyHigh[$month], (float)$row['max_temp']) : (float)$row['max_temp'];
  $monthlyLow[$month] = isset($monthlyLow[$month]) ? min($monthlyLow[$month], (float)$row['min_temp']) : (float)$row['min_temp'];
}
mysqli_free_result($result);
$years = array_keys($years); sort($years); $latestYear = end($years);
$latestValues = $latestYear ? array_column($data[$latestYear] ?? [], 'avg') : [];
$latestAverage = $latestValues ? array_sum($latestValues) / count($latestValues) : null;
$series = [];
foreach ($years as $year) {
  $points = [];
  for ($month=1; $month<=12; $month++) $points[] = isset($data[$year][$month]) ? $data[$year][$month]['avg'] : null;
  $series[] = ['name'=>(string)$year,'data'=>$points,'visible'=>$year >= $latestYear - 3];
}
?>
<main class="annual-workspace">
  <header class="annual-header">
    <div><span class="annual-eyebrow">Annual reports · Temperature</span><h1>Temperature yearbook</h1><p>Compare monthly means and observed temperature envelopes across the complete station archive.</p></div>
    <nav class="annual-switcher" aria-label="Annual report type"><a aria-current="page" href="/reporttempyeartotals.php"><i class="fas fa-temperature-half"></i> Temperature</a><a href="/reportrainyeartotals.php"><i class="fas fa-cloud-rain"></i> Rain</a><a href="/reportwindyeartotals.php"><i class="fas fa-wind"></i> Wind</a></nav>
  </header>
  <section class="annual-kpis" aria-label="Temperature summary">
    <div class="annual-kpi" style="--kpi-accent:#2563eb"><span>Archive mean</span><strong><?php echo $count ? number_format($sum/$count,1) : '—'; ?></strong><small>°C</small></div>
    <div class="annual-kpi" style="--kpi-accent:#dc5b49"><span>Record high</span><strong><?php echo $recordHigh === null ? '—' : number_format($recordHigh,1); ?></strong><small>°C</small></div>
    <div class="annual-kpi" style="--kpi-accent:#3b82c4"><span>Record low</span><strong><?php echo $recordLow === null ? '—' : number_format($recordLow,1); ?></strong><small>°C</small></div>
    <div class="annual-kpi" style="--kpi-accent:#0891b2"><span><?php echo $latestYear; ?> mean to date</span><strong><?php echo $latestAverage === null ? '—' : number_format($latestAverage,1); ?></strong><small>°C</small></div>
  </section>
  <section class="annual-chart-panel"><div class="annual-panel-header"><div><h2>Monthly mean comparison</h2><p>Latest four years shown · use the legend to reveal earlier years</p></div></div><div id="annual-temp-chart" class="annual-chart"></div></section>
  <section class="annual-table-panel"><div class="annual-panel-header"><div><h2>Monthly detail</h2><p>Avg / high / low in °C · monthly archive extremes are highlighted</p></div></div><div class="annual-table-scroll"><table class="annual-table"><thead><tr><th class="month-cell" rowspan="2">Month</th><?php foreach($years as $year){ ?><th colspan="3"><?php echo $year; ?></th><?php } ?></tr><tr><?php foreach($years as $year){ ?><th>Avg</th><th>High</th><th>Low</th><?php } ?></tr></thead><tbody>
  <?php for($month=1;$month<=12;$month++){ ?><tr><td class="month-cell"><?php echo date('F',mktime(0,0,0,$month,10)); ?></td><?php foreach($years as $year){ $cell=$data[$year][$month]??null; if(!$cell){ ?><td>—</td><td>—</td><td>—</td><?php }else{ ?><td><?php echo number_format($cell['avg'],1); ?></td><td class="<?php echo $cell['max']===$monthlyHigh[$month]?'is-high':''; ?>"><?php echo number_format($cell['max'],1); ?></td><td class="<?php echo $cell['min']===$monthlyLow[$month]?'is-low':''; ?>"><?php echo number_format($cell['min'],1); ?></td><?php }} ?></tr><?php } ?>
  </tbody></table></div></section>
</main>
<script>document.addEventListener('DOMContentLoaded',function(){Highcharts.chart('annual-temp-chart',{chart:{type:'spline',backgroundColor:'transparent',spacing:[16,12,8,8]},title:{text:null},xAxis:{categories:<?php echo json_encode(array_map(fn($m)=>date('M',mktime(0,0,0,$m,10)),range(1,12))); ?>},yAxis:{title:{text:'Mean temperature (°C)'},startOnTick:false,endOnTick:false,minPadding:.1,maxPadding:.1},tooltip:{shared:true,valueSuffix:' °C'},legend:{align:'center',verticalAlign:'bottom'},plotOptions:{series:{marker:{enabled:false},lineWidth:2,connectNulls:false}},series:<?php echo json_encode($series); ?>,responsive:{rules:[{condition:{maxWidth:620},chartOptions:{yAxis:{title:{text:null}},legend:{itemStyle:{fontSize:'10px'}}}}]}});});</script>
<?php include('footer.php'); ?>
