<?php
if (isset($_POST['loginSubmit'])) {
define('NineteenEleven', TRUE);
require_once '../includes/config.php';
require_once '../includes/class_lib.php';
$log = new log;
	$user_name=$_POST['user_name'];
	$password=$_POST['password'];
	$mysqliS = new mysqli(SB_HOST,SB_USER,SB_PASS,SOURCEBANS_DB)or die(logError($mysqliS->error . " " . $mysqliS->errno));
	
	// To protect MySQL injection (more detail about MySQL injection)
	$Suser_name = stripslashes($_POST['user_name']);
	$password = sha1(sha1(SB_SALT . $password));
	//$Suser_name = $mysqliS->real_escape_string($Suser_name);
	//$password = $mysqliS->real_escape_string($password);

	$sql="SELECT * FROM ".SB_PREFIX."_admins WHERE user='{$Suser_name}' and password='{$password}' and srv_group = '".SB_ADMINS."';";
	//echo "SELECT * FROM ".SB_PREFIX." WHERE username='{$Suser_name}' and password='{$password}' and srv_group = '".SB_ADMINS."';";
	//get number of rows retured from sql query, if there is a row, it must be our user
	$count = 0;
	if ($result = $mysqliS->query($sql)or die($mysqliS->error . " " . $mysqliS->errno)) {
		$count = $result->num_rows;
		
	}

	if($count===1){
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			$email = $row['email'];
			//$authlevel = $row['authlevel'];
		}
		$mysqliS->close();
	session_start();
	$_SESSION['username'] = $Suser_name;
	$_SESSION['email'] = $email;
	//$_SESSION['authlevel'] = $authlevel;
		print("<center><h1 class='success'> Welcome back {$Suser_name} </h1></center>");
		$log->logAction("$user_name logged in.");
				print("<script type='text/javascript'> setTimeout('reload()' , 1000) 
		function reload(){
			window.location='show_donations.php'
		}</script>");
				exit();
		
	}else{
		print "<center><h1 class='error'>Wrong Username or Password</h1></center>";
		$log->logAction("Failed login attempt for user name: $user_name");
	
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
							<td colspan="3"><strong>Admin Login </strong></td>
						</tr>
						<tr>
							<td width="78">Username</td>
							<td width="6">:</td>
							<td width="294"><input name="user_name" type="text" id="user_name"></td>
						</tr>
						<tr>
							<td>Password</td>
							<td>:</td>
							<td><input name="password" type="password" id="password"></td>
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

