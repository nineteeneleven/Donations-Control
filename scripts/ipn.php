<?php
define('NineteenEleven', TRUE);
require_once'../includes/config.php';
require_once '../includes/class_lib.php';
require_once 'rcon_code.php';
$ConvertID = new SteamIDConvert;
$sb = new SourceBans;
$tools = new tools;
$sysLog = new log;
$mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB)or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: ". __LINE__));


$log=fopen('../admin/logs/IPN-'.date('d-m-Y_G-i-s').'.log', "a");

$req = 'cmd=_notify-validate';
 
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
if (PP_SANDBOX) {
    $fp = @fsockopen ('ssl://sandbox.paypal.com', 443, $errno, $errstr, 30);
}else{
    $fp = @fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
}
if (!$fp)
    { 
        fclose($log);
        die($sysLog->logError('Error contacting PayPal. Probaby some l33t h4x3r.'));
    }
    else
    {
        fputs ($fp, $header . $req);

        fwrite($log, date('m/j/Y g:i:s A') .": PayPal IPN recieved \r\n");

        $userInfo = explode("?", $_POST['custom']);
        $steamid_user = $userInfo[0];
        $amount = $userInfo[1];
        $sign_up_date = $userInfo[2];
        $expire = $userInfo[3];
        $days_purchased = $userInfo[4];
        if (TIERED_DONOR) {
           $tier = $userInfo[5];
           if ($tier=="1") {
                $srv_group = $group1['name'];
                $group_id = $group1['group_id'];
                $srv_group_id = $group1['srv_group_id'];
                $server_id = $group1['server_id'];
           }else{
                $srv_group = $group2['name'];
                $group_id = $group2['group_id'];
                $srv_group_id = $group2['srv_group_id'];
                $server_id = $group2['server_id'];
                if (CCC) {
                    $nameColor = str_replace("#", "", $_POST['option_name1']);
                    $chatColor = str_replace("#", "", $_POST['option_name2']);                   
                }

           }
        }else{
            $srv_group = $group1['name'];
            $group_id = $group1['group_id'];
            $srv_group_id = $group1['srv_group_id'];
            $server_id = $group1['server_id'];
            $tier = '1';
        }
        $email = $_POST['payer_email'];
        $txn_id = $_POST['txn_id'];
        $steamArray= $ConvertID->SteamIDCheck($steamid_user);
        $steamID64 = $steamArray['steamID64'];
        $steam_link = $steamArray['steam_link'];
        $steam_link_xml = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=xml&steamids=" . $steamID64;
        $tag = $group2['name'];
    



        $cacheReturn=$mysqliD->query("SELECT * FROM `player_tracker` WHERE auth ='" . $steamid_user ."';")or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: ". __LINE__));
        if($cacheReturn->num_rows > 0) {
            $cacheResult = $cacheReturn->fetch_array(MYSQLI_ASSOC);
            $username = $cacheResult['name'];
         fwrite($log, "grabbed player name from cache. \r\n");
        }else{

            $xml = @simplexml_load_file($steam_link_xml)
            or $username = "ERROR FINDING USER";
            if(!empty($xml)) {
            $username = $xml->players->player->personaname;
            fwrite($log, "grabbed player name from Steam Servers. \r\n");
            }
        } 
        //strip the username of special chars and escape  it for mysql
        $username =$tools->cleanUser($username);       
        $username = $mysqliD->real_escape_string($username);
        $steamid_user = $mysqliD->real_escape_string($steamid_user);        
        fwrite($log, "Steam ID: " . $steamid_user." (". $username . ")\r\n"."amount: " . $amount . "\r\n"."Sign Up Date: " . $sign_up_date . "\r\n".
            "Days Purchased: " . $days_purchased . "\r\n"."Email: " . $email . "\r\n"."Transaction ID: " . $txn_id . "\r\n".
            "XML Steam Link: " . $steam_link_xml . "\r\n");
        if (TIERED_DONOR&&CCC&&$tier=='2') {
            fwrite($log, "Tag: {$tag}\r\nName Color: {$nameColor}\r\nChat Color: {$chatColor}\r\n");
        }

        //checking if donor already exists

        $result = $mysqliD->query("SELECT user_id,total_amount,expiration_date,txn_id,activated FROM donors WHERE steam_id = '{$steamid_user}';")or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: ". __LINE__));
        if($result){
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $expDate = $row['expiration_date'];
            $n = "+".$days_purchased . " days";
            if ($row['activated']=="2") {
                $expiration_date = $expire;
            }else{
                $expiration_date = strtotime($n,$expDate);
            }
            unset($n);
            $total_amount = $row['total_amount'];
            $total_amount = $total_amount + $amount;
            $renewal_date = $sign_up_date;
            $current_amount = $amount;
            $user_id = $row['user_id'];

        }else{exit(fwrite($log,"Something went shit nuts with the database.\r\n"));}
        
            if (isset($user_id)) {
                fwrite($log, "User is already in database! repeat IPN?\r\n");
                if($txn_id != $row['txn_id']){
                    fwrite($log, "Nope, just a repeat donor\r\n");
                    $insert_sql="UPDATE `donors` SET `renewal_date` = '{$renewal_date}', 
                                                    `current_amount` = '{$current_amount}', 
                                                    `total_amount` = '{$total_amount}', 
                                                    `expiration_date` = '{$expiration_date}', 
                                                   `activated` = '1',
                                                   `txn_id` = '{$txn_id}',
                                                   `tier` = '{$tier}'
                                                    WHERE `steam_id` = '{$steamid_user}';";

                }else{
                    die(fwrite($log,"YEP! fuckin PayPal! Duplicate Transaction ID, GET OUT OF HERE !!!!.\r\n"));
                }

            } else {
                //not in database, new donor
                $insert_sql = "INSERT INTO donors (username,
                                                    steam_id,
                                                    sign_up_date,
                                                    email,
                                                    renewal_date,
                                                    current_amount,
                                                    total_amount,
                                                    expiration_date,
                                                    steam_link,
                                                    activated,
                                                    txn_id,
                                                    tier) 
                                                     VALUES ('{$username}', 
                                                        '{$steamid_user}', 
                                                        '{$sign_up_date}', 
                                                        '{$email}', 
                                                        '0',
                                                        '{$amount}',
                                                        '{$amount}',
                                                        '{$expire}',
                                                        '{$steam_link}', 
                                                        '1', 
                                                        '{$txn_id}',
                                                        '{$tier}');";
            }
            $mysqliD->query($insert_sql) or die($sysLog->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: ". __LINE__));
            unset($insert_sql);
            if(TIERED_DONOR&&CCC&&$tier=='2'){

                $result = $mysqliD->query("SELECT * FROM `custom_chatcolors` WHERE identity = '{$steamid_user}';");
                if ($result->num_rows >= 1) {
                    @$mysqliD->query("DELETE FROM `custom_chatcolors` WHERE identity = '{$steamid_user}';");
                }
                
                $ccc_sql = "INSERT INTO `custom_chatcolors` (`tag`, `identity`, `namecolor`, `textcolor`) VALUES ('{$tag}','{$steamid_user}','{$nameColor}','{$chatColor}');";
                if($mysqliD->query($ccc_sql)){
                
                    if($sb->queryServers("sm_reloadccc")){
                        fwrite($log, "reloaded CCC successfully.\r\n");
                    }else{
                        fwrite($log, "reloading CCC failed.\r\n");
                    }
                }else{
                    $sysLog->logError($mysqliD->error . " " . $mysqliD->errno ." Line Number: ". __LINE__);
                }
                unset($ccc_sql); 
            }
            $mysqliD->close();
            fwrite($log, "Finished inserting into the donor database, preparing for sourcebans insertion.\r\n");

                    $mysqliS = new mysqli(SB_HOST,SB_USER,SB_PASS,SOURCEBANS_DB)or die($sysLog->logError($mysqliS->error . " " . $mysqliS->errno ." Line Number: ". __LINE__));

                    //check sourcebans database to see if user is already in there
                    
                    $result = $mysqliS->query("SELECT * FROM `".SB_PREFIX."_admins` WHERE authid='".$steamid_user."';") or die($sysLog->logError($mysqliS->error . " " . $mysqliS->errno ." Line Number: ". __LINE__));

                        if($result){

                            $row = $result->fetch_array(MYSQLI_ASSOC);
                            $sb_aid = $row['aid'];

                            //$result->free();

                        } 
                    if (!isset($sb_aid)) {

                        //if not, PUT EM IN!
                                $sb_pw = "1fcc1a43dfb4a474abb925f54e65f426e932b59e";

                                if($mysqliS->query("INSERT INTO `" . SOURCEBANS_DB . "` . `".SB_PREFIX."_admins` (user,authid,password,gid,extraflags,immunity,srv_group) VALUES ('{$username}', '{$steamid_user}', '{$sb_pw}' , '-1' , '0' , '0', '{$srv_group}');")){
                                     fwrite($log, "inserted into sb_admins.\r\n");
                                 }else{
                                    die($sysLog->logError($mysqliS->error . " " . $mysqliS->errno ." Line Number: ". __LINE__));
                                }

                                if($admin_id = $mysqliS->insert_id){
                                     fwrite($log, "got new id.\r\n");
                                }

                                if($mysqliS->query("INSERT INTO `" . SOURCEBANS_DB . "` . `".SB_PREFIX."_admins_servers_groups` (admin_id,group_id,srv_group_id,server_id) VALUES('{$admin_id}', '{$group_id}', '{$srv_group_id}', '{$server_id}');")){
                                     fwrite($log, "inserted into sb_admins_servers_groups.\r\n");
                                }else{
                                    die($sysLog->logError($mysqliS->error . " " . $mysqliS->errno ." Line Number: ". __LINE__));
                                }

                                $mysqliS->close();

                                if($sb->queryServers('sm_reloadadmins')){
                                    fwrite($log, "Sourcebans admin cache reloaded. \r\n");
                                }
                        }else{
                            fwrite($log, "{$username} is alreay in sourcebans, skipping.\r\n");
                        }
                $sysLog->logAction("AUTOMATIC ACTION: $username Added (New Donation)");
            if(sys_email){

                $mail_body = "{$username} Has made a donation of \${$amount} though PayPal, and their donor perks have been automatically activated"; 
                $subject = "New \${$amount} donation from {$username}";
                $mailHeader = "From: ". $mail['name'] . " <" . $mail['email'] . ">\r\n";

                ini_set( $email, $recipient); // for windows mail servers

                if ($mail['useBCC']) {
                    $to = $mail['recipient'] .', ' . $mail['BCC'];
                }else{
                    $to = $mail['recipient'];
                } 
                @mail($to, $subject, $mail_body, $mailHeader);


                if ($mail['donor']) {
                    @mail($email, $mail['donorSubject'], $mail['donorMsg'], $mailHeader);
                }

            }
        
        fclose ($fp);
    }
if (STATS) {
    @$sysLog->stats("IPN");
}
    $mysqliD->close();
    $mysqliS->close();
    unset($mysqliD);
    unset($mysqliS);
    unset($sb);
    unset($tools);
    unset($ConvertID);
    unset($log);
    fwrite($log, "All done here, closing log file....good bye.");
fclose($log);
//End Paypal code

?>