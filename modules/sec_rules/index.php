<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2017 Issabel Foundation                                |
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
  $Id: index.php, Fri 05 Jun 2020 12:38:07 PM EDT, nicolas@issabel.com
*/
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "modules/sec_ports/libs/paloSantoPortService.class.php";
include_once "libs/paloSantoJSON.class.php";
require_once "libs/paloSantoNetwork.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoRules.class.php";

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
    $accion = getAction();

    switch($accion){
        case "new":
            $content = constructForm($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, array());
            break;
        case "desactivatefirewall":
            $content = desactivateFirewallJSON($smarty,$module_name,$local_templates_dir,$pDB,$arrConf);
            break;
        case "activatefirewall":
            $content = activateFirewallJSON($smarty,$module_name,$local_templates_dir,$pDB,$arrConf);
            break;
        case "save":
            $content = saveRulesAjax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "getPorts":
            $content = getPorts($pDB);
            break;
        case "getRules":
            $content = getRules($pDB);
            break;
        case "getRulesGEOIP":
            $content = getRulesGEOIP($pDB);
            break;
        case "sort":
            $content = dosort($pDB);
            break;
        case "deleterules":
            $content = deleteRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "isexecute":
            $content = isexecute($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = showDataTables($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function dosort($pDB) {
    $pRules = new paloSantoRules($pDB);
    $data = json_decode($_POST['json'],1);
    $sum=0;
    if(isset($_POST['geoip'])) {
        $sum=100000;
    }
    $pRules->resort($data,$sum);
    return "{\"message\":\"ok\"}";
}

function constructForm($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrValues, $action="")
{
    $arrFormRules = createFieldForm($pDB,$arrValues);
    $oForm = new paloForm($smarty,$arrFormRules);
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "modules/$module_name/images/security_firewall_rules.png");
    $traffic = isset($arrValues['id_traffic']) ? $arrValues['id_traffic'] : "";
    $select_traffic_1 = ($traffic == "INPUT"  ) ? "selected" : "";
    $select_traffic_2 = ($traffic == "OUTPUT" ) ? "selected" : "";
    $select_traffic_3 = ($traffic == "FORWARD") ? "selected" : "";
    //************************************************************************
    $traffic_html =
        "<select id='id_traffic' name='id_traffic' onChange='showElementByTraffic();' >".
        "<option value='INPUT'   $select_traffic_1>"._tr("INPUT")."</option>".
        "<option value='OUTPUT'  $select_traffic_2>"._tr("OUTPUT")."</option>".
        "<option value='FORWARD' $select_traffic_3>"._tr("FORWARD")."</option>".
        "</select>";
    $smarty->assign("action_detail", _tr("ACTION DETAIL"));
    $smarty->assign("ip_detail", _tr("IP DETAILS"));
    $smarty->assign("traffic_html", $traffic_html);
    $smarty->assign("traffic_label", _tr("Traffic"));
    //************************************************************************
    $protocol = isset($arrValues['id_protocol']) ? $arrValues['id_protocol'] : "";
    $protocol1 = ($protocol == "ALL") ? "selected" : "";
    $protocol2 = ($protocol == "TCP") ? "selected" : "";
    $protocol3 = ($protocol == "UDP") ? "selected" : "";
    $protocol4 = ($protocol == "ICMP") ? "selected" : "";
    $protocol5 = ($protocol == "IP") ? "selected" : "";
    $protocol6 = ($protocol == "STATE") ? "selected" : "";
    $protocol7 = ($protocol == "GEOIP") ? "selected" : "";

    $protocol_html =
        "<select id='id_protocol' name='id_protocol' onChange='showElementByProtocol();' >".
        "<option value='ALL' $protocol1>"._tr("ALL")."</option>".
        "<option value='TCP' $protocol2>TCP</option>".
        "<option value='UDP' $protocol3>UDP</option>".
        "<option value='ICMP' $protocol4>ICMP</option>".
        "<option value='IP' $protocol5>IP</option>".
        "<option value='STATE' $protocol6>"._tr("STATE")."</option>".
        "<option value='GEOIP' $protocol7>"._tr("GEOIP")."</option>".
        "</select>";

    $smarty->assign("protocol_html", $protocol_html);
    $smarty->assign("protocol_label", _tr("Protocol"));
    $smarty->assign("protocol_detail", _tr("PROTOCOL DETAILS"));
    //************************************************************************
    $arrValues['ip_source'] = (isset($arrValues['ip_source'])) ? $arrValues['ip_source'] : "0.0.0.0";
    $arrValues['mask_source'] = (isset($arrValues['mask_source'])) ? $arrValues['mask_source'] : "24";
    $arrValues['ip_destin'] = (isset($arrValues['ip_destin'])) ? $arrValues['ip_destin'] : "0.0.0.0";
    $arrValues['mask_destin'] = (isset($arrValues['mask_destin'])) ? $arrValues['mask_destin'] : "24";
    $arrValues['geoipcountries'] = (isset($arrValues['geoipcountries'])) ? $arrValues['geoipcountries'] : "";
    $arrValues['geoipcontinents'] = (isset($arrValues['geoipcontinents'])) ? $arrValues['geoipcontinents'] : "";

    if($action == "edit") {
        $title = _tr("Edit Rule");
    } else {
        $title = _tr("New Rule");
    }
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",$title, $arrValues);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldForm($pDB,$arrValues = array())
{

    global $arrConf;

    $pDBcountries = new paloDB($arrConf['dsn_conn_database_countries']);
    $query = "SELECT continents.name AS continent,countries.code AS code,countries.name AS country ";
    $query.= "FROM countries LEFT JOIN continents ON continent_code=continents.code ";
    $query.= "ORDER BY continent_code,countries.name";
    $arrCountries = $pDBcountries->fetchTable($query, true);
    $arrPais = array();
    $continent='';
    foreach($arrCountries as $idx=>$data) {
        $arrPais[$data['continent']][$data['code']]=$data['country'];
    }

    $query = "SELECT code,name FROM continents";
    $arrContinents = $pDBcountries->fetchTable($query, true);
    $arrContinente = array();
    $continent='';
    foreach($arrContinents as $idx=>$data) {
        $arrContinente[$data['code']]=$data['name'];
    }

    $oPort = new paloSantoPortService($pDB);
    $pRules = new paloSantoRules($pDB);
    if(isset($arrValues['id_protocol']))
        $Ports = ($arrValues['id_protocol'] == "TCP") ? $oPort->getTCPortNumbers() : $oPort->getUDPortNumbers();
    else
        $Ports = $oPort->getTCPortNumbers();
    $type = $oPort->getICMPType();
    $protocol_number = $oPort ->getIPProtNumber();
    $arrInterface['ANY'] = _tr('ANY');
    $arrInterfacetmp = $pRules->obtener_nombres_interfases_red();

    $vlan_dictionary = array();
    $vlan_dictionary['eth0.100']='WAN';
    $vlan_dictionary['eth0.200']='LAN';

    $hideNic=array();
    foreach($arrInterfacetmp as $dev=>$name) {
        if(preg_match("/\./",$dev)) {
            // if vlan, hide parent/real nic
            $parts = preg_split("/\./",$dev);
            $hideNic[] = $parts[0];
        } else if(preg_match("/^dummy/",$dev)) {
            $hideNic[] = $dev;
        }
        if($dev==$name) {
            if(isset($vlan_dictionary[$dev])) $arrInterfacetmp[$dev]=$vlan_dictionary[$dev];
        }
    }
    foreach($hideNic as $nic) {
        unset($arrInterfacetmp[$nic]);
    }

    foreach($arrInterfacetmp as $key => $value)
        $arrInterface[$key] = $value;
    $arrTarget    = array("ACCEPT" => _tr("ACCEPT"), "DROP" => _tr("DROP"), "REJECT" => _tr("REJECT"));
    $arrType['ANY'] = _tr('ANY');
    foreach($type as $key => $value){
        $arrType[$value["id"]] = $value["name"];
    }
    $arrPort['ANY'] = _tr('ANY');
    foreach($Ports as $key => $value){
        $arrPort[$value['id']] = $value['name'];
    }
    $arrIP['ANY'] = _tr('ANY');
    foreach($protocol_number as $key => $value){
        $arrIP[$value['id']] = $value['name'];
    }
    $arrFields = array(
            "interface_in"    => array( "LABEL"                  => _tr("Interface IN"),
                "REQUIRED"               => "no",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $arrInterface,
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
                "EDITABLE"               => "yes",
                ),
            "interface_out"   => array( "LABEL"                  => _tr("Interface OUT"),
                "REQUIRED"               => "no",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $arrInterface,
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
                "EDITABLE"               => "yes",
                ),
            "ip_source"       => array( "LABEL"                  => _tr("IP Source"),
                "REQUIRED"               => "no",
                "INPUT_TYPE"             => "TEXT",
                "INPUT_EXTRA_PARAM"      => array("style" => "width:110px", "class" => "frm-control"),
                "VALIDATION_TYPE"        => "ereg",
                "VALIDATION_EXTRA_PARAM" => "^([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})$"
                ),
            "mask_source"     => array( "LABEL"                  => "mask_source",
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => array("style" => "width:20px", "class" => "frm-control"),
                    "VALIDATION_TYPE"        => "numeric",
                    "VALIDATION_EXTRA_PARAM" => ""
                    ),
            "ip_destin"      => array(  "LABEL"                  => _tr("IP Destiny"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => array("style" => "width:110px", "class" => "frm-control"),
                    "VALIDATION_TYPE"        => "ereg",
                    "VALIDATION_EXTRA_PARAM" => "^([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})$"
                    ),
            "mask_destin"     => array( "LABEL"                  => "mask_destiny",
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => array("style" => "width:20px", "class" => "frm-control"),
                    "VALIDATION_TYPE"        => "numeric",
                    "VALIDATION_EXTRA_PARAM" => ""
                    ),
            "port_in"         => array( "LABEL"                  => _tr("Port Source"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrPort,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "port_out"        => array( "LABEL"                  => _tr("Port Destine"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrPort,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "type_icmp"       => array( "LABEL"                  => _tr("Type"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrType,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "id_ip"           => array( "LABEL"                  => _tr("ID"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrIP,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "established"     => array( "LABEL"                  => _tr("Established"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "CHECKBOX",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "related"         => array( "LABEL"                  => _tr("Related"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "CHECKBOX",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "geoipcountries"  => array( "LABEL"                  => _tr("Countries"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrPais,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    "MULTIPLE"               => TRUE
                    ),
            "geoipcontinents"  => array( "LABEL"                  => _tr("Continents"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrContinente,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    "MULTIPLE"               => TRUE
                    ),

            //REJECT, ACCEPT, DROP
            "target"          => array( "LABEL"                  => _tr("Target"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrTarget,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),

            "orden"           => array(  "LABEL"                  => _tr("Order"),
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "id"              => array(  "LABEL"                  => "",
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    ),
            "state"           => array(  "LABEL"                  => "",
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => "",
                    "EDITABLE"               => "yes",
                    )

                );
    return $arrFields;
}

function saveRulesAjax($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $arrValues = array();
    $str_error = "";
    $arrFormNew = createFieldForm($pDB);
    $oForm = new paloForm($smarty, $arrFormNew);
    $id = getParameter("id");
    $arrValues['id'] = $id;
    if($id == "") {
        $state = "new";
    } else {
        $state = "edit";
    }
    //************************************************************************************************************
    //** TRAFFIC **
    //************************************************************************************************************
    $arrValues['traffic'] = getParameter("id_traffic");
    if( $arrValues['traffic'] == "INPUT" ){
        $arrValues['interface_in'] = getParameter("interface_in");
        if( strlen($arrValues['interface_in']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_in" : ", interface_in" ;

        $arrValues['interface_out'] = null;
    }
    else if( $arrValues['traffic'] == "OUTPUT" ){
        $arrValues['interface_out'] = getParameter("interface_out");
        if( strlen($arrValues['interface_out']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_out" : ", interface_out" ;

        $arrValues['interface_in'] = null;
    }
    else if( $arrValues['traffic'] == "FORWARD" )
    {
        $arrValues['interface_in'] = getParameter("interface_in");
        if( strlen($arrValues['interface_in']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_in" : ", interface_in" ;

        $arrValues['interface_out'] = getParameter("interface_out");
        if( strlen($arrValues['interface_out']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_out" : ", interface_out" ;
    }

    //************************************************************************************************************
    //** SOURCE **
    //************************************************************************************************************

    $arrValues['ip_source'] = getParameter("ip_source");
    $arrValues['mask_source'] = getParameter("mask_source");
    $arrValues['ip_destin'] = getParameter("ip_destin");
    $arrValues['mask_destin'] = getParameter("mask_destin");

    //************************************************************************************************************
    //** PROTOCOL **
    //************************************************************************************************************

    $arrValues['protocol'] = getParameter("id_protocol");
    if( $arrValues['protocol'] == 'TCP' || $arrValues['protocol'] == 'UDP' )
    {
        $arrValues['port_in'] = getParameter("port_in");
        if( strlen($arrValues['port_in']) == 0 ) $str_error .= ( strlen($str_error) == 0 ) ? "port_in" : ", port_in" ;

        $arrValues['port_out'] = getParameter("port_out");
        if(strlen($arrValues['port_out']) == 0) $str_error .= ( strlen($str_error) == 0 ) ? "port_out" : ", port_out" ;

        $arrValues['type_icmp'] = null;
        $arrValues['id_ip'] = null;
        $arrValues['state'] = "";
    }
    else if( $arrValues['protocol'] == 'ICMP' )
    {
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;
        $arrValues['state'] = "";

        $arrValues['type_icmp'] = getParameter("type_icmp");
        if( strlen($arrValues['type_icmp']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "type" : ", type";

        $arrValues['id_ip'] = null;
    }
    else if( $arrValues['protocol'] == 'IP' )
    {
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;
        $arrValues['type_icmp'] = null;
        $arrValues['state'] = "";

        $arrValues['id_ip'] = getParameter("id_ip");
        if( strlen($arrValues['id_ip']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "id" : ", id";
    }
    else if($arrValues['protocol'] == 'STATE'){
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;
        $arrValues['type_icmp'] = null;
        $arrValues['id_ip'] = null;
        $established = getParameter("established");
        $related = getParameter("related");
        if($established == "on"){
            $arrValues['state'] = "Established";
            if($related == "on")
                $arrValues['state'].= ",Related";
        }
        else{
            if($related == "on")
                $arrValues['state'] = "Related";
            else{
                $str_error .= ( strlen($str_error) == 0) ? _tr("You have to select at least one state") : ", "._tr("You have to select at least one state");
            }
        }
    } else if($arrValues['protocol'] == 'GEOIP') {
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;
        $arrValues['type_icmp'] = null;
        $arrValues['id_ip'] = null;
        $geoipcountries = getParameter("geoipcountries");
        $geoipcontinents = getParameter("geoipcontinents");

        if($geoipcountries == '' && $geoipcontinents == '') {
            $str_error .= _tr("You have to list some countries or continents");
        }

        $arrValues['geoipcountries'] = $geoipcountries;
        $arrValues['geoipcontinents'] = $geoipcontinents;

    } else {
        $arrValues['port_in'] = "";
        $arrValues['port_out'] = "";
        $arrValues['type_icmp'] = "";
        $arrValues['id_ip'] = "";
        $arrValues['state'] = "";
    }

    //************************************************************************************************************
    //** TARGET **
    //************************************************************************************************************

    $arrValues['target'] = getParameter("target");
    if( strlen($arrValues['target']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "target" : ", target";

    $arrValues['orden'] = getParameter("orden");
    //**********************
    //MENSSAGE ERROR
    //**********************

    if( strlen($str_error) != 0 ){
        $mb_title = "ERROR";
        $mb_message =  $str_error;
        return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
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
        $mb_title   = _tr("Validation Error");
        $mb_message = $strErrorMsg;
        return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";

    }
    else if($arrValues['mask_source'] > 32 || $arrValues['mask_destin'] > 32){
        $mb_title   = _tr("Validation Error");
        $mb_message = _tr("The bit masks must be values less than 33");
        return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
    }
    else if(($arrValues['ip_source'] != "0.0.0.0" && $arrValues['ip_source'] != "" && $arrValues['mask_source'] == "0")||($arrValues['ip_destin'] != "0.0.0.0" && $arrValues['ip_destin'] != "" && $arrValues['mask_destin'] == "0")){
        $mb_title   = _tr("Validation Error");
        $mb_message = _tr("Wrong Mask");
        return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
    }

    $arrValues['ip_source'] = ($arrValues['ip_source'] == "")? "0.0.0.0" : $arrValues['ip_source'];
    $arrValues['ip_destin'] = ($arrValues['ip_destin'] == "")? "0.0.0.0" : $arrValues['ip_destin'];
    $ipOrigen = explode(".",$arrValues['ip_source']);
    $ipDestino = explode(".",$arrValues['ip_destin']);
    if($ipOrigen[0]>255 || $ipOrigen[1]>255 || $ipOrigen[2]>255 || $ipOrigen[3]>255 || $ipDestino[0]>255 || $ipDestino[1]>255 || $ipDestino[2]>255 || $ipDestino[3]>255){
        $mb_title   = _tr("Validation Error");
        $mb_message = _tr("Wrong value for ip");
        return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
    }
    $arrValues['mask_source'] = ($arrValues['ip_source'] == "0.0.0.0") ? "0" : $arrValues['mask_source'];
    $arrValues['mask_destin'] = ($arrValues['ip_destin'] == "0.0.0.0") ? "0" : $arrValues['mask_destin'];
    $pNet = new paloNetwork();
    $oPalo = new paloSantoRules($pDB);
    if($arrValues['ip_source'] != "0.0.0.0" && $arrValues['mask_source'] != "" && $arrValues['ip_source'] != ""){
        $arrValues['ip_source'] = $pNet->getNetAdress($arrValues['ip_source'],$arrValues['mask_source']);
    }
    if($arrValues['ip_destin'] != "0.0.0.0" && $arrValues['mask_destin'] != "" && $arrValues['ip_destin'] != ""){
        $arrValues['ip_destin'] = $pNet->getNetAdress($arrValues['ip_destin'],$arrValues['mask_destin']);
    }
    if($id == ""){
        if( $oPalo->saveRule( $arrValues ) == true )
        {
            $mb_title   = _tr("MESSAGE");
            $mb_message = _tr("Successful Save");
            return "{\"status\":\"success\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
        }
        else
        {
            $mb_title   = "ERROR";
            $mb_message = $oPalo->errMsg;
            return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
        }
    } else {
        if( $oPalo->updateRule($arrValues,$id) == true )
        {
            $mb_title   =  _tr("MESSAGE");
            $mb_message = _tr("Successful Update");
            return "{\"status\":\"success\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
        }
        else
        {
            $mb_title   = "ERROR";
            $mb_message = $oPalo->errMsg;
            return "{\"status\":\"error\", \"title\":\"$mb_title\", \"message\": \"$mb_message\"}";
        }
    }
}

function isexecute($smarty,$module_name,$local_templates_dir,$pDB,$arrConf)
{
    $pRules = new paloSantoRules($pDB);
    if($pRules->isExecutedInSystem()){
        $ret = "{\"status\":\"success\",\"message\":\"yes\"}";
    } else {
        $ret = "{\"status\":\"success\",\"message\":\"no\"}";
    }
    return $ret;
}

function activateFirewallJSON($smarty,$module_name,$local_templates_dir,$pDB,$arrConf)
{
    $pRules = new paloSantoRules($pDB);
    $bFirstTime = $pRules->isFirstTime();
    $pRules->noMoreFirstTime();
    if (!$pRules->activateRules()) {
        $ret = "{\"status\":\"error\",\"title\":\"". _tr("Error during execution of rules")."\",\"message\":\"".$pRules->errMsg."\"}";
        return $ret;
    }
    if ($bFirstTime) {
        $ret = "{\"status\":\"success\",\"message\":\"". _tr("The firewall has been activated")."\"}";
    } else {
        $ret = "{\"status\":\"success\",\"message\":\"". _tr("The rules have been executed in the system")."\"}";
    }
    $pRules->updateExecutedInSystem();
    return $ret;

}

function deleteRules($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pRules = new paloSantoRules($pDB);
    $reglas = preg_split("/,/",$_POST['rules']);
    $count=0;
    foreach($reglas as $regla_id){
        $count++;
        $pRules->deleteRule($regla_id);
    }
    return "{\"status\":\"success\", \"message\":\"".sprintf(_tr("%d rules were deleted"),$count)."\"}";
}

function getPorts($pDB)
{
    $jsonObject = new PaloSantoJSON();
    $oPort = new paloSantoPortService($pDB);

    $protocol = getParameter("protocol");
    if($protocol == "TCP")
        $Ports = $oPort->getTCPortNumbers();
    else
        $Ports = $oPort->getUDPortNumbers();
    $arrPort['ANY'] = _tr('ANY');
    foreach($Ports as $key => $value){
        $arrPort[$value['id']] = $value['name'];
    }
    $jsonObject->set_message($arrPort);
    return $jsonObject->createJSON();
}

function getRules($pDB)
{
    $jsonObject = new PaloSantoJSON();
    $pRules = new paloSantoRules($pDB);

    $arrResult = $pRules->ObtainRules(1000000,0);
    $jsonObject->set_message($arrResult);
    return $jsonObject->createJSON();
}

function getRulesGEOIP($pDB)
{
    $jsonObject = new PaloSantoJSON();
    $pRules = new paloSantoRules($pDB);

    $arrResult = $pRules->ObtainRulesGEOIP(1000000,0);
    $jsonObject->set_message($arrResult);
    return $jsonObject->createJSON();
}

function desactivateFirewallJSON($smarty,$module_name,$local_templates_dir,$pDB,$arrConf)
{
    $pRules = new paloSantoRules($pDB);
    if($pRules->flushRules() && $pRules->setFirstTime()){
        $ret = "{\"status\":\"success\",\"message\":\"". _tr("The firewall has been desactivated")."\"}";
    }else{
        $ret = "{\"status\":\"error\",  \"message\":\"". _tr("The firewall could not be desactivated")."\"}";
    }
    return $ret;
}

function escapeQuote($val) {
   $val = addcslashes($val, '"');
   return $val;
}

function showDataTables($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf) {

    global $arrLang;

    $pRules = new paloSantoRules($pDB);
    $action = getParameter("action");
    $id     = getParameter("id");

    $arrLangEscaped = array_map(escapeQuote, $arrLang);

    $smarty->assign("ID", $id);
    $smarty->assign("LANG", $arrLangEscaped);

    if($pRules->hasGeoip()==true) {
        $smarty->assign("HASGEOIP", "yes");
    } else {
        $smarty->assign("HASGEOIP", "no");
    }

    if($pRules->isExecutedInSystem()==true) {
        $smarty->assign("EXECUTED", "no");
    } else {
        $smarty->assign("EXECUTED", "yes");
    }

    if($pRules->isFirstTime()==true) { 
        $smarty->assign("FIRSTTIME", "yes");
    } else {
        $smarty->assign("FIRSTTIME", "no");
    }

    if($action == 'edit'){
        $arrtmp = $pRules->getRule($id);
        $arripsource = explode("/",$arrtmp['ip_source']);
        $arripdst = explode("/",$arrtmp['ip_destiny']);
        $arrValues['id_traffic']=$arrtmp['traffic'];
        $arrValues['interface_in']=$arrtmp['eth_in'];
        $arrValues['interface_out']=$arrtmp['eth_out'];
        $arrValues['ip_source']=$arripsource[0];
        $arrValues['mask_source']=$arripsource[1];
        $arrValues['port_in']=$arrtmp['sport'];
        $arrValues['ip_destin']=$arripdst[0];
        $arrValues['mask_destin']=$arripdst[1];
        $arrValues['port_out']=$arrtmp['dport'];
        $arrValues['type_icmp']=$arrtmp['icmp_type'];
        $arrValues['id_ip']=$arrtmp['number_ip'];
        $arrValues['id_protocol']=$arrtmp['protocol'];
        $arrValues['target']=$arrtmp['target'];
        $arrValues['orden']=$arrtmp['rule_order'];
        $arrValues['state']=$arrtmp['state'];
        $arrValues['geoipcountries']=explode(",",$arrtmp['countries']);
        $arrValues['geoipcontinents']=explode(",",$arrtmp['continents']);
        $arrValues['id']=$id;
        $content = constructForm($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $action);
        return $content;
    }elseif($action == 'Activate'){
        $pRules->setActivated($id);
    }
    elseif($action == 'Desactivate'){
        $pRules->setDesactivated($id);
    }

    $smarty->assign('module_name', $module_name);
    $content = $smarty->fetch("$local_templates_dir/datatables.tpl");
    return $content;
}

function getAction()
{
    if(getParameter("new"))
        return "new";
    else if(getParameter("save"))
        return "save";
    else if(getParameter("action") == "getPorts")
        return "getPorts";
    else if(getParameter("action") == "getRules")
        return "getRules";
    else if(getParameter("action") == "getRulesGEOIP")
        return "getRulesGEOIP";
    else if(getParameter("action") == "sort")
        return "sort";
    else if(getParameter("action") == "deleterules")
        return "deleterules";
    else if(getParameter("action") == "isexecute")
        return "isexecute";
    else if(getParameter("action")=="show") 
        return "show";
    else if(getParameter("desactivatefirewall"))
        return "desactivatefirewall";
    else if(getParameter("activatefirewall"))
        return "activatefirewall";
    else
        return "report";
}
?>
