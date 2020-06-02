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
  $Id: default.conf.php, Sun 17 May 2020 01:06:41 PM EDT, nicolas@issabel.com
*/
    global $arrConf;
    global $arrConfModule;

    $arrConfModule['module_name']       = 'sec_whitelist';
    $arrConfModule['templates_dir']     = 'themes';
    $arrConfModule['dsn_conn_database'] = "sqlite3:///$arrConf[issabel_dbdir]/iptables.db";
?>
