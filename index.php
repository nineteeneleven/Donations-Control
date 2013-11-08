<!--Written by NineteenEleven for Kablowsion Inc.-->
<?php
define('NineteenEleven', TRUE);
include_once 'includes/config.php';
include_once 'includes/class_lib.php';
  $found_user = false;
  $timestamp = date('U');
  $cacheExpire = cache_time * 86400;
if (PLAYER_TRACKER) {

    $mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
    //$SteamQuery = new SteamQuery;
    $ConvertID = new SteamIDConvert;
    $userip = $_SERVER['REMOTE_ADDR'];

    $result = $mysqliD->query("SELECT * FROM `player_tracker` WHERE playerip='". $userip . "';")or die("Failed to connect to donations database");

    function getXML($steam_link_xml, $steamid,$timestamp){
      $mysqliC = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
      global $avatarmedium, $personaname;

      $xml = @simplexml_load_file($steam_link_xml);
        if(!empty($xml)) {
            $avatar = $xml->players->player->avatar;
            $avatarmedium = $xml->players->player->avatarmedium;
            $avatarfull = $xml->players->player->avatarfull;
            $personaname =$xml->players->player->personaname;
            $steamid64 = $xml->players->player->steamid;
            $steam_link = $xml->players->player->profileurl;
            //update cache database
            $mysqliC->query("INSERT INTO `cache` (steamid,
                                                    avatar,
                                                    avatarmedium,
                                                    avatarfull,
                                                    personaname,
                                                    timestamp,
                                                    steamid64,
                                                    steam_link) 
                                            VALUES ('{$steamid}',
                                              '{$avatar}',
                                              '{$avatarmedium}',
                                              '{$avatarfull}',
                                              '{$personaname}',
                                              '{$timestamp}',
                                              '{$steamid64}',
                                              '{$steam_link}' 
                                              );")or die("Failed to update cache");


        }

      $mysqliC->close();
      }

      if($result->num_rows > 0){

          $row = $result->fetch_array(MYSQLI_ASSOC);

              $playername = $row['playername'];
              $steamid = $row['steamid'];

              $found_user = true;

              $steamid64 = $ConvertID->IDto64($steamid);
              $steam_link_xml = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=xml&steamids=" . $steamid64;

            $cacheReturn = $mysqliD->query("SELECT * FROM `cache` WHERE steamid ='" . $steamid ."';");
              //$cacheReturn = mysql_query($chkCacheSQL);
                if($cacheReturn->num_rows > 0) {

                  $cacheResult = $cacheReturn->fetch_array(MYSQLI_ASSOC);

                  if($cacheResult['timestamp'] > $cacheExpire){

                    //cache still valid

                    $avatarmedium = $cacheResult['avatarmedium'];

                  }else{
                    //cache expired, updating

                    $mysqliD->query("DELETE FROM `cache` WHERE steamid = '".$cacheResult['steamid'] ."';");

                    getXML($steam_link_xml, $steamid,$timestamp);
                  }
                  
                }else{
                  //nothing in cache, getting stuff
                  getXML($steam_link_xml,$steamid,$timestamp);
                }
            
        }

$mysqliD->close();
    print("<!DOCTYPE html>");
    print("<html>");
    print("<head>");

    //Javascript to allow gifting
    print("<script type=\"text/javascript\">");
    print("function gift() {document.getElementById('steamid-box').style.display = 'block';
      document.getElementById('id-field').value = '';
      document.getElementById('id-field').placeholder = 'Enter SteamID here';
      document.getElementById('userid').style.display = 'none';
      document.getElementById('infobox').style.display = 'block';
    }");

    print("</script>");
    print("</head>");
    print("<body id='original'>");
    print("<style type=\"text/css\">#infobox{font-size: 12px;}</style>");
    print("<center>");
    print("<input type=\"image\" src=\"images/btn_donateCC_LG.gif\" form=\"donate_form\" />");
    print("<form action=\"donate.php\" target=\"blank\" id=\"donate_form\">");
    print("<p>Amount: $<input type=\"text\" name=\"amount\" size=\"5\" class=\"inputbox\" value=\"5\" required=\"true\"></p>");
    if(TIERED_DONOR){
          print("<input type=\"radio\" name=\"tier\" value=\"1\" checked =\"1\" id=\"tier1\">".$group1['name']." <input type=\"radio\" name=\"tier\" value=\"2\" id=\"tier2\">".$group2['name']."<br />");
    }
      if($found_user){
        print("<div id='steamid-box' style=\"display:none;\" ><label for='steamid_user'>Steam ID:<br /></label>");
        print("<input type=\"text\" name=\"steamid_user\" required=\"true\" id=\"id-field\"  value=\"{$steamid}\" ></div>");
        print("<div id=\"userid\">Welcome back {$playername} <br />");
        print("<img src='{$avatarmedium}' style=\"border:1px solid black;border-radius:5px;\" /><br />");
        print("<a href='#' onclick=\"gift();\"> Donate for someone else </a></div>");
        print("<div id='infobox' style=\"display:none;\">");
        print("<p>Acceptable formats:<br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/{name} or {name}<br /></p>");
        print("</div>");
      }else{
        print("<label for=\"paypaloption1\">Steam ID:<br /></label><input type=\"text\" id=\"paypaloption1\" name=\"steamid_user\" required=\"true\" id=\"id-box\" placeholder=\"Please enter your SteamID\" required=\"true\" size=\"30\"></p>");
        print("<div id='infobox'>");
        print("<p>Acceptable formats:<br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/{name} or {name}<br /></p>");
        print("</div>");
      }
    print("</form>");
    print("</center>");
    print("</body>");
    print("</html>");
}else{
  print("<!DOCTYPE html>");
  print("<html>");
  print("<body>");
  print("<style type=\"text/css\">#infobox{font-size: 12px;}</style>");
  print("<center>");
  print("<input type=\"image\" src=\"images/btn_donateCC_LG.gif\" form=\"donate_form\" />");
  print("<form action=\"donate.php\" target=\"blank\" id=\"donate_form\">");
  print("<p>Amount: $<input type=\"text\" id=\"paypalamount\" name=\"amount\" size=\"5\" class=\"inputbox\" value=\"5\" required=\"true\"></p>");
  if(TIERED_DONOR){
        print("<input type=\"radio\" name=\"tier\" value=\"1\" checked =\"1\" id=\"tier1\">".$group1['name']." <input type=\"radio\" name=\"tier\" value=\"2\" id=\"tier2\">".$group2['name']."<br />");
  }
  print("<label for=\"paypaloption1\">Steam ID:<br /></label><input type=\"text\" id=\"paypaloption1\" name=\"steamid_user\" required=\"true\" id=\"id-box\" placeholder=\"Please enter your SteamID\" required=\"true\" size=\"30\"></p>");
  print("<div id='infobox'>");
  print("<p>");
  print("Acceptable formats:<br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/{name} or {name}<br /></p>");
  print("</div>");
  print("</form>");
  print("</center>");
  print("</body>");
  print("</html>");
}

?>

