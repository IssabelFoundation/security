<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0.4-5                                               |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2011-05-13 11:05:31 Estefanía Morán Meza emoran@palosanto.com Exp $ */
//include issabel framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";
require_once("libs/misc.lib.php");

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoChangePassword.class.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $action = getAction();
    $content = "";
    switch($action){
        case "update_advanced_security_settings":
            $content = updateAdvancedSecuritySettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "update_status_ipbx_frontend":
            $content = updateStatusIssabelPBXFrontend($arrConf);
            break;
        case "update_status_anonymous_sip":
            $content = updateStatusAnonymousSIP($arrConf);
            break;
        default: // view_form_advanced_security_settings
            $content = viewFormAdvancedSecuritySettings($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
    }
    return $content;
}

function viewFormAdvancedSecuritySettings($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $pAdvancedSecuritySettings       = new paloSantoAdvancedSecuritySettings($arrConf);
    $value_ipbx_frontend             = $pAdvancedSecuritySettings->isActivatedIssabelPBXFrontend();
    $value_anonymous_sip             = $pAdvancedSecuritySettings->isActivatedAnonymousSIP();
    $arrFormAdvancedSecuritySettings = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormAdvancedSecuritySettings);

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("subtittle1", _tr("Enable access"));
    $smarty->assign("subtittle2", _tr("Change Password"));
    $smarty->assign("value_ipbx_frontend", $value_ipbx_frontend);
    $smarty->assign("value_anonymous_sip", $value_anonymous_sip);
    $smarty->assign("icon", "modules/".$module_name."/images/security_advanced_settings.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Advanced Security Settings"), $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function updateStatusIssabelPBXFrontend($arrConf)
{
    $pAdvanceSecuritySettings = new paloSantoAdvancedSecuritySettings($arrConf);
    $jsonObject               = new PaloSantoJSON();
    $statusIssabelPBXFrontend    = getParameter("new_status_ipbx_frontend");
    $result = $pAdvanceSecuritySettings->updateStatusIssabelPBXFrontend($statusIssabelPBXFrontend);
    $arrData['result']       = $result;
    $arrData['button_title'] = _tr("Dismiss");
    if($statusIssabelPBXFrontend == "1")
	$word = "enabled";
    else
	$word = "disabled";
    if($result){
	$arrData['message_title'] = _tr("Information").":<br/>";
	$arrData['message']       = _tr("Access direct to IssabelPBX has been $word.");
    }
    else{
	$arrData['message_title'] = _tr("Error").":<br/>";
	$arrData['message']       = _tr("Access direct to IssabelPBX has not been $word.");
    }
    $jsonObject->set_message($arrData);
    Header('Content-Type: application/json');
    return $jsonObject->createJSON();
}

function updateStatusAnonymousSIP($arrConf)
{
    $pAdvanceSecuritySettings = new paloSantoAdvancedSecuritySettings($arrConf);
    $jsonObject               = new PaloSantoJSON();
    $statusAnonymousSIP    = getParameter("new_status_anonymous_sip");
    $result = $pAdvanceSecuritySettings->updateStatusAnonymousSIP($statusAnonymousSIP);
    $arrData['result']       = $result;
    $arrData['button_title'] = _tr("Dismiss");
    if($statusAnonymousSIP == "1")
        $word = "enabled";
    else
        $word = "disabled";
    if($result){
        $arrData['message_title'] = _tr("Information").":<br/>";
        $arrData['message']       = _tr("Anonymous SIP calls are now $word.");
    } else {
        $arrData['message_title'] = _tr("Error").":<br/>";
        $arrData['message']       = _tr("Anonymous SIP calls cannot be $word.");
    }
    $jsonObject->set_message($arrData);
    Header('Content-Type: application/json');
    return $jsonObject->createJSON();
}

function updateAdvancedSecuritySettings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $arrFormAdvancedSecuritySettings = createFieldForm();
    $oForm                           = new paloForm($smarty,$arrFormAdvancedSecuritySettings);

    //GET FIELDS
    $ipbx_password 	  = '';
    $ipbx_confirm_passwrod = '';
    if(isset($_POST["ipbx_password"]))        $ipbx_password         = getParameter("ipbx_password");
    if(isset($_POST["ipbx_confirm_passwrod"])) $ipbx_confirm_passwrod  = getParameter("ipbx_confirm_passwrod");

    //VALIDATIONS OF PASSWORD
    $msgValidationEmptyFields = "";
    $msgValidationPassword = "";
    $msgValidationConfirPassword = "";

    //VALIDATION OF EMPTY FIELDS
    if($ipbx_password == '')
      $msgValidationEmptyFields .= _tr("Database and Web Administration IssabelPBX Password")."<br/>";
    if($ipbx_confirm_passwrod == '')
      $msgValidationEmptyFields .= _tr("Password Confirmation")."<br/>";

    //VALIDATION OF PASSWORD
    if(!validatePassword($ipbx_password))
      $msgValidationPassword = _tr("Password may only contain alphanumeric characters, spaces, or the following: .&-@=_!<>.");

    //VALIDATION OF CONFIRMATION PASSWORD
    if(!validateConfirmationPassword($ipbx_password, $ipbx_confirm_passwrod))
      $msgValidationConfirPassword = _tr("Password and confirmation do not match!");

    //ERROR MESSAGE
    if($msgValidationEmptyFields != '' || $msgValidationPassword != '' || $msgValidationConfirPassword != ''){
	$smarty->assign("mb_title", _tr("Validation Error"));
	if($msgValidationEmptyFields != ''){
	  $strErrorMsg = "<b>"._tr("The following fields are required").":</b><br/>";
	  $strErrorMsg .= $msgValidationEmptyFields;
	}
	else{
	  if($msgValidationPassword != ''){
	    $strErrorMsg = "<b>"._tr("The following field contain errors").":</b><br/>";
	    $strErrorMsg .= $msgValidationPassword;
	  }else{
	    $strErrorMsg = "<b>"._tr("The following field contain errors").":</b><br/>";
	    $strErrorMsg = $msgValidationConfirPassword;
	  }
	}
        $smarty->assign("mb_message", $strErrorMsg);
    }
    else{
        //SAVE CONFIGURATIONS
	$pAdvancedSecuritySettings = new paloSantoAdvancedSecuritySettings($arrConf,$pDB);
        //Save configurations ChangePassword
	$resultChangePass = $pAdvancedSecuritySettings->changeIssabelPBXPassword($ipbx_password, $arrConf);
	if(is_array($resultChangePass) && isset($resultChangePass['result']) && $resultChangePass['result']){
	    $smarty->assign("mb_title", _tr("Information").":");
	    $messageChangePassword = "<br/>"._tr("Password has been updated.");
	    $_POST["ipbx_password"] = "";
	    $_POST["ipbx_confirm_passwrod"] = "";
	}else{
	    $smarty->assign("mb_title", _tr("Error").":");
	    $messageChangePassword = "<br/>"._tr("Password has not been updated.");
	    if(is_array($resultChangePass['arrUpdateFiles']) && count($resultChangePass['arrUpdateFiles']) > 0){
		$messageChangePassword.= _tr(" But the following files have been modified: ");
		foreach($resultChangePass['arrUpdateFiles'] as $updateFile ){
		    $messageChangePassword.= $updateFile." ,";
		}
	    }
	}
	$smarty->assign("mb_message", $messageChangePassword);
    }
    $content = viewFormAdvancedSecuritySettings($smarty, $module_name, $local_templates_dir, $arrConf);
    return $content;
}

function validatePassword($ipbx_password){
    $patron = '/^[a-zA-Z0-9-@=_!<> .|&]+$/';
    $result = preg_match($patron, $ipbx_password);
    if(trim($ipbx_password) != '' && $result)
	return true;
    return false;
}

function validateConfirmationPassword($ipbx_password, $ipbx_confirm_passwrod){
    if($ipbx_password == $ipbx_confirm_passwrod)
      return true;
    return false;
}

function createFieldForm()
{
    $arrFields = array(
	     "status_ipbx_frontend"    => array ("LABEL"                  => _tr('Enable direct access (Non-embedded) to IssabelPBX'),
						"REQUIRED"               => "no",
						"INPUT_TYPE"             => "CHECKBOX",
						"INPUT_EXTRA_PARAM"      => "",
						"VALIDATION_TYPE"        => "text",
						"VALIDATION_EXTRA_PARAM" => "",
					       ),
         "status_anonymous_sip"    => array ("LABEL"                  => _tr('Enable anonymous SIP calls'),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "CHECKBOX",
                        "INPUT_EXTRA_PARAM"      => "",
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => "",
                           ),
            "ipbx_password"	      => array ("LABEL"                  => _tr("Database and Web Administration IssabelPBX Password"),
						"REQUIRED"               => "no",
						"INPUT_TYPE"             => "PASSWORD",
						"INPUT_EXTRA_PARAM"      => "",
						"VALIDATION_TYPE"        => "",
						"VALIDATION_EXTRA_PARAM" => ""
                                               ),
            "ipbx_confirm_passwrod"    => array ("LABEL"                  => _tr("Password Confirmation"),
						"REQUIRED"               => "no",
						"INPUT_TYPE"             => "PASSWORD",
						"INPUT_EXTRA_PARAM"      => "",
						"VALIDATION_TYPE"        => "",
						"VALIDATION_EXTRA_PARAM" => ""
						)
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("update_advanced_security_settings")) //Get parameter by POST (submit)
        return "update_advanced_security_settings";
    if(getParameter("action")=="update_status_ipbx_frontend")
        return "update_status_ipbx_frontend";
    if(getParameter("action")=="update_status_anonymous_sip")
        return "update_status_anonymous_sip";
    else
        return "view_form";
}
?>
