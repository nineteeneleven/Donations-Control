<?php
//This script will query the database and remove all expired donors
//set up with cron to call @daily
define('NineteenEleven', TRUE);
require_once'../includes/config.php';
require_once '../includes/class_lib.php';
require_once 'rcon_code.php';
$sb = new SourceBans;
$tools = new tools;
$sysLog = new log;
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno));


$log=fopen('../admin/logs/Remove-Expired-'.date('m-d-Y_G-i-s'). '.log', "a");

$activated = "2";
$today = date('U');
$query_sb = false;
$i=0;
//query database
$sql = "SELECT * FROM donors WHERE expiration_date <= '" . $today . "' AND `activated` = 1;";
//$sql = "SELECT steam_id, expiration_date FROM donors WHERE 1";

$result = $mysqliD->query($sql) or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno));
while($donor = $result->fetch_array(MYSQLI_ASSOC)){
		$i++;

		$steam_id = $donor['steam_id'];
		$username = $donor['username'];
		$tier = $donor['tier'];
		//change $activated
		$mysqliD->query("UPDATE `donors` SET `activated` = '{$activated}' WHERE `steam_id` = '{$steam_id}';")or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno));
		//turn off sourcebans
			if($sb->removeDonor($steam_id,$tier)){
				$query_sb = true;
				fwrite($log, "$username removed from sourcebans successfully\r\n");
				$sysLog->logAction("AUTOMATIC ACTION: $username Removed (Perks Expired)");
			}else{
				fwrite($log, "Something went wrong with removing $username from sourcebans\r\n");
			}
			if(TIERED_DONOR&&CCC){
				@$mysqliD->query("DELETE FROM `custom_chatcolors` WHERE identity ='" . $steam_id . "';");
			}
}



if ($query_sb) {
	if($sb->queryServers('sm_reloadadmins')){
		fwrite($log, "Servers Rehashed\r\n");
	}
}

fwrite($log, $i . " Users expired\r\n");
$mysqliD->close();
unset($mysqliD);
unset($sb);
fwrite($log, "All done here, closing log file....good bye.");
fclose($log);
?>