<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel Version 4.0.0                                                |
  +----------------------------------------------------------------------+
  | Copyright (c) 2017 Issabel Foundation                                |
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
*/
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/IssabelF2Bservice.class.php";
    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case 'save':
            $content = saveJail($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            $pDB =NULL;
            break;
        case 'new': case 'view': case 'edit':
            $content = NewViewZone($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $accion);
            break;
        case 'cancel':
            $content = reportJails($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'start':
            exec('/usr/bin/issabel-helper fb_client start', $respuesta, $retorno);
            $smarty->assign("mb_title", "MESSAGE");
            $smarty->assign("mb_message", _tr("Fail2ban has been activated"));
            $content = reportJails($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'stop':
            exec('/usr/bin/issabel-helper fb_client stop', $respuesta, $retorno);
            $smarty->assign("mb_title", "MESSAGE");
            $smarty->assign("mb_message", _tr("Fail2ban has been deactivated"));
            $content = reportJails($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportJails($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportJails($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $iJails = new IssabelF2BService($pDB);

    $iJails->creaTablaSiNoExiste();

    $field_type = null;
    $field_pattern = null;
    $oGrid  = new paloSantoGrid($smarty);

    $totalJails = $iJails->obtainNumJails();

    $limit  = 20;
    $total  = $totalJails;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->setTitle(_tr("Jails"));
    $oGrid->setIcon("modules/$module_name/images/security_define_ports.png");
    $oGrid->pagingShow(true);
    $offset = $oGrid->calculateOffset();
    $url = array(
        "menu"         =>  $module_name,
        "filter_type"  =>  $field_type,
        "filter_txt"   =>  $field_pattern
    );
    $oGrid->setURL($url);

    $arrData = null;
    $arrResult = $iJails->obtainJails($limit, $offset);
    $button_eliminar = "";
    $arrColumns = array(_tr("Name"),_tr("Count Failed Attempts"),_tr("Ban Time (hours)"),_tr("Whitelist"),_tr("Enabled"),'');
    $oGrid->setColumns($arrColumns);
    if( is_array($arrResult) && $total>0 ){
        foreach($arrResult as $key => $value){
            //$arrTmp[0] = "<input type='checkbox' name='".$value['id']."' id='".$value['id']."'>";
            $arrTmp[0] = $value['name'];
            $arrTmp[1] = $value['maxretry'];
            $arrTmp[2] = $value['bantime'];
            $arrTmp[3] = $value['ignoreip'];
            $arrTmp[4] = $value['enabled'];
            $arrTmp[5] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$value['id']."'>"._tr('View')."</a>";
            $arrData[] = $arrTmp;
        }
    }

    if($iJails->isactive()) {
        // if systemctl status is off (1) then offer option to enable
        $oGrid->customAction("start",_tr("Enable fail2ban"));


    } else {
        $oGrid->customAction("stop",_tr("Disable fail2ban"));
    }

    $oGrid->setData($arrData);

    $contenidoModulo = $oGrid->fetchGrid();

    if (strpos($contenidoModulo, '<form') === FALSE) {
        $contenidoModulo = "<form method='POST' style='margin-bottom:0;' action=$url>$contenidoModulo</form>";
    }

 



    return $contenidoModulo;
}

function NewViewZone($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $action)
{
    $arrFormNew = createFieldFormNew();
    $oForm      = new paloForm($smarty, $arrFormNew);
    $titulo     = "";

    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("SAVE", _tr('Save'));

    if( $action == 'edit' ) {

        $id = $_POST['idtemp'];
        $smarty->assign("IDTEMP", $id);

        $titulo = _tr('Edit Jail');
        $iJails  = new IssabelF2BService($pDB);
        $result = $iJails->loadJail($id);

        $_POST['name']      = $result['name'];
        $_POST['maxretry']  = $result['maxretry'];
        $_POST['bantime']   = $result['bantime'];
        $_POST['ignoreip'] = $result['ignoreip'];
        $_POST['enabled']   = $result['enabled'];
 
        if($result['enabled'] == '1') {
            $_POST['enabled'] = 'on';
        } else {
            $_POST['enabled'] = 'off';    
        }

    } else if( $action == 'view' ) {

        $id = $_GET['id'];
        $smarty->assign("IDTEMP", $id);

        $titulo = _tr('View Jail');
        $iJails = new IssabelF2BService($pDB);
        $result = $iJails->loadJail($id);

        $_POST['name']      = $result['name'];
        $_POST['maxretry']  = $result['maxretry'];
        $_POST['bantime']   = $result['bantime'];
        $_POST['ignoreip'] = $result['ignoreip'];
        $_POST['enabled']   = $result['enabled'];
        
        if($result['enabled'] == '1') {
            $_POST['enabled'] = 'on';
        } else {
            $_POST['enabled'] = 'off';    
        }
            
        $oForm->setViewMode();
    }
  
    $smarty->assign("MODE", $action);
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $titulo, $_POST);
    $contenidoModulo = "<form method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldFormNew() {

    $arrFields = array(
        "name" => array(
            "LABEL"                  => _tr("Name"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
            "EDITABLE       "        => "no",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "" 
        ),
        "maxretry" => array(
            "LABEL"                  => _tr("Count Failed Attempts"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
            "EDITABLE       "        => "yes",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "" 
        ),
        "bantime" => array(
            "LABEL"                  => _tr("Ban Time (hours)"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
            "EDITABLE       "        => "yes",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "" 
        ),
        "ignoreip" => array(   
            "LABEL"                  => _tr("Whitelist"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXTAREA",
            "INPUT_EXTRA_PARAM"      => array("style" => "width:162px", "id"=>"ignoreipfield"),
            "VALIDATION_TYPE"        => "",
            "VALIDATION_EXTRA_PARAM" => "" 
        ),
        "enabled"    => array(        
            "LABEL"                  => _tr("Enabled"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "CHECKBOX",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        )
    );
    return $arrFields;
}

function saveJail($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $arrFormNew = createFieldFormNew();
    $oForm      = new paloForm($smarty, $arrFormNew);
    $mode       = getParameter("mode");
    $maxretry   = getParameter("maxretry");
    $bantime    = getParameter("bantime");
    $ignoreip   = getParameter("ignoreip");
    $enabled    = getParameter("enabled");

    $id_except  = getParameter('idtemp');
    $isError    = false;

    // Be sure ignoreip in fail2ban is separated with spaces (and one space) only
    $ignoreip = str_replace(","," ",$ignoreip);
    $ignoreip = preg_replace('/\s+/', ' ', $ignoreip);
    
    $iJails = new IssabelF2BService($pDB);

    if($maxretry == null){
            $strErrorMsg = _tr("The following fields contain errors".":</b><br/>");
            $strErrorMsg .= "IP:[Is Empty]";
            $isError = true;
    }else if($bantime == null){
            $strErrorMsg = _tr("The following fields contain errors".":</b><br/>");
            $strErrorMsg .= "Netmask:[Is Empty]";
            $isError = true;
    }
    
    if($enabled == 'on' || $enabled == 'On' || $enabled == 'ON') {
        $enabled = '1';
    } else {
        $enabled = '0';
    }

    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
        $arrErrores = $oForm->arrErroresValidacion;
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k: [$v[mensaje]] <br /> ";
            }
        }
        $isError = true;
    }

    if($isError){ // validation errors
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", $strErrorMsg);
        return NewViewZone($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
    }

    if($iJails->updateJail($id_except, $maxretry, $bantime, $ignoreip, $enabled)) {

        $iJails->F2BUpdateJails($pDB);
    
        $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Update correctly"));
        }
        else{
           $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", $iJails->errMsg);
            return NewViewZone($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
        }
    
    return reportJails($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("save"))
        return "save";
    else if(getParameter("edit"))
        return "edit";
    else if(getParameter("cancel"))
        return "cancel";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else if(getParameter("action")=="view") //Get parameter by GET (command pattern, links)
        return "view";
    else if(getParameter("start")) //Get parameter by GET (command pattern, links)
        return "start";
    else if(getParameter("stop")) //Get parameter by GET (command pattern, links)
        return "stop";
    else
        return "report";
}

?>
