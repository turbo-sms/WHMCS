<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function turbo_sms_config() {
    $configarray = array(
        "name" => "TURBO SMS",
        "description" => "If you need Bulk SMS Service Contact with <a href='https://turbosms.com.bd'> turbosms..com.bd</a>",
        "version" => "2.0",
        "author" => "Turbo SMS Technical Department",
		"language" => "english",
    );
    return $configarray;
}

function turbo_sms_activate() {

    $query = "CREATE TABLE IF NOT EXISTS `turbosms_messages` (`id` int(11) NOT NULL AUTO_INCREMENT,`sender` varchar(40) NOT NULL,`to` varchar(15) DEFAULT NULL,`text` text,`msgid` varchar(50) DEFAULT NULL,`status` varchar(10) DEFAULT NULL,`errors` text,`logs` text,`user` int(11) DEFAULT NULL,`datetime` datetime NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `turbosms_settings` (`id` int(11) NOT NULL AUTO_INCREMENT,`api` varchar(40) CHARACTER SET utf8 NOT NULL,`apiparams` varchar(500) CHARACTER SET utf8 NOT NULL,`wantsmsfield` int(11) DEFAULT NULL,`gsmnumberfield` int(11) DEFAULT NULL,`dateformat` varchar(12) CHARACTER SET utf8 DEFAULT NULL,`version` varchar(6) CHARACTER SET utf8 DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "INSERT INTO `turbosms_settings` (`api`, `apiparams`, `wantsmsfield`, `gsmnumberfield`,`dateformat`, `version`) VALUES ('turbo', '{\"senderid\":\"\",\"signature\":\"\"}', 0, 0,'%d.%m.%y','1.1.3');";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `turbosms_templates` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(50) CHARACTER SET utf8 NOT NULL,`type` enum('client','admin') CHARACTER SET utf8 NOT NULL,`admingsm` varchar(255) CHARACTER SET utf8 NOT NULL,`template` varchar(240) CHARACTER SET utf8 NOT NULL,`variables` varchar(500) CHARACTER SET utf8 NOT NULL,`active` tinyint(1) NOT NULL,`extra` varchar(3) CHARACTER SET utf8 NOT NULL,`description` text CHARACTER SET utf8,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

	//Create a New Table for OTP 
    $query = "CREATE TABLE IF NOT EXISTS `turbosms_otp` (`id` int(11) NOT NULL AUTO_INCREMENT,`otp` varchar(50) CHARACTER SET utf8 NOT NULL,`type` enum('client','admin') CHARACTER SET utf8 DEFAULT 'client',`relid` int(10) DEFAULT 0,`request` varchar(50) CHARACTER SET utf8 NOT NULL,`text` text,`status` tinyint(1) DEFAULT 0, `datetime` datetime NOT NULL, `phonenumber` text, PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);
	
    //Creating hooks
	require_once("smsclass.php");
    $class = new turbosms();
    $class->checkHooks();

    return array('status'=>'success','description'=>'Turbo SMS successfully activated :)');
}

function turbo_sms_deactivate() {

    $query = "DROP TABLE `turbosms_templates`";
	mysql_query($query);
    $query = "DROP TABLE `turbosms_settings`";
    mysql_query($query);
    $query = "DROP TABLE `turbosms_messages`";
    mysql_query($query);
	//DROP Table for OTP
    $query = "DROP TABLE `turbosms_otp`";
    mysql_query($query);

    return array('status'=>'success','description'=>'Turbo SMS successfully deactivated :(');
}

function turbo_sms_upgrade($vars) {
    $version = $vars['version'];

    switch($version){
        case "1":
        case "1.0.1":
            $sql = "ALTER TABLE `turbosms_messages` ADD `errors` TEXT NULL AFTER `status` ;ALTER TABLE `turbosms_templates` ADD `description` TEXT NULL ;ALTER TABLE `turbosms_messages` ADD `logs` TEXT NULL AFTER `errors` ;";
            mysql_query($sql);
        case "1.1":
            $sql = "ALTER TABLE `turbosms_settings` CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;";
            mysql_query($sql);
        case "1.1.1":
        case "1.1.2":
            $sql = "ALTER TABLE `turbosms_settings` ADD `dateformat` VARCHAR(12) NULL AFTER `gsmnumberfield`;UPDATE `turbosms_settings` SET dateformat = '%d.%m.%y';";
            mysql_query($sql);
        case "1.1.3":
        case "1.1.4":
            $sql = "ALTER TABLE `turbosms_templates` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `type` `type` ENUM( 'client', 'admin' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `admingsm` `admingsm` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `template` `template` VARCHAR( 240 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `variables` `variables` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `extra` `extra` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `turbosms_settings` CHANGE `api` `api` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `dateformat` `dateformat` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `version` `version` VARCHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `turbosms_messages` CHANGE `sender` `sender` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `to` `to` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `msgid` `msgid` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `status` `status` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `errors` `errors` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `logs` `logs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);

            $sql = "ALTER TABLE `turbosms_templates` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `turbosms_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `turbosms_messages` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
        case "1.1.5":
        case "1.1.6":
        case "1.1.7":
            break;

    }

    $class = new turbosms();
    $class->checkHooks();
}

function turbo_sms_output($vars){
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	putenv("TZ=Asia/dhaka");

    $class = new turbosms();

    $tab = $_GET['tab'];
    echo '<div id="turbo_plugin_container">
    <style>
    .contentarea{
        background: #f5f5f5 !important;
    }
    #clienttabs *{
    margin: inherit;
    padding: inherit;
    border: inherit;
    color: inherit;
    background: inherit;
    background-color: inherit;
    }
    #turbo_plugin_container textarea{
        border: 1px solid #cccccc !important;
        padding: 5px !important;
    }
    #turbo_plugin_container .internalDiv {
        text-align: left !important;;
        background:#fff !important;;
        margin: 0px !important;;
        padding: 5px !important;;
        border: 1px solid #ddd !important;
    }
    #turbo_plugin_container .button {
        width: 140px !important;
        height: 43px !important;
        color: #666 !important;
        padding: 10px !important;
        margin-left: 31% !important;
        margin-top: 10px !important;
    }
    #turbo_plugin_container input[type="checkbox"] { border-radius: 0px !important;}
    #turbo_plugin_container textarea ,#turbo_plugin_container input,#turbo_plugin_container .sel {
        width: 35% !important;
    }

    #clienttabs{position: relative; z-index: 99;}
     #clienttabs ul li {
        display: inline-block;
        margin-right: 3px;
        border: 1px solid #ddd;
        border-bottom:0px;
        padding: 12px;
        margin-bottom: -1px;
     }
     #clienttabs ul a {
     border: 0px;;
     }
     #clienttabs ul {
        float:left;
        margin-bottom:0px;
     }
     #clienttabs{
//        margin-bottom:10px;
        float:left;
     }
     .tabselected{
        background-color:#fff !important;
     }
     table.form td.fieldarea{
        background-color:white !important;
     }
     table.form td {
        padding: 3px 15px !important;
     }

     table.form {
     padding-top: 20px !important;
     }

    </style>
    
   <a href="https://www.turbosms.com.bd" target="_blank" class="logo"><img src="/modules/addons/turbo_sms/images/logo.png" alt="TURBO SMS">
    
    <div id="clienttabs">
        <ul>
            <li class="' . (($tab == "settings" || (@$_GET['type'] == "" && $tab == ""))?"tabselected":"tab") . '"><a href="addonmodules.php?module=turbo_sms&tab=settings">'.$LANG['settings'].'</a></li>
            <li class="' . ((@$_GET['type'] == "client")?"tabselected":"tab") . '"><a href="addonmodules.php?module=turbo_sms&tab=templates&type=client">'.$LANG['clientsmstemplates'].'</a></li>
            <li class="' . ((@$_GET['type'] == "admin")?"tabselected":"tab") . '"><a href="addonmodules.php?module=turbo_sms&tab=templates&type=admin">'.$LANG['adminsmstemplates'].'</a></li>
            <li class="' . (($tab == "sendbulk")?"tabselected":"tab") . '"><a href="addonmodules.php?module=turbo_sms&tab=sendbulk">'.$LANG['sendsms'].'</a></li>
            <li class="' . (($tab == "messages")?"tabselected":"tab") . '"><a href="addonmodules.php?module=turbo_sms&amp;tab=messages">'.$LANG['messages'].'</a></li>
            
        </ul>
    </div>
    <div style="clear:both;"></div>
    ';
    if (!isset($tab) || $tab == "settings")
    {
        /* UPDATE SETTINGS */
        if ($_POST['params']) {
            $update = array(
                "api" => $_POST['api'],
                "apiparams" => json_encode($_POST['params']),
                'wantsmsfield' => $_POST['wantsmsfield'],
                'gsmnumberfield' => $_POST['gsmnumberfield'],
                'dateformat' => $_POST['dateformat']
            );
            update_query("turbosms_settings", $update, "");
        }
        /* UPDATE SETTINGS */

        $settings = $class->getSettings();
        $apiparams = json_decode($settings['apiparams']);

        /* CUSTOM FIELDS START */
//        $where = array(
//            "fieldtype" => array("sqltype" => "LIKE", "value" => "tickbox"),
//            "showorder" => array("sqltype" => "LIKE", "value" => "on")
//        );
//
//        $result = select_query("tblcustomfields", "id,fieldname", $where);
//        $wantsms = '<option value="">Select</option>';
//        while ($data = mysql_fetch_array($result)) {
//            if ($data['id'] == $settings['wantsmsfield']) {
//                $selected = 'selected="selected"';
//            } else {
//                $selected = "";
//            }
//            $wantsms .= '<option value="' . $data['id'] . '" ' . $selected . '>' . $data['fieldname'] . '</option>';
//        }
//
//        $where = array(
//            "fieldtype" => array("sqltype" => "LIKE", "value" => "text"),
//            "showorder" => array("sqltype" => "LIKE", "value" => "on")
//        );
//        $result = select_query("tblcustomfields", "id,fieldname", $where);
//        $gsmnumber = '<option value="">Select</option>';
//        while ($data = mysql_fetch_array($result)) {
//            if ($data['id'] == $settings['gsmnumberfield']) {
//                $selected = 'selected="selected"';
//            } else {
//                $selected = "";
//            }
//            $gsmnumber .= '<option value="' . $data['id'] . '" ' . $selected . '>' . $data['fieldname'] . '</option>';
//        }
        /* CUSTOM FIELDS FINISH HIM */

        $classers = $class->getSenders();
        $classersoption = '';
        $classersfields = '';

        foreach($classers as $classer){
            $classersoption .= '<option value="'.$classer['value'].'" ' . (($settings['api'] == $classer['value'])?"selected=\"selected\"":"") . '>'.$classer['label'].'</option>';
            if($settings['api'] == $classer['value']){
                foreach($classer['fields'] as $field){
                    $classersfields .=
                        '<tr>
                            <td class="fieldlabel" width="30%"><a href="http://turbosms.top/Developers" target="_blank" style="color:blue;">Your API Key</a></td>
                            <td class="fieldarea"><input type="text" name="params['.$field.']" placeholder="SMS API Key" size="40" value="' . $apiparams->$field . '"></td>
                        </tr>';
                }
            }
        }

        echo '
        <script type="text/javascript">
            $(document).ready(function(){
                $("#api").change(function(){
                    $("#form").submit();
                });
            });
        </script>
        <form action="" method="post" id="form">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>
                        <tr>
                                <input type="hidden"  value="turbo" name="api" id="api"/>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%"><a href="http://turbosms.top/Messaging/SenderId" target="_blank" style="color:blue;">'.$LANG['senderid'].'</a></td>
                            <td class="fieldarea"><input type="text" name="params[senderid]" size="40" placeholder="e.g. TURBO SMS/09XXXXXXXXX" value="' . $apiparams->senderid . '"></td>
                        </tr>
                        '.$classersfields.'
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['signature'].'</td>
                            <td class="fieldarea"><textarea  name="params[signature]" rows="4" cols="39" placeholder="Team - Your Company Name.">' . $apiparams->signature . '</textarea></td>
                        </tr>
                        <!-- section one removed from here-->
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['dateformat'].'</td>
                            <td class="fieldarea"><input type="text" name="dateformat" size="40" value="' . $settings['dateformat'] . '"> e.g:  %d.%m.%y (27.01.2014)</td>
                        </tr>
                    </tbody>
                </table>
                <p align="left"><input type="submit" value="'.$LANG['save'].'" class="button" /></p>
            </div>
        </form>
        ';
    }
    elseif ($tab == "templates")
    {
        if ($_POST['submit']) {
            $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
            $result = select_query("turbosms_templates", "*", $where);
            while ($data = mysql_fetch_array($result)) {
                if ($_POST[$data['id'] . '_active'] == "on") {
                    $tmp_active = 1;
                } else {
                    $tmp_active = 0;
                }
                $update = array(
                    "template" => $_POST[$data['id'] . '_template'],
                    "active" => $tmp_active
                );

                if(isset($_POST[$data['id'] . '_extra'])){
                    $update['extra']= trim($_POST[$data['id'] . '_extra']);
                }
                if(isset($_POST[$data['id'] . '_admingsm'])){
                    $update['admingsm']= $_POST[$data['id'] . '_admingsm'];
                    $update['admingsm'] = str_replace(" ","",$update['admingsm']);
                }
                update_query("turbosms_templates", $update, "id = " . $data['id']);
            }
        }

        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>';
        $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
        $result = select_query("turbosms_templates", "*", $where);

        while ($data = mysql_fetch_array($result)) {
            if ($data['active'] == 1) {
                $active = 'checked = "checked"';
            } else {
                $active = '';
            }
            $desc = json_decode($data['description']);
            if(isset($desc->$LANG['lang'])){
                $name = $desc->$LANG['lang'];
            }else{
                $name = $data['name'];
            }
            echo '
                <tr>
                    <td class="fieldlabel" width="30%">' . $name . '</td>
                    <td class="fieldarea">
                        <textarea cols="50" name="' . $data['id'] . '_template">' . $data['template'] . '</textarea>
                    </td>
                </tr>';
            echo '
            <tr>
                <td class="fieldlabel"  style="float:right;">'.$LANG['parameter'].'</td>
                <td>' . $data['variables'] . '</td>
            </tr>
            ';
            if(!empty($data['extra'])){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['ekstra'].'</td>
                    <td class="fieldarea">
                        <input type="text" name="'.$data['id'].'_extra" value="'.$data['extra'].'">
                    </td>
                </tr>
                ';
            }
            if($_GET['type'] == "admin"){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['admingsm'].'</td>
                    <td class="fieldarea">
                        <input type="text" class="extraField" name="'.$data['id'].'_admingsm" placeholder="e.g. 018XXXXXXXX,017XXXXXXXX,019XXXXXXXX" value="'.$data['admingsm'].'">
                    </td>
                </tr>
                ';
            }
            echo '
            <tr>
                <td class="fieldlabel" width="30%" style="float:right;">'.$LANG['active'].'</td>
                <td><input type="checkbox" value="on" name="' . $data['id'] . '_active" ' . $active . '></td>
            </tr>
            ';




            echo '<tr>
                <td colspan="2"><hr></td>
            </tr>';
        }
        echo '
        </tbody>
                </table>
            <p align="left"><input type="submit" name="submit" value="Save Changes" class="button" /></p>
            </div>
        </form>';

    }
    elseif ($tab == "messages")
    {
        if(!empty($_GET['deletesms'])){
            $smsid = (int) $_GET['deletesms'];
            $sql = "DELETE FROM turbosms_messages WHERE id = '$smsid'";
            mysql_query($sql);
        }
        echo  '
        <!--<script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" type="text/css">
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css" type="text/css">
        <script type="text/javascript">
            $(document).ready(function(){
                $(".datatable").dataTable();
            });
        </script>-->

        <div class="internalDiv" style="padding:20px !important;">
        <table class="datatable" border="0" cellspacing="1" cellpadding="3" style="margin: 0px; border: 0px;">
        <thead>
            <tr>
                <th>#</th>
                <th>'.$LANG['client'].'</th>
                <th>'.$LANG['gsmnumber'].'</th>
                <th width="50%" >'.$LANG['message'].'</th>
                <th>'.$LANG['datetime'].'</th>
                <th>'.$LANG['status'].'</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        ';

        // Getting pagination values.
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = (isset($_GET['limit']) && $_GET['limit']<=50) ? (int)$_GET['limit'] : 10;
        $start  = ($page > 1) ? ($page*$limit)-$limit : 0;
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        /* Getting messages order by date desc */
        $sql = "SELECT `m`.*,`user`.`firstname`,`user`.`lastname`
        FROM `turbosms_messages` as `m`
        JOIN `tblclients` as `user` ON `m`.`user` = `user`.`id`
        ORDER BY `m`.`datetime` {$order} limit {$start},{$limit}";
        $result = mysql_query($sql);
        $i = 0;

        //Getting total records
        $total = "SELECT count(id) as toplam FROM `turbosms_messages`";
        $sonuc = mysql_query($total);
        $sonuc = mysql_fetch_array($sonuc);
        $toplam = $sonuc['toplam'];

        //Page calculation
        $sayfa = ceil($toplam/$limit);

        while ($data = mysql_fetch_array($result)) {
            if($data['msgid'] && $data['status'] == ""){
                $status = $class->getReport($data['msgid']);
                mysql_query("UPDATE turbosms_messages SET status = '$status' WHERE id = ".$data['id']);
            }else{
                $status = $data['status'];
            }

            $i++;

            echo  '<tr>

            <td>'.$data['id'].'</td>
            <td><a href="clientssummary.php?userid='.$data['user'].'">'.$data['firstname'].' '.$data['lastname'].'</a></td>
            <td>'.$data['to'].'</td>
            <td>'.$data['text'].'</td>
            <td>'.$data['datetime'].'</td>
            <td>'.$LANG[$status].'</td>
            <td><a href="addonmodules.php?module=turbo_sms&tab=messages&deletesms='.$data['id'].'" title="'.$LANG['delete'].'"><img src="images/delete.gif" width="16" height="16" border="0" alt="Delete"></a></td></tr>';
        }
        /* Getting messages order by date desc */

        echo '
        </tbody>
        </table>

        ';  
        $list="";
        for($a=1;$a<=$sayfa;$a++)
        {
            $selected = ($page==$a) ? 'selected="selected"' : '';
            $list.="<option value='addonmodules.php?module=turbo_sms&tab=messages&page={$a}&limit={$limit}&order={$order}' {$selected}>{$a}</option>";
        }
        echo "<select  onchange=\"this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);\">{$list}</select></div>";

    }
    elseif($tab=="sendbulk")
    {
        $settings = $class->getSettings();

        if(!empty($_POST['client'])){
            $userinf = explode("_",$_POST['client']);
            $userid = $userinf[0];
            $gsmnumber = $userinf[1];
            $country = $userinf[4];

            $replacefrom = array("{firstname}","{lastname}");
            $replaceto = array($userinf[2],$userinf[3]);
            $message = str_replace($replacefrom,$replaceto,$_POST['message']);

            ;

            $class->setCountryCode($class->getCodeBy($country));
            $class->setGsmnumber($gsmnumber);
            $class->setMessage($message);
            $class->setUserid($userid);

            $result = $class->send();
            if($result == false){
                $responseToShow =  $class->getErrors();
            }else{
                $responseToShow =  $LANG['smssent'].' '.$gsmnumber;
            }

            if($_POST["debug"] == "ON"){
                $debug = 1;
            }
        }

//        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
//        FROM `tblclients` as `a`
//        JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id`
//        JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
//        WHERE `b`.`fieldid` = '".$settings['gsmnumberfield']."'
//        AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
//        AND `c`.`value` = 'on' order by `a`.`firstname`";

        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`country`, `a`.`phonenumber` as `gsmnumber`
        FROM `tblclients` as `a` order by `a`.`firstname`";

//        $clients = '<option value="">Set the custom field first. See doc for more information</option>';
        $clients = '';
        $result = mysql_query($userSql);
        while ($data = mysql_fetch_array($result)) {
            $clients .= '<option value="'.$data['id'].'_'.$data['gsmnumber'].'_'.$data['firstname'].'_'.$data['lastname'].'_'.$data['country'].'">'.$data['firstname'].' '.$data['lastname'].' (#'.$data['id'].')</option>';
        }

        echo '
        <script>
        jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
          return this.each(function() {
            var select = this;
            var options = [];
            $(select).find("option").each(function() {
              options.push({value: $(this).val(), text: $(this).text()});
            });
            $(select).data("options", options);
            $(textbox).bind("change keyup", function() {
              var options = $(select).empty().scrollTop(0).data("options");
              var search = $.trim($(this).val());
              var regex = new RegExp(search,"gi");

              $.each(options, function(i) {
                var option = options[i];
                if(option.text.match(regex) !== null) {
                  $(select).append(
                     $("<option>").text(option.text).val(option.value)
                  );
                }
              });
              if (selectSingleMatch === true && 
                  $(select).children().length === 1) {
                $(select).children().get(0).selected = true;
              }
            });
          });
        };
        $(function() {
          $("#clientdrop").filterByText($("#textbox"), true);
        });  
        </script>';




        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv" >'.$responseToShow.'
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['client'].'</td>
                            <td class="fieldarea">
                                <input id="textbox" type="text" placeholder="Type client name" style="width:498px;padding:5px"><br>
                                <select name="client" class="sel" multiple id="clientdrop" style="padding:5px">
                                    <option value="">'.$LANG['selectclient'].'</option>
                                    ' . $clients . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['message'].'</td>
                            <td class="fieldarea">
                               <textarea cols="70" rows="5" name="message" style="width:498px;padding:5px"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">Parameters:</td>
                            <td class="fieldarea">
                                {firstname},{lastname}
                            </td>
                        </tr>
                       
                        <tr>
                            <td class="fieldlabel" width="30%"></td>
                            <td class="fieldlabel"><div style="text-align:left;">'. ((isset($debug))?$class->getLogs():"") .'</div></td>
                        </tr>
                    </tbody>
                </table>
            <p align="left"><input type="submit" value="'.$LANG['send'].'" class="button"  /></p>
            </div>
        </form>';


    }
    elseif($tab == "update"){
        //to change the url here.
        $currentversion = file_get_contents("https://turbosms.com.bd/integration");
        echo '<div class="internalDiv" style="padding:20px !important;">';
        if($version != $currentversion){
            echo $LANG['newversion'];
        }else{
            echo $LANG['uptodate'].'<br><br>';
        }
        echo '</div>';
    }


 $connct =  $class->getconnect();
    if($connct){
        echo '
             '.$connct.'';
    } 






    $credit =  $class->getBalance();
    if($credit){
        echo '
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 8px; border: 2px solid #ddd;">
            <b>Your Remaining Sms Balance</b> '.$credit.'
            
            </div>';
            
    }

	echo '
           
            <p style="text-align: right;"><a href="https://turbosms.com.bd" style="color:blue;" target="_blank">Developed by Turbo SMS Technical Department</a></p>
            
            </div>';
    echo '</div>';
}
