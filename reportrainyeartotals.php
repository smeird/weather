<?php
include('header.php');
include('dbconn.php');

echo "<div class=\"container-fluid\">
  <div class=\"d-sm-flex align-items-center justify-content-between mb-2\">
    <h1 class=\"h3 mb-0 text-gray-800\">Rain by Year</h1>
  </div>
  <div class=\"card shadow\">
    <div class=card-header>Monthly Rainfall Comparison (mm)</div>
    <div class=\"card-body\">
";

// Execute the SQL query
$sql = "
SELECT
    YEAR(FROM_UNIXTIME(dateTime)) AS year,
    MONTH(FROM_UNIXTIME(dateTime)) AS month,
    ROUND(SUM(rain), 1) *10 AS total_rain_mm
FROM archive
GROUP BY year, month
ORDER BY year, month;
";

$result = mysqli_query($link, $sql);

if (!$result) {
    die('Invalid query: ' . mysqli_error($link));
}

// Initialize arrays to hold the data
$rainfall_data = array();
$years = array();
$months = array();

// Initialize arrays to hold maximum rainfall per month and the years that had the maximum
$max_rainfall_per_month = array();
$years_with_max_rainfall = array();

// Initialize array to hold total rainfall per year
$total_rainfall_per_year = array();

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
    $year = intval($row['year']);
    $month = intval($row['month']);
    $total_rain_mm = $row['total_rain_mm'];

    // Store unique years and months
    if (!in_array($year, $years)) {
        $years[] = $year;
    }
    if (!in_array($month, $months)) {
        $months[] = $month;
    }

    // Store the rainfall data
    $rainfall_data[$year][$month] = $total_rain_mm;

    // Update total rainfall per year
    if (!isset($total_rainfall_per_year[$year])) {
        $total_rainfall_per_year[$year] = $total_rain_mm;
    } else {
        $total_rainfall_per_year[$year] += $total_rain_mm;
    }

    // Update maximum rainfall per month
    if (!isset($max_rainfall_per_month[$month])) {
        // First data point for this month
        $max_rainfall_per_month[$month] = $total_rain_mm;
        $years_with_max_rainfall[$month] = array($year);
    } elseif ($total_rain_mm > $max_rainfall_per_month[$month]) {
        // New maximum found
        $max_rainfall_per_month[$month] = $total_rain_mm;
        $years_with_max_rainfall[$month] = array($year);
    } elseif ($total_rain_mm == $max_rainfall_per_month[$month]) {
        // Another year with the same maximum
        $years_with_max_rainfall[$month][] = $year;
    }
}

mysqli_free_result($result);

// Sort the years and months
sort($years);
sort($months);

// Generate the HTML table
echo "<table border='1' cellpadding='5' cellspacing='0'>
    <tr>
        <th>Month</th>";

// Right-align the header cells
foreach ($years as $year) {
    echo "<th style=\"text-align: right;\">$year</th>";
}

echo "</tr>";

// Table rows for each month
foreach ($months as $month) {
    echo "<tr>";
    // Display the month name
    $month_name = date('F', mktime(0, 0, 0, $month, 10));
    echo "<td>$month_name</td>";

    // Display rainfall data for each year
    foreach ($years as $year) {
        if (isset($rainfall_data[$year][$month])) {
            $rain_mm = $rainfall_data[$year][$month];
            $style = "text-align: right;";
            // Check if this is the maximum rainfall for this month
            if (in_array($year, $years_with_max_rainfall[$month])) {
                // Apply red color
                $style .= " color: red;";
            }
            echo "<td style=\"$style\">$rain_mm</td>";
        } else {
            echo "<td style=\"text-align: right;\">0</td>"; // No data for this month and year
        }
    }

    echo "</tr>";
}

// Add the total row
echo "<tr>";
echo "<td><strong>Total</strong></td>";

foreach ($years as $year) {
    $total_rain_mm = isset($total_rainfall_per_year[$year]) ? $total_rainfall_per_year[$year] : 0;
    echo "<td style=\"text-align: right;\"><strong>$total_rain_mm</strong></td>";
}

echo "</tr>";

echo "</table>";
echo "</div></div></div>";
?>

