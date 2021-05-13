<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4                                                    |
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
  | The Initial Developer of the Original Code is Issabel Foundation     |
  +----------------------------------------------------------------------+
  $Id: index.php, Thu 13 May 2021 06:21:48 PM EDT, German Venturino german@issabel.com
*/
//include issabel framework
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/issabelGeoIP_KEY.class.php";

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
    //$pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB = "";

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewGeoIP_KEY($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default: // view_form
            $content = viewFormGeoIP_KEY($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormGeoIP_KEY($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pGeoIP_KEY = new issabelGeoIP_KEY($pDB);
    $arrFormGeoIP_KEY = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormGeoIP_KEY);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataGeoIP_KEY = $pGeoIP_KEY->getGeoIP_KEYById($id);
        if(is_array($dataGeoIP_KEY) & count($dataGeoIP_KEY)>0)
            $_DATA = $dataGeoIP_KEY;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pGeoIP_KEY->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("GeoIP KEY"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewGeoIP_KEY($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pGeoIP_KEY = new issabelGeoIP_KEY($pDB);
    $arrFormGeoIP_KEY = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormGeoIP_KEY);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormGeoIP_KEY($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE
        $key = stripslashes(trim(getParameter('key')));
        $respuesta = array();
        exec('/usr/bin/issabel-helper manage_geoip_key save '.$key, $respuesta, $retorno);
        $output = implode(" ",$respuesta);
        $output2      = trim($output);
        
        if ($retorno == 0){ 
        $smarty->assign("mb_message", _tr("updated"));
        } else {
        $smarty->assign("mb_message", _tr("ERROR"));
        }
        $content = viewFormGeoIP_KEY($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    return $content;
}

function createFieldForm()
{
    $respuesta = array();
    exec('/usr/bin/issabel-helper manage_geoip_key get ', $respuesta, $retorno);
    $output = implode(" ",$respuesta);
    $saved_key      = trim($output);
    if (!isset($_POST['key'])) {
        $_POST['key'] = $saved_key;
    }
    $arrOptions = array('val1' => 'Value 1', 'val2' => 'Value 2', 'val3' => 'Value 3');

    $arrFields = array(
            "key"   => array( "LABEL"                  => _tr("key"),
                              "REQUIRED"               => "no",
                              "INPUT_TYPE"             => "TEXT",
                              "INPUT_EXTRA_PARAM"      => "",
                              "VALIDATION_TYPE"        => "text",
                              "VALIDATION_EXTRA_PARAM" => ""
                            ),

            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
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
