<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
include ("include/format.inc.php");
include ("include/photo.class.php");

require_once 'include/templating.php';

if($read_only) {
  $data["editing_disabled"] = true;
} 
else {
  if($submit) {
    $data["action"] = "submit";
    
    $sql = "INSERT INTO $table_groups (domain_id, group_name, group_header, group_footer,  group_parent_id)
                                VALUES ('$domain_id', '$group_name','$group_header','$group_footer','$group_parent_id')";
    $result = mysql_query($sql);

    // -- Add people to a group
  } 
  else if($new) {
    $data["action"] = "new";
  
    if(isset($table_groups) and $table_groups != "" and !$is_fix_group) {
      $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
      $result_groups = mysql_query($sql);
      $data["group_names"] = array();

      while ($myrow = mysql_fetch_array($result_groups)) {
          $data["group_names"][] = $myrow["group_name"];
      }
    }	
  } 
  else if($delete) {
    $data["action"] = "delete";

    // Remove the groups
    foreach($selected as $group_id)
    {
      // Delete links between addresses and groups
      $sql = "delete from $table_grp_adr where domain_id = $domain_id AND group_id = $group_id";
      $result = mysql_query($sql);

      // Delete groups
      $sql = "delete from $groups_from_where AND group_id = $group_id";
      $result = mysql_query($sql);
    }
  }
  else if($add) {
    $data["action"] = "add";
    
    // Lookup for the group_id
    $sql = "select * from $groups_from_where AND group_name = '$to_group'";

    $result = mysql_query($sql);

    $myrow = mysql_fetch_array($result);
    $group_id   = $myrow["group_id"];
    $group_name = $myrow["group_name"];

    // Add people to the group, who are not alread in the group!
    $data["selected"] = false;
    if(isset($selected)) {
      $data["selected"] = true;
      $data["group_name"] = $group_name;
      
      foreach($selected as $user_id) {
        $sql = "insert into $table_grp_adr (domain_id, id, group_id, created, modified) 
                                    values ($domain_id, $user_id, $group_id, now(), now())";
        $result = mysql_query($sql);
      }
    }
  }       
  // -- Remove people from a group
  else if($remove) {
    $data["action"] = "remove";
  
    // Lookup for the group_id
    $sql = "select * from $table_groups where group_name = '$group'";

    $result = mysql_query($sql);
    // $resultsnumber = mysql_numrows($result);

    $myrow = mysql_fetch_array($result);
    $group_id   = $myrow["group_id"];
    $group_name = $myrow["group_name"];
    $data["group_name"] = $group_name;

    // Remove people from the group, who are not alread in the group!
    foreach($selected as $user_id) {
      $sql = "delete from $table_grp_adr where id = $user_id AND group_id = $group_id";
      $result = mysql_query($sql);
    }
  }
  else if($update) {
    $data["action"] = "update";
    
    $sql="SELECT * FROM $table_groups WHERE group_id=$id";
    $result = mysql_query($sql);
    $resultsnumber = mysql_numrows($result);

    $data["success"] = false;
    if($resultsnumber > 0) {
      $data["success"] = true;
    
      if (!is_numeric($group_parent_id))
        $gpid='null';
      else
        $gpid=$group_parent_id;
            
      $sql = "UPDATE $table_groups SET group_name='$group_name'".
                                  ", group_header='$group_header'".
                                  ", group_footer='$group_footer'". 
                                  ", group_parent_id=$gpid".
                                " WHERE group_id=$id";
      $result = mysql_query($sql);
    }
  }
  // Open for Editing
  else if($edit || $id) {
    $data["action"] = "id";
    
    if($edit)
    $id = $selected[0];

    $result = mysql_query("$select_groups AND groups.group_id=$id",$db);
    $data["data"] = mysql_fetch_array($result);
    
    $sql="SELECT group_name, group_id FROM $table_groups WHERE group_id != $id
          ORDER BY lower(group_name) ASC;";

    $result_groups = mysql_query($sql);
    $result_gropup_snumber = mysql_numrows($result_groups);

    // has parent row in list been found?
    $data["parents"] = array();
    while ($myrow2 = mysql_fetch_array($result_groups))
    {
      $data["parents"][$myrow2['group_id']] = $myrow2["group_name"]; 
    }
  }
  else {
    $result = mysql_query($select_groups." ORDER BY groups.group_name");
    $resultsnumber = mysql_numrows($result);
    
    $data["data"] = array();
    while ($myrow = mysql_fetch_array($result)) {
      $data["data"][] = $myrow;
    }
  }
}

header('Content-Type:text/html; charset=UTF-8');
echo $twig->render('group.twig', $data);