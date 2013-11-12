<?php
if (!defined('adminPage')) {
	exit("Direct access not premitted.");
}
if (!defined('NineteenEleven')) {
	define('NineteenEleven', TRUE);
}


	echo '
	<div id="searchBox">
		<form action="show_donations.php"  method="POST" id="searchForm">
			<input type="text" size="30" placeholder="'.$lang->admin[0]->searchmsg.'" id="searchInput" name="searchInput" /><input type="image" src="images/search-button.png" id="searchButton" form="searchForm" />
		</form>';


		if(isset($_POST['show_expired'])){
		echo "	
		<form action='show_donations.php' method='POST' id='clear_expired'>
			<input id='clearExpired' type='submit' value='".$lang->admin[0]->hide."' form='clear_expired' />
		</form>";		
	}else{
		echo "
		<form action='show_donations.php' method='POST' id='show_expired'>
			<input id='clearExpired' type='submit' value='".$lang->admin[0]->show."' form='show_expired' name='show_expired'/>
		</form>";
	}
	echo "
	</div>
			<!--<div class='content'>-->
			<br />
			<br />
			<br />
			<br />
	";


	$total=0;
	$totalC=0;
	if (isset($_POST['searchInput'])) {
		$search = $_REQUEST['searchInput'];

		$sql = "SELECT * FROM `donors` WHERE username LIKE '%" . $search . "%' OR steam_id LIKE '%" . $search . "%' OR email LIKE '%" . $search . "%';";

		print("<a href='show_donations.php' id='clearSearch'> ".$lang->admin[0]->clear." </a>");

	}elseif (isset($_POST['show_expired'])){

		$sql = "SELECT * FROM donors ORDER BY `expiration_date`;";

	}else{
		$sql = "SELECT * FROM donors  WHERE activated != '2' ORDER BY`expiration_date`;";
	}
	//query the database

	$result = $mysqliD->query($sql)or die($mysqliD->error . " " . $mysqliD->errno);
	unset($sql);
	//create the table
	echo "<table border='2'>";
	echo "<tr><th>".$lang->admin[0]->steamname."</th><th>".$lang->admin[0]->info."</th><th>".$lang->admin[0]->sud."</th><th>".$lang->admin[0]->email."</th><th>".$lang->admin[0]->rd."</th><th>".$lang->admin[0]->current."</th><th>".$lang->admin[0]->total."</th><th>".$lang->admin[0]->ed."</th>";
	if (TIERED_DONOR) {
		echo "<th>".$lang->admin[0]->tier."</th>";
	}

	echo "<th>".$lang->admin[0]->notes."</th></tr>";
	//loop through rows and print values to the table
	while ($db_field = $result->fetch_array(MYSQLI_ASSOC)) {

		if ($db_field['renewal_date'] =="0") {
			$renewal_date ="None";
		}else{
			$renewal_date = date('n/j/Y',$db_field['renewal_date']);
		}
	 
		if (PLAYER_TRACKER) {
	    $PTresult = $mysqliD->query("SELECT * FROM `player_tracker` WHERE steamid='". $db_field['steam_id'] . "';")or die($mysqliD->error . " " . $mysqliD->errno);

	        $tracker = $PTresult->fetch_array(MYSQLI_ASSOC);
	   
		}

		$totalC = ($totalC + $db_field['current_amount']);
		$total = ($total + $db_field['total_amount']);
		//change color of expiration date, based on status

		switch ($db_field['activated']) {
			case 1:
				$expiration_date = "<div style='color:green; border:none;'>".date('n/j/Y',$db_field['expiration_date'])."</div>";
				break;
			case 2:
				$expiration_date = "<div style='color:red; border:none;'>".date('n/j/Y',$db_field['expiration_date'])."</div>";
				break;
			default:
				$expiration_date = "<div style='color:yellow; border:none;'>".date('n/j/Y',$db_field['expiration_date'])."</div>";
				break;
		}

		echo "<tr>";
		
		echo "<td><a href='".$db_field['steam_link']."' target='_blank'>" . $db_field['username'] . "</a></td>";
		 if (PLAYER_TRACKER) {

		echo "<td class='click'><div class='steamid'> " . $db_field['steam_id'] . "</div>"
		." <div class='ptInfo' > <a href='http://www.geoiptool.com/en/?IP=".$tracker['playerip']."' target='blank'>".$tracker['playerip']."</a>" . "(".$tracker['geoipcountry'].")</div></td>";
		
		}else{
		echo "<td>" . $db_field['steam_id']. "</td> ";	
		}
		echo "<td>".  date('n/j/Y',$db_field['sign_up_date'])
		. "</td><td>". "<a href='mailto:".$db_field['email']."' target='_top'>".$db_field['email']."</a>"
		. "</td><td>".  $renewal_date
		. "</td><td>$". $db_field['current_amount']
		. "</td><td>$". $db_field['total_amount']
		. "</td><td>". $expiration_date;

	if (TIERED_DONOR) {
		if ($db_field['tier'] == 1) {
			echo "</td><td>".$group1['name'];
		}else{
			echo "</td><td>".$group2['name'];
		}
	}

		echo "</td><td>". $db_field['notes']
		. "</td><td><a href='?edit_user&user_id=".$db_field['user_id']."' id='editUserButton'>".$lang->admin[0]->edituser."</a>"
		. "</td>";
		echo "</tr>";

	}
	echo "<tr><td></td><td></td><td></td><td></td><td></td><td>{$totalC}</td><td>{$total}</td><td></td><td></td></tr>";
	echo "</table>";

?>