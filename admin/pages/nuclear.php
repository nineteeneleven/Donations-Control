<div class='content'>

        <form action='show_donations.php?server_query' method='post' name='CUSTOMCOMMAND'>
            <font class='info'><?php echo $lang->admin[0]->sq; ?></font><br>  
            <input type='text' size='40' name='COMMAND' class='searchBox' /><input type='submit' value='Submit'>
        </form>          


<?php
if (isset($_POST['COMMAND'])) {
	$sb = new SourceBans;
	$query = $_POST['COMMAND'];
	
	if(!$sb->queryServersResponse($query)){
		echo "<h1 class='error>".$lang->admin[0]->sq4."</h1>";
	}
	if (STATS) {
		@$log->stats("SQ");
	}
	$log->logAction(sprintf($lang->sysmsg[0]->nuclear, $_SESSION['username'], $query));

	unset($sb);
}else{
	echo "<h3><u>".$lang->admin[0]->sq1."</u></h3>";
	echo "<p>".$lang->admin[0]->sq2."<p>";
	echo "<p>".$lang->admin[0]->sq3."</p>";
}
?>

</div>
