<?php
include('header.php');
include('dbconn.php');

echo "<div class=\"container\"><div class=card>
    <h1 class=\"display-4\">Monthly Wind Data Comparison</h1>
";

// Execute the SQL query
$sql = "
SELECT
    t.year,
    t.month,
    ROUND(t.avg_wind_speed, 1) AS avg_wind_speed,
    ROUND(t.max_wind_gust, 1) AS max_wind_gust,
    t.max_wind_dir
FROM (
    SELECT
        YEAR(FROM_UNIXTIME(dateTime)) AS year,
        MONTH(FROM_UNIXTIME(dateTime)) AS month,
        AVG(windSpeed) AS avg_wind_speed,
        MAX(windGust) AS max_wind_gust,
        (SELECT windDir FROM archive a2 WHERE a2.dateTime = a1.dateTime AND a2.windGust = max_wind_gust LIMIT 1) AS max_wind_dir
    FROM archive a1
    GROUP BY year, month
) t
ORDER BY t.year, t.month;
";

$result = mysqli_query($link, $sql);

if (!$result) {
    die('Invalid query: ' . mysqli_error($link));
}

// Initialize arrays to hold the data
$wind_data = array();
$years = array();
$months = array();

// Initialize arrays to hold maximum wind gusts per month and the years that had the maximum
$max_gusts_per_month = array();
$years_with_max_gust = array();

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

    // Update maximum wind gusts per month
    if (!isset($max_gusts_per_month[$month]) || $max_wind_gust > $max_gusts_per_month[$month]) {
        $max_gusts_per_month[$month] = $max_wind_gust;
        $years_with_max_gust[$month] = array($year);
    } elseif ($max_wind_gust == $max_gusts_per_month[$month]) {
        $years_with_max_gust[$month][] = $year;
    }
}

mysqli_free_result($result);

// Sort the years and months
sort($years);
sort($months);

// Generate the HTML table
echo "<table border='1' cellpadding='5' cellspacing='0'>
    <tr>
        <th rowspan='2'>Month</th>";

// Header cells for each year
foreach ($years as $year) {
    echo "<th colspan='3' style=\"text-align: center;\">$year</th>";
}

echo "</tr><tr>";
// Sub-headers for Avg Speed, Max Gust, Max Gust Dir
foreach ($years as $year) {
    echo "<th style=\"text-align: right;\">Avg Speed</th>";
    echo "<th style=\"text-align: right;\">Max Gust</th>";
    echo "<th style=\"text-align: right;\">Gust Dir</th>";
}

echo "</tr>";

// Table rows for each month
foreach ($months as $month) {
    echo "<tr>";
    // Display the month name
    $month_name = date('F', mktime(0, 0, 0, $month, 10));
    echo "<td>$month_name</td>";

    // Display wind data for each year
    foreach ($years as $year) {
        if (isset($wind_data[$year][$month])) {
            $data = $wind_data[$year][$month];
            $avg_wind_speed = $data['avg_wind_speed'];
            $max_wind_gust = $data['max_wind_gust'];
            $max_wind_dir = $data['max_wind_dir'];

            // Style for max wind gust
            $max_gust_style = "text-align: right;";

            // Highlight the maximum wind gust
            if (in_array($year, $years_with_max_gust[$month])) {
                $max_gust_style .= " color: red;";
            }

            echo "<td style=\"text-align: right;\">$avg_wind_speed</td>";
            echo "<td style=\"$max_gust_style\">$max_wind_gust</td>";
            echo "<td style=\"text-align: right;\">$max_wind_dir</td>";
        } else {
            // No data for this month and year
            echo "<td style=\"text-align: right;\">-</td>";
            echo "<td style=\"text-align: right;\">-</td>";
            echo "<td style=\"text-align: right;\">-</td>";
        }
    }

    echo "</tr>";
}

echo "</table>";
echo "</div>";
?>
