<?php
ob_start();
session_start();

date_default_timezone_set('Europe/Istanbul');
error_reporting(E_ALL);


/*** GLOBAL ***/



/* --------------------------------------------------- ROOT */

/**
 * title: root_path()
 * desc: root_path() fonksiyonu, lazim olan dosyalari ice aktarirken kok dizini bulmamızı kolaylastirir.
 */
define( 'ROOT_PATH', dirname(__FILE__));

function get_root_path($val) {
	return ROOT_PATH.'/'.$val;
}
function root_path($val) {
	echo get_root_path($val);
}
include get_root_path('database.php');


// config
define('_root_path', $_SERVER['DOCUMENT_ROOT'].'/');

// Bağlatıyı Kuralım.
$db = new mysqli(_serverName, _userName, _userPassword);
if($db->connect_errno){
  echo "Bağlantı Hatası:".$db->connect_errno;
  exit;
}

// Veritabanımızı Seçelim.
$db->select_db(_dbName);
$db->query("SET NAMES 'utf8'");



function db() {
	global $db;
	return $db;
}


// global ön ek
$extra = new stdclass;
global $extra;

function dbname($val)
{ global $extra;
	return _prefix.$val;
}


function extra($val='')
{ global $extra;
	return $extra;
}





$extra->pg = new StdClass;
$extra->pg->list_limit = 20; // bir sayfada gosterilecek listeleme limiti, tablonun row limiti




function extra_include($val) {
	require_once get_root_path('includes/'.$val.'.php');
}




/* --------------------------------------------------- THEME */

/**
 * title: get_header()
 * desc: header.php dosyasını include eder.
 */
function get_header()
{
	include get_root_path('content/themes/default/header.php');
}

/**
 * title: get_footer()
 * desc: footer.php dosyasını include eder.
 */
function get_footer()
{
	include get_root_path('content/themes/default/footer.php');
}

/**
 * title: get_sidebar()
 * desc: sidebar.php dosyasını include eder.
 */
function get_sidebar()
{
	include get_root_path('content/themes/default/sidebar.php');
}



/* --------------------------------------------------- URL */
/**
 * title: get_site_url()
 * desc: site adresini dondurur
 */
function get_site_url($val='', $val_2=false)
{
	if(_helper_site_url($val)) {
		$val = _helper_site_url($val);

		if(is_numeric($val_2)) {
			$val = $val.'?id='.$val_2;
		} else {
			$val = $val.'?'.$val_2;
		}
	}

	if(substr($val, 0,1) == '/') {
		return _site_url.''.$val;
	} else {
		return _site_url.'/'.$val;
	}

}
/**
 * title: site_url()
 * desc: site adresini gosterir
 * func: get_site_url()
 */
function site_url($val='', $val_2=false)
{
	echo get_site_url($val, $val_2);
}

/*
 * _helper_site_url()
 *	get_site_url() fonksiyonu icin kisaltmalari olusturur
 */
function _helper_site_url($val) {
	if($val == 'form') {
		return 'admin/form/detail.php';
	} else if($val == 'account') {
		return 'admin/account/detail.php';
	} else if($val == 'payment') {
		return 'admin/payment/detail.php';
	} else if($val == 'item') {
		return 'admin/item/detail.php';
	} else if($val == 'message') {
		return 'admin/user/message/detail.php';
	} else if($val == 'task') {
		return 'admin/user/task/detail.php';
	} else {
		return false;
	}
}



/**
 * title: get_template_url()
 * desc: secili temanın klasör adresini url olarak döndürür
 */
function get_template_url($val='')
{
	return get_site_url().'/content/themes/default/'.$val;
}
/**
 * title: template_url()
 * func: get_template_url()
 */
function template_url($val='')
{
	echo get_template_url($val);
}





function is_home() {
	if(extra_active_site_url() == get_site_url()) {
		return true;
		exit;
	} elseif(str_replace('index.php', '', extra_active_site_url()) == get_site_url()) {
		return true;
		exit;
	} else { return false; }
}




/* --------------------------------------------------- INCLUDE FUNCTIONS */
extra_include('db');
extra_include('lang');
extra_include('login');
extra_include('helper');
extra_include('input');
extra_include('user');
extra_include('log');
extra_include('options');
extra_include('theme');
extra_include('account');
extra_include('item');
extra_include('form');
extra_include('case');
extra_include('chartjs');
extra_include('message');
extra_include('upload');
extra_include('task');
extra_include('notification');
extra_include('mail');













/* --------------------------------------------------- ADDSLASHES */
if(!get_magic_quotes_gpc()) {
	if (isset($_GET) OR isset($_POST) OR isset($_COOKIE) OR isset($_REQUEST) ) {
	    $_GET 		= extra_get_addslashes($_GET);
	    $_POST 		= extra_get_addslashes($_POST);
	    $_COOKIE 	= extra_get_addslashes($_COOKIE);
	    $_REQUEST 	= extra_get_addslashes($_REQUEST);

	}
}





/* --------------------------------------------------- DEFAULT FUNCTIONS */
is_login();







// company info
extra()->fixed 						= '2';
extra()->company 					= new stdclass();
extra()->company->name 		= '!';
extra()->company->address 	= '';
extra()->company->district = '';
extra()->company->city 		= '';
extra()->company->country 	= 'TURKEY';
extra()->company->email 		= '';
extra()->company->phone 		= '';
extra()->company->gsm 			= '';

$get_option = get_option('company');
if ( !empty($get_option) ) {
	foreach ($get_option as $key => $value) {
		if ( !empty($value) ) {
			extra()->company->$key = $value;
		}
	}
}




$_args = array(
	'taxonomy'=>'form',
	'name'=>'Form',
	'description'=>'Genel form durumu yönetimi',
	'in_out'=>true,
	'sms_template'=>true,
	'email_template'=>true,
	'color'=>true,
	'bg_color'=>true
);
register_form_status($_args);
unset($_args);



$_args = array(
	//'taxonomy'=>'teknik_servis',
	//'name'=>'Teknik Servis',
	'description'=>'',
	'in_out'=>false,
	'sms_template'=>false,
	'email_template'=>false,
	'color'=>true,
	'bg_color'=>false
);
register_form_status($_args);
unset($_args);















?>
