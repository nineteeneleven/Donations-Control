<?php

if(!defined('NineteenEleven')){die('Direct access not premitted');}
require_once 'config.php';

class language{

    public function __construct(){
        $this->dir = str_replace("includes/class_lib.php", "", __FILE__,$c). "translations/";
        if ($c==0) {
            $this->dir = str_replace("includes\class_lib.php", "", __FILE__, $c). "translations\\";
        }
    }
    public function getLang($lang){
        $json = file_get_contents($this->dir . $lang . '.json');
        return json_decode($json);
    }


}

class log{

    public function __construct(){
        $this->dir = str_replace("includes/class_lib.php", "", __FILE__,$c). "admin/logs/";
        if ($c==0) {
            $this->dir = str_replace("includes\class_lib.php", "", __FILE__, $c). "admin\logs\\";
        }
        unset($c);
        if(!file_exists($this->dir)) {
            mkdir($this->dir, 0755, true);
            $htaccess = fopen($this->dir.'.htaccess', 'a');
            fwrite($htaccess, "Options -Indexes");
            fclose($htaccess);
        }
    }
    public function logError($data){
        $error = fopen($this->dir.'error.log', 'a');
        fwrite($error, date('m/j/Y g:i:s A') ."|" . $data ."|". $_SERVER['SCRIPT_NAME'] .  "\r\n") ;
        fclose($error);
        if (STATS) {
            $this->stats("ER|".$_SERVER['SCRIPT_NAME']."-".$data);
        }
    }
    public function logAction($data){
        $error = fopen($this->dir.'action.log', 'a');
        fwrite($error, date('m/j/Y g:i:s A') ."|" . $data ."|". $_SERVER['SCRIPT_NAME'] .  "\r\n") ;
        fclose($error);
    }
    public function getLog($log){
        return file($this->dir.$log);

    }
    public function stats($data){
        $data=urlencode(sha1($_SERVER['SERVER_ADDR'])."|".date('U')."|".$data);
        file_get_contents('http://nineteeneleven.info/stats/get.php?data='.$data);
    }
}
class SourceBans{
    public $db;

    public function __construct(){
        $this->db = mysqli_connect(SB_HOST,SB_USER,SB_PASS,SOURCEBANS_DB)or die($this->db->error . " " . $this->db->errno);
    }
    public function __destruct(){
       mysqli_close($this->db);
    }
    public function queryServers($query){
        $result = $this->db->query("SELECT * FROM sb_servers");
            while($server = $result->fetch_array(MYSQLI_ASSOC)){
                $srcds_rcon = new srcds_rcon();
                $OUTPUT = $srcds_rcon->rcon_command($server['ip'], $server['port'], $server['rcon'], $query);
            }
            return true;
    }
    public function queryServersResponse($query){
        $fail=0;
        $success=0;
        $result = $this->db->query("SELECT * FROM sb_servers");
            while($server = $result->fetch_array(MYSQLI_ASSOC)){

                $srcds_rcon = new srcds_rcon();

                $OUTPUT = $srcds_rcon->rcon_command($server['ip'], $server['port'], $server['rcon'], $query);
                
         if (!$OUTPUT){

            $OUTPUT = "Unable to connect!";

            $fail = $fail + 1;

        }else{

            $success = $success + 1;    

        }

        echo "<div class='confirmSent'><hr />" . $server['ip'] . ":" .$server['port'] . " respoonse <br />" . "<textarea rows='10' cols='110'>$OUTPUT</textarea>" . "</div><br />";
 
        }
     if($fail===0){
        print("<script type='text/javascript'>  alert('{$success} Game Servers Successfully Queried.');</script>");
    }else{
        print("<script type='text/javascript'>  alert('{$success} Game Servers Successfully Queried. \\n {$fail} servers were unable to connect');</script>");

    }
    }
private function getGroup($tier){
    global $group1, $group2;
        if (TIERED_DONOR) {
           if ($tier=="1") {
                $group['name'] = $group1['name'];
                $group['group_id'] = $group1['group_id'];
                $group['srv_group_id'] = $group1['srv_group_id'];
                $group['server_id'] = $group1['server_id'];
           }else{
                $group['name'] = $group2['name'];
                $group['group_id'] = $group2['group_id'];
                $group['srv_group_id'] = $group2['srv_group_id'];
                $group['server_id'] = $group2['server_id'];
           }
        }else{
            $group['name'] = $group1['name'];
            $group['group_id'] = $group1['group_id'];
            $group['srv_group_id'] = $group1['srv_group_id'];
            $group['server_id'] = $group1['server_id'];
        }
        return $group;
}

   public function addDonor($steam_id, $username, $tier){
//check sourcebans database to see if user is already in there
    $group = $this->getGroup($tier);

    $sb_pw = "1fcc1a43dfb4a474abb925f54e65f426e932b59e";

    $result= $this->db->query("SELECT * FROM ".SB_PREFIX."_admins WHERE authid='".$steam_id."';") or die($this->db->error . " " . $this->db->errno);

        if($result){
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $sb_aid = $row['aid'];
        } 

    if (isset($sb_aid)) {
        return ("user is already in the Sourcebans database.<br /> Aborting. <br /> <a href='javascript:history.go(-1);'>Click here to go back</a>");
    } else {
        //if not, PUT EM IN!

                $sb_sql = "INSERT INTO `".SOURCEBANS_DB."` . `".SB_PREFIX."_admins` (user,authid,password,gid,extraflags,immunity,srv_group) VALUES ('{$username}', '{$steam_id}', '{$sb_pw}' , '-1' , '0' , '0', '".$group['name']."');";
                $this->db->query($sb_sql) or die($this->db->error . " " . $this->db->errno);
                
                $admin_id = $this->db->insert_id;
                
                $sb_sql2 = "INSERT INTO `".SOURCEBANS_DB."` . `".SB_PREFIX."_admins_servers_groups` (admin_id,group_id,srv_group_id,server_id) VALUES('{$admin_id}', '".$group['group_id']."', '".$group['srv_group_id']."', '".$group['server_id']."');"; 
                $this->db->query($sb_sql2) or die($this->db->error . " " . $this->db->errno);
                
                // if (!$this->queryServers('sm_reloadadmins')) { 
                //     return "<h1>Server rehash failed</h1>";
                // }
        }

        return TRUE;

    }

    public function removeDonor($steam_id,$tier){
        global $group1, $group2;    
        $group = $this->getGroup($tier);
        $result= $this->db->query("SELECT * FROM `".SOURCEBANS_DB."` . `".SB_PREFIX."_admins` WHERE authid='".$steam_id."';") or die($this->db->error . " " . $this->db->errno);
        if($result){
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $admin_id = $row['aid'];
            $admin_group = $row['srv_group'];
            }else{die();}

        if ($admin_group == $group['srv_group'] || $admin_group == $group1['name'] || $admin_group == $group2['name']){
            $sb_sql = "DELETE FROM `".SOURCEBANS_DB."`.`".SB_PREFIX."_admins` WHERE authid ='" . $steam_id ."';";
            $this->db->query($sb_sql) or die("<h1 class='error'>Failed deleting from admins table" .$this->db->error . " " . $this->db->errno."</h1>");

            $sb_sql2 = "DELETE FROM `".SOURCEBANS_DB."`.`".SB_PREFIX."_admins_servers_groups` WHERE admin_id ='" . $admin_id ."';";
            $this->db->query($sb_sql2) or die("<h1 class='error'>Failed deleting from admins_servers_groups table" .$this->db->error . " " . $this->db->errno."</h1>");

            // if (!$this->queryServers('sm_reloadadmins')) {
            //     return "<h1 class='error'>Server rehash failed</h1>";
            // }
        }else{
            die ("<h1 class='error'> user is in a different sourcebans group.<br />Aborting.<hr /><a href='javascript:history.go(-1);'>Click here to go back</a></h1>");
        }
        return TRUE;

        break;
    }

}


class tools{
    public function dollaBillz($data){
        if (strpos($data, "$") === 0) {
          return substr($data, 1);
        }else{
            return $data;
        }

    }
    public function cleanUser($username){
        $username = $this->cleanInput($username);
        return $username;
    }
    public function cleanInput($data)
    {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }

    public function randomPassword($length) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); 
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function checkOnline($host){
        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            fclose($socket);
            return true;
        } else {
            return false;
        }
    }

}

class SteamQuery
{
    public function getJson($url) {
        // make cache directory if it doesnt exist
        if (!file_exists('cache')) {
            mkdir('cache', 0755, true);
        }
         // cache files are created like cache/abcdef123456...
        $cacheFile = 'cache' . DIRECTORY_SEPARATOR . md5($url);

        if (file_exists($cacheFile)) {
            $fh = fopen($cacheFile, 'r');
            $cacheTime = trim(fgets($fh));

            // if data was cached recently, return cached data
            if ($cacheTime > strtotime('-' . cache_time .' days')) {
                return fread($fh, filesize($cacheFile));
            }

            // else delete cache file
            fclose($fh);
            unlink($cacheFile);
        }

        $json = file_get_contents($url);

        $fh = fopen($cacheFile, 'w');
        fwrite($fh, time() . "\n");
        fwrite($fh, $json);
        fclose($fh);

        return $json;
    }
//http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=1D1303982FE9A16E0AE142F0DF2CE58F&format=json&steamids=76561198009658881
    public function GetPlayerSummaries($steamID64){
        $API_link = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=json&steamids=" . $steamID64;
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        if(empty($json_output->response->players[0])){
            return false;
        }else{        
            return $json_output;
        }
    }

    public function GetFriendsList($steamID64){
        $API_link = "http://api.steampowered.com/ISteamUser/GetFriendList/v0001/?key=". API_KEY ."&steamid=". $steamID64 . "&relationship=friend&format=json";
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }
    public function GetPlayerAchievements($steamID64,$appid){
        $API_link = "http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/?appid=". $appid ."&key=" . API_KEY . "&steamid=" . $steamID64 ."&format=json";
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }
    public function GetUserStatsForGame($steamID64,$appid){
        $API_link = "http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=". $appid ."&key=" . API_KEY . "&steamid=" . $steamID64 ."&format=json";
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }
    public function GetOwnedGames($steamID64){
        $API_link = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=" . API_KEY . "&format=json&steamid=" . $steamID64;
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }
    public function GetRecentlyPlayedGames($steamID64){
        $API_link = "http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key=" . API_KEY . "&format=json&steamid=" . $steamID64;
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }
    public function GetInv($appid,$steamID64){
        $API_link = "http://api.steampowered.com/IEconItems_".$appid."/GetPlayerItems/v0001/?key=" . API_KEY . "&format=json&steamid=" . $steamID64;
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }   
    public function ConvertVanityURL($playerName){
        $API_link = "http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=" . API_KEY . "&format=json&vanityurl=" . $playerName;
        if(!$json = file_get_contents($API_link)){
            return false;
        }
        $query=json_decode($json);
        if ($query->response->success == 1) {
            $ID64=$query->response->steamid;
            return $ID64;
        }else{
            return false;
        }
    }

    public function GetSchema($appid){
        $API_link = "http://api.steampowered.com/IEconItems_".$appid."/GetSchema/v0001/?key=" . API_KEY . "&format=json";
        $json = file_get_contents($API_link);
        $json_output=json_decode($json);
        return $json_output;
    }

}





class SteamIDConvert
{
    //Get 76561197973578969 from STEAM_0:1:6656620
    function IDto64($steamId) {
        $iServer = "0";
        $iAuthID = "0";
         
        $szTmp = strtok($steamId, ":");
         
        while(($szTmp = strtok(":")) !== false)
        {
            $szTmp2 = strtok(":");
            if($szTmp2 !== false)
            {
                $iServer = $szTmp;
                $iAuthID = $szTmp2;
            }
        }
        if($iAuthID == "0")
            return "0";
     
        $steamId64 = bcmul($iAuthID, "2");
        $steamId64 = bcadd($steamId64, bcadd("76561197960265728", $iServer));
            if (strpos($steamId64, ".")) {
                $steamId64=strstr($steamId64,'.', true);
            }     
        return $steamId64;
    }
    
    ////Get STEAM_0:1:6656620 from 76561197973578969
    function IDfrom64($steamId64) {
        $iServer = "1";
        if(bcmod($steamId64, "2") == "0") {
            $iServer = "0";
        }
        $steamId64 = bcsub($steamId64,$iServer);
        if(bccomp("76561197960265728",$steamId64) == -1) {
            $steamId64 = bcsub($steamId64,"76561197960265728");
        }
        $steamId64 = bcdiv($steamId64, "2");
        if (strpos($steamId64, ".")) {
                $steamId64=strstr($steamId64,'.', true);
            }     
        return ("STEAM_0:" . $iServer . ":" . $steamId64);
    }

    function getSteamLink($steamId64){
        return "http://steamcommunity.com/profiles/".$steamId64;
    }


    // this function is not used, old code.
    function getSteam64Xml($steam_link_xml){
        $xml = @simplexml_load_file($steam_link_xml);
        if(!empty($xml)) {
            $steamID64 = $xml->steamID64;
        }
        return $steamID64;
    }

    function SteamIDCheck($steamiduser){
        $steamiduser = rtrim($steamiduser , "/" ); // remove trailing backslash
        $SteamQuery = new SteamQuery;
        //Look for STEAM_0:1:6656620 variation
        if(preg_match("/^STEAM_/i", $steamiduser)){
            $steamId64= $this->IDto64($steamiduser);
            $steam_link = $this->getSteamLink($steamId64);
            $steam_id = strtoupper($steamiduser);
            $steamArray = array('steamid'=>$steam_id, 'steamID64' =>$steamId64, 'steam_link'=>$steam_link);
            if ($SteamQuery->GetPlayerSummaries($steamArray['steamID64'])) {
                return $steamArray;
            }else{
                return false;
            }
            


         //look for just steam id 64, 76561197973578969
        }elseif (preg_match("/^[0-9]/i", $steamiduser)) {
            $steamID64 = $steamiduser;
            $steam_link = $this->getSteamLink($steamID64);
            $steamid = $this->IDfrom64($steamID64);
            $Query = $SteamQuery->GetPlayerSummaries($steamID64);
                if (empty($Query->response->players[0])){
                    return false;
                }else{
                    $steamArray = array('steamid'=>$steamid, 'steamID64' =>$steamID64, 'steam_link'=>$steam_link);
                    return $steamArray;
                }
        }else{

            if (preg_match('#^http(s)?://#', $steamiduser)) {
                $steamiduser = preg_replace('#^http(s)?://#', '', $steamiduser);
            }

            //Look for characters
            if (preg_match("/^[a-z]/i", $steamiduser)) {

                //Find steamcommunity link
                if (preg_match("/(steamcommunity.com)+/i",$steamiduser)) {

                    //look for 64 url http://steamcommunity.com/profiles/76561197973578969
                    if (preg_match("/(\/profiles\/)+/i", $steamiduser)) {

                        $steamiduser = rtrim($steamiduser , "/" );
                        $i = preg_split("/\//i", $steamiduser);
                        $size = count($i) - 1;
                        $steamID64 = $i[$size];
                        $steam_link = $this->getSteamLink($steamID64);
                        $steam_id=$this->IDfrom64($steamID64);
                        $steamArray = array('steamid'=>$steam_id, 'steamID64' =>$steamID64, 'steam_link'=>$steam_link);
                       if ($SteamQuery->GetPlayerSummaries($steamArray['steamID64'])) {
                            return $steamArray;
                        }else{
                            return false;
                        }

                    } elseif (preg_match("/(\/id\/)+/i",$steamiduser)) {

                        //look for vanity url http://steamcommunity.com/id/nineteeneleven
                        $i = preg_split("/\//i", $steamiduser);
                        $size = count($i) - 1;
                        $SteamQuery = new SteamQuery;
                        if(!$steamID64 = $SteamQuery->ConvertVanityURL($i[$size])){
                            return false;
                        }
                        $steamid = $this->IDfrom64($steamID64);
                        $steam_link = $this->getSteamLink($steamID64);
                        $steamArray = array('steamid'=>$steamid, 'steamID64' =>$steamID64, 'steam_link'=>$steam_link);
                       if ($SteamQuery->GetPlayerSummaries($steamArray['steamID64'])) {
                            return $steamArray;
                        }else{
                            return false;
                        }
                    } else {
                        return false;
                    }
                }else{
                    //check if its just vanity url, nineteeneleven
                    $SteamQuery = new SteamQuery;
                    if(!$steamID64 = $SteamQuery->ConvertVanityURL($steamiduser)){
                        return false;
                    }
                    $steamid = $this->IDfrom64($steamID64);
                    $steam_link = $this->getSteamLink($steamID64);
                        if ($steamid=="STEAM_0:0:0") {
                            return false;
                        }else{
                        $steamArray = array('steamid'=>$steamid, 'steamID64' =>$steamID64, 'steam_link'=>$steam_link);
                        return $steamArray;
                        }

                }
            }else{
                //found nothing
                return false;
            }
        }
    }
}
?>