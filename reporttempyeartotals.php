<?php
include('header.php');
require_once 'dbconn.php';

echo "<div class=\"container mx-auto p-4\">\n";
echo "  <div class=\"bg-white shadow rounded p-4\">\n";
echo "    <h1 class=\"text-xl font-bold mb-4\">Monthly Outside Temperature Comparison (°C)</h1>\n";
echo "    <div class=\"overflow-x-auto\">\n";

// Execute the SQL query
$sql = "
SELECT
  YEAR(FROM_UNIXTIME(dateTime)) AS year,
  MONTH(FROM_UNIXTIME(dateTime)) AS month,
  ROUND(AVG(outTemp), 1) AS avg_temp,
  ROUND(MAX(outTemp), 1) AS max_temp,
  ROUND(MIN(outTemp), 1) AS min_temp
FROM archive
GROUP BY year, month
ORDER BY year, month;
";

 $result = db_query($sql);

// Initialize arrays to hold the data
$temperature_data = array();
$years = array();
$months = array();

// Initialize arrays to hold maximum and minimum temperatures per month
$max_temps_per_month = array();
$min_temps_per_month = array();
$years_with_max_temp = array();
$years_with_min_temp = array();

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
  $year = intval($row['year']);
  $month = intval($row['month']);
  $avg_temp = $row['avg_temp'];
  $max_temp = $row['max_temp'];
  $min_temp = $row['min_temp'];

  // Store unique years and months
  if (!in_array($year, $years)) {
    $years[] = $year;
  }
  if (!in_array($month, $months)) {
    $months[] = $month;
  }

  // Store the temperature data
  $temperature_data[$year][$month] = array(
    'avg_temp' => $avg_temp,
    'max_temp' => $max_temp,
    'min_temp' => $min_temp
  );

  // Update maximum temperatures per month
  if (!isset($max_temps_per_month[$month]) || $max_temp > $max_temps_per_month[$month]) {
    $max_temps_per_month[$month] = $max_temp;
    $years_with_max_temp[$month] = array($year);
  } elseif ($max_temp == $max_temps_per_month[$month]) {
    $years_with_max_temp[$month][] = $year;
  }

  // Update minimum temperatures per month
  if (!isset($min_temps_per_month[$month]) || $min_temp < $min_temps_per_month[$month]) {
    $min_temps_per_month[$month] = $min_temp;
    $years_with_min_temp[$month] = array($year);
  } elseif ($min_temp == $min_temps_per_month[$month]) {
    $years_with_min_temp[$month][] = $year;
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

// Header cells for each year
foreach ($years as $year) {
  echo "<th>$year Avg</th><th>$year Max</th><th>$year Min</th>";
}

echo "</tr>\n          </thead>\n          <tbody>";

// Table rows for each month
foreach ($months as $month) {
  echo "          <tr class=\"hover:bg-gray-100 odd:bg-gray-50\">";
  // Display the month name
  $month_name = date('F', mktime(0, 0, 0, $month, 10));
  echo "            <td>$month_name</td>";

  // Display temperature data for each year
  foreach ($years as $year) {
    if (isset($temperature_data[$year][$month])) {
        $data = $temperature_data[$year][$month];
        $avg_temp = $data['avg_temp'];
        $max_temp = $data['max_temp'];
        $min_temp = $data['min_temp'];

        // Styles for max and min temperatures
        $max_style = "text-align: right;";
        $min_style = "text-align: right;";

        // Highlight the maximum temperature
        if (in_array($year, $years_with_max_temp[$month])) {
            $max_style .= " color: red;";
        }

        // Highlight the minimum temperature
        if (in_array($year, $years_with_min_temp[$month])) {
            $min_style .= " color: blue;";
        }

        echo "            <td style=\"text-align: right;\">$avg_temp</td>";
        echo "            <td style=\"$max_style\">$max_temp</td>";
        echo "            <td style=\"$min_style\">$min_temp</td>";
    } else {
        // No data for this month and year
        echo "            <td style=\"text-align: right;\">-</td>";
        echo "            <td style=\"text-align: right;\">-</td>";
        echo "            <td style=\"text-align: right;\">-</td>";
    }
  }

  echo "          </tr>";
}

echo "          </tbody>\n";
echo "        </table>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";
?>

