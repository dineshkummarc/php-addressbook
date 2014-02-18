<?php

include ("include/dbconnect.php");
include ("include/format.inc.php");
echo "<title>Membership | Address Book</title>";
include ("include/header.inc.php");


echo "<h1>".ucfmsg('MEMBERSHIP')."</h1>";

function addRow($row) {

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
}
    
if($read_only) {
	echo "<br /><div class='msgbox'>Editing is disabled.<br /><i>return to the <a href='membership$page_ext'>membership page</a></i></div>";
} else {
if($save)
{
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
	      echo "<br /><div class='msgbox'>Memberships have been updated.</div>";
	    }
	  }
	}
}
}
?>
<form accept-charset="utf-8" name="MainForm" method="post" action="membership<?php echo $page_ext; ?>">
<table id="maintable" class="sortcompletecallback-applyZebra">
<tr>
<?php
echo "<th class='sortable'>".ucfmsg('NAME')."</th>";
$sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
$result_groups = mysql_query($sql);
$groups = array();
while ($myrow = mysql_fetch_array($result_groups))
{
  $groups[] = $myrow["group_name"];
  echo "<th class='sortable'>".$myrow["group_name"]."</th>";
}
?>
</tr>
<?php

$alternate = "2"; 

include ("include/guess.inc.php");

$addresses = Addresses::withSearchString($searchstring, $alphabet);

while ($addr = $addresses->nextAddress()) {
  $color = ($alternate++ % 2) ? "odd" : "";
  echo "<tr class='".$color."' name='entry'>";
  
  addRow("first_last");
  foreach($groups as $group) {
    addRow($group);
  }
  
  echo "</tr>";
}

?>
</table><br/>
<div class='right'><input type='submit' name='save' value='<?php echo ucfmsg("SAVE_MEMBERSHIPS") ?>'/></div>
<?php		
include ("include/footer.inc.php");
?>
<script type="text/javascript" src="js/tablesort.min.js"></script>