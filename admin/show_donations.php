<?php
session_start();
if(!isset($_SESSION['username'])){
	header("location:index.php");
}
else{
	$Suser_name = $_SESSION['username'];
	$Semail = $_SESSION['email'];
}
define('NineteenEleven', TRUE);
define('adminPage', TRUE);

require_once '../includes/config.php';
require_once '../includes/class_lib.php';
require_once '../scripts/rcon_code.php';
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($mysqliD->error . " " . $mysqliD->errno);
$log = new log;
echo '<html>';
echo '<head>';
echo '<link type="text/css" rel="stylesheet" href="style.css" />';
echo'<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />';
echo'<script src="http://code.jquery.com/jquery-1.9.1.js"></script>			';
echo'<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>';
echo'<script>
	$(document).ready(function() {
		$(".date").datepicker({ dateFormat: "mm/dd/y" });
	});
</script>';
echo '<title>Donor List</title>';
echo '</head>';
echo '<body>';
echo '<nav>';
echo '<ul>';
echo '<li><a href="show_donations.php" id="List">Home</a></li>';
echo '<li><a href="show_donations.php?manual_entry" id="manualEntryButton"> Manual Entry </a></li>';
echo '<li><a href="show_donations.php?server_query" id="server_query"> Query Servers </a></li>';;
echo '<li><a href="show_donations.php?action_log" id="action">Action Logs</a></li>';
echo '<li><a href="show_donations.php?error_log" id="errorLog">Error Logs</a></li>';
echo '<li><a href="show_donations.php?logout" id="logout">Log out</a></li>';
echo '</ul>';
echo '</nav>';
if (isset($_GET['logout'])) {

	require_once 'pages/logout.php';
	
}elseif (isset($_GET['manual_entry']) || isset($_POST['manual_entry'])) {

	require_once 'pages/manual_entry.php';
	
}elseif(isset($_GET['edit_user']) || isset($_POST['edit_user_form'])){

	require_once 'pages/edit_user.php';

}elseif(isset($_GET['delete_user']) && $_GET['delete_user'] == 1){
	$sb = new SourceBans;
	$steam_id = $_GET['steam_id'];
	$tier = $_GET['tier'];
	$username = $_GET['username'];
	$delete_sql = "DELETE FROM `donors` WHERE `steam_id` ='" . $steam_id . "';"; 
	$mysqliD->query($delete_sql) or die("<h1 class='error'>Failed to delete $username from donations database.</h1>" . $log->logError($mysqliD->error . " " . $mysqliD->errno));
	if ($sb->removeDonor($steam_id,$tier)) {
		if($sb->queryServers('sm_reloadadmins')){
			$log->logAction('Rehashed all servers');
		}else{
			$log->logError('Failed to rehash servers.');
		}
	}else{
		echo "<h1 class='error'>There was a problem removing {$username} from sourcebans.</h1>";
		$log->logError('Unable to remove $username from sourcebans.');
	}
	unset($sb);
	if(TIERED_DONOR&&CCC&&$tier=='2'){
		@$mysqliD->query("DELETE FROM `custom_chatcolors` WHERE `identity` ='" . $steam_id . "';");
	}
	$_SESSION['message']="<h3 class='success'>Steam ID {$username} has been removed completly from the system.</h3>";

	$log->logAction($_SESSION['username'] ." deleted $username");

	header('location: show_donations.php');

}elseif(isset($_GET['error_log'])){
	require_once 'pages/error_log.php';
}elseif(isset($_GET['action_log'])){
	require_once 'pages/action_log.php';
}elseif(isset($_GET['server_query'])||isset($_POST['COMMAND'])){
	require_once 'pages/nuclear.php';
}else{
	if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	}
	require_once 'pages/list.php';

}


echo "<html>";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "$footer";
echo "</html>";

$mysqliD->close();
?>

