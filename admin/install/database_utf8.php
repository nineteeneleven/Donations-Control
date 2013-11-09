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

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($mysqli->error . " " . $mysqli->errno);
echo '<meta http-equiv="Content-Type"
content="text/html;charset=UTF8">';



$mysqli->query("alter table donors charset=utf8;")or die($mysqli->error . " " . $mysqli->errno);
$mysqli->query("alter table cache charset=utf8;")or die($mysqli->error . " " . $mysqli->errno);
$mysqli->query("alter table custom_chatcolors charset=utf8;")or die($mysqli->error . " " . $mysqli->errno);
$mysqli->query("alter table player_tracker charset=utf8;")or die($mysqli->error . " " . $mysqli->errno);
$mysqli->query("alter database ".DONATIONS_DB." charset=utf8;")or die($mysqli->error . " " . $mysqli->errno);
$mysqli->query("ALTER TABLE `donors` MODIFY username varchar(255);");
$mysqli->close();

echo "<h1>databases updated to utf8</h1><br>";
echo"<a href='database_utf8.php?delete_me' style='padding: 10px; background-color: rgba(15, 17, 14, 0.8);border: 1px solid white;border-radius: 5px;margin: 10px;text-decoration: none;color: white;text-decoration: none;'> Click here to Delete this file</a><br /><br /><br /><br />";

?>