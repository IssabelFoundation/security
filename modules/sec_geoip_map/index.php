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
  $Id: index.php, Thu 13 May 2021 06:31:37 PM EDT, nicolas@issabel.com
  */

function _moduleContent(&$smarty, $module_name) {
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/issabelAttacks.class.php";

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

    $pDB     = new paloDB($arrConf['dsn_conn_database']);
    $pAttack = new issabelAttacks($pDB);

    $action = getAction();
    if($action=="") {
        $content = viewGeoIP_MAP($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    } else {
        $return = '';
        switch($action) {
            case 'block':
                $pais = getParameter('block');
                $sComando = '/usr/bin/issabel-helper fwconfig --block_country='.escapeshellarg($pais);
                exec($sComando, $output, $ret);
                $return = "$pais blocked";
                break;
            case 'unblock':
                $pais = getParameter('unblock');
                $sComando = '/usr/bin/issabel-helper fwconfig --whitelist_country='.escapeshellarg($pais);
                exec($sComando, $output, $ret);
                $return = "$pais whitelisted";
                break;
            case 'noneblock':
                $pais = getParameter('noneblock');
                $sComando = '/usr/bin/issabel-helper fwconfig --none_country='.escapeshellarg($pais);
                exec($sComando, $output, $ret);
                $return = "$pais delisted";
                break;
            case 'f2b':
                $sComando = '/usr/bin/issabel-helper fwconfig --listfail2banblocked';
                $output = $ret = NULL;
                exec($sComando, $output, $ret);
                $return = isset($output[0])?$output[0]:'';
                break;
            case 'homecountry':
                $sComando = '/usr/bin/issabel-helper fwconfig --homecountry';
                $output = $ret = NULL;
                exec($sComando, $output, $ret);
                $return = isset($output[0])?$output[0]:'';
                break;
            case 'pass':
                $sComando = '/usr/bin/issabel-helper fwconfig --listgeopass';
                $output = $ret = NULL;
                exec($sComando, $output, $ret);
                $return = isset($output[0])?$output[0]:'';
                break;
             case 'blocked':
                $sComando = '/usr/bin/issabel-helper fwconfig --listgeoblocked';
                $output = $ret = NULL;
                exec($sComando, $output, $ret);
                $return = isset($output[0])?$output[0]:'';
                break;
             case 'getattacks':
                 $result = $pAttack->getAttacks();
                 header('Content-type: application/json; charset=utf-8');
                 echo json_encode($result,1);
                 break;
            default:
                break;
        }
        return $return;
    }
}

function viewGeoIP_MAP($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang) {
    $lang = get_language();
    $smarty->assign("lang",strtoupper($lang));
    $smarty->assign("LANG",$arrLang);
    $smarty->assign('module_name', $module_name);
    $content = $smarty->fetch("$local_templates_dir/map.tpl");
    return $content;
}

function getAction() {
    if(getParameter("block")) //Get parameter by POST (submit)
        return "block";
    else if(getParameter("unblock"))
        return "unblock";
    else if(getParameter("noneblock"))
        return "noneblock";
    else if(getParameter("f2b")) 
        return "f2b";
    else if(getParameter("homecountry")) 
        return "homecountry";
    else if(getParameter("pass"))
        return "pass";
    else if(getParameter("blocked"))
        return "blocked";
    else if(getParameter("getattacks"))
        return "getattacks";
    else
        return ""; 
}

?>
