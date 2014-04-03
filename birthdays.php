<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
include ("include/format.inc.php");
include ("include/photo.class.php");
include ("include/guess.inc.php");

require_once 'include/templating.php';

// get data from db
$sql="SELECT DISTINCT $table.*, $month_lookup.* ,
      IF ($month_lookup.bmonth_num < MONTH( CURDATE( ) )
        OR $month_lookup.bmonth_num = MONTH( CURDATE( ) )
            AND $table.bday < DAYOFMONTH( CURDATE( ) ) , CONCAT( ' ', YEAR( CURDATE( ) ) +1 ) , ''
      ) display_year,
      IF (
      $month_lookup.bmonth_num < MONTH( CURDATE( ) )
      OR $month_lookup.bmonth_num = MONTH( CURDATE( ) )
      AND $table.bday < DAYOFMONTH( CURDATE( ) ) , $month_lookup.bmonth_num+12, $month_lookup.bmonth_num
      )*32+bday prio
      FROM $month_lookup,
      $base_from_where AND $table.bmonth = $month_lookup.bmonth AND $table.bday > 0
      ORDER BY prio ASC;";

$result = mysql_query($sql);
$resultsnumber = mysql_num_rows($result);

// create array for template
$data["dates"] = array();
$i = 0;
while ($myrow = mysql_fetch_array($result)) {
  $data["dates"][$i] = array();

  $data["dates"][$i]["firstname"]  = $myrow["firstname"];
  $data["dates"][$i]["id"]         = $myrow["id"];
  $data["dates"][$i]["lastname"]   = $myrow["lastname"];
  $data["dates"][$i]["middlename"] = $myrow["middlename"];

  $data["dates"][$i]["email"]  = ($myrow["email"] != "" ? $myrow["email"] : ($myrow["email2"] != "" ? $myrow["email2"] : ""));
  $data["dates"][$i]["email2"] = $myrow["email2"];

  $data["dates"][$i]["home"]   = $myrow["home"];
  $data["dates"][$i]["mobile"] = $myrow["mobile"];
  $data["dates"][$i]["work"]   = $myrow["work"];

  $data["dates"][$i]["homepage"] = "";
  $data["dates"][$i]["homepage_guessed"] = false;
  if($myrow["homepage"] != "") {
    $data["dates"][$i]["homepage"] = (strcasecmp(substr($myrow["homepage"], 0, strlen("http")),"http")== 0
                ? $myrow["homepage"]
                : "http://".$myrow["homepage"]);
  } 
  elseif(($myrow["homepage"] = guessHomepage($myrow["email"], $myrow["email2"])) != "") {
    $data["dates"][$i]["homepage"] = "http:/".$myrow["homepage"];
    $data["dates"][$i]["homepage_guessed"] = true;
  }

  // Phone order home->mobile->work
  $phone = ($myrow["home"] != "" ? $myrow["home"] : ($myrow["mobile"] != "" ? $myrow["mobile"] : $myrow["work"]));
  $data["dates"][$i]["phone"] = str_replace("'", "",
            str_replace('/', "",
            str_replace(" ", "",
            str_replace(".", "", $phone))));

  $data["dates"][$i]["bday"]         = $myrow["bday"];
  $data["dates"][$i]["bmonth"]       = $myrow["bmonth"];
  $data["dates"][$i]["bmonth_num"]   = $myrow["bmonth_num"];
  $data["dates"][$i]["byear"]        = $myrow["byear"];
  //$data["dates"][$i]["display_year"] = $myrow["display_year"];

  // Current year

  $addr = new Address($myrow);
  $data["dates"][$i]["age"] = "";
  if($addr->getBirthday()->getAge() != -1) {
    $data["dates"][$i]["age"] = $addr->getBirthday()->getAge();
  }
    

  // Last year
  /* -- commented to reduce traffic
  $date = gmmktime(0,0,0,$bmonth_num,$bday,date('Y')-1,0);
  Birthday2vCal($date);
  */

  $data["dates"][$i]["date"] = gmmktime(0,0,0,$myrow["bmonth_num"],$myrow["bday"],date('Y'));

  // Next year
  $data["dates"][$i]["date2"] = gmmktime(0,0,0,$myrow["bmonth_num"],$myrow["bday"],date('Y')+1);
  $data["dates"][$i]["age2"] = ($myrow["byear"] != "" ? " (".(date('Y', $data["dates"][$i]["date2"])-$myrow["byear"]).")" : "");
  
  $i++;
}

// sort array by month and day of birthday
function cmp($a, $b) {
  if($a["bmonth_num"] != $b["bmonth_num"]) {  
    return strcmp($a["bmonth_num"], $b["bmonth_num"]);
  }
  else {
    return strcmp($a["bday"], $b["bday"]);
  }
}
usort($data["dates"], "cmp");

// choose template
$use_ics = isset($_REQUEST['ics']);
if($use_ics) {
  header('Content-type: text/calendar; charset=utf-8');
  header('Content-Disposition: inline; filename=calendar.ics');

  $data["version"] = $version;
   
  $template = "birthdays_ics.twig";
}
else {
  header('Content-Type:text/html; charset=UTF-8');
  
  $template = "birthdays.twig";
}

echo $twig->render($template, $data);