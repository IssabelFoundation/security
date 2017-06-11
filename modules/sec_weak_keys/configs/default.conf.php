<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0.0-31                                               |
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
  $Id: default.conf.php,v 1.1 2010-12-21 09:08:11 Alberto Santos asantos@palosanto.com Exp $ */
    global $arrConf;
    global $arrConfModule;

    $arrConfModule['module_name']       = 'sec_weak_keys';
    $arrConfModule['templates_dir']     = 'themes';
    //ex1: $arrConfModule['dsn_conn_database'] = "sqlite3:///$arrConf[issabel_dbdir]/base_name.db";
    //$arrConfModule['dsn_conn_database'] = "mysql://root:palosanto@localhost/asterisk";
    $arrConfModule['dsn_conn_database'] = '';
    $arrConfModule['AMI_HOST'] = "127.0.0.1";
    $arrConfModule['AMI_USER'] = "admin";
    $arrConfModule['AMI_PASS'] = obtenerClaveAMIAdmin();
?>
