<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version {ISSBEL_VERSION}                                               |
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
  $Id: Issabelwhitelist.class.php, Sun 17 May 2020 01:06:25 PM EDT, nicolas@issabel.com
*/
class Issabelwhitelist{
    var $_DB;
    var $errMsg;

    function Issabelwhitelist(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /*HERE YOUR FUNCTIONS*/

    function getNumwhitelist()
    {
        $where    = "";
        $arrParam = null;
        if(isset($filter_field) & $filter_field !=""){
            $where    = "where $filter_field like ?";
            $arrParam = array("$filter_value%");
        }

        $query   = "SELECT COUNT(*) FROM whitelist $where";

        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getwhitelist($limit, $offset)
    {
        $where    = "";
        $arrParam = null;
        if(isset($filter_field) & $filter_field !=""){
            $where    = "where $filter_field like ?";
            $arrParam = array("$filter_value%");
        }

        $query   = "SELECT * FROM whitelist $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getwhitelistByIp($ip_address)
    {
        $query = "SELECT * FROM whitelist WHERE ip_address=?";

        $result=$this->_DB->getFirstRowQuery($query, true, array("$ip_address"));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function deletewhitelist($ip_address)
    {
        $query = "DELETE FROM whitelist WHERE ip_address=?";

        $result = $this->_DB->genQuery($query,array($ip_address));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }

        $sComando = '/usr/bin/issabel-helper fwconfig --remove_wl '.escapeshellarg($ip_address).' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            return FALSE;
        }
        return TRUE;

    }

    function savewhitelist($ip_address,$note)
    {
        $query = "INSERT INTO whitelist (ip_address,note) VALUES ( ?, ? )";

        $result = $this->_DB->genQuery($query,array($ip_address, $note));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }

        $sComando = '/usr/bin/issabel-helper fwconfig --add_wl '.escapeshellarg($ip_address).' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);

        if ($ret != 0) {
            return FALSE;
        }
        return TRUE;

    }

    function validateIpOrMask($variable) {

        $error = "";
        if(!preg_match("/^([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})$/", $variable, $arrReg)) {

            if(!preg_match("/^([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\/([[:digit:]]{1,2})$/", $variable, $arrReg)) {
                $error = _tr("Invalid Format");
            } else {
                if(($arrReg[1]<=255) and ($arrReg[1]>0) and ($arrReg[2]<=255) and ($arrReg[2]>=0) and
                    ($arrReg[3]<=255) and ($arrReg[3]>=0) and ($arrReg[4]<=255) and ($arrReg[4]>=0) and
                    ($arrReg[5]>=0) and ($arrReg[5]<=32)) {
                    $return = true;
                } else {
                    $error = _tr("Invalid Format");
                }
            }

        } else {
            if(($arrReg[1]<=255) and ($arrReg[1]>0) and ($arrReg[2]<=255) and ($arrReg[2]>=0) and
               ($arrReg[3]<=255) and ($arrReg[3]>=0) and ($arrReg[4]<=255) and ($arrReg[4]>=0)) {
                $return = true;
            } else {
                $error = _tr("Invalid Format");
            }
        }
        return $error;

    }

    public function checkTable() {
        if(!$this->tableExists()) {
            $this->createTable();
        }
    }

    private function createTable() {
        $query = "CREATE TABLE whitelist(
            ip_address TEXT NOT NULL UNIQUE, 
            note TEXT
        )";
        return $this->_DB->genExec($query);
    }
    
    private function tableExists() {
        $query = "SELECT * FROM whitelist";
        $result = $this->_DB->genQuery($query);
        if ($result === false) {
            if (preg_match("/No such table/i", $this->_DB->errMsg))
                return false;
            else
                return true;
        }
        else
            return true;
    }


}
