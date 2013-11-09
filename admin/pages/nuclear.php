<div class='content'>

        <form action='show_donations.php?server_query' method='post' name='CUSTOMCOMMAND'>
            <font class='info'>Server Query:</font><br>  
            <input type='text' size='40' name='COMMAND' class='searchBox' /><input type='submit' value='Submit'>
        </form>          


<?php
if (isset($_POST['COMMAND'])) {
	$sb = new SourceBans;
	$query = $_POST['COMMAND'];
	
	if(!$sb->queryServersResponse($query)){
		echo "<h1 class='error>Something went wrong querying the servers</h1>";
	}
	if (STATS) {
		@$log->stats("SQ");
	}
	$log->logAction($_SESSION['username']." sent command '".$query."' to all servers");
	unset($sb);
}else{
	echo "<h3><u>About this tool</u></h3>";
	echo "<p>This tool will send the command you specify to all of the game servers you have connected to SourceBans.<p>";
	echo "<p>There are no limitations to the command you can send. So be careful.</p>";
}
?>

</div>
