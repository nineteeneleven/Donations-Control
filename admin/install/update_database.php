<?php
define('NineteenEleven', TRUE);
require_once '../../includes/config.php';
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($mysqliD->error . " " . $mysqliD->errno);


if (isset($_GET['delete_me'])) {
	if (unlink(__FILE__)) {
		echo "Poof!\r\n update_database.php has been removed successfully<br />";
	}else{
		"Failed to remove update_database.php please do it manually.";
	}
	exit('Good Bye...');
}


echo"<a href='update_database.php?delete_me' style='padding: 10px; background-color: rgba(15, 17, 14, 0.8);border: 1px solid white;border-radius: 5px;margin: 10px;text-decoration: none;color: white;text-decoration: none;'> Click here to Delete this file</a><br /><br /><br /><br />";
//check for CCC database, if exist add auto incremenet, if not add table.
if ($mysqliD->query("DESCRIBE `custom_chatcolors`")) {
	echo "Found CCC table.\r\n";
	if($mysqliD->query("ALTER TABLE `custom_chatcolors` ADD index INT PRIMARY KEY AUTO_INCREMENT;")){
		echo "CCC table modified successfully to work with Donations Control.\r\n";
	}else{
		echo "Failed to update CCC table.\r\n";
	}
}else{
	$ccc_create = "CREATE TABLE IF NOT EXISTS `custom_chatcolors` (
				  `index` int(11) NOT NULL AUTO_INCREMENT,
				  `identity` varchar(32) NOT NULL,
				  `flag` char(1) DEFAULT NULL,
				  `tag` varchar(32) DEFAULT NULL,
				  `tagcolor` varchar(8) DEFAULT NULL,
				  `namecolor` varchar(8) DEFAULT NULL,
				  `textcolor` varchar(8) DEFAULT NULL,
				  PRIMARY KEY (`index`),
				  UNIQUE KEY `identity` (`identity`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	if($mysqliD->query($ccc_create)){
		echo "CCC table created.\r\n";
	}else{
		echo "Failed to created CCC table.\r\n";
	}
}

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
	CHANGE `renewal_date` `renewal_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0', 
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


// add column tier to the donations database
if($mysqliD->query("ALTER TABLE `donors` ADD COLUMN `tier` varchar(10);")){
	echo "Added column 'tier' to the donor table successfully.<br />";
}else{
	echo "failed to add 'tier' column to database.<br />";
}



// convert dates in database to epioch, populate tier column.
$result = $mysqliD->query("SELECT * FROM `donors` WHERE 1")or die("Database query failed.");

while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	$user_id = $row['user_id'];
	$sDate=strtotime($row['sign_up_date']);
		if (empty($sDate)) {
			$sDate=$row['sign_up_date'];
			echo "Not updating sign_up_date for ". $row['username']." date mis-match in database.<br />";
		}

	if($row['renewal_date']=='N/A' || $row['renewal_date']=='n/a'  || $row['renewal_date']=='0'|| empty($row['renewal_date'])){
		$rdate = 0;
		if (empty($rDate)) {
			$rDate=$row['renewal_date'];
			echo "Not updating renewal_date for ". $row['username']." date mis-match in database.<br />";
		}
	}else{
		$rDate=strtotime($row['renewal_date']);
		if (empty($rDate)) {
			$rDate=$row['renewal_date'];
			echo "Not updating renewal_date for ". $row['username']." date mis-match in database.<br />";
		}
	}
		$eDate=strtotime($row['expiration_date']);
		if (empty($eDate)) {
			$eDate=$row['expiration_date'];
			echo "Not updating expiration_date for ". $row['username']." date mis-match in database.<br />";

		}

	if($mysqliD->query("UPDATE `donors` SET `sign_up_date`= '{$sDate}', `renewal_date` = '{$rDate}', `expiration_date` = '{$eDate}', `tier` ='1' WHERE user_id = {$user_id};"))
	{
		echo 'Updated: ' . $row['username'] . '<br />';
	}else{
		echo '<h3>FAILED TO UPDATE: ' . $row['username'] . '</h3><br />';
	}
}

$mysqliD->close();

?>