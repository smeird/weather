<?php

 $link = mysqli_connect('accounts', 'root', '92987974');
 if (!$link)
     {
     die('Not connected : ' . mysqli_error());
     }

// make auto the current db
 $db_selected = mysqli_select_db($link,'wn_pro_mysql');
 if (!$db_selected)
     {
     die('Can\'t use foo : ' . mysqli_error());
     }

//echo $SQL;
 $SQL    = "SELECT distinct word FROM wn_pro_mysql.wn_synset";
 $result = mysqli_query($link,$SQL);
 if (!$result)
     {
     die('Invalid query: ' . mysqli_error());
     }
 echo "<table>";
 while ($row = mysqli_fetch_row($result))
     {
     $word    = strtolower($row[0]);
     $arr1    = str_split($word);
     $numbers = array("1","2","3","4","5","6","7","8","9","10","11","12","13","14",
      "15","16","17","18","19","20","21","22","23","24","25","26");
     $words   = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
      "p","q","r","s","t","u","v","w","x","y","z");
     $change  = str_replace($words, $numbers, $arr1);
     $value   = array_sum($change);

     if ($value == 100)
         {
//if (count($change)<6){
         $c++;
         $working = implode("+", $change);
         echo "<tr><td>$c</td><td>" . $word . "</td><td>$working=" . $value . "</td></tr>";
         }

//if (substr($value, -1)==0){
//$c++;
//echo "<tr><td>$c</td><td>".$word."</td><td>".$value."</td></tr>";
//}
     }

 echo "</table>";
?>