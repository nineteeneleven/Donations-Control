<?php
define('NineteenEleven', TRUE);
require_once'includes/config.php';
require_once 'includes/class_lib.php';
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
$tools = new tools;
//$SteamQuery = new SteamQuery;
$ConvertID = new SteamIDConvert;
$steamid_user =$tools->cleanInput($_REQUEST['steamid_user']);

if(TIERED_DONOR){
     $tier = $tools->cleanInput($_REQUEST['tier']);
}

$useCache = false;

$cacheQuery = $mysqliD->query("SELECT * FROM `cache` WHERE steamid ='" . $steamid_user ."';")or die('Failed to query database');

if($cacheQuery->num_rows > 0) {

    $userInfo = $cacheQuery->fetch_array(MYSQLI_ASSOC);
    $useCache = true;
    $username = $userInfo['personaname'];
    $avatarfull = $userInfo['avatarfull'];
    $steamID64 =$userInfo['steamid64'];
    $steam_link = $userInfo['steam_link'];
    $steam_id=$userInfo['steamid'];

  }else{
    //The only way a user can get here, is with a direct link.
    $userInfo = $ConvertID->SteamIDCheck($steamid_user);
    $userInfo['XMLlink'] = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=xml&steamids=". $userInfo['steamID64'];
    $steam_link = $userInfo['steam_link'];
}


function dun_fucked_up(){
    if ($tools->checkOnline('steamcommunity.com')) {
        die("<h3>The Steam Community is currently down,".
        "<br />We were unable to get your ID based on what you entered.".
        "<br />Please enter your STEAM:0:0:00000 ID or wait untill Steam comes back online" .
        "<br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>");
    }else{
    die("<h3>Sorry we were unable to get your Steam ID.".
        "<br />You probably entered an incorrect Steam ID".
        "<br />Please check and make sure you entered a valid Steam ID and try again." .
        "<br /><a href='javascript:history.go(-1);'>Click here to go back</a></h3>");
    }
}
///////////////////////
$amount = $tools->cleanInput($_REQUEST['amount']);
if (strpos($amount, "$") === 0) {
  $amount = substr($amount, 1);
}

if ($amount < "5") {
    $amountSmall = true;
}else{
    $amountSmall = false;
}

$amount = round($amount);

$sign_up_date = date('U');

if (TIERED_DONOR) {
    if ($tier == "1") {
        $days_purchased = round(($amount * $group1['multiplier']));
    }else{
        $days_purchased = round(($amount * $group2['multiplier']));
    }
}else{
    $days_purchased = round(($amount * $group1['multiplier']));
}

$n= "+".$days_purchased . " days";
$expire = strtotime($n,$sign_up_date);
unset($n);

if(!$useCache){
    // Get User Profile Data
    $xml = @simplexml_load_file($userInfo['XMLlink'])
    or dun_fucked_up();
    if(!empty($xml)) {
        $username = $xml->players->player->personaname;
        $avatarfull = $xml->players->player->avatarfull;
    }
}
$username = preg_replace("/[^[:print:]]/", ' ', $username);

//check if current donor

$result = $mysqliD->query("SELECT expiration_date FROM donors WHERE steam_id = ".$userInfo['steamid'].";");
if($result){
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $expiration_date = $row['expiration_date'];
}
 if (isset($expiration_date)) {
    $return_donor = true;
} else{
    $return_donor = false;
}
$mysqliD->close();
echo'
<html>
<head>
    <script type="text/javascript" src="scripts/jscolor/jscolor.js"></script>
    <style type="text/css">
        body{background-color: gray;}
        .content{ width: 50%;
            margin-left: auto;
            margin-right: auto;
            border:3px solid black;
            border-radius: 5px;
            padding: 20px;
            background-color: white;}
        #welcome_back{color: green;}
        input[type=submit]{padding: 10px; 
            background-color: rgba(15, 17, 14, 0.8);
            color: white;
            border: 1px solid black;
            border-radius: 5px;}
        input[type=submit]:hover{font-size:150%;
            background-color: rgba(215, 44, 44, 0.8); 
            color: black;
        }
        #sorry{color:red;
            padding: 10px;
            background-color: rgba(15, 17, 14, 0.4);
            border:2px solid black;
            border-radius: 10px;}
    </style>
</head>
    <body>
    <title>DONATE!</title>
    <div class="content">
        <center>';
            if($return_donor===true){
                echo "<p id='welcome_back'>Welcome back " . $username . " your current donor perks expire on ". date('l F j Y',$expiration_date) ."</p>";} 
                echo "<br />";
                if ($amountSmall) {
                    exit("<h3 id='sorry'>Sorry, the minimum donation that can be processed is $5. Please adjust accordingly</h3>");
                }
   if (PP_SANDBOX) {
            print('<form id="donate" name="_xclick" action="https://sandbox.paypal.com/cgi-bin/webscr" method="post" >');
            print('<input type="hidden" name="business" value="' . PP_SANDBOX_EMAIL . '">');
        }else{
            print('<form id="donate" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post" >');
            print('<input type="hidden" name="business" value="' . PP_EMAIL . '">');
        }            
	            echo "<h1> Please make sure this is you!</h1><br/ >";
	            echo "<img src='{$avatarfull}' /><br /> <h1>  <a href='{$steam_link}' target='_blank'>{$username}</a><h1><hr /><h3>";
                echo "<p>You wish to donate $".$amount.". For your generosity, you will receive ".$days_purchased." days of donor perks";
                if(TIERED_DONOR){
                    if ($tier=="1") {
                        echo " on the " . $group1['name']." level. ";
                    }else{
                        echo " on the " . $group2['name']." level. ";
                    }
                }else{
                    echo ". ";
                }
                echo "Beginning on ". date('l F j Y',$sign_up_date). " and ending on ". date('l F j Y',$expire).".</p>";
                //https://www.paypal.com/cgi-bin/webscr?cmd=_pdn_xclick_options_help_outside#
                if(TIERED_DONOR && $tier =="2" && CCC){
                    echo "<p> Please select the colors and tag you would like to use for your in-game chat.<p>";
                    echo "<input type=\"hidden\" name=\"os0\" value=\"nameColor\"><input type=\"hidden\" name=\"os1\" value=\"chatColor\">";
                    echo "<p><input class='color' name='on0' value='#33CC99' id='colorInput'>Name Color <input class='color' name='on1' value='#990000' id='colorInput'>Chat Color</p>";
                }
            	echo "<p> If this is you, and you agree please click below, to proceed with your donation, you will be redirected to PayPal to process your contribution.</p>";
                echo "</h3>";

     
            print('<input type="hidden" name="cmd" value="_xclick">');
            print('<input type="hidden" name="no_note" value="1">');
            print('<input type="hidden" name="amount" value="' . $amount . '">');
            print('<input type="hidden" name="item_name" value="' . PP_DESC . '">');
            print('<input type="hidden" name="no_shipping" value="1">');
            print('<input type="hidden" name="rm" value="2">');
            print('<input type="hidden" name="return" value="' . PP_SUCCESS. '">');
            print('<input type="hidden" name="notify_url" value="' . PP_IPN . '">');
            print('<input type="hidden" name="cancel_return" value="' . PP_FAIL . '">');
            print('<input type="hidden" name="currency_code" value="' . PP_CURRENCY . '">');
            if(TIERED_DONOR){
                print('<input type="hidden" name="custom" value="' . $userInfo['steamid'] .'?'.$amount.'?'.$sign_up_date.'?'.$expire.'?'.$days_purchased.'?'.$tier.'">');
            }else{
                print('<input type="hidden" name="custom" value="' . $userInfo['steamid'] .'?'.$amount.'?'.$sign_up_date.'?'.$expire.'?'.$days_purchased.'">');
            }
            print('<br />');
            print('<br />');
            print('<input type="submit" value="DONATE!" form="donate">');
            print('</form>');
?>
        </center>
    </div>
</body>
<?php echo $footer ?>
</html>