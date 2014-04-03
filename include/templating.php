<?php

error_reporting(E_ALL);
ini_set("display_errors", "On");
ini_set("display_startup_errors", "On"); 

// load template engine (Twig)  
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => 'cache',
    'cache' => false,
    'debug' => true
));

require_once("prefs.inc.php");
require_once("translator.class.php");

$trans = new GetTextTranslator();
//print_r($trans->getSupportedLangs());
//print_r($trans->getLocales());
//$default_lang    = $trans->getDefaultLang();
//$supported_langs = $trans->getSupportedLangs();
//$right_to_left_languages = array('ar', 'fa', 'he');

//
// Handle language choice
//
$choose_lang = false;
if(getPref('lang') != NULL) {
    $lang = getPref('lang');
} else {
  if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $lang = $trans->getBestAcceptLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);
  } else {
    $lang = $trans->getDefaultLang(); //$trans->getBestAcceptLang(array());
  }
}
$locale = $trans->getLocale($lang);

// set i18n
$twig->addExtension(new Twig_Extensions_Extension_I18n());
// Set language
//$lang = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
putenv('LC_ALL='.$locale); 
setlocale(LC_ALL, $locale.'.utf8'); 
// Specify location of translation tables
bindtextdomain("php-addressbook", "./translations/LOCALES");
bind_textdomain_codeset('php-addressbook', 'UTF-8');
// Choose domain 
textdomain("php-addressbook");
  
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

$data["public_group_edit"] = $public_group_edit;
$data["is_fix_group"] = $is_fix_group;
$data["table_groups"] = $table_groups;