<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.2                                               |
  | http://www.elastix.com                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
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
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.2 2010-12-10 03:09:32 Alberto Santos asantos@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoPortService.class.php";

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
            $content = savePuerto($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'new': case 'view': case 'edit':
            $content = NewViewPuerto($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $accion);
            break;
        case 'delete':
            $content = deletePuertos($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'cancel':
            $content = reportPuertos($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportPuertos($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportPuertos($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pPuertos = new paloSantoPortService($pDB);

    $field_type = getParameter("filter_type");
    $field_pattern = getParameter("filter_txt");
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->addNew("new",_tr("Define Port"));
    $oGrid->deleteList("Are you sure you wish to delete the port(s).?","delete",_tr("Delete"));

    $totalPuertos = $pPuertos->ObtainNumPuertos($field_type, $field_pattern);

    $limit  = 20;
    $total  = $totalPuertos;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->setTitle(_tr("Define Ports"));
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
    $arrResult = $pPuertos->ObtainPuertos($limit, $offset, $field_type, $field_pattern);
    $button_eliminar = "";
    $arrColumns = array($button_eliminar,_tr("Name"),_tr("Protocol"),_tr("Details"),_tr("Option"));
    $oGrid->setColumns($arrColumns);
    if( is_array($arrResult) && $total>0 ){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = "<input type='checkbox' name='".$value['id']."' id='".$value['id']."'>";
            $arrTmp[1] = $value['name'];
            $arrTmp[2] = $value['protocol'];
            if($value['protocol'] == "TCP" || $value['protocol'] == "UDP"){
                $port = $value['details'];
                $arrTmp[3] = ( stripos($port,":") === false ) ? _tr('Port')."  ".$value['details'] : _tr('Ports')."  ".$value['details'];
            }elseif($value['protocol'] == "ICMP"){
                $arr = explode(":",$value['details']);
                if(isset($arr[1]))
                    $arrTmp[3] = "Type: ".$arr[0]." Code: ".$arr[1];
            }else
                $arrTmp[3] = "Protocol Number: ".$value['details'];
            $arrTmp[4] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$value['id']."'>"._tr('View')."</a>";
            $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);
    //begin section filter
    $arrFormFilterPuertos = createFieldForm();
    $oFilterForm = new paloForm($smarty, $arrFormFilterPuertos);
    $smarty->assign("SHOW", _tr("Show"));

    $_POST["filter_type"]  = $field_type;
    $_POST["filter_txt"] = $field_pattern;

    if(is_null($field_type) || $field_type==""){
        $nameFieldType = "";
    }else{
        $nameFieldType = $arrFormFilterPuertos["filter_type"]["INPUT_EXTRA_PARAM"][$field_type];
    }

    $oGrid->addFilterControl(_tr("Filter applied: ").$nameFieldType." = ".$field_pattern,$_POST, array("filter_type" => "name","filter_txt" => "x"));

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid();
    if (strpos($contenidoModulo, '<form') === FALSE)
        $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>$contenidoModulo</form>";
    //end grid parameters

    return $contenidoModulo;
}

function createFieldForm()
{
    $arrType = array("name" => _tr("Name"), "protocol" => _tr("Protocol"));

    $arrFormElements = array(
            "filter_type"  => array(   "LABEL"                  => _tr("Search"),
                                       "REQUIRED"               => "no",
                                       "INPUT_TYPE"             => "SELECT",
                                       "INPUT_EXTRA_PARAM"      => $arrType,
                                       "VALIDATION_TYPE"        => "text",
                                       "VALIDATION_EXTRA_PARAM" => ""),
            "filter_txt"   => array(   "LABEL"                  => "",
                                       "REQUIRED"               => "no",
                                       "INPUT_TYPE"             => "TEXT",
                                       "INPUT_EXTRA_PARAM"      => array("id" => "filter_value"),
                                       "VALIDATION_TYPE"        => "text",
                                       "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}

function NewViewPuerto($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $action)
{
    $arrFormNew = createFieldFormNew();
    $oForm = new paloForm($smarty, $arrFormNew);
    $titulo = "";
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");
    $protocol = getParameter("protocol");

    if( $action == 'new' )
    {
        $smarty->assign("SAVE", _tr("Save"));
        if($protocol=="ICMP"){
            $smarty->assign("port_style", "style = 'display:none;'");
            $smarty->assign("protocol_style", "style = 'display:none;'");
        }elseif($protocol=="IP"){
            $smarty->assign("port_style", "style = 'display:none;'");
            $smarty->assign("type_style", "style = 'display:none;'");
            $smarty->assign("code_style", "style = 'display:none;'");
        }else{
            $smarty->assign("protocol_style", "style = 'display:none;'");
            $smarty->assign("type_style", "style = 'display:none;'");
            $smarty->assign("code_style", "style = 'display:none;'");
        }
        $titulo = _tr('Define Port');
    }
    else if( $action == 'edit' )
    {
        $id = $_POST['idtemp'];

        $smarty->assign("IDTEMP", $id);
        $smarty->assign("SAVE", _tr("Save"));

        $oPalo = new paloSantoPortService($pDB);
        $result = $oPalo->loadPuerto($id);

        $smarty->assign("EDIT", _tr("Edit"));
        $smarty->assign("IDTEMP", $id);

        $_POST['name'] = $result['name'];
        $_POST['protocol'] = $result['protocol'];

        if($result['protocol'] == "TCP" || $result['protocol'] == "UDP"){
            $hasGuion = 'yes';
            $arrPort = explode(':', $result['details'] );
            $_POST['port'] = $arrPort[0];
            $_POST['port2'] = isset( $arrPort[1] ) ? $arrPort[1] : '';
            $smarty->assign("type_style", "style = 'display:none;'");
            $smarty->assign("code_style", "style = 'display:none;'");
            $smarty->assign("protocol_style", "style = 'display:none;'");
            if( $_POST['port2'] == '' ) $hasGuion = 'no';
            $smarty->assign("HAS", $hasGuion);
        }elseif($result['protocol'] == "ICMP"){
               $smarty->assign("port_style", "style = 'display:none;'");
               $smarty->assign("protocol_style", "style = 'display:none;'");
               $value = explode(":",$result['details']);
               $_POST['type'] = $value[0];
               $_POST['code'] = $value[1];
        }else{
               $smarty->assign("port_style", "style = 'display:none;'");
               $smarty->assign("type_style", "style = 'display:none;'");
               $smarty->assign("code_style", "style = 'display:none;'");
               $_POST['protocol_number'] = $result['details'];
        }


        $_POST['comment'] = $result['comment'];

        $titulo = _tr('Edit Port');
    }
    else if( $action == 'view' )
    {
        $id = $_GET['id'];
        $oPalo = new paloSantoPortService($pDB);
        $result = $oPalo->loadPuerto($id);

        $smarty->assign("EDIT", _tr("Edit"));
        $smarty->assign("IDTEMP", $id);

        $_POST['name'] = $result['name'];
        $_POST['protocol'] = $result['protocol'];

        if($result['protocol'] == "TCP" || $result['protocol'] == "UDP"){
            $hasGuion = 'yes';
            $arrPort = explode(':', $result['details'] );
            $_POST['port'] = $arrPort[0];
            $_POST['port2'] = isset( $arrPort[1] ) ? $arrPort[1] : '';
            $smarty->assign("type_style", "style = 'display:none;'");
            $smarty->assign("code_style", "style = 'display:none;'");
            $smarty->assign("protocol_style", "style = 'display:none;'");
            if( $_POST['port2'] == '' ) $hasGuion = 'no';
            $smarty->assign("HAS", $hasGuion);
        }elseif($result['protocol'] == "ICMP"){
               $smarty->assign("port_style", "style = 'display:none;'");
               $smarty->assign("protocol_style", "style = 'display:none;'");
               $value = explode(":",$result['details']);
               $_POST['type'] = $value[0];
               $_POST['code'] = $value[1];
        }else{
               $smarty->assign("port_style", "style = 'display:none;'");
               $smarty->assign("type_style", "style = 'display:none;'");
               $smarty->assign("code_style", "style = 'display:none;'");
               $_POST['protocol_number'] = $result['details'];
        }
        $_POST['comment'] = $result['comment'];

        $oForm->setViewMode();
        $titulo = _tr('View Port');


    }

    $smarty->assign("MODE", $action);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $titulo, $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldFormNew()
{
    $arrProtocols = array('TCP' => 'TCP', 'UDP' => 'UDP', 'ICMP' => 'ICMP', 'IP' => 'IP');

    $arrFields = array(
            "name"      => array(   "LABEL"                  => _tr("Name"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
            "protocol"  => array(   "LABEL"                  => _tr("Protocol"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrProtocols,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => "",
                                    "ONCHANGE"               => "hideField(this.value)"),
            "port"      => array(   "LABEL"                  => _tr("Port"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:76px"),
                                    "VALIDATION_TYPE"        => "numeric",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
            "port2"     => array(   "LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:76px"),
                                    "VALIDATION_TYPE"        => "numeric",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
            "type"     => array(    "LABEL"                  => _tr("Type"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[a-zA-Z0-9]+$" ),
            "code"     => array(    "LABEL"                  => _tr("Code"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
                                    "VALIDATION_TYPE"        => "numeric",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
    "protocol_number"  => array(    "LABEL"                  => _tr("Protocol Number"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:162px"),
                                    "VALIDATION_TYPE"        => "numeric",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
            "comment"   => array(   "LABEL"                  => _tr("Comment"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXTAREA",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => "" ),
            );

    return $arrFields;
}

function savePuerto($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $arrFormNew = createFieldFormNew();
    $oForm      = new paloForm($smarty, $arrFormNew);
    $mode       = getParameter('mode');
    $name       = getParameter("name");
    $protocol   = getParameter("protocol");
    $port1      = getParameter("port");
    $port2      = getParameter("port2");
    $port       = ($port2 == "") ? $port1 : $port1.":".$port2;
    $type       = getParameter("type");
    $code       = getParameter("code");
    $protocol_number = getParameter("protocol_number");
    $comment    = getParameter("comment");
    $id_except  = ( $mode == 'new' ) ? 0 : getParameter('idtemp');
    $isError    = false;

    $oPalo = new paloSantoPortService($pDB);
    $portName = "";
    if($oPalo->hasPuerto($protocol, $port, $type, $code, $protocol_number, $portName, $id_except) == true ){
        $strErrorMsg = _tr("This port had already been defined").": $portName";
        $isError = true;
    }
    else if(($protocol == "TCP" || $protocol == "UDP") && ($name=="" || $port1=="")){
        $strErrorMsg = _tr("The name and the port can not be empty fields");
        $isError = true;
    }
    else if($protocol == "ICMP" && ($name=="" || $type=="" || $code=="")){
        $strErrorMsg = _tr("The name, type and code can not be empty fields");
        $isError = true;
    }
    else if($protocol == "IP" && ($name=="" || $protocol_number=="")){
        $strErrorMsg = _tr("The name and the protocol number can not be empty fields");
        $isError = true;
    }
    else if(!$oForm->validateForm($_POST)) {
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

    $desactivated = false;
    if($isError){ // validation errors
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", $strErrorMsg);
        return NewViewPuerto($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
    }
    else if($mode == 'new'){
        if($oPalo->savePuertos($name, $protocol, $port, $type, $code, $protocol_number, $comment)){
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Save correctly"));
        }
        else{
            $smarty->assign("mb_title", "Error");
            $smarty->assign("mb_message", $oPalo->errMsg);
            return NewViewPuerto($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
        }
    }
    else{//edit
        if($oPalo->updatePuertos($id_except, $name, $protocol, $port, $type, $code, $protocol_number, $comment, $desactivated)){
	    $msg = _tr("Update correctly");
	    if($desactivated)
		$msg .= "<br />"._tr("Some rules could be deactivated due to these changes, please check the firewall rules");
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", $msg);
        }
        else{
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", $oPalo->errMsg);
            return NewViewPuerto($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $mode);
        }
    }
    return reportPuertos($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function deletePuertos($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $oPalo = new paloSantoPortService($pDB);
    $str_msj_error = "";

    foreach( $_POST as $key => $value ){
        if( $value == "on" ){
	    $port = "";
	    if(!$oPalo->isPortInService($key, $port)){
		if( $oPalo->deletePuerto($key) == false )
		    $str_msj_error .= $oPalo->errMsg."<br />";
	    }
	    else
		$str_msj_error .= _tr("Port used in a firewall rule").": $port[name]. "._tr("You have to delete the rule related in order to delete this port")."<br />";
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

    return reportPuertos($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}


function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    if(getParameter("delete")) //Get parameter by POST (submit)
        return "delete";
    else if(getParameter("new"))
        return "new";
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
    else
        return "report";
}
?>
