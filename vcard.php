<?php

include ("include/dbconnect.php");

if ($id) {

   $sql = "SELECT * FROM $month_from_where AND $table.id=$id";

   $result = mysqli_query($db,$sql);
   $links  = mysqli_fetch_array($result);
   
   require "include/export.vcard.php";
   
   header2vcard($links);
   echo address2vcard($links);
	
} else {

	echo "You need to select an ID number of a data entry";

}
?>
