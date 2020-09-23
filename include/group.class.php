<?php

function saveGroup($group_name, $group_header = "", $group_footer = "") {

  global $db, $domain_id, $table_groups;
  
  $sql = "INSERT INTO $table_groups (domain_id,    group_name,   group_header,    group_footer) 
                             VALUES ('$domain_id', '$group_name','$group_header', '$group_footer')";
  $result = mysqli_query($db,$sql);

}

class Group {

    function __construct($data) {
    	$this->group = $data;
    }

}
?>