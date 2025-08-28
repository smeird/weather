<?php
// connect to MySQL
 require_once '../dbconn.php';
 db_query('call clean_raw;');
echo "Cleaned";
flush();
 db_query('call weather.fill_cubes_1d();');
echo "..6..";
 db_query('call weather.fill_cubes_1h();');
echo "..5..";
 db_query('call weather.fill_cubes_1d();');
echo "..4..";
 db_query('call weather.fill_cubes_min_max_1d();');
echo "..3..";
 db_query('call weather.fill_cubes_min_max_1h();');
echo "..2..";
 db_query('call weather.fill_cubes_min_max_1m();');

 echo "Done";
 ?>
