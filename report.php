<?php
include('header.php');
require_once 'dbconn.php';

echo "<div class=\"container\">
    <h2 class=\"display-1\">Precipitation Data (cm)</h2>
";

$year = 2023; // Change this to the desired year

$sql = "
SELECT
    d.year,
    d.month,
    ROUND(SUM(d.daily_rain_mm) / 10, 2) AS total_precip_cm,
    ROUND(md.max_daily_precip_mm / 10, 2) AS max_daily_precip_cm,
    GROUP_CONCAT(DISTINCT CASE WHEN d.daily_rain_mm = md.max_daily_precip_mm THEN d.day END) AS max_day,
    SUM(CASE WHEN d.daily_rain_mm > 0.03 THEN 1 ELSE 0 END) AS days_over_0_03cm,
    SUM(CASE WHEN d.daily_rain_mm > 0.3 THEN 1 ELSE 0 END) AS days_over_0_30cm,
    SUM(CASE WHEN d.daily_rain_mm > 3.0 THEN 1 ELSE 0 END) AS days_over_3_00cm
FROM (
    SELECT
        YEAR(FROM_UNIXTIME(dateTime)) AS year,
        MONTH(FROM_UNIXTIME(dateTime)) AS month,
        DAY(FROM_UNIXTIME(dateTime)) AS day,
        SUM(rain) AS daily_rain_mm
    FROM archive
    WHERE dateTime BETWEEN UNIX_TIMESTAMP('$year-01-01') AND UNIX_TIMESTAMP('$year-12-31 23:59:59')
    GROUP BY year, month, day
) AS d
JOIN (
    SELECT
        year,
        month,
        MAX(daily_rain_mm) AS max_daily_precip_mm
    FROM (
        SELECT
            YEAR(FROM_UNIXTIME(dateTime)) AS year,
            MONTH(FROM_UNIXTIME(dateTime)) AS month,
            DAY(FROM_UNIXTIME(dateTime)) AS day,
            SUM(rain) AS daily_rain_mm
        FROM archive
        WHERE dateTime BETWEEN UNIX_TIMESTAMP('$year-01-01') AND UNIX_TIMESTAMP('$year-12-31 23:59:59')
        GROUP BY year, month, day
    ) AS daily_data
    GROUP BY year, month
) AS md ON d.year = md.year AND d.month = md.month
GROUP BY d.year, d.month
ORDER BY d.year, d.month;
";

 $result = db_query($sql);

// Initialize arrays to hold monthly data and totals
$monthly_data = array();
$totals = array(
    'total_precip_cm' => 0,
    'max_daily_precip_cm' => 0,
    'max_month' => '',
    'max_day' => '',
    'days_over_0_03cm' => 0,
    'days_over_0_30cm' => 0,
    'days_over_3_00cm' => 0,
);

while ($row = mysqli_fetch_assoc($result)) {
    $month = intval($row['month']);
    $monthly_data[$month] = $row;

    // Accumulate totals
    $totals['total_precip_cm'] += $row['total_precip_cm'];
    $totals['days_over_0_03cm'] += $row['days_over_0_03cm'];
    $totals['days_over_0_30cm'] += $row['days_over_0_30cm'];
    $totals['days_over_3_00cm'] += $row['days_over_3_00cm'];

    // Check for maximum daily precipitation
    if ($row['max_daily_precip_cm'] > $totals['max_daily_precip_cm']) {
        $totals['max_daily_precip_cm'] = $row['max_daily_precip_cm'];
        $totals['max_month'] = date('M', mktime(0, 0, 0, $row['month'], 10));
        $totals['max_day'] = $row['max_day'];
    }
}

mysqli_free_result($result);

// Generate the HTML table
echo "<table class=\"min-w-full divide-y divide-gray-200 border border-gray-300 text-sm\" data-tabulator=\"true\">";
echo "<thead class=\"bg-gray-50\"><tr><th>YR</th><th>MO</th><th>TOTAL</th><th>MAX DAY</th><th>Over 0.03</th><th>Over 0.30</th><th>Over 3.00</th></tr></thead><tbody>";

for ($month = 1; $month <= 12; $month++) {
    if (isset($monthly_data[$month])) {
        $data = $monthly_data[$month];
        echo "<tr>
            <td>{$data['year']}</td>
            <td>" . str_pad($data['month'], 2, '0', STR_PAD_LEFT) . "</td>
            <td>{$data['total_precip_cm']}</td>
            <td>{$data['max_daily_precip_cm']} ({$data['max_day']})</td>
            <td>{$data['days_over_0_03cm']}</td>
            <td>{$data['days_over_0_30cm']}</td>
            <td>{$data['days_over_3_00cm']}</td>
        </tr>";
    } else {
        // No data for this month
        echo "<tr>
            <td>$year</td>
            <td>" . str_pad($month, 2, '0', STR_PAD_LEFT) . "</td>
            <td colspan='5'>No Data</td>
        </tr>";
    }
}

// Display totals
echo "<tr>
    <td colspan='2'>Totals</td>
    <td>{$totals['total_precip_cm']}</td>
    <td>{$totals['max_daily_precip_cm']} ({$totals['max_month']})</td>
    <td>{$totals['days_over_0_03cm']}</td>
    <td>{$totals['days_over_0_30cm']}</td>
    <td>{$totals['days_over_3_00cm']}</td>
</tr>";

echo "</tbody></table>";
echo "</div>";
?>
