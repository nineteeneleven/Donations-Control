<?php
/*Donations Control version 2.0 by NineteenEleven. 
*http://nineteeneleven.info
*if you find this helpful please consider donating.
*
*Instruction manual provided at http://nineteeneleven.info/dc_manual.html
*
*/
if(!defined('NineteenEleven')){die('Direct access not premitted');}
//Fill in your preferences, and information

///////////////
//PayPal Info//
///////////////


define('PP_EMAIL', 'your@paypal.com');              //The Paypal account's email address
define('PP_DESC',  'Donation to your clans servers');  //Paypal purchase description
define('PP_IPN', 'http://yourdomain.com/donation/scripts/ipn.php'); //Address to ipn.php included within the donations folder
define('PP_SUCCESS', 'http://yourdomain.com/');        //Address to send donor to after successful donation
define('PP_FAIL', 'http://yourdomain.com/');       //Address to send donor to after cancel while donating / other error
define('PP_CURRENCY', 'USD'); //https://developer.paypal.com/webapps/developer/docs/classic/api/currency_codes/#id09A6G0U0GYK


//Use PayPal Sandbox for testing?
define('PP_SANDBOX', false);
define('PP_SANDBOX_EMAIL', 'yoursandbox@paypal.com');

///////////////////////////
//Donations Database info//
///////////////////////////

define('DB_HOST' , 'localhost');        //set MySQL host
define('DB_USER' , 'root');             //MySQL username
define('DB_PASS' , 'password2strong');         //MySQL password
define('DONATIONS_DB' , 'donations');   //donations database


////////////////////
//Sourcebans info//
///////////////////

//Sourcebans is required as of 2.0.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
define('SB_DB', false); //ONLY SET TRUE IF SOURCEBANS IS ON A DIFFERENT MYSQL SERVER
define('SB_SV_HOST' , 'localhost');      //set MySQL host ONLY NEEDED IF SOURCEBANS IS ON A DIFFERENT MYSQL SERVER
define('SB_SV_USER' , 'root');         //MySQL username ONLY NEEDED IF SOURCEBANS IS ON A DIFFERENT MYSQL SERVER
define('SB_SV_PASS' , 'password2strong');       //MySQL password ONLY NEEDED IF SOURCEBANS IS ON A DIFFERENT MYSQL SERVER
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
define('SOURCEBANS_DB', 'sourcebans'); // sourcebans database, this is needed.
define('SB_PREFIX', 'sb'); //Sourcebans database prefix. Only change this value if you changed your database prefix when setting up SourceBans.
define('SB_SALT', 'SourceBans'); //dont change this unless you changed your salt in sourcebans (if you dont know what salt is, you didnt change it)
define('SB_ADMINS', 'Administrators'); //name of admin group in sourcebans which has access to the donor panel
///////////////////////////////////////////////////////////////////////////////////////////
define('TIERED_DONOR', true); //multiple class of donors eg. VIP/Elite

/*How to get this information
*Add a donor inside your sourcebans web panel, you can use anyone.
*make sure the donor belongs to a server admins group that has the proper flags
*for your donors. 
*Also make sure you have a server group, that includes the servers your donors have perks on.
*and make sure the donor is assigned to that group.
*Then use the config_lookup.php and look up that donors steamid, and enter those values here.
*/
//Tier 1 donors (required)
$group1['name'] = 'Donor';
$group1['group_id'] = '0';
$group1['srv_group_id'] = '0';
$group1['server_id'] = '0';
$group1['multiplier'] = '6.2'; //This will control how many days a donor gets perks for. $5 * 6.2 days = 31 days of perks
////////////////////////////////////////////



//Tier 2 donors (only needed if TIERED_DONOR is true)
$group2['name'] = 'VIP';
$group2['group_id'] = '0';
$group2['srv_group_id'] = '0';
$group2['server_id'] = '0';
$group2['multiplier'] = '3.1'; // $10 * 3.1 days = 31 days of perks
///////////////////////////////////////////////////////////


//////////////////////////
//System Emails Settings//
//////////////////////////

define('sys_email', true);              //Turn on system emails?
$mail['name'] = 'Donations';                 //senders name
$mail['email'] = 'donations@yourdomain.com';    //senders e-mail adress
$mail['recipient'] = 'your@email.com';   //recipient
$mail['useBCC'] = true;                         //add BCC
$mail['BCC'] = 'your@friend.com';           //BCC
$mail['donor'] = true; //Send confimation/ thankyou email to the donor?
$mail['donorSubject'] = 'Thank your for your donation';
$mail['donorMsg'] = 'Message to send to your donors';

//////////////////////
////Miscellaneous////
////////////////////

define('CCC', true); //https://forums.alliedmods.net/showpost.php?p=1738314&postcount=56#MySQLModule
date_default_timezone_set('America/New_York'); //http://php.net/manual/en/timezones.php
define('cache_time', '15'); //days to resolve cache for information from steam, mainly the avatar image, and display name.
define('PLAYER_TRACKER', true); //use player tracker to automatically fetch steam ids?
define("DEFAULT_LANGUAGE", "en-us"); // name of file in translation folder. dont add .json
$availableLanguages = array('en-us' =>'English' , '1337' =>'1337 5pEek','pt-br' => 'Portuguese (Brazil)', 'es-mx' => 'Spanish (Mexico)'); //set friendly display names here http://msdn.microsoft.com/en-us/library/ms533052(vs.85).aspx
define('API_KEY' , 'XXXXXXXXXXXXXXXXXXXXXXXXXX');
define("STATS", true);
////////////////////////
//dont edit this stuff//
////////////////////////
if (SB_DB) {
define('SB_HOST', SB_SV_HOST);
define('SB_USER', SB_SV_USER);
define('SB_PASS', SB_SV_PASS);
}else{
define('SB_HOST', DB_HOST);
define('SB_USER', DB_USER);
define('SB_PASS', DB_PASS);
}

//Please consider donating before removing/changing the footer http://nineteeneleven.info
$footer = "<div id='footer' style='background-color:black;border-radius:10px;padding:10px;margin:5px;color:white;position:fixed;bottom:0px;width:97%;z-index:99;'><a style='text-decoration:none;color:white;' href='http://nineteeneleven.info' target='_blank' onmouseover=\"this.style.backgroundColor='red'\" onmouseout=\"this.style.backgroundColor=''\">Donations Control 2.0.4 Powered by NineteenEleven</a></div>";
?>
