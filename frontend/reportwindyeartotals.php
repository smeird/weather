<?php
include('header.php');
require_once '../dbconn.php';

echo "<div class=\"container mx-auto p-4\">\n";
echo "  <div class=\"bg-white shadow rounded p-4\">\n";
echo "    <h1 class=\"text-xl font-bold mb-4\">Monthly Wind Data Comparison</h1>\n";
echo "    <div class=\"overflow-x-auto\">\n";

// Execute the SQL query using a single aggregation and join to find the
// direction of the strongest gust for each month. This avoids the
// correlated subquery which was slowing the report.
$sql = "
SELECT
  g.year,
  g.month,
  ROUND(g.avg_wind_speed, 1) AS avg_wind_speed,
  ROUND(g.max_wind_gust, 1) AS max_wind_gust,
  SUBSTRING_INDEX(GROUP_CONCAT(a.windDir ORDER BY a.dateTime), ',', 1) AS max_wind_dir
FROM (
  SELECT
    YEAR(FROM_UNIXTIME(dateTime)) AS year,
    MONTH(FROM_UNIXTIME(dateTime)) AS month,
    AVG(windSpeed) AS avg_wind_speed,
    MAX(windGust) AS max_wind_gust
  FROM archive
  GROUP BY YEAR(FROM_UNIXTIME(dateTime)), MONTH(FROM_UNIXTIME(dateTime))
) g
JOIN archive a ON YEAR(FROM_UNIXTIME(a.dateTime)) = g.year
             AND MONTH(FROM_UNIXTIME(a.dateTime)) = g.month
             AND a.windGust = g.max_wind_gust
GROUP BY g.year, g.month
ORDER BY g.year, g.month;
";

 $result = db_query($sql);

// Initialize arrays to hold the data
$wind_data = array();
$years = array();
// Ensure all twelve months appear in the table, even if no data exists
$months = range(1, 12);

// Initialize arrays to hold maximum and minimum average wind speeds and gusts per month
$max_avg_speed_per_month = array();
$min_avg_speed_per_month = array();
$years_with_max_avg = array();
$years_with_min_avg = array();

$max_gusts_per_month = array();
$min_gusts_per_month = array();
$years_with_max_gust = array();
$years_with_min_gust = array();

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
    $year = intval($row['year']);
    $month = intval($row['month']);
    $avg_wind_speed = $row['avg_wind_speed'];
    $max_wind_gust = $row['max_wind_gust'];
    $max_wind_dir = round($row['max_wind_dir'], 1);

    // Store unique years
    if (!in_array($year, $years)) {
        $years[] = $year;
    }

    // Store the wind data
    $wind_data[$year][$month] = array(
        'avg_wind_speed' => $avg_wind_speed,
        'max_wind_gust' => $max_wind_gust,
        'max_wind_dir' => $max_wind_dir
    );

    // Update average wind speed extremes per month
    if (!isset($max_avg_speed_per_month[$month]) || $avg_wind_speed > $max_avg_speed_per_month[$month]) {
        $max_avg_speed_per_month[$month] = $avg_wind_speed;
        $years_with_max_avg[$month] = array($year);
    } elseif ($avg_wind_speed == $max_avg_speed_per_month[$month]) {
        $years_with_max_avg[$month][] = $year;
    }

    if (!isset($min_avg_speed_per_month[$month]) || $avg_wind_speed < $min_avg_speed_per_month[$month]) {
        $min_avg_speed_per_month[$month] = $avg_wind_speed;
        $years_with_min_avg[$month] = array($year);
    } elseif ($avg_wind_speed == $min_avg_speed_per_month[$month]) {
        $years_with_min_avg[$month][] = $year;
    }

    // Update maximum and minimum wind gusts per month
    if (!isset($max_gusts_per_month[$month]) || $max_wind_gust > $max_gusts_per_month[$month]) {
        $max_gusts_per_month[$month] = $max_wind_gust;
        $years_with_max_gust[$month] = array($year);
    } elseif ($max_wind_gust == $max_gusts_per_month[$month]) {
        $years_with_max_gust[$month][] = $year;
    }

    if (!isset($min_gusts_per_month[$month]) || $max_wind_gust < $min_gusts_per_month[$month]) {
        $min_gusts_per_month[$month] = $max_wind_gust;
        $years_with_min_gust[$month] = array($year);
    } elseif ($max_wind_gust == $min_gusts_per_month[$month]) {
        $years_with_min_gust[$month][] = $year;
    }
}

mysqli_free_result($result);

// Sort the years; months are already in chronological order
sort($years);

// Generate the HTML table
echo "        <table class=\"min-w-full bg-white text-sm\">\n";
echo "          <thead>\n";
echo "          <tr>\n";
echo "            <th class=\"px-4 py-2 bg-gray-200 text-gray-600 border-b border-gray-300 text-left text-sm uppercase font-semibold\" rowspan=\"2\">Month</th>";

foreach ($years as $year) {
  echo '            <th class="px-4 py-2 bg-gray-200 text-gray-600 text-center text-sm uppercase font-semibold" colspan="3">' . $year . '</th>';
}

echo "          </tr>\n";
echo "          <tr>";
foreach ($years as $year) {
  echo '            <th class="px-4 py-2 bg-gray-200 text-gray-600 text-center text-sm uppercase font-semibold">Avg</th>' .
       '<th class="px-4 py-2 bg-gray-200 text-gray-600 text-center text-sm uppercase font-semibold">Max</th>' .
       '<th class="px-4 py-2 bg-gray-200 text-gray-600 text-center text-sm uppercase font-semibold">Dir</th>';
}
echo "          </tr>\n";
echo "          </thead>\n";
echo "          <tbody class=\"divide-y divide-gray-200\">\n";

// Table rows for each month
foreach ($months as $month) {
    echo "          <tr>";
    // Display the month name
    $month_name = date('F', mktime(0, 0, 0, $month, 10));
    echo "            <td class=\"px-4 py-2 text-left\">$month_name</td>";

    // Display wind data for each year
    foreach ($years as $year) {
        if (isset($wind_data[$year][$month])) {
            $data = $wind_data[$year][$month];
            $avg_wind_speed = number_format($data['avg_wind_speed'], 1);
            $max_wind_gust = number_format($data['max_wind_gust'], 1);
            $max_wind_dir = number_format($data['max_wind_dir'], 1);

            $avg_class = "px-4 py-2 text-right";
            if (isset($years_with_max_avg[$month]) && in_array($year, $years_with_max_avg[$month])) {
                $avg_class .= " text-red-500";
            }
            if (isset($years_with_min_avg[$month]) && in_array($year, $years_with_min_avg[$month])) {
                $avg_class .= " text-blue-500";
            }

            $gust_class = "px-4 py-2 text-right";
            if (isset($years_with_max_gust[$month]) && in_array($year, $years_with_max_gust[$month])) {
                $gust_class .= " text-red-500";
            }
            if (isset($years_with_min_gust[$month]) && in_array($year, $years_with_min_gust[$month])) {
                $gust_class .= " text-blue-500";
            }

            echo '            <td class="' . $avg_class . '">' . $avg_wind_speed . "</td>";
            echo '            <td class="' . $gust_class . '">' . $max_wind_gust . "</td>";
            echo '            <td class="px-4 py-2 text-center">' . $max_wind_dir . "</td>";
        } else {
            // No data for this month and year
            echo '            <td class="px-4 py-2 text-right">-</td>' .
                 '<td class="px-4 py-2 text-right">-</td>' .
                 '<td class="px-4 py-2 text-center">-</td>';
        }
    }

    echo "          </tr>";
}

echo "</tbody></table>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";
?>
