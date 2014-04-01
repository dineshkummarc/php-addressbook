<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
//include ("include/format.inc.php");
include ("include/photo.class.php");
include ("include/guess.inc.php");

require_once 'include/templating.php';

/*function addRow($row) {

    global $addr;
	
    $myrow = $addr->getData();
    
    foreach($myrow as $mycol => $mycolval) {
       ${$mycol} = $mycolval;
    }
    
    switch ($row) {
      case "first_last":
	  echo "<td>$firstname ".(!empty($middlename) ? $middlename." " : "")."$lastname</td>";
	  break;
      default:
	$groups = $addr->getGroups();
	$groupSelected = in_array($row, $groups);
	echo "<td class='center'><input type='checkbox' id='$id:$row' name='selected[]' value='$id:$row' title='Select ($row)' alt='Select ($row)' ";
	echo ($groupSelected ? "checked" : "");
	echo "/></td>";
	break;
    }
}*/
    
if($read_only) {
  $data["editing_disabled"] = true;
} else if($save) {
  $data["action"] = "save";
  $data["skin_color"] = "green";

  $sql = "SELECT group_name, group_id FROM $groups_from_where ORDER BY lower(group_name) ASC";
  $result = mysql_query($sql);
  $groups = array();
  while ($row = mysql_fetch_array($result))
  {
    $groups[$row["group_id"]] = $row["group_name"];
  }
  
  $sql = "SELECT id FROM $table ORDER BY id ASC";
  $result = mysql_query($sql);
  $ids = array();
  while ($row = mysql_fetch_array($result))
  {
    $ids[] = $row["id"];
  }
  
  $data = array();
  foreach($ids as $id) {
    foreach($groups as $group) {
      $data[$id][$group] = array(false, false);
    }
  }

  foreach($selected as $value) {
    $value = explode(":", $value, 2);
    if(count($value) === 2 AND is_numeric($value[0])) {
      //$input[$data[0]][] = $data[1];
      if(in_array($value[1], $groups)) {
        $data[$value[0]][$value[1]][0] = true;
      }
    }
  }
    
  $sql = "SELECT id, group_name FROM $table_grp_adr ";
  $sql .= "LEFT JOIN $table_groups ON ($table_grp_adr.group_id = $table_groups.group_id) ";
  $result = mysql_query($sql);
  $db_data = array();
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    //$db_data[$row["id"]][] = $row["group_name"];
    $data[$row["id"]][$row["group_name"]][1] = true;
  }
  
  foreach($ids as $id) {
    foreach($groups as $group) {
      //echo $id.", ".$group.":".$data[$id][$group][0]." ".$data[$id][$group][0]."<br/>";
      if(($data[$id][$group][0] != $data[$id][$group][1])) {
        //echo "difference at: ".$id." ".$group;
        if($data[$id][$group][1] == true) {
          $sql = "DELETE FROM $table_grp_adr WHERE id = $id AND group_id = ".array_search($group, $groups);
          $result = mysql_query($sql);
        }
        else {
          $sql = "INSERT INTO $table_grp_adr (domain_id, id, group_id, created, modified) 
                  VALUES ($domain_id, $id, ".array_search($group, $groups).", NOW(), NOW())";
          $result = mysql_query($sql);
        }
        
        $data["saved"] = true;
      }
    }
  }
}
else {
  $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
  $result_groups = mysql_query($sql);
  $data["group_names"] = array();
  while ($myrow = mysql_fetch_array($result_groups))
  {
    $data["group_names"][] = $myrow["group_name"];
  }
  
  $addresses = Addresses::withSearchString($searchstring, 0, 0, $alphabet);
  $data["addresses"] = array();
  while ($addr = $addresses->nextAddress()) {
    $data["addresses"][] = $addr;
  }
}

header('Content-Type:text/html; charset=UTF-8');
echo $twig->render('membership.html', $data);