<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2020 Issabel Foundation                                |
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
  $Id: index.php, Sun 17 May 2020 01:06:09 PM EDT, nicolas@issabel.com
*/
//include issabel framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/Issabelwhitelist.class.php";

    //include file language agree to issabel configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case 'save':
            $content = savewhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'new': case 'view': case 'edit':
            $content = newwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $action);
            break;
        case 'delete':
            $content = deletewhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'cancel':
            $content = reportwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;



}

function reportwhitelist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pwhitelist = new Issabelwhitelist($pDB);

    $pwhitelist->checkTable();

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->addNew("new",_tr("Add IP Address"));
    $oGrid->deleteList("Are you sure you wish to delete the IP address?","delete",_tr("Delete"));
    $oGrid->setTitle(_tr("White List"));
    $oGrid->pagingShow(true); // show paging section.

    //$oGrid->enableExport();   // enable export.
    //$oGrid->setNameFile_Export(_tr("whitelist"));

    $url = array(
        "menu"         =>  $module_name,
    );
    $oGrid->setURL($url);

    $arrColumns = array("",_tr("IP Address"),_tr("Note"),);
    $oGrid->setColumns($arrColumns);

    $total   = $pwhitelist->getNumwhitelist();
    $arrData = null;
    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();
    }

    $arrResult =$pwhitelist->getwhitelist($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
            $arrTmp[0] = "<input type='checkbox' name='ips[]' value='".$value['ip_address']."'>";
            $arrTmp[1] = $value['ip_address'];
            $arrTmp[2] = $value['note'];
            $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}

function newwhitelist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $action)
{
    $arrFormNew = createFieldForm();
    $oForm = new paloForm($smarty, $arrFormNew);
    $titulo = "";
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");

    $smarty->assign("SAVE", _tr("Save"));
    $titulo = _tr('Define IP Address');

    $smarty->assign("MODE", $action);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $titulo, $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;

}

function deletewhitelist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $oIssa = new Issabelwhitelist($pDB);
    $str_msj_error = "";

    foreach( $_POST['ips'] as $key => $value ){
        if( $oIssa->deletewhitelist($value) == false ) {
            $str_msj_error .= $oIssa->errMsg."<br />";
        }
    }

    if( strlen($str_msj_error) == 0 ){
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("Delete correctly"));
    }
    else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", $str_msj_error);
    }

    return reportwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function savewhitelist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{

    $arrFormNew = createFieldForm();
    $oForm      = new paloForm($smarty, $arrFormNew);

    $oIssa = new Issabelwhitelist($pDB);
    $isError = false;

    $ip_address = $_POST['ip_address'];
    $note       = $_POST['note'];

    if($oIssa->getwhitelistByIp($ip_address) == true ){
        $strErrorMsg = _tr("This IP address had already been defined").": $portName";
        $isError = true;
    }
    else if($oIssa->validateIpOrMask($_POST['ip_address'])!="") {
        $err = $oIssa->validateIpOrMask($_POST['ip_address']);
        $strErrorMsg = "<b>"._tr('The IP address or network format is not valid');
        $isError = true;
    }

    if($isError){ // validation errors
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", $strErrorMsg);
        return newwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
    } else {
 
        if($oIssa->savewhitelist($ip_address, $note)) {
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Save correctly"));
        }
        else{
            $smarty->assign("mb_title", "Error");
            $smarty->assign("mb_message", $oIssa->errMsg);
            return newwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
        }
    }

    return reportwhitelist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function createFieldForm(){

    $arrFormElements = array(
        "ip_address" => array("LABEL"                  => _tr("IP Address"),
                              "REQUIRED"               => "no",
                              "INPUT_TYPE"             => "TEXT",
                              "INPUT_EXTRA_PARAM"      => $arrFilter,
                              "VALIDATION_TYPE"        => "ip/mask",
                              "VALIDATION_EXTRA_PARAM" => ""),
            "note" => array(  "LABEL"                  => _tr("Note"),
                              "REQUIRED"               => "no",
                              "INPUT_TYPE"             => "TEXT",
                              "INPUT_EXTRA_PARAM"      => "",
                              "VALIDATION_TYPE"        => "text",
                              "VALIDATION_EXTRA_PARAM" => ""),
    );
    return $arrFormElements;
}

function getAction()
{
    if(getParameter("save")) //Get parameter by POST (submit)
        return "save";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else
        return "report"; //cancel
}
?>
