<?php
define("NineteenEleven", true);
include_once '../../includes/class_lib.php';
include_once '../../includes/config.php';
if (isset($_GET['delete_me'])) {
	if (unlink(__FILE__)) {
		$_SESSION['message'] =  "database_charset.php has been removed successfully<br />";
		header("location: ../show_donations.php");
	}else{
		"Failed to remove database_charset.php please do it manually.";
	}
	exit('Good Bye...');
}

$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($mysqliD->error . " " . $mysqliD->errno);
echo '<meta http-equiv="Content-Type"
content="text/html;charset=UTF8">';



//Set database to UTF8
$mysqliD->query("alter table donors charset=utf8;")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("alter table cache charset=utf8;")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("alter table custom_chatcolors charset=utf8;")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("alter table player_tracker charset=utf8;")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("alter database ".DONATIONS_DB." charset=utf8;")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("ALTER TABLE `donors` MODIFY username varchar(255);")or die($mysqliD->error . " " . $mysqliD->errno);
$mysqliD->query("ALTER TABLE  `player_tracker` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	CHANGE  `steamid`  `steamid` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `playername`  `playername` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `playerip`  `playerip` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `servertype`  `servertype` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `serverip`  `serverip` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `serverport`  `serverport` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `geoipcountry`  `geoipcountry` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	CHANGE  `status`  `status` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
$mysqliD->query("ALTER TABLE `donors` CHANGE `user_id` `user_id` INT(11) NOT NULL AUTO_INCREMENT,
	CHANGE `username` `username` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	CHANGE `steam_id` `steam_id` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	CHANGE `sign_up_date` `sign_up_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	CHANGE `email` `email` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `renewal_date` `renewal_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `current_amount` `current_amount` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	CHANGE `total_amount` `total_amount` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `expiration_date` `expiration_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
	CHANGE `steam_link` `steam_link` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `notes` `notes` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `activated` `activated` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0', 
	CHANGE `txn_id` `txn_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
	CHANGE `tier` `tier` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
$mysqliD->query("ALTER TABLE  `cache` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	CHANGE  `steamid`  `steamid` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `avatar`  `avatar` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `avatarmedium`  `avatarmedium` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `avatarfull`  `avatarfull` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `personaname`  `personaname` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `timestamp`  `timestamp` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `steamid64`  `steamid64` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
	CHANGE  `steam_link`  `steam_link` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
$mysqliD->close();

echo "<h1>databases updated to utf8</h1><br>";
echo"<a href='database_utf8.php?delete_me' style='padding: 10px; background-color: rgba(15, 17, 14, 0.8);border: 1px solid white;border-radius: 5px;margin: 10px;text-decoration: none;color: white;text-decoration: none;'> Click here to Delete this file</a><br /><br /><br /><br />";

?>