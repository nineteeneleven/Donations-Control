<?php
define('NineteenEleven', TRUE);
require_once'includes/config.php';
require_once 'includes/class_lib.php';
$language = new language;
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
$tools = new tools;
$ConvertID = new SteamIDConvert;
$steamid_user =$tools->cleanInput($_REQUEST['steamid_user']);

if (isset($_POST['langSelect'])) {
    $lang = $language->getLang($_POST['langSelect']);
}else{
    $lang = $language->getLang(DEFAULT_LANGUAGE);
}


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
    //user not in cache
    $userInfo = $ConvertID->SteamIDCheck($steamid_user) or cantFindUser();
    $userInfo['XMLlink'] = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=xml&steamids=". $userInfo['steamID64'];
    $steam_link = $userInfo['steam_link'];
}


function cantFindUser(){
    global $tools, $lang;
    if (!$tools->checkOnline('steamcommunity.com')) {
        die("<h3>". $lang->steamdown[0]->msg1.
        "<br />".$lang->steamdown[0]->msg2.
        "<br />".$lang->steamdown[0]->msg3.
        "<br /><a href='javascript:history.go(-1);'>".$lang->misc[0]->msg1."</a></h3>");
    }else{
    die("<h3>".$lang->steamdown[0]->msg4.
        "<br />". $lang->steamdown[0]->msg5.
        "<br />".$lang->steamdown[0]->msg6.
        "<br /><a href='javascript:history.go(-1);'>".$lang->misc[0]->msg1."</a></h3>");
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
$username = $tools->cleanUser($username);
//check if current donor
if($result = $mysqliD->query("SELECT expiration_date FROM donors WHERE steam_id = '".$userInfo['steamid']."';")){
    if($result->num_rows > 0){
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $expiration_date = $row['expiration_date'];
    }
}
 if (isset($expiration_date)) {
    $return_donor = true;
} else{
    $return_donor = false;
}
$mysqliD->close();
echo'
<html>
<meta http-equiv="Content-Type"content="text/html;charset=UTF8">
<head>
    <script type="text/javascript" src="scripts/jscolor/jscolor.js"></script>
    <script>
    function change(){
        document.getElementById("langSelect").submit();
    }
    </script>
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
        #langSelect{
            position:relative;
            z-index:99;
            float:right;
        }
    </style>
</head>
    <body>';

    echo "<title>". $lang->donate[0]->msg1 ."</title>";
    echo '<div class="content"><center>';
    echo '<form id="langSelect" method="post">Change Language:
    <select name = "langSelect" onchange="change()">';
        $langList = $language->listLang();
        foreach ($langList as $list) {
            if ($list == $lang->language) {
               printf('<option value="%s" selected>%s</option>',$list,$availableLanguages[$list]);
            }else{
                printf('<option value="%s">%s</option>',$list,$availableLanguages[$list]);
            }
           
        }
        unset($i);
        printf("<input type='hidden' name='steamid_user' value='%s'><input type='hidden' name='amount' value='%s'>",$userInfo['steamid'],$amount);
    if (TIERED_DONOR) {
        printf("<input type='hidden' name='tier' value='%s'>",$tier);
    }
    echo'</select>
    </form>';    
            if($return_donor===true){
                printf("<p id='welcome_back'>" . $lang->donate[0]->msg2 ." ". date('l F j Y',$expiration_date) ."</p>" , $username);
            } 
                echo "<br />";
                if ($amountSmall) {
                    exit("<h3 id='sorry'>".$lang->donate[0]->msg3."</h3>");
                }
   if (PP_SANDBOX) {
            print('<form id="donate" name="_xclick" action="https://sandbox.paypal.com/cgi-bin/webscr" method="post" >');
            print('<input type="hidden" name="business" value="' . PP_SANDBOX_EMAIL . '">');
        }else{
            print('<form id="donate" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post" >');
            print('<input type="hidden" name="business" value="' . PP_EMAIL . '">');
        }            
	            echo "<h1>".$lang->donate[0]->msg4."</h1><br/ >";
	            echo "<img src='{$avatarfull}' /><br /> <h1>  <a href='{$steam_link}' target='_blank'>{$username}</a><h1><hr /><h3>";
                printf("<p>". $lang->donate[0]->msg5, $amount , $days_purchased);
                if(TIERED_DONOR){
                    if ($tier=="1") {
                        printf(" ". $lang->donate[0]->msg6." ",$group1['name']. "</p>");
                    }else{
                        printf(" ". $lang->donate[0]->msg6." ",$group2['name']. "</p>");
                    }
                }else{
                    echo ".</p> ";
                }
                printf($lang->donate[0]->msg7.".</p>",date('l F j Y',$sign_up_date), date('l F j Y',$expire));
                if(TIERED_DONOR && $tier =="2" && CCC){
                    echo "<p>".$lang->donate[0]->msg8."<p>";
                    echo "<input type=\"hidden\" name=\"os0\" value=\"nameColor\"><input type=\"hidden\" name=\"os1\" value=\"chatColor\">";
                    echo "<p><input class='color' name='on0' value='#33CC99' id='colorInput'>".$lang->misc[0]->msg3." <input class='color' name='on1' value='#990000' id='colorInput'>".$lang->misc[0]->msg2."</p>";
                }
            	echo "<p>".$lang->donate[0]->msg9."</p>";
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