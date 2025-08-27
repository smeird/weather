<?php
// connect to MySQL
 include ('dbconn.php');
mysqli_query($link,'call clean_raw;') or die(mysqli_error());
echo "Cleaned";
flush();
mysqli_query($link,'call weather.fill_cubes_1d();') or die(mysqli_error());
echo "..6..";
mysqli_query($link,'call weather.fill_cubes_1h();') or die(mysqli_error());
echo "..5..";
mysqli_query($link,'call weather.fill_cubes_1d();') or die(mysqli_error());
echo "..4..";
mysqli_query($link,'call weather.fill_cubes_min_max_1d();') or die(mysqli_error());
echo "..3..";
mysqli_query($link,'call weather.fill_cubes_min_max_1h();') or die(mysqli_error());
echo "..2..";
mysqli_query($link,'call weather.fill_cubes_min_max_1m();') or die(mysqli_error());

echo "Done";
?>