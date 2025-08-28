<?php
include('header.php');
require_once 'dbconn.php';

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
$months = array();

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
    $max_wind_dir = $row['max_wind_dir'];

    // Store unique years and months
    if (!in_array($year, $years)) {
        $years[] = $year;
    }
    if (!in_array($month, $months)) {
        $months[] = $month;
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

// Sort the years and months
sort($years);
sort($months);

// Generate the HTML table
echo "        <table class=\"min-w-full divide-y divide-gray-200 border border-gray-300 text-sm\" data-tabulator=\"true\">\n";
echo "          <thead class=\"bg-gray-50\">\n";
echo "          <tr>\n";
echo "            <th rowspan=\"2\">Month</th>";

foreach ($years as $year) {
    echo "            <th colspan=\"3\">$year</th>";
}

echo "          </tr>\n";
echo "          <tr>";
foreach ($years as $year) {
    echo "            <th>Avg</th><th>Max</th><th>Dir</th>";
}
echo "          </tr>\n";
echo "          </thead>\n";
echo "          <tbody>\n";

// Table rows for each month
foreach ($months as $month) {
    echo "          <tr class=\"hover:bg-gray-100 odd:bg-gray-50\">";
    // Display the month name
    $month_name = date('F', mktime(0, 0, 0, $month, 10));
    echo "<td>$month_name</td>";

    // Display wind data for each year
    foreach ($years as $year) {
        if (isset($wind_data[$year][$month])) {
            $data = $wind_data[$year][$month];
            $avg_wind_speed = number_format($data['avg_wind_speed'], 1);
            $max_wind_gust = number_format($data['max_wind_gust'], 1);
            $max_wind_dir = $data['max_wind_dir'];

            $avg_style = "text-align: right;";
            if (in_array($year, $years_with_max_avg[$month])) {
                $avg_style .= " color: red;";
            }
            if (in_array($year, $years_with_min_avg[$month])) {
                $avg_style .= " color: blue;";
            }

            $gust_style = "text-align: right;";
            if (in_array($year, $years_with_max_gust[$month])) {
                $gust_style .= " color: red;";
            }
            if (in_array($year, $years_with_min_gust[$month])) {
                $gust_style .= " color: blue;";
            }

            echo "            <td style=\"$avg_style\">$avg_wind_speed</td>";
            echo "            <td style=\"$gust_style\">$max_wind_gust</td>";
            echo "            <td style=\"text-align: center;\">$max_wind_dir</td>";
        } else {
            // No data for this month and year
            echo "            <td style=\"text-align: right;\">-</td><td style=\"text-align: right;\">-</td><td style=\"text-align: center;\">-</td>";
        }
    }

    echo "          </tr>";
}

echo "</tbody></table>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";
?>
