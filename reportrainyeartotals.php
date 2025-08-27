<?php
include('header.php');
require_once 'dbconn.php';

echo "<div class=\"container mx-auto p-4\">\n";
echo "  <div class=\"bg-white shadow rounded p-4\">\n";
echo "    <h1 class=\"text-xl font-bold mb-4\">Rain by Year</h1>\n";
echo "    <div class=\"overflow-x-auto\">\n";

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

 $result = db_query($sql);

// Initialize arrays to hold the data
$rainfall_data = array();
$years = array();
$months = array();

// Initialize arrays to hold maximum rainfall per month and the years that had the maximum
// as well as minimum rainfall and the years that had the minimum
$max_rainfall_per_month = array();
$min_rainfall_per_month = array();
$years_with_max_rainfall = array();
$years_with_min_rainfall = array();

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
    if (!isset($max_rainfall_per_month[$month]) || $total_rain_mm > $max_rainfall_per_month[$month]) {
        $max_rainfall_per_month[$month] = $total_rain_mm;
        $years_with_max_rainfall[$month] = array($year);
    } elseif ($total_rain_mm == $max_rainfall_per_month[$month]) {
        $years_with_max_rainfall[$month][] = $year;
    }

    // Update minimum rainfall per month
    if (!isset($min_rainfall_per_month[$month]) || $total_rain_mm < $min_rainfall_per_month[$month]) {
        $min_rainfall_per_month[$month] = $total_rain_mm;
        $years_with_min_rainfall[$month] = array($year);
    } elseif ($total_rain_mm == $min_rainfall_per_month[$month]) {
        $years_with_min_rainfall[$month][] = $year;
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
echo "            <th>Month</th>";

foreach ($years as $year) {
    echo "<th>$year</th>";
}

echo "</tr>\n          </thead>\n          <tbody>";

// Table rows for each month
foreach ($months as $month) {
    echo "          <tr class=\"hover:bg-gray-100 odd:bg-gray-50\">";
    // Display the month name
    $month_name = date('F', mktime(0, 0, 0, $month, 10));
    echo "            <td>$month_name</td>";

    // Display rainfall data for each year
    foreach ($years as $year) {
        if (isset($rainfall_data[$year][$month])) {
            $rain_mm = $rainfall_data[$year][$month];
            $cell_class = "text-right";

            $border_style = "";
            if (in_array($year, $years_with_max_rainfall[$month])) {
                $border_style = "border:2px solid red;";
            }
            if (in_array($year, $years_with_min_rainfall[$month])) {
                $border_style = "border:2px solid blue;";
            }
            $style_attr = $border_style ? " style=\\\"$border_style\\\"" : "";
            echo "            <td class=\"$cell_class\"$style_attr>$rain_mm</td>";
        } else {
            echo "            <td class=\"text-right\">0</td>"; // No data for this month and year
        }
    }

    echo "          </tr>";
}

// Add the total row
echo "          <tr class=\"bg-gray-200 font-bold\">";
echo "            <td>Total</td>";

foreach ($years as $year) {
    $total_rain_mm = isset($total_rainfall_per_year[$year]) ? $total_rainfall_per_year[$year] : 0;
    echo "            <td class=\"text-right\">$total_rain_mm</td>";
}

echo "          </tr>";

echo "          </tbody>\n";
echo "        </table>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";
?>

