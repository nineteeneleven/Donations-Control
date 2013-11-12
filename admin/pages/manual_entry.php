<?php
if (isset($_POST['manual_entry'])) {
	if (!defined('adminPage')) {
		exit("Direct access not premitted.");
	}
	if (!defined('NineteenEleven')) {
		define('NineteenEleven', TRUE);
	}
	$ConvertID = new SteamIDConvert;
	$tools = new tools;
	$SteamQuery = new SteamQuery;
	$sb = new SourceBans;
	$steamid_user = $mysqliD->real_escape_string($tools->cleanInput($_POST['steam_id']));
	$userInfo = $ConvertID->SteamIDCheck($steamid_user);
	$steam_id = $mysqliD->real_escape_string($userInfo['steamid']);
	$steamId64 = $mysqliD->real_escape_string($userInfo['steamID64']);
	$steam_link = $mysqliD->real_escape_string($userInfo['steam_link']);
	$sign_up_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['sign_up_date'])));
	$email = $mysqliD->real_escape_string($tools->cleanInput($_POST['email']));
	$renewal_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['renewal_date'])));
	$current_amount = $mysqliD->real_escape_string($tools->dollaBillz($tools->cleanInput($_POST['current_amount'])));
	$total_amount = $mysqliD->real_escape_string($tools->dollaBillz($tools->cleanInput($_POST['total_amount'])));
	$expiration_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['expiration_date'])));
	$notes = $mysqliD->real_escape_string($tools->cleanInput($_POST['notes']));
	$activated = $_POST['activated'];
	if(TIERED_DONOR){
		$tier = $_POST['tier'];
	}else{
		$tier = '1';
	}
	if (empty($renewal_date)) {
		$renewal_date = 0;
	}

$profile = $SteamQuery->GetPlayerSummaries($userInfo['steamID64']);
$username = $profile->response->players[0]->personaname;
// Get User Profile Data


$username =$tools->cleanUser($username);   
$username = $mysqliD->real_escape_string($username);
    $result = $mysqliD->query("SELECT user_id FROM donors WHERE steam_id = '{$steam_id}';")or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));
    if($result){
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $user_id = $row['user_id'];
    }else{die();}


    if (isset($user_id)) {
    	die("<h1 class='error'>This user is already in the database</h1><br /><p><a href='javascript:history.go(-1);'>Click here to go back</a></p>");
    	exit();
    } else {
    	$insert_sql = "INSERT INTO donors (username,steam_id,sign_up_date,renewal_date,current_amount,total_amount,expiration_date,steam_link,email,notes,activated,tier) VALUES ('{$username}', '{$steam_id}', '{$sign_up_date}', '{$renewal_date}','{$current_amount}','{$total_amount}','{$expiration_date}','{$steam_link}','{$email}','{$notes}','{$activated}','{$tier}');";
    }
	$mysqliD->query($insert_sql) or die("<h1 class='error'>FAILED TO UPDATE USER</h1><br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>". $log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));

	//insert/remove user from sourcebans database
		switch ($activated) {
				case 1:
					//check sourcebans database to see if user is already in there
					$r = $sb->addDonor($steam_id , $username, $tier);


					if($r===TRUE){
						if(!$sb->queryServers('sm_reloadadmins')){
							echo "<h1 class='error'> Server Rehash Failed</h1>";
							$log->logError('Failed to rehash servers.');
						}
					}

					unset($r);					

					$insert_sql="UPDATE `donors` SET `username` = '{$username}', `steam_id` = '{$steam_id}', `sign_up_date` = '{$sign_up_date}', `email` = '{$email}', `renewal_date` = '{$renewal_date}', `current_amount` = '{$current_amount}', `total_amount` = '{$total_amount}', `expiration_date` = '{$expiration_date}', `steam_link` = '{$steam_link}', `notes` = '{$notes}', `activated` = '{$activated}', `tier` = '{$tier}' WHERE `user_id` = '{$user_id}';";
					$mysqliD->query($insert_sql) or die("<h1 class='error'>FAILED TO UPDATE USER</h1><br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>".$log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));
					break;

				case 2:
				//remove donor from sourcebans
						$r = $sb->removeDonor($steam_id , $tier);

					if($r===TRUE){
						if(!$sb->queryServers('sm_reloadadmins')){
							echo "<h1 class='error'> Server Rehash Failed</h1>";
						}
					}
					unset($r);
							
					$insert_sql="UPDATE `donors` SET `username` = '{$username}', `steam_id` = '{$steam_id}', `sign_up_date` = '{$sign_up_date}', `email` = '{$email}', `renewal_date` = '{$renewal_date}', `current_amount` = '{$current_amount}', `total_amount` = '{$total_amount}', `expiration_date` = '{$expiration_date}', `steam_link` = '{$steam_link}', `notes` = '{$notes}', `activated` = '{$activated}', `tier` = '{$tier}' WHERE `user_id` = '{$user_id}';";
					$mysqliD->query($insert_sql)or die("<h1 class='error'>FAILED TO UPDATE USER</h1><br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>". $log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));

					break;


				default:
				//if user was never initially activated, yell at admin.
					 print("<script type='text/javascript'>");
					 print("alert('Y NO PERKS!??!?!?! \\n Going back to edit page');");
					 print("history.go(-1);");
					 print("</script>");
					 $goBack = false;
					break;
			}

unset($ConvertID);
unset($tools);
unset($SteamQuery);
unset($sb);
$_SESSION['message'] = "<h1 class='success'>".sprintf($lang->sysmsg[0]->successenter)."</h1>";
if (STATS) {
	@$log->stats("ME");
}
$log->logAction(sprintf($lang->logmsg[0]->manualentry,$_SESSION['username'], $username));
header("Location: show_donations.php");
}
?>
		<form action='show_donations.php' method='POST' id='manual_entry_form'>
			<fieldset id='edit_user_form'>
				<table>
				<?php
				echo "
					<tr>
						<th>".$lang->admin[0]->steamid."</th>
						<th>".$lang->admin[0]->sud."</th>
						<th>".$lang->admin[0]->email."</th>
						<th>".$lang->admin[0]->rd."</th>
						<th>".$lang->admin[0]->current."</th>
						<th>".$lang->admin[0]->total."</th>
						<th>".$lang->admin[0]->ed."</th>
						<th>".$lang->admin[0]->notes."</th>
					</tr>";
					?>
					<tr>
				        <td><input name='steam_id' placeholder="STEAM_X:X:XXXXXX" type="text" required='true' title="You can use any variation of a Steam ID here."/></td>
				        <td><input name='sign_up_date' placeholder="DD/MM/YY" type="text" required='true' class='date'/></td>
				        <td><input name='email' placeholder="<?php echo $lang->admin[0]->semail; ?>" type="text" /></td>
				        <td><input name='renewal_date' placeholder="DD/MM/YY" type="text" class='date'/></td>
				        <td><input name='current_amount' placeholder="5" type="text" required='true' /></td>
				        <td><input name='total_amount' placeholder="5" type="text" required='true' /></td>
				        <td><input name='expiration_date' placeholder="DD/MM/YY" type="text" required='true' class='date'/></td>
				        <td><textarea name='notes' placeholder='<?php echo $lang->admin[0]->snotes; ?>'></textarea></td>
				        <input type="hidden" name="manual_entry" value="1">
			        <tr>
			    </table>   
			    <br />
			    <br />	
			    	<div id='activSwitch'>
			        	<input type="radio" name='activated' value='1' id='perkRadio' checked /><?php echo $lang->admin[0]->perkson; ?>
			        	<input type="radio" name='activated' value='2' id='perkRadio' /> <?php echo $lang->admin[0]->perksoff; ?>
			        </div>
			        
			     <?php
			     if(TIERED_DONOR){
			     		echo "<div id='vipSwitch'>";
			        	echo "<input type='radio' name='tier' value='1' id='tierRadio'  checked />".$group1['name'] . " ";
			        	echo "<input type='radio' name='tier' value='2' id='tierRadio' />".$group2['name'] . " ";
			        	echo "</div>";
			     }?>
			 
		    </fieldset>
		  
		   <input type='submit' value='Manual Entry' form='manual_entry_form' />
		   <input type='reset' value='Reset Fields' form='manual_entry_form' />
			</form>