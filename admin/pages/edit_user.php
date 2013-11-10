<?php

if (!defined('adminPage')) {
	exit("Direct access not premitted.");
}
if (!defined('NineteenEleven')) {
	define('NineteenEleven', TRUE);
}

if (isset($_POST['edit_user_form'])) {

	$ConvertID = new SteamIDConvert;
	$tools = new tools;
	$SteamQuery = new SteamQuery;
	$sb = new SourceBans;
	$log = new log;
	$user_id = $mysqliD->real_escape_string($tools->cleanInput($_POST['user_id']));
	$username = $mysqliD->real_escape_string($tools->cleanUser($_POST['username']));
	$steamid_user = $mysqliD->real_escape_string($tools->cleanInput($_POST['steam_id']));
	$userInfo = $ConvertID->SteamIDCheck($steamid_user);
	$steam_id = $mysqliD->real_escape_string($userInfo['steamid']);
	$steamId64 = $mysqliD->real_escape_string($userInfo['steamID64']);
	$steam_link = $mysqliD->real_escape_string($_POST['steam_link']);
	$sign_up_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['sign_up_date'])));
	$email = $mysqliD->real_escape_string($tools->cleanInput($_POST['email']));
	if ($_POST['renewal_date'] == 'Never' || empty($_POST['renewal_date'])) {
		$renewal_date = 0;
	}else{
		$renewal_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['renewal_date'])));
	}
	$current_amount = $mysqliD->real_escape_string($tools->dollaBillz($tools->cleanInput($_POST['current_amount'])));
	$total_amount = $mysqliD->real_escape_string($tools->dollaBillz($tools->cleanInput($_POST['total_amount'])));
	$expiration_date = $mysqliD->real_escape_string(strtotime($tools->cleanInput($_POST['expiration_date'])));
	$notes = $mysqliD->real_escape_string($tools->cleanInput($_POST['notes']));
	$activated = $_POST['activated'];
	if(TIERED_DONOR){
		$tier = $_POST['tier'];
	}else{
		$tier = "1";
	}
	$rehash = false;
	$goBack = true;

	//look up player, and see if they are activated
	$get_record = "SELECT activated,tier FROM donors WHERE user_id=" . $user_id;
	$result = $mysqliD->query($get_record);
	if($result){
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$post_activated = $row['activated'];
		$post_tier = $row['tier'];

	} else {
	  die("<h1 class='error'>Error locating donor with ID {$user_id}</h1>" . $log->logError('Error locating donor with ID $user_id'));
	}

	//check to see if there was change in activation
	if($post_activated != $activated){
		//if so, switch them, and update all databases
		//1 = activated 2=disabled 0 = awaiting initial activation

		switch ($activated) {
			case 1:
					$r = $sb->addDonor($steam_id , $username, $tier);
					if($r===TRUE){
						$rehash = true;
					}
				unset($r);
				break;

			case 2:
					$r = $sb->removeDonor($steam_id , $tier);
					if($r===TRUE){
						$rehash = true;
					}
				unset($r);
				if(TIERED_DONOR&&CCC&&$post_tier=='2'){
					$mysqliD->query("DELETE FROM `custom_chatcolors` WHERE identity ='" . $steam_id . "';")or die($log->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: " . __LINE__));
				}
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
	}

	if (TIERED_DONOR) {
		if ($post_tier != $tier) {
			$r = $sb->removeDonor($steam_id , $tier);
				if($r===TRUE){
					$a = $sb->addDonor($steam_id , $username , $tier);
					if($a===TRUE){
						$rehash = true;
					}
				}
			unset($r);
			unset($a);
		}
	}

	$insert_sql="UPDATE `donors` SET `username` = '{$username}', `steam_id` = '{$steam_id}', `sign_up_date` = '{$sign_up_date}', `email` = '{$email}', `renewal_date` = '{$renewal_date}', `current_amount` = '{$current_amount}', `total_amount` = '{$total_amount}', `expiration_date` = '{$expiration_date}', `steam_link` = '{$steam_link}', `notes` = '{$notes}', `activated` = '{$activated}', `tier` = '{$tier}' WHERE `user_id` = '{$user_id}';";
	$mysqliD->query($insert_sql) or die("<h1 class='error'>FAILED TO UPDATE USER</h1><br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>". $log->logError($mysqliD->error. " " . $mysqliD->errno ." Line Number: " . __LINE__));

	if($rehash){
		if(!$sb->queryServers('sm_reloadadmins')){
			$_SESSION['message'] = "<h1 class='success'>{$username} edited successfully</h1><br /><h1 class='error'> Server rehash Failed</h1>";
			$log->logAction($_SESSION['username'] . " edited $username");
			$log->logError('Server rehash failed');
		}else{
			$_SESSION['message'] = "<h1 class='success'>{$username} edited successfully</h1>";
			$log->logAction($_SESSION['username']. " edited $username");
			$log->logAction('Rehashed all servers');
		}
	}else{
		$_SESSION['message'] = "<h1 class='success'>{$username} edited successfully</h1>";
		$log->logAction($_SESSION['username']. " edited $username");		
	}
	if (STATS) {
		@$log->stats("EU");
	}
	unset($ConvertID);
	unset($tools);
	unset($SteamQuery);
	unset($sb);
	if ($goBack) {
	header("Location: show_donations.php");
	}
}

$user_id = $_GET['user_id'];
$result = $mysqliD->query("SELECT * FROM donors WHERE user_id=" . $user_id);
if($result){
	$row =$result->fetch_array(MYSQLI_ASSOC);
	$username = $row['username'];
	$steam_id = $row['steam_id'];
	$sign_up_date = date('n/j/Y', $row['sign_up_date']);
	$email = $row['email'];
	if ($row['renewal_date'] == 0) {
		$renewal_date = 0;
	}else{
		$renewal_date = date('n/j/Y', $row['renewal_date']);
	}
	$current_amount = $row['current_amount'];
	$total_amount = $row['total_amount'];
	$expiration_date = date('n/j/Y',$row['expiration_date']);
	$steam_link = $row['steam_link'];
	$notes = $row['notes'];
	$activated = $row['activated'];
	if(TIERED_DONOR){
		$tier = $row['tier'];
	}

} else {
	//$mysqliD->close();
    die("<h1 class='error'>Error locating donor with ID {$user_id}");
}
?>

<html>
	<head>
		<script type="text/javascript">
		<?php
			if(TIERED_DONOR){
				echo 'function delete_confirm(steam_id,tier){
					if (confirm("Are you sure you want to delete this user, and remove them from all databases? \n This is permanent and can\'t be un-done!")){
						window.location = "show_donations.php?delete_user=1&steam_id=" + steam_id + "&tier=" + tier;
					}
				};';
			}else{
				echo 'function delete_confirm(steam_id){
					if (confirm("Are you sure you want to delete this user, and remove them from all databases? \n This is permanent and can\'t be un-done!")){
						window.location = "show_donations.php?delete_user=1&steam_id=" + steam_id;
					}
				};';
			}		
			?>
			$(document).ready(function() {
				$(".date").datepicker({ dateFormat: "mm/dd/y" });
			});
		</script>
	</head>
	<body>
		<form action='show_donations.php' method='POST' id='edit_user_form'>
			<fieldset id='edit_user_form'>
				<table>
					<tr>
						<th>Steam Name</th>
						<th>Steam ID</th>
						<th>Sign up Date</th>
						<th>email</th>
						<th>Renewal Date</th>
						<th>Current Amount</th>
						<th>Total Amount</th>
						<th>Expiration Date</th>
						<th>Steam link</th>
						<th>Notes</th>
					</tr>
					<tr>
						<td><input name='username' value="<?php echo $username ?>" type="text" /></td>
				        <td><input name='steam_id' value="<?php echo $steam_id?>" type="text" /></td>
				        <td><input name='sign_up_date' value="<?php echo $sign_up_date ?>" type="text" class='date' /></td>
				        <td><input name='email' value="<?php echo $email ?>" type="text" /></td>
				        <?php if ($renewal_date==0) {
				        	echo "<td><input name='renewal_date' value='Never' type='text' class='date' /></td>";
				        }else{
				        	echo "<td><input name='renewal_date' value="  .$renewal_date . " type='text' class='date' /></td>";
				        }
				        ?>
				        <td><input name='current_amount' value="<?php echo $current_amount ?>" type="text" /></td>
				        <td><input name='total_amount' value="<?php echo $total_amount ?>" type="text" /></td>
				        <td><input name='expiration_date' value="<?php echo $expiration_date ?>" type="text" class='date' /></td>
				        <td><textarea name='steam_link'><?php echo $steam_link ?></textarea></td>
				        <td><textarea name='notes'><?php echo $notes ?></textarea></td>
			        <tr>
			    </table>
			    <br />
			    <br />	
			        	<input name='user_id' value="<?php echo $user_id ?>" type="hidden" />
			        	<input type="radio" name='activated' value='1' <?php if($activated==='1'){echo "checked /> Perks Activated";} else {echo "/> Add Perks";} ?> 
			        	<input type="radio" name='activated' value='2' <?php if($activated==='2'){echo "checked /> Perks Off";} else {echo "/> No Perks";} ?> 
			     <?php
			     if(TIERED_DONOR){
			     		if($tier =="1"){
			        	echo "<input type='radio' name='tier' value='1' id='tierRadio' checked /> ".$group1['name'] . " ";
			        	echo "<input type='radio' name='tier' value='2' id='tierRadio' />".$group2['name'] . " ";
			        }else{
			        	echo "<input type='radio' name='tier' value='1' id='tierRadio' /> ".$group1['name'] . " ";
			        	echo "<input type='radio' name='tier' value='2' id='tierRadio' checked />".$group2['name'] . " ";
			        }
			     }?>
			      <input type="hidden" name="edit_user_form" value="1">
		    </fieldset>
		   </form>
		   <input type='submit' value='Edit User' form='edit_user_form' />
		   <?php
		   if (TIERED_DONOR) {
		   		echo '<input type="button" onclick="delete_confirm(\''.$steam_id.'\',\''.$tier.'\');" value="Delete '. $username.' "/>';
		   }else{
		   		echo '<input type="button" onclick="delete_confirm(\''.$steam_id.'\');" value="Delete '. $username.' "/>';
		   }   
		
echo $footer;
echo "</html>";
 ?>

