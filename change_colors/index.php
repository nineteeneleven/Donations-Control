<?php
define('NineteenEleven', TRUE);
require_once '../includes/config.php';
require_once '../includes/class_lib.php';
if (isset($_POST['loginSubmit'])) {

	$log = new log;
	$ConvertID = new SteamIDConvert;
	$tools = new tools;
	$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));

	$steamid_user=$tools->cleanInput($_POST['steamid']);
	$email=$mysqliD->real_escape_string($tools->cleanInput($_POST['email']));

	$userInfo = $ConvertID->SteamIDCheck($steamid_user);
	$steamid = $userInfo['steamid'];


	$sql="SELECT * FROM `donors` WHERE steam_id='{$steamid}' AND email='{$email}' AND tier = '2' AND activated ='1';";

	$count = 0;
	if ($result = $mysqliD->query($sql)or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__)) ){
		$count = $result->num_rows;	
	}

	if($count===1){
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			$username = $row['username'];
			$exp = $row['expiration_date'];
		}
		$mysqliD->close();


	session_start();
	$_SESSION['username'] = $username;
	$_SESSION['email'] = $email;
	$_SESSION['steamid'] = $steamid;
	$_SESSION['exp'] = $exp;
	$ip = $_SERVER['REMOTE_ADDR'];

		print("<center><h1 class='success'> Welcome back {$username} </h1></center>");
		$log->logAction("$username/$email/$ip logged into ". $group2['name']." panel.");
				print("<script type='text/javascript'> setTimeout('reload()' , 1000) 
		function reload(){
			window.location='perks.php'
		}</script>");
		unset($mysqliD);
		unset($log);
				exit();
		
	}else{
		print "<center><h1 class='error'>Wrong Username or Password</h1></center>";	
	unset($mysqliD);
	unset($log);
	}
}

?>
<div id='login'>
	<table width="300" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
		<tr>
			<form id="loginSubmit" method="POST" action="index.php">
				<td>
					<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
						<tr>
							<td colspan="3"><strong><center><?php echo $group2['name'] . " login"; ?> </center> </strong></td>
						</tr>
						<tr>
							<td width="120">Steam ID</td>
							<td width="6">:</td>
							<td width="294"><input name="steamid" type="text" id="steamid"></td>
						</tr>
						<tr>
							<td>PayPal Email</td>
							<td>:</td>
							<td><input name="email" type="email" id="email"></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><input type="submit" name="loginSubmit" value="Login" form='loginSubmit' /><input type='button' id='hideLogin' value='Cancel' /></td>
							
						</tr>
					</table>
				</td>
			</form>
		</tr>
	</table>
</div>

