<?php
	include ("include/dbconnect.php");
	include ("include/photo.class.php");
	
if ($id) {

   $sql = "SELECT photo FROM $base_from_where AND $table.id='$id'";
   $result = mysqli_query($db,$sql);
   $r = mysqli_fetch_array($result);

   $resultsnumber = mysqli_num_rows($result);
}

$encoded = $r['photo'];

header('Content-Type: image/jpeg');
echo binaryImg($encoded);

?>