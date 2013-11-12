<?php
session_start();
if(!isset($_SESSION['username'])){
	header("location:index.php");
}	
define('NineteenEleven', TRUE);
require_once '../includes/config.php';
require_once '../includes/class_lib.php';
$log = new log;
$ConvertID = new SteamIDConvert;
$tools = new tools;
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));	
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$steamid = $_SESSION['steamid'];
$ip = $_SERVER['REMOTE_ADDR'];
$exp = $_SESSION['exp'];


if (isset($_POST['index'])) {
	require_once '../scripts/rcon_code.php';
	$sb = new SourceBans;
	$index = $mysqliD->real_escape_string($_POST['index']);
    $nameColor = $mysqliD->real_escape_string(str_replace("#", "", $_POST['nameColor']));
    $chatColor = $mysqliD->real_escape_string(str_replace("#", "", $_POST['chatColor'])); 
	$mysqliD->query("UPDATE `custom_chatcolors` SET  `namecolor` =  '".$nameColor."',`textcolor` =  '".$chatColor."' WHERE `index` ='".$index."';")or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));
	$log->logAction("$username/$email/$ip changed thier colors to $chatColor(chat) and $nameColor(name).");
	if($sb->queryServers("sm_reloadccc")){
		printf("<center><h1>You name color has been changed to %s, and chat color to %s</h1></center>", $nameColor , $chatColor);
		$log->logAction("CCC reloaded successfully");
	}

}else{
	echo "<center><h1> Welcome back $username your donor perks expire on " .date('l F j, Y',$exp) . "</h1></center>";
}
if($result = $mysqliD->query("SELECT * FROM `custom_chatcolors` WHERE identity = '$steamid';")or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__))){
    if($result->num_rows > 0){
        $row = $result->fetch_array(MYSQLI_ASSOC);
		$nameColor = $row['namecolor'];
		$chatColor = $row['textcolor'];
		$index = $row['index'];
    }else{
    	printf("Unable to find chat colors for %s" , $username);
    }
}



echo '
<html>
<body>
<head>
<script type="text/javascript" src="../scripts/jscolor/jscolor.js"></script>
</head>
<center>
<form id="color" method="post" action="perks.php">
<p><input class="color" name="nameColor" value="#'.$nameColor.'" id="colorInput">Name Color<input class="color" name="chatColor" value="#'.$chatColor.'" id="colorInput">Chat Color</p>
<input type="hidden" name="index" value="'.$index.'">
<input type="submit" value="Change Colors" form="color">
</form>
</center>
</body>
</html>
';
unset($mysqliD);
?>
