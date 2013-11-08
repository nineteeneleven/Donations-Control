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
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	if($mysqliD->query($ccc_create)){
		echo "CCC table created.\r\n";
	}else{
		echo "Failed to created CCC table.\r\n";
	}
}



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
	if($row['renewal_date']=='N/A' || $row['renewal_date']=='n/a' || empty($row['renewal_date'])){
		$rdate = 0;
	}else{
		$rDate=strtotime($row['renewal_date']);
	}
		$eDate=strtotime($row['expiration_date']);

	if($mysqliD->query("UPDATE `donors` SET `sign_up_date`= '{$sDate}', `renewal_date` = '{$rDate}', `expiration_date` = '{$eDate}', `tier` ='1' WHERE user_id = {$user_id};"))
	{
		echo 'Updated: ' . $row['username'] . '<br />';
	}else{
		echo '<h3>FAILED TO UPDATE: ' . $row['username'] . '</h3><br />';
	}
}
$mysqliD->close();

?>