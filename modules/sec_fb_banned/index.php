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
        case 'delete':
            $content = deleteBloqueados($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
         default:
            $content = reportBloqueados($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function fail2ban_rejected()
{

    $jails  = array('sshd','asterisk','apache-auth','issabel-gui');
    $allban = array();
    foreach($jails as $jail) {
        $respuesta = array();
        exec('/usr/bin/issabel-helper fb_client status '.$jail, $respuesta, $retorno);
        $output = implode(" ",$respuesta);
        $allban[$jail] = trim($output);
    }

    $id=0;    
    $rejected=array();
    foreach($allban as $jail=>$content) {
        if(strlen($content)){
            $allbanarr = explode(" ",$content);
            foreach ($allbanarr as $v) {
                $rejected[]=array("id"=>$id,"jail"=>$jail,"ip"=>$v);
                $id++;
            }
        }
    }
    
    return $rejected;
}

function reportBloqueados($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{

    $field_type = null;
    $field_pattern = null;
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $oGrid->deleteList("Are you sure you wish to unban the record(s).?","delete",_tr("Unban"));

    $rejected=fail2ban_rejected();

    $totalBloqueados =  count($rejected);    
    $limit  = 20;
    $total  = $totalBloqueados;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->setTitle(_tr("Rejected IP"));
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
    
    $arrResult = array_slice($rejected, $offset, ($totalBloqueados-$offset) < $limit ? ($totalBloqueados-$offset) : $limit);    
    $button_eliminar = "";
    $arrColumns = array($button_eliminar,_tr("Jail"),_tr("IP"),_tr("Country"));
    $oGrid->setColumns($arrColumns);
    if( is_array($arrResult) && $total>0 ){
        foreach($arrResult as $key => $value){
            $country = geoip_country_name_by_name($value['ip']);
            $code    = geoip_country_code_by_name($value['ip']);
            $arrTmp[0] = "<input type='checkbox' name='".$value['id']."' id='".$value['id']."'>";
            $arrTmp[1] = $value['jail'];
            $arrTmp[2] = $value['ip'];
            $bandera = "<div class='flag ".strtolower($code)."'></div>";
            $arrTmp[3] = $country." ".$bandera;



            $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    $contenidoModulo = $oGrid->fetchGrid();
    if (strpos($contenidoModulo, '<form') === FALSE)
        $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>$contenidoModulo</form>";
    //end grid parameters

    $attribution="<div style='font-size:8px;'>Geolocation data provided either by <a href='https://db-ip.com'>DB-IP</a> or <a href='https://www.maxmind.com'>MaxMind</a></div>";
    return $contenidoModulo.$attribution;
}

function deleteBloqueados($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $str_msj_error = "Error";

    $rejected=fail2ban_rejected();
    $error=0;
    foreach( $_POST as $key => $value ){
        if( $value == "on" ){
            $jail = strtolower($rejected[$key]["jail"]);
            $ip   = $rejected[$key]["ip"];    

            exec("/usr/bin/issabel-helper fb_client unban ".escapeshellarg($jail)." ".escapeshellarg($ip), $respuesta, $retorno);
            if($retorno==1) { $error=1; }
        }
    }
    if( $error == 0 ){
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("Unbanned correctly"));
    }
    else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", $str_msj_error);
    }

    return reportBloqueados($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function getAction()
{
    if(getParameter("delete")) //Get parameter by POST (submit)
        return "delete";
    else 
        return "report";
}
?>
