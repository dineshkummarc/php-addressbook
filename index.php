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
  $data["read_only"] = $read_only;
  $data["use_ajax"] = $use_ajax;
  $data["use_doodle"] = $use_doodle;
  $data["version"] = $version;
  
  $is_mobile = false;
  $data["is_mobile"] = $is_mobile;
  
  $data["mailer"] = getMailer();
  
  $data["map_guess"] = $map_guess;
  
  $data["url_images"] = $url_images;
  
  $data["disp_cols"] = $disp_cols;
  
  $data["searchstring"] = $searchstring;

  if($group_name) {
    $data["group_name"] = $group_name;
  }
  
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
  echo $twig->render('index.html', $data);
