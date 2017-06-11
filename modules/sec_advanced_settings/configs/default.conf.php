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
  $Id: default.conf.php,v 1.1 2011-05-13 11:05:31 Estefanía Morán Meza emoran@palosanto.com Exp $ */
    global $arrConf;
    global $arrConfModule;

    $arrConfModule['module_name']                 = 'sec_advanced_settings';
    $arrConfModule['templates_dir']               = 'themes';
    $arrConfModule['dsn_conn_database']           = generarDSNSistema('root','asterisk','/var/www/html/');
    $arrConfModule['arr_conf_file'] 	          = array( array("name"=>"amportal.conf",  "pass_name"=>"AMPDBPASS", "path"=>"/etc/"),
					                   array("name"=>"res_mysql.conf", "pass_name"=>"dbpass",    "path"=>"/etc/asterisk/"),
					                   array("name"=>"cbmysql.conf",   "pass_name"=>"password",  "path"=>"/etc/asterisk/"),
					                   array("name"=>"cdr_mysql.conf", "pass_name"=>"password",  "path"=>"/etc/asterisk/")
						         );
?>
