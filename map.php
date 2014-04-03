<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
include ("include/format.inc.php");

require_once 'include/templating.php';

// Check if we have a key for this domain?
if(!isset($google_maps_key) ||  $google_maps_key == "") {
  $google_maps_key = "";
  if(isset($google_maps_keys)) {
    foreach($google_maps_keys as $domain => $key) {
      if(str_replace($domain,"",$_SERVER['SERVER_NAME']).$domain == $_SERVER['SERVER_NAME']) {
              $google_maps_key = $key;
      }
    }
  }
}
  
$delay = 200000; // usecs before each fetching
$base_url = "http://maps.google.ch/maps/geo?output=csv&key=".$google_maps_key;
$first_fetch = true;

$cache_write = true;
$has_620 = false;
$single_address = false;

$addresses = Addresses::withSearchString($searchstring, $alphabet);
$result = $addresses->getResults();
$coords = array();

$data["url_not_loading"] = false;
while($myrow = mysql_fetch_array($result)) {
  $coord['addr']   = trim(str_replace("\n", ", ", trim($myrow['address'])),",");
  $coord['html']    = "<b>".$myrow['firstname'].(isset($myrow['middlename']) ? " ".$myrow['middlename'] : "")." ".$myrow['lastname']."</b><br>";
  $coord['html']   .= ($myrow['company'] != "" ? "<i>".$myrow['company']."</i><br>" : "");
  $coord['html']   .= str_replace("\n","",str_replace("\r","",nl2br($myrow['address'])));
  $coord['id']     = $myrow['id'];
  $coord['long']   = $myrow['addr_long'];
  $coord['lati']   = $myrow['addr_lat'];
  $coord['status'] = $myrow['addr_status'];

  //
  // Geo-code if long/lat is not yet defined
  //
  if(!($coord['status'] == 200 || $coord['status'] == 602 )) {
    $request_url = $base_url . "&q=" . urlencode($coord['addr']);
    if($first_fetch) usleep($delay);
    $first_fetch = false;
    try {
      $csv = file_get_contents($request_url);
    }
    catch(Exception $e) {
      $data["url_not_loading"] = true; 
      continue; 
    }//die("url not loading");

    $csvSplit = explode(",", $csv);

    // http://code.google.com/intl/de-DE/apis/maps/documentation/javascript/v2/reference.html#GGeoStatusCode
    $coord['status'] = $csvSplit[0];
    if($coord['status'] == 200) {
      $coord['lati']   = $csvSplit[2];
      $coord['long']   = $csvSplit[3];
                        
      $sql = "UPDATE $table 
                  SET addr_long   = '".$coord['long']."'
                    , addr_lat    = '".$coord['lati']."'
                    , addr_status = '".$coord['status']."'
                WHERE id        = '".$myrow['id']."'
                  AND domain_id = '$domain_id'
                  AND deprecated is null;";
      $upd_result = mysql_query($sql);
    } else {
      $sql = "UPDATE $table 
                  SET addr_status = '".$coord['status']."'
                WHERE id        = '".$myrow['id']."'
                  AND domain_id = '$domain_id'
                  AND deprecated is null;";
      $upd_result = mysql_query($sql);
    }          
  }
  $coords[] = $coord;
}
  
if($single_address) {
  $coords = array();
  $coords[] = $single_coord;
}
  
//
// Concat multiple entries on one place:
// * Sort places
// * Concat content
//
$longs = array();
$latis = array();
foreach ($coords as $key => $coord) {
  $longs[$key] = $coord['long'];
  $latis[$key] = $coord['lati'];
  
  $coords[$key]['bubble']  = $coord['html']."<br>";
  $coords[$key]['bubble'] .= "<b><a href='view.php?id=".$coord['id']."'>...".msg('MORE')."</a></b>";   
}
array_multisort($longs, SORT_ASC, $latis, SORT_ASC, $coords);
// print_r($coords);

$i = 0;
$result_coords = array();
for($i = 0; $i < count($coords); $i++) {
  $coord = $coords[$i];
  if($coord['status'] != 200) {
    continue;
  }

  if( isset($coords[$i+1])
      && $coords[$i]['long'] == $coords[$i+1]['long']
      && $coords[$i]['lati'] == $coords[$i+1]['lati']) {
    // Add html to next bubble
    $coords[$i+1]['bubble'] .= "<br><br>".$coords[$i]['bubble'];
    continue;
  }
  
  $result_coords[] = $coord;
}

$data["coords"] = $result_coords;
 
header('Content-Type:text/html; charset=UTF-8');
echo $twig->render('map.twig', $data);