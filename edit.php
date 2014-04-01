<?php

require_once '3rdparty/autoload.php';

include ("include/dbconnect.php");
include ("include/format.inc.php");
include ("include/photo.class.php");

require_once 'include/templating.php';

$data["refresh"] = false;
if((isset($submit) && $submit) || (isset($update) && $update)) { 
  $data["refresh"] = true;
}


//$resultsnumber = 0;
if ($id) {
   $sql = "SELECT * FROM $base_from_where AND $table.id='$id'";
   $result = mysql_query($sql, $db);
   $data["r"] = mysql_fetch_array($result);
   
   $data["title"] = $data["r"]["firstname"]
      .(isset($data["r"]['middlename']) ? " ".$data["r"]['middlename']:"")." "
      .$data["r"]["lastname"]." ".($group_name != "" ? "($group_name)":"")."\n";

   //$resultsnumber = mysql_numrows($result);
}

//if( ($resultsnumber == 0 && !isset($all)) || (!$id && !isset($all))) {
//   ?><!--<title>Address book <?php //echo ($group_name != "" ? "($group_name)":""); ?></title>--><?php
//   include ("include/header.inc.php");
//} 
//else {
  
  /* ?><title><?php echo $r["firstname"].(isset($r['middlename']) ? " ".$r['middlename']:"")." ".$r["lastname"]." ".($group_name != "" ? "($group_name)":"")."\n"; ?></title><?php
   if( !isset($_GET["print"]))
   {
     include ("include/header.inc.php");
   } else {
     echo '</head><body>';
     // echo '</head><body onload="javascript:window.setTimeout(window.print(self), 1000)";>';
   }*/
//}

if($submit) {
  $data["action"] = "submit";
  if(! $read_only)
  {
    /*
    //
    // Primitiv filter against spam on "sourceforge.net".
    //
    if($_SERVER['SERVER_NAME'] == "php-addressbook.sourceforge.net") {
      
      $spam_test = $firstname.$middlename.$lastname.$address.$home.$mobile.$work.$email.$email2.$email3.$bday.$bmonth.$byear.$aday.$amonth.$ayear.$dataess2.$phone2;
      $blacklist = array( 'viagra', 'seroquel', 'zovirax', 'ultram', 'mortage', 'loan'
                        , 'accutane', 'ativan', 'gun', 'sex', 'porn', 'arachidonic'
                        , 'recipe', 'comment1'
                        , 'naked', 'gay', 'fetish', 'domina', 'fakes', 'drugs'
                        , 'methylphenidate', 'nevirapine', 'viramune' );
      foreach( $blacklist as $blackitem ) {
          if(strpos(strtolower($spam_test), $blackitem) !== FALSE ) {
            exit;
          }
      }
      if(   preg_match('/\D{3,}/', $home) > 0
          || preg_match('/\D{3,}/', $mobile) > 0) {
            exit;
      }
      if(   strlen($home)   > 15 
          || strlen($mobile) > 15) {
            exit;
      }
    }*/
    
    $addr['firstname'] = $firstname;
    $addr['middlename']= $middlename;
    $addr['lastname']  = $lastname;
    $addr['nickname']  = $nickname;
    $addr['title']     = $title;
    $addr['company']   = $company;
    $addr['address']   = $address;
    $addr['home']      = $home;
    $addr['mobile']    = $mobile;
    $addr['work']      = $work;
    $addr['fax']       = $fax;
    $addr['email']     = $email;
    $addr['email2']    = $email2;
    $addr['email3']    = $email3;
    $addr['homepage']  = $homepage;
    $addr['bday']      = $bday;
    $addr['bmonth']    = $bmonth;
    $addr['byear']     = $byear;
    $addr['aday']      = $aday;
    $addr['amonth']    = $amonth;
    $addr['ayear']     = $ayear;
    $addr['address2']  = $address2;
    $addr['phone2']    = $phone2;
    $addr['notes']     = $notes;
    $addr['groups']    = $groups;
    
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] <= 0) {
      $file_tmp_name = $_FILES["photo"]["tmp_name"];
      $file_name     = $_FILES["photo"]["name"];    
      $photo = new Photo($file_tmp_name);
      $photo->scaleToMaxSide(150);
      $addr['photo'] = $photo->getBase64();
    } 
    
    /*if(isset($table_groups) and $table_groups != "" ) {
      if( !$is_fix_group ) {
        $g_name = $new_group;
      } else {
        $g_name = $group_name;
      }*/
      
    saveAddress($data, $g_name);
    $data["saved"] = true;
    //}

  } 
  else {
    $data["editing_disabled"] = true;
  }
}
else if($update) {
  $data["action"] = "update";
  if(! $read_only)
  {
    $addr['id']        = $id;
    $addr['firstname'] = $firstname;
    $addr['middlename']= $middlename;
    $addr['lastname']  = $lastname;
    $addr['nickname']  = $nickname;
    $addr['title']     = $title;
    $addr['company']   = $company;
    $addr['address']   = $address;
    $addr['home']      = $home;
    $addr['mobile']    = $mobile;
    $addr['work']      = $work;
    $addr['fax']       = $fax;
    $addr['email']     = $email;
    $addr['email2']    = $email2;
    $addr['email3']    = $email3;
    $addr['homepage']  = $homepage;
    $addr['bday']      = $bday;
    $addr['bmonth']    = $bmonth;
    $addr['byear']     = $byear;
    $addr['aday']      = $aday;
    $addr['amonth']    = $amonth;
    $addr['ayear']     = $ayear;
    $addr['address2']  = $address2;
    $addr['phone2']    = $phone2;
    $addr['notes']     = $notes;
    $addr['groups']    = $groups;

    $keep_photo = true;
    if(isset($delete_photo)) {
      $keep_photo =  !$delete_photo;
    }
            
    if(isset($_FILES["photo"])
          && $_FILES["photo"]["error"] <= 0) {
      $file_tmp_name = $_FILES["photo"]["tmp_name"];
      $file_name     = $_FILES["photo"]["name"];    
      $photo = new Photo($file_tmp_name);
      $photo->scaleToMaxSide(150);
      $addr['photo'] = $photo->getBase64();
      $keep_photo = false;
    } else  {
      $addr['photo']  = '';
    }
    
    $data["updated"] = updateAddress($addr, $keep_photo);
  } else
    $data["editing_disabled"] = true;
}
else if($id) {
  $data["action"] = "id";
  if(! $read_only)
  {
    //$result = mysql_query("SELECT * FROM $base_from_where AND $table.id=$id",$db);
    //$data["address"] = mysql_fetch_array($result);
    
    $addresses = Addresses::withID($id);
    $address = $addresses->nextAddress();
    $data["address"] = $address->getData();
    $data["address"]["groups"] = $address->getGroups();
    print_r($data["address"]["groups"]);

    /*if($group_name) {
      $data["group_name"] = $group_name;
    }*/
    
    $data["is_fix_group"] = $is_fix_group;
    $data["table_groups"] = $table_groups;
    
    if(isset($table_groups) and $table_groups != "" and !$is_fix_group) {
      $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
      $result_groups = mysql_query($sql);
      $data["group_names"] = array();

      while ($myrow = mysql_fetch_array($result_groups)) {
          $data["group_names"][] = $myrow["group_name"];
      }
    }
  }
  else {
    $data["editing_disabled"] = true;
  }
}
else if( !(isset($_POST['quickskip']) || isset($_POST['quickadd'])) 
         && (isset($_GET['quickadd']) || isset($_POST['quickadd']) || $quickadd)) {
  $data["action"] = "quickadd";
}
else {
  $data["action"] = "none";
  if(! $read_only) {
    if(isset($_POST['quickadd'])) {
      
      include_once("include/guess.inc.php");
      $addr = guessAddressFields($address);
      // echo nl2br(print_r($data, true));
    } else {        
      $addr = array();        
    }
    $data["address"] = $addr;
    
    $data["is_fix_group"] = $is_fix_group;
    $data["table_groups"] = $table_groups;
    
    if(isset($table_groups) and $table_groups != "" and !$is_fix_group) {
      $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
      $result_groups = mysql_query($sql);
      $data["group_names"] = array();

      while ($myrow = mysql_fetch_array($result_groups)) {
          $data["group_names"][] = $myrow["group_name"];
      }
    }
  } 
  else {
    $data["editing_disabled"] = true;
  }
}

header('Content-Type:text/html; charset=UTF-8');
echo $twig->render('edit.html', $data);
