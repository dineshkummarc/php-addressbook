<?php

  error_reporting(E_ALL);
  ini_set("display_errors", "On");
  ini_set("display_startup_errors", "On");
  
  // some includes
  require_once 'vendor/autoload.php';
  
  include ("include/dbconnect.php");
  //include ("include/format.inc.php");
  include ("include/photo.class.php");
  include ("include/guess.inc.php");
    
  // load template engine (Twig)  
  $loader = new Twig_Loader_Filesystem('templates');
  $twig = new Twig_Environment($loader, array(
      //'cache' => 'cache',
      'cache' => false,
      'debug' => true
  ));
  
  // set i18n
  $twig->addExtension(new Twig_Extensions_Extension_I18n());
  // Set language to German
  putenv('LC_ALL=de_DE'); 
  setlocale(LC_ALL, 'de_DE'); 
  // Specify location of translation tables
  bindtextdomain("php-addressbook-de", "translations"); 
  // Choose domain 
  textdomain("php-addressbook-de");
  
  // main output function
  $addRow = new Twig_SimpleFunction('addRow', function ($row) {
    global $addr, $page_ext_qry, $url_images, $read_only, $map_guess, $full_phone, $homepage_guess;
        
    $myrow = $addr->getData();
    
    foreach($myrow as $mycol => $mycolval) {
      ${$mycol} = $mycolval;
    }
    
    $email = $addr->firstEMail();
    if($email != "" && $email != $myrow['email2']) {
      $email2 = $myrow['email2'];
    } else {
      $email2 = "";
    }
    
    // Special value for short phone
    $row = ($row == "telephone" ? "phone" : $row);
    
    if($row == "phone") {
      if($full_phone) {
        $phone  = $addr->firstPhone();
      } else {
          $phone  = $addr->shortPhone();
      }
    }
    
    switch ($row) {
      case "select":
        $emails = implode(getMailerDelim(), $addr->getEMails());
        echo "<td class='center'><input type='checkbox' id='$id' name='selected[]' value='$id' title='Select ($firstname $lastname)' alt='Select ($firstname $lastname)' accept='$emails' /></td>";
        break;
      case "first_last":
        echo "<td>$firstname ".(!empty($middlename) ? $middlename." " : "")."$lastname</td>";
        break;
      case "last_first":
        echo "<td>".(!empty($middlename) ? $middlename." " : "")."$lastname $firstname</td>";
        break;
      case "photo":
//        echo "<td>".embeddedImg($photo)."</td>";
///*
        if($photo != "") {
          echo "<td><img width=75 src='photo.php?id=".$id."'></td>";
        } else {
          echo "<td></td>";
        }
//*/        
        break;
      case "email":
      case "email2":
        echo "<td><a href='".getMailer()."${$row}'>${$row}</a></td>";
        break;
      case "all_phones":
        $phones = $addr->shortPhones();
          echo "<td>".implode("<br>", $phones)."</td>";
        break;
      case "all_emails":
        $emails = $addr->getEMails();
        $amails = array();
        foreach($emails as $amail) {
          $amails[] = "<a href='".getMailer()."$amail'>$amail</a>";
        }
        echo "<td>".implode("<br>", $amails)."</td>";
        break;
      case "all_groups":
        $groups = $addr->getGroups();
        $groupLinks = array();
        foreach($groups as $group) {
          $groupLinks[] = "<a href='index.php?group=$group'>$group</a>";
        }
        echo "<td>".implode("<br>", $groupLinks)."</td>";
        break;
      case "address":
        echo "<td>".str_replace("\n", "<br>", $address)."</td>";
        break;
      case "edit":
        echo "<td class='center'><a href='view${page_ext_qry}id=$id'><img src='${url_images}icons/status_online.png' title='".ucfmsg('DETAILS')."' alt='".ucfmsg('DETAILS')."' /></a></td>";
        if(! $read_only) {
          echo "<td class='center'><a href='edit${page_ext_qry}id=$id'><img src='${url_images}icons/pencil.png' title='".ucfmsg('EDIT')."' alt='".ucfmsg('EDIT')."'/></a></td>";
        }
        break;
      case "vcard":
        echo "<td class='center'><a href='vcard${page_ext_qry}id=$id'><img src='${url_images}icons/vcard.png' title='vCard' alt='vCard'/></a></td>";        
        break;
      case "map":      
        if($map_guess) {
          if($myrow["address"] != "") {
            echo "<td class='center'>";
            echo "  <a href='http://maps.google.com/maps?q=".urlencode(trim(str_replace("\r\n", ", ", trim($myrow["address"]))))."&amp;t=h' target='_blank'>";
            echo "  <img src='${url_images}icons/car.png' title='Google Maps' alt='vCard'/></a>";
            echo "</td>";
          }
          else echo "<td/>";
        }
        break;
      case "homepage":    
        if($homepage != "") {
          $homepage = (strcasecmp(substr($homepage, 0, strlen("http")),"http")== 0
                      ? $homepage : "http://".$homepage);
          echo "<td class='center'>";
          echo "  <a href='$homepage'><img src='${url_images}icons/house.png' title='$homepage' alt='$homepage'/></a>";
          echo "</td>";
        } elseif($homepage_guess && ($homepage = guessHomepage($email, $email2)) != "") {
          echo "<td class='center'>";
          echo "  <a href='http://$homepage'><img src='${url_images}icons/house.png' title='".ucfmsg("GUESSED_HOMEPAGE")." ($homepage)' alt='".ucfmsg("GUESSED_HOMEPAGE")." ($homepage)'/></a>";
          echo "</td>";
        } else {
          echo "<td/>";
        }                   
        break;
      case "details":
        echo "<td class='center'>";
        echo "  <a href='vcard${page_ext_qry}id=$id'><img src='${url_images}icons/vcard.png' title='vCard' alt='vCard'/></a>";
        echo "</td>";        
        if($map_guess) {
          if($myrow["address"] != "") {
            echo "<td class='center'>";
            echo "  <a href='http://maps.google.com/maps?q=".urlencode(trim(str_replace("\r\n", ", ", trim($myrow["address"]))))."&amp;t=h' target='_blank'>";
            echo "  <img src='${url_images}icons/car.png' title='Google Maps' alt='vCard'/></a>";
            echo "</td>";
          }
          else echo "<td/>";
        }
        
        if($homepage != "") {
          $homepage = (strcasecmp(substr($homepage, 0, strlen("http")),"http")== 0
                      ? $homepage : "http://".$homepage);
          echo "<td class='center'>";
          echo "  <a href='$homepage'><img src='${url_images}icons/house.png' title='$homepage' alt='$homepage'/></a>";
          echo "</td>";
        } elseif($homepage_guess && ($homepage = guessHomepage($email, $email2)) != "") {
          echo "<td class='center'>";
          echo "  <a href='http://$homepage'><img src='${url_images}icons/house.png' title='".ucfmsg("GUESSED_HOMEPAGE")." ($homepage)' alt='".ucfmsg("GUESSED_HOMEPAGE")." ($homepage)'/></a>";
          echo "</td>";
        } else {
          echo "<td/>";
        }                   
        break;
      default: // firstname, lastname, home, mobile, work, fax, phone2
        echo "<td>${$row}</td>";
    }
  });
  $twig->addFunction($addRow);

    
  $data["skin_color"] = $skin_color;
  // Define default map guessing
  switch($skin_color) {
    case "blue":
      $skin_mt_color = '#739fce';
      break;
    case "brown":
      $skin_mt_color = '#c59469';
      break;
    case "green":
      $skin_mt_color = '#66a749';
      break;
    case "grey":
      $skin_mt_color = '#777777';
      break;
    case "pink":
      $skin_mt_color = '#a84989';
      break;
    case "purple":
      $skin_mt_color = '#5349a9';
      break;
    case "red":
      $skin_mt_color = '#b63a3a';
      break;
    case "turquoise":
      $skin_mt_color = '#48a89d';
      break;
    case "yellow":
      $skin_mt_color = '#b4b43a';
      break;
  }
  $data["skin_mt_color"] = $skin_mt_color;
  
  if(is_right_to_left($lang)) $data["rtl"] = true;
  else $data["rtl"] = false;
  
  $data["url_images"] = $url_images;
  $data["remote_addr"] = $_SERVER['REMOTE_ADDR'];
  
  $data["use_ajax"] = $use_ajax;
  $data["version"] = $version;
  
  $is_mobile = false;
  $data["is_mobile"] = $is_mobile;
  
  $data["disp_cols"] = $disp_cols;

  if($group_name) $data["group_name"] = $group_name;
  
  if(isset($table_groups) and $table_groups != "" and !$is_fix_group) {
    $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
    $result_groups = mysql_query($sql);
    $group_names = array();

    while ($myrow = mysql_fetch_array($result_groups)) {
        $group_name[] = $myrow["group_name"];
    }
    
    $data["group_names"] = $group_names;
  }
  
  // get addresses
  // Pagination
  // http://php.about.com/od/phpwithmysql/ss/php_pagination.htm
  $limit = 
  $page = $_GET["page"];
  if(!is_int($page)) {
    echo $page;
    $page = 1;
  }

  $addresses = Addresses::withSearchString($searchstring, $alphabet);
  $result = $addresses->getResults();
  $resultsnumber = $addresses->countAll();//mysql_numrows($result);
  
  $data["addresses"] = array();
  while ($addr = $addresses->nextAddress()) {
    $data["addresses"][] = $addr;
  }

  header('Content-Type:text/html; charset=UTF-8');
  echo "Test";
  echo $twig->render('index.html', $data);
