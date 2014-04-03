<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
include ("include/format.inc.php");
include ("include/photo.class.php");
include ("include/guess.inc.php");

require_once 'include/templating.php';


$tablespace = 0;

$alternate = "2";

	
/*
  function Birthday2vCal($date, $age) {

  	global $id, $firstname, $middlename, $lastname, $email, $email2, $home, $mobile, $work, $byear;

    echo "BEGIN:VEVENT\r\n";
    echo "UID:".date('Y', $date).$id."@php-addressbook.sourceforge.net\r\n";
    echo "DTSTART;VALUE=DATE:".date("Ymd", $date)."\r\n";
    echo "DTEND;VALUE=DATE:".date("Ymd", $date+(24*3600))."\r\n";
    echo "DTSTAMP:".date("Ymd\THi00\Z")."\r\n";
    echo "CREATED:".date("Ymd\THi00\Z")."\r\n";
    echo "DESCRIPTION:\r\n";
    echo "LAST-MODIFIED:".date("Ymd\THi00\Z")."\r\n";
    echo "LOCATION:\r\n";
    echo "STATUS:CONFIRMED\r\n";
    echo "SUMMARY:".ucfmsg("BIRTHDAY")." ".trim($firstname.(isset($middlename) ? " ".$middlename:"")." ".$lastname)
                    ." ".$age."\r\n";
    echo "DESCRIPTION:Mail:\\n- ".$email
                         ."\\n- ".$email2
                         ."\\n- ".$email3
                         ."\\n\\n".ucfmsg("TELEPHONE")
                         .($home   != "" ? "\\n- ".$home   : "")
                         .($mobile != "" ? "\\n- ".$mobile : "")
                         .($work   != "" ? "\\n- ".$work   : "")
                         ."\r\n";
    echo "END:VEVENT\r\n";
  }*/

$lastmonth = '';

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

$data["dates"] = array();
$i = 0;

while ($myrow = mysql_fetch_array($result)) {
  $data["dates"][$i] = array();

  $date["dates"][$i]["firstname"]  = $myrow["firstname"];
  $date["dates"][$i]["id"]         = $myrow["id"];
  $date["dates"][$i]["lastname"]   = $myrow["lastname"];
  $date["dates"][$i]["middlename"] = $myrow["middlename"];

  $date["dates"][$i]["email"]  = ($myrow["email"] != "" ? $myrow["email"] : ($myrow["email2"] != "" ? $myrow["email2"] : ""));
  $date["dates"][$i]["email2"] = $myrow["email2"];

  $date["dates"][$i]["home"]   = $myrow["home"];
  $date["dates"][$i]["mobile"] = $myrow["mobile"];
  $date["dates"][$i]["work"]   = $myrow["work"];

  $date["dates"][$i]["homepage"] = $myrow["homepage"];

  // Phone order home->mobile->work
  $phone = ($myrow["home"] != "" ? $myrow["home"] : ($myrow["mobile"] != "" ? $myrow["mobile"] : $myrow["work"]));
  $date["dates"][$i]["phone"] = str_replace("'", "",
            str_replace('/', "",
            str_replace(" ", "",
            str_replace(".", "", $phone))));

  $date["dates"][$i]["bday"]         = $myrow["bday"];
  $date["dates"][$i]["bmonth"]       = $myrow["bmonth"];
  $date["dates"][$i]["bmonth_num"]   = $myrow["bmonth_num"];
  $date["dates"][$i]["byear"]        = $myrow["byear"];
  $date["dates"][$i]["display_year"] = $myrow["display_year"];

  // Current year

  $addr = new Address($myrow);
  $date["dates"][$i]["age"] = "";
  if($addr->getBirthday()->getAge() != -1) {
    $date["dates"][$i]["age"] = $addr->getBirthday()->getAge();
  }
    
  if($use_ics) {

      // Last year
      /* -- commented to reduce traffic
      $date = gmmktime(0,0,0,$bmonth_num,$bday,date('Y')-1,0);
      Birthday2vCal($date);
      */

      $date["dates"][$i]["date"] = gmmktime(0,0,0,$myrow["bmonth_num"],$myrow["bday"],date('Y'),0);

      // Next year
      $date["dates"][$i]["date2"] = gmmktime(0,0,0,$myrow["bmonth_num"],$myrow["bday"],date('Y')+1);
      $date["dates"][$i]["age2"] = ($myrow["byear"] != "" ? " (".(date('Y', $date["dates"][$i]["date2"])-$myrow["byear"]).")" : "");

  } else {

      if($lastmonth != $bmonth) {

            $lastmonth = $bmonth;

            if ($tablespace >=1) {
                    echo "<tr class='tablespace'><td colspan='10'><br /></td></tr>";
            } else {}

            echo "<tr><th colspan='11'>".ucfmsg(strtoupper($myrow["bmonth"])).$myrow["display_year"]."</th></tr>";
            $alternate = "0";
      }

      $tablespace++;
  }
  
  $i++;
}

$use_ics = isset($_REQUEST['ics']);
if($use_ics) {

  header('Content-type: text/calendar; charset=utf-8');
  header('Content-Disposition: inline; filename=calendar.ics');

  $data["version"] = $version;
   
  $template = "birthdays_ics.twig"

} else {

  header('Content-Type:text/html; charset=UTF-8');
  
  $template = "birthdays.twig"
}

echo $twig->render($template, $data);