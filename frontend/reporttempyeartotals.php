<?php
include('header.php');
require_once '../dbconn.php';

echo "<div class=\"container mx-auto p-4\">\n";
echo "  <div class=\"bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow rounded p-4\">\n";
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
// Ensure all twelve months appear in the table, even if no data exists
$months = range(1, 12);

// Initialize arrays to hold maximum and minimum temperatures per month
$max_temps_per_month = array();
$min_temps_per_month = array();
$years_with_max_temp = array();
$years_with_min_temp = array();
// Initialize arrays for yearly averages
$yearly_avg_sum = array();
$yearly_avg_count = array();
$yearly_max_sum = array();
$yearly_max_count = array();
$yearly_min_sum = array();
$yearly_min_count = array();

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
  $year = intval($row['year']);
  $month = intval($row['month']);
  $avg_temp = $row['avg_temp'];
  $max_temp = $row['max_temp'];
  $min_temp = $row['min_temp'];

  // Initialize yearly arrays
  if (!isset($yearly_avg_sum[$year])) {
    $yearly_avg_sum[$year] = 0;
    $yearly_avg_count[$year] = 0;
    $yearly_max_sum[$year] = 0;
    $yearly_max_count[$year] = 0;
    $yearly_min_sum[$year] = 0;
    $yearly_min_count[$year] = 0;
  }

  // Accumulate yearly sums and counts
  $yearly_avg_sum[$year] += $avg_temp;
  $yearly_avg_count[$year] += 1;
  $yearly_max_sum[$year] += $max_temp;
  $yearly_max_count[$year] += 1;
  $yearly_min_sum[$year] += $min_temp;
  $yearly_min_count[$year] += 1;

  // Store unique years
  if (!in_array($year, $years)) {
    $years[] = $year;
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

// Sort the years; months are already in chronological order
sort($years);

// Generate the HTML table
echo "        <table class=\"min-w-full bg-white dark:bg-gray-800 text-sm\">\n";
echo "          <thead>\n";
echo "          <tr>\n";
echo "            <th class=\"px-4 py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 text-left text-sm uppercase font-semibold\" rowspan=\"2\">Month</th>";

// Header cells for each year
foreach ($years as $year) {
  echo '            <th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center text-sm uppercase font-semibold" colspan="3">' . $year . '</th>';
}

echo "          </tr>\n";
echo "          <tr>";
foreach ($years as $year) {
  echo '            <th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center text-sm uppercase font-semibold">Avg</th>' .
       '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center text-sm uppercase font-semibold">Max</th>' .
       '<th class="px-4 py-2 text-gray-600 dark:text-gray-300 text-center text-sm uppercase font-semibold">Min</th>';
}
echo "          </tr>\n          </thead>\n          <tbody class=\"divide-y divide-gray-200 dark:divide-gray-700\">";

// Table rows for each month
foreach ($months as $month) {
  echo "          <tr>";
  // Display the month name
  $month_name = date('F', mktime(0, 0, 0, $month, 10));
  echo "            <td class=\"px-4 py-2 text-left\">$month_name</td>";

  // Display temperature data for each year
  foreach ($years as $year) {
    if (isset($temperature_data[$year][$month])) {
        $data = $temperature_data[$year][$month];
        $avg_temp = $data['avg_temp'];
        $max_temp = $data['max_temp'];
        $min_temp = $data['min_temp'];

        $avg_class = "px-4 py-2 text-right";
        $max_class = "px-4 py-2 text-right";
        $min_class = "px-4 py-2 text-right";

        if (isset($years_with_max_temp[$month]) && in_array($year, $years_with_max_temp[$month])) {
          $max_class .= " text-red-500";
        }

        if (isset($years_with_min_temp[$month]) && in_array($year, $years_with_min_temp[$month])) {
          $min_class .= " text-blue-500";
        }

        echo "            <td class=\"$avg_class\">$avg_temp</td>";
        echo "            <td class=\"$max_class\">$max_temp</td>";
        echo "            <td class=\"$min_class\">$min_temp</td>";
    } else {
        // No data for this month and year
        echo "            <td class=\"px-4 py-2 text-right\">-</td>";
        echo "            <td class=\"px-4 py-2 text-right\">-</td>";
        echo "            <td class=\"px-4 py-2 text-right\">-</td>";
    }
  }

  echo "          </tr>";
}

// Add row for yearly averages
echo "          <tr class=\"font-semibold bg-gray-100\">\n";
echo "            <td class=\"px-4 py-2 text-left\">Average</td>\n";
foreach ($years as $year) {
  if (isset($yearly_avg_sum[$year]) && $yearly_avg_count[$year] > 0) {
    $avg_avg = round($yearly_avg_sum[$year] / $yearly_avg_count[$year], 1);
    $avg_max = round($yearly_max_sum[$year] / $yearly_max_count[$year], 1);
    $avg_min = round($yearly_min_sum[$year] / $yearly_min_count[$year], 1);
    echo "            <td class=\"px-4 py-2 text-right\">$avg_avg</td>\n";
    echo "            <td class=\"px-4 py-2 text-right\">$avg_max</td>\n";
    echo "            <td class=\"px-4 py-2 text-right\">$avg_min</td>\n";
  } else {
    echo "            <td class=\"px-4 py-2 text-right\">-</td>\n";
    echo "            <td class=\"px-4 py-2 text-right\">-</td>\n";
    echo "            <td class=\"px-4 py-2 text-right\">-</td>\n";
  }
}
echo "          </tr>\n";

echo "          </tbody>\n";
echo "        </table>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";
