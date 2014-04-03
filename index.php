<?php

// some includes
require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
//include ("include/format.inc.php");
include ("include/photo.class.php");
include ("include/guess.inc.php");

require_once 'include/templating.php';

$data["disp_cols"] = $disp_cols;

$data["searchstring"] = $searchstring;

if($group_name) {
  $data["group_name"] = $group_name;
}

if(isset($table_groups) and $table_groups != "" and !$is_fix_group) {
  $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
  $result_groups = mysql_query($sql);
  $data["group_names"] = array();

  while ($myrow = mysql_fetch_array($result_groups)) {
      $data["group_names"][] = $myrow["group_name"];
  }
}

// get addresses
// Pagination
// http://php.about.com/od/phpwithmysql/ss/php_pagination.htm
if(!isset($limit)) $limit = 10;
$data["limit"] = $limit;
  
if(!is_numeric($page)) {
  $page = 1;
} else {
  $page = (int)$page;
  if($page < 1) $page = 1;
}

$addresses = Addresses::withSearchString($searchstring, $page, $data["limit"], $alphabet);
$result = $addresses->getResults();
$data["resultsnumber"] = $addresses->countAll();//mysql_numrows($result);

$data["addresses"] = array();
while ($addr = $addresses->nextAddress()) {
  $data["addresses"][] = $addr;
}

$data["last"] = ceil($data["resultsnumber"]/$data["limit"]);
if ($page > $data["last"]) {
  $page = $data["last"]; 
}
$data["page"] = $page;
if ($limit > 0 && $page > 1) $data["prev"] = $page - 1;
if ($limit > 0 && $page < $data["last"]) $data["next"] = $page + 1;

header('Content-Type:text/html; charset=UTF-8');
echo $twig->render('index.twig', $data);
