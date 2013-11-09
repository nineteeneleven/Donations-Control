<?php
if(file_exists("logs/action.log")){

$log = new log;

$error=array_reverse($log->getLog('action.log'));


echo "<table border='2'>";
echo "<tr><th>Date/Time</th><th>Action</th></tr>";
foreach ($error as $err) {
	$data = explode("|", $err);
	echo "<tr>";
	echo"<td>".$data[0]."</td>";
	echo"<td>".$data[1]."</td>";
	//echo"<td>".$data[2]."</td>";
	echo "</tr>";
}
echo "</table>";
unset($log);
}else{
	$_SESSION['message'] = "<h1 class='success'>No Actions Reported</h1>";
	header("location: show_donations.php");
}
?>