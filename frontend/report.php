<?php
include('header.php');
require_once '../dbconn.php';

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
echo "<table class=\"min-w-full bg-white text-sm\">";
echo "<thead>\n";
echo "  <tr>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-left text-sm uppercase font-semibold\" rowspan=\"2\">YR</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-left text-sm uppercase font-semibold\" rowspan=\"2\">MO</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-right text-sm uppercase font-semibold\" rowspan=\"2\">TOTAL</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-right text-sm uppercase font-semibold\" rowspan=\"2\">MAX DAY</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-center text-sm uppercase font-semibold\" colspan=\"3\">Days Over</th>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-center text-sm uppercase font-semibold\">0.03</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-center text-sm uppercase font-semibold\">0.30</th>\n";
echo "    <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-center text-sm uppercase font-semibold\">3.00</th>\n";
echo "  </tr>\n";
echo "</thead><tbody class=\"divide-y divide-gray-200\">";

for ($month = 1; $month <= 12; $month++) {
    if (isset($monthly_data[$month])) {
        $data = $monthly_data[$month];
        echo "<tr>";
        echo "  <td class=\"px-4 py-2 text-left\">{$data['year']}</td>";
        echo "  <td class=\"px-4 py-2 text-left\">" . str_pad($data['month'], 2, '0', STR_PAD_LEFT) . "</td>";
        echo "  <td class=\"px-4 py-2 text-right\">{$data['total_precip_cm']}</td>";
        echo "  <td class=\"px-4 py-2 text-right\">{$data['max_daily_precip_cm']} ({$data['max_day']})</td>";
        echo "  <td class=\"px-4 py-2 text-right\">{$data['days_over_0_03cm']}</td>";
        echo "  <td class=\"px-4 py-2 text-right\">{$data['days_over_0_30cm']}</td>";
        echo "  <td class=\"px-4 py-2 text-right\">{$data['days_over_3_00cm']}</td>";
        echo "</tr>";
    } else {
        // No data for this month
        echo "<tr>";
        echo "  <td class=\"px-4 py-2 text-left\">$year</td>";
        echo "  <td class=\"px-4 py-2 text-left\">" . str_pad($month, 2, '0', STR_PAD_LEFT) . "</td>";
        echo "  <td class=\"px-4 py-2 text-center\" colspan='5'>No Data</td>";
        echo "</tr>";
    }
}

// Display totals
echo "<tr class=\"font-bold\">";
echo "  <td class=\"px-4 py-2 text-left\" colspan='2'>Totals</td>";
echo "  <td class=\"px-4 py-2 text-right\">{$totals['total_precip_cm']}</td>";
echo "  <td class=\"px-4 py-2 text-right\">{$totals['max_daily_precip_cm']} ({$totals['max_month']})</td>";
echo "  <td class=\"px-4 py-2 text-right\">{$totals['days_over_0_03cm']}</td>";
echo "  <td class=\"px-4 py-2 text-right\">{$totals['days_over_0_30cm']}</td>";
echo "  <td class=\"px-4 py-2 text-right\">{$totals['days_over_3_00cm']}</td>";
echo "</tr>";

echo "</tbody></table>";
echo "    </div>";
echo "  </div>";
echo "</div>";
?>
