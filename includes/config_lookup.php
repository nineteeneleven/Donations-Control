<?php 
define('NineteenEleven', TRUE); 
require_once 'config.php'; 

if (isset($_POST['steamid_user'])) { 
  $steamid = $_POST['steamid_user']; 
  $mysqli = new mysqli(SB_HOST,SB_USER,SB_PASS,SOURCEBANS_DB); 
  

  $result = $mysqli->query("SELECT * FROM sb_admins WHERE `authid` ='".$steamid."';")or die($mysqli->error); 

if($result){ 
  $row = $result->fetch_array(MYSQLI_ASSOC); 
  $aid = $row['aid']; 
  $srv_group2 = $row['srv_group'];
} 

unset($result); 
 $result = $mysqli->query("SELECT * FROM sb_admins_servers_groups WHERE `admin_id` ='".$aid."';")or die($mysqli->error); 

if ($result->num_rows >= 1) { 
    $row = $result->fetch_array(MYSQLI_ASSOC); 
      $group_id2 = $row['group_id']; 
      $srv_group_id2 = $row['srv_group_id']; 
      $server_id2 = $row['server_id']; 
  } 
  echo "Name: $srv_group2 <br />";
  echo "group_id: {$group_id2} <br />"; 
  echo "srv_group_id: {$srv_group_id2} <br />"; 
  echo "server_id: {$server_id2} <br />"; 
}else{ 

  print("<!DOCTYPE html>"); 
  print("<html>"); 
  print("<body>"); 
  print("<center>"); 
  print("<h1> delete this file when you are done</h1>");
  print("<input type=\"submit\" form=\"donate_form\" />"); 
  print("<form action=\"config_lookup.php\" method=\"POST\" id=\"donate_form\">"); 
  print("<label for=\"paypaloption1\">Steam ID:<br /></label><input type=\"text\" id=\"paypaloption1\" name=\"steamid_user\" required=\"true\" id=\"id-box\" placeholder=\"SteamID of donor\" required=\"true\" size=\"30\"></p>"); 
  print("<div id='infobox'>"); 
  print("</div>"); 
  print("</form>"); 
  print("</center>"); 
  print("</body>"); 
  print("</html>"); 
} 
?>