<?php

  error_reporting(E_ALL);
  ini_set("display_errors", "On");
  ini_set("display_startup_errors", "On");
  
  // some includes
  require_once 'vendor/autoload.php';
  
  include ("include/dbconnect.php");
  //include ("include/format.inc.php");
  include ("include/photo.class.php");
  
  // main output function
  function addRow($row) {
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
  }
  
  // load template engine (Twig)  
  $loader = new Twig_Loader_Filesystem('templates');
  $twig = new Twig_Environment($loader, array(
      //'cache' => 'cache',
      'cache' => false,
      'debug' => true
  ));
  
  /*// set i18n
  $twig->addExtension(new Twig_Extensions_Extension_I18n());
  // Set language to German
  putenv('LC_ALL=de_DE'); 
  setlocale(LC_ALL, 'de_DE'); 
  // Specify location of translation tables
  bindtextdomain("php-addressbook-de", "translations"); 
  // Choose domain 
  textdomain("php-addressbook-de");*/
    
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
  
  //if(is_right_to_left($lang)) $data["rtl"] = true;
  //else $data["rtl"] = false;
  
  $data["url_images"] = $url_images;
  $data["remote_addr"] = $_SERVER['REMOTE_ADDR'];

  header('Content-Type:text/html; charset=UTF-8');
  echo "Test";
  echo $twig->render('index.html', $data);
?>

<!-- searchform with or without ajax -->
<div id="search-az">
  <?php if(! $use_ajax ) 
  { ?>
    <form accept-charset="utf-8" method="get" name="searchform">
      <input type="text" value="<?php echo $searchstring; ?>" name="searchstring" title="<?php echo ucfmsg('SEARCH_FOR_ANY_TEXT'); ?>" size="45" tabindex="0"/>
      <input name="submitsearch" type="submit" value="<?php echo ucfirst(msg('SEARCH')) ?>" />    
    </form>
  <?php
    $link = "index${page_ext_qry}alphabet";
    echo "<div id='a-z'><a href='$link=a'>A</a> | <a href='$link=b'>B</a> | <a href='$link=c'>C</a> | <a href='$link=d'>D</a> | <a href='$link=e'>E</a> | <a href='$link=f'>F</a> | <a href='$link=g'>G</a> | <a href='$link=h'>H</a> | <a href='$link=i'>I</a> | <a href='$link=j'>J</a> | <a href='$link=k'>K</a> | <a href='$link=l'>L</a> | <a href='$link=m'>M</a> | <a href='$link=n'>N</a> | <a href='$link=o'>O</a> | <a href='$link=p'>P</a> | <a href='$link=q'>Q</a> | <a href='$link=r'>R</a> | <a href='$link=s'>S</a> | <a href='$link=t'>T</a> | <a href='$link=u'>U</a> | <a href='$link=v'>V</a> | <a href='$link=w'>W</a> | <a href='$link=x'>X</a> | <a href='$link=y'>Y</a> | <a href='$link=z'>Z</a> | <a href='index$page_ext'>".ucfmsg('ALL')."</a></div>" ;
  } 
  else 
  { ?>
    <?php if($_SERVER['SERVER_NAME'] == "php-addressbook.sourceforge.net") 
    { ?>
      <table border=0>
        <tr>
          <td>
            <i><b>PHP-Addressbook</b> + iPhone-Contacts-Synchronization</i><br>
            <img src="icons/cross.png">
            <a href="http://swiss-addressbook.com">www.swiss-addressbook.com</a>
            <img src="icons/cross.png"><br>
            Advertisment: 2 months for free, then just 2$ per month!<br>
          </td>
        </tr>
      </table>
      <br>
    <?php 
    } ?>
    <form accept-charset="utf-8" method="get" name="searchform" onsubmit="return false">
      <input type="text" value="<?php echo $searchstring; ?>" name="searchstring" title="<?php echo ucfmsg('SEARCH_FOR_ANY_TEXT'); ?>" size="45" tabindex="0" 
      <?php if($use_ajax) { ?>onkeyup="filterResults(this)"/><?php } ?>
    </form>
  <?php 
  } ?>
  <script type="text/javascript">document.searchform.searchstring.focus();</script>
</div>
<br />

<!-- horizontal line -->
<hr />

<!-- get addresses -->
<?php
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

?>

<!-- group selector -->
<?php
echo "<label style='width:24em;'><strong>".msg('NUMBER_OF_RESULTS').": <span id='search_count'>$resultsnumber</span></strong></label>";

if(isset($table_groups) and $table_groups != "" and !$is_fix_group) 
{ ?>
  <form id="right" method="get">
    <select name="group" onchange="this.parentNode.submit()">
      <!--<?php /*if($group_name != "") 
      {
        echo "<option>$group_name</option>\n";
      }*/ ?>-->
      <option value="">[<?php echo msg("ALL"); ?>]</option>
      <option value="[none]">[<?php echo msg("NONE"); ?>]</option>
      <?php
        $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
        $result_groups = mysql_query($sql);
        $result_gropup_snumber = mysql_numrows($result_groups);
      
        while ($myrow = mysql_fetch_array($result_groups)) {
            echo "<option";
            if($group_name === $myrow["group_name"]) echo " selected";
            echo ">".$myrow["group_name"]."</option>\n";
        }
      ?>
    </select>
</form>
<?php
} ?>
<br />
<br class="clear" />

<!-- output of table -->
<form accept-charset="utf-8" name="MainForm" method="post" action="group<?php echo $page_ext; ?>">
  <input type="hidden" name="group" value="<?php echo $group; ?>" />
  &nbsp;<input type='checkbox' id='MassCB' onclick=\"MassSelection()\" /> <em><strong><?php echo ucfmsg("SELECT_ALL") ?></strong></em><br><br>
  <table id="maintable" class="sortcompletecallback-applyZebra">
    <!-- table header -->
    <tr>
      <?php					
      $is_mobile = false;
  
      if(! $is_mobile) {
        foreach($disp_cols as $col) {
            
            if(!in_array($col, array("home", "work", "mobile", "select", "edit", "vcard", "map", "homepage", "details"))) {
                  echo "<th class='sortable'>".ucfmsg(strtoupper($col))."</th>";
            } elseif(in_array($col, array("home", "work", "mobile"))) {
                  echo "<th>".ucfmsg("PHONE_".strtoupper($col))."</th>";
                } else {
            echo "<th></th>";
                    if($col == "edit" && !$read_only) { // row for edit
                echo "<th></th>";
            }
                    if($col == "details") {
                echo "<th></th>";
                echo "<th></th>";
            }
                }
        }
      } ?>      
    </tr>
    <?php
    $alternate = "2"; 
    include ("include/guess.inc.php");

    // table content (addresses
    while ($addr = $addresses->nextAddress()) {
      $color = ($alternate++ % 2) ? "odd" : "";
      echo "<tr class='".$color."' name='entry'>";

      if($is_mobile) {
        // addRow("select");
        addRow("lastname");
        addRow("firstname");
        // addRow("first_last");
        // addRow("all_phones");
        // addRow("email");
        addRow("edit");
      } else {
        foreach($disp_cols as $col) {
          addRow($col);
        }
      }

      echo "</tr>\n";
    } ?>
  </table>
  &nbsp;<input type='checkbox' id='MassCB' onclick=\"MassSelection()\" /> <em><strong><?php echo ucfmsg("SELECT_ALL") ?></strong></em><br><br>
  
  <!-- actions (left: send mail, add to group, delete) -->
  <div class='left'>
    <?php
    if($use_doodle) {
      echo "<input type='button' value=\"".ucfmsg("DOODLE")."\"   onclick=\"Doodle()\" />";
    }
    echo "<input type='button' value=\"".ucfmsg("SEND_EMAIL")."\" onclick=\"MailSelection()\" />";
    
    if(! $read_only) {
      if(isset($table_groups) and $table_groups != "" and !$is_fix_group)
      {
        // -- Remove from group --
        if($group_name != "" and $group_name != "[none]") 
        {
          echo "<br/><input type='submit' name='remove' value='".ucfmsg("REMOVE_FROM")." \"$group_name\"'/>";
        } 
        //else
        //echo "<div></div>";

        // -- Add to a group --
        echo "<br/><input type='submit' name='add' value='".ucfmsg("ADD_TO")."'/>-";
        echo "<select name='to_group'>";
        $sql="SELECT group_name FROM $groups_from_where ORDER BY lower(group_name) ASC";
        $result = mysql_query($sql);
        $resultsnumber = mysql_numrows($result);
  
        while ($myrow = mysql_fetch_array($result))
        {
          echo "<option>".$myrow["group_name"]."</option>\n";
        }
        echo "</select>";
      }
      echo "<br/><input type='button' value=\"".ucfmsg("DELETE")."\"     onclick=\"DeleteSel()\" />";
    } ?>
  </div>
</form>

<!-- show group footer -->
<?php if($group_name != "" and $group_myrow['group_footer'] != "") {
  echo "<hr />".$group_myrow['group_footer']."<hr />";
} ?>

<!-- print actions -->
<div class="right" style="clear:right">
  <form>
    <input type="button" value="<?php echo msg('PRINT_ALL'); ?>" onclick="window.location.href='view<?php echo $page_ext_qry; ?>all&amp;print'">
    <br/><input type="button" value="<?php echo msg('PRINT_PHONES'); ?>" onclick="window.location.href='view<?php echo $page_ext_qry; ?>all&amp;print&amp;phones'">
  </form>
</div>

<!-- output footer -->
<?php include("include/footer.inc.php"); ?>

<!-- javascript functions -->
<script type="text/javascript">
<!--
// Select All/None items
function MassSelection() {
  select_count = document.getElementsByName("selected[]").length;
  all_checked  = document.getElementById("MassCB").checked;
  
  for (i = 0; i < select_count; i++) {
    // select only visible items
    if( document.getElementsByName("selected[]")[i].parentNode.parentNode.style.display != "none") {
      document.getElementsByName("selected[]")[i].checked = all_checked;
    }
  }
}

// Send Mail to selected persons
function MailSelection() {
  var addresses = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      if( selected_i.accept != "" && selected_i.accept != null) {
        if(dst_count > 0) {
          addresses = addresses + "<?php echo getMailerDelim(); ?>";
        }
        addresses = addresses + selected_i.accept;
        dst_count++;
      }
    }
  }

  if(dst_count == 0)
    alert("No address selected.");
  else
    location.href = "<?php echo getMailer(); ?>"+addresses;
}

function Doodle() {
  var participants = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      participants += selected_i.id+";";
      dst_count++;
    }
  }
  alert(participants);
  
  if(dst_count == 0)
    alert("No paticipants selected.");
  else
    location.href = "./doodle.php?part="+participants;
}

function DeleteSel() {
  var participants = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      participants += selected_i.id+";";
      dst_count++;
    }
  }

  if(dst_count == 0)
    alert("No paticipants selected.");
  else {
    if(confirm('Delete '+dst_count+' addresses?')) {
      location.href = "./delete.php?part="+participants;
    }
  }
}

function applyZebra() {
  // loop over all lines
  var maintable = document.getElementById("maintable")
  var tbody     = maintable.getElementsByTagName("tbody");
  var entries   = tbody[0].children;
  var zebraCnt  = 0;

  // Skip header(0) + selection row(length-1)
  for(i = 1; i < entries.length; i++) {
    if(entries[i].style.display != "none") {
      if((zebraCnt % 2) == 0) {
        entries[i].className = "";
      } else {
        entries[i].className = "odd";
      }
      zebraCnt++;
    }
  }
}

// Filter the items in the fields
function filterResults(field) {
  var query = field.value;
  
  // split lowercase on white spaces
  var words = query.toLowerCase().split(" ");

  // loop over all lines
  var maintable = document.getElementById("maintable")
  var tbody     = maintable.getElementsByTagName("tbody");
  var entries   = tbody[0].children;
  var foundCnt  = 0;
  
  // Skip header(0) + selection row(length-1)
  for(i = 1; i < entries.length; i++) {
    // Use all columns that don't have the css class "center"
    var content = entries[i].childNodes[0].childNodes[0].accept;
    for(var j=0;j<entries[i].childNodes.length;j++) {
      if(entries[i].childNodes[j].className == "center") continue;
      content += " "+entries[i].childNodes[j].innerHTML;
    }
                        
    // Don't be case sensitive
    content = content.toLowerCase();

    // check if all words are present  		            
    var foundAll = true;
    for(j = 0; j < words.length; j++) {
      foundAll = foundAll && (content.search(words[j]) != -1);
    }
            
    // Keep selected entries
    foundAll = foundAll || entries[i].childNodes[0].childNodes[0].checked;
            
    // ^Hide entry
    if(foundAll) {
      entries[i].style.display = "";
      foundCnt++;  			
    } else {
      entries[i].style.display = "none";
    }
  }
  document.getElementById("search_count").innerHTML = foundCnt;
  
  applyZebra();
}

<?php if($use_ajax) { ?>
  filterResults(document.getElementsByName("searchstring")[0]);
<?php } ?>

//-->
</script>
<script type="text/javascript" src="js/tablesort.min.js"></script>