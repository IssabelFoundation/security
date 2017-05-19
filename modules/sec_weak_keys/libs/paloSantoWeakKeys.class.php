<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-31                                               |
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
  $Id: paloSantoWeakKeysChecker.class.php,v 1.1 2010-12-21 09:08:11 Alberto Santos asantos@palosanto.com Exp $ */
class paloSantoWeakKeys {
    var $_DB;      // Reference to the active DB
    var $errMsg;   // Variable where the errors are stored


    /**
     * Constructor of the class, receives as a parameter the database, which is stored in the class variable $_DB
     *  .
     * @param string    $pDB     object of the class paloDB    
     */
    function paloSantoWeakKeys(&$pDB)
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

    /**
     * Function that returns the number of devices sip or iax in the database
     *  .
     * @param string    $filter_field       string that indicates the filter applying 
     * @param string    $filter_value       string that has the value of the filter
     *
     * @return integer  0 in case of an error or the number of devices sip or iax in the database
     */
    function getNumWeakKeys($filter_field,$filter_value)
    {
        $arrParam = "";
        $where = "true";
        if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
           $arrParam = array("$filter_value%"); 
           $where = "id like ?";
        }

 /*       $query   = "select count(*),".
                            "if(id=ext or id=ext1,'igual',if(id/1000<10,'menor','')) as mensaje ".
                    "from (".
                            "select id,".
                                "tech,".
                                "description ".
                            "from devices ".
                            "where (tech = 'sip' ".
                                "or tech = 'iax2' ".
                                "or id/1000<10) and $where ) dev ".
                    "left join (".
                            "select data as ext ".
                            "from sip ".
                            "where keyword = 'secret' ".
                            "group by data) s ".
                    "on (dev.id=s.ext ".
                            "and tech ='sip') ".
                    "left join (".
                            "select data as ext1 ".
                            "from iax ".
                            "where keyword = 'secret' ".
                            "group by data) iax ".
                    "on (dev.id=iax.ext1 ".
                            "and tech ='iax2' )";*/
        
        $query = "select count(*) from devices where (tech = 'sip' or tech = 'iax2') and $where";

        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    /**
     * Function that returns an array with all the devices sip or iax in the database with their respective key 
     *
     * @param integer    $limit             Value to limit the result of the query
     * @param integer    $offset            Value for the offset of the query
     * @param string     $filter_field      string that indicates the filter applying 
     * @param string     $filter_value      string that has the value of the filter
     *
     * @return array   empty if an error occurs or the data with the key of the devices
     */
    function getWeakKeys($limit,$offset,$filter_field,$filter_value)
    {
        $same = _tr('Same Key');
        $short = _tr('Short Key');
        $where = "true";
        $arrParam = "";
        if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
            $where = "devices.id like ?";
            $arrParam = array("$filter_value%",$limit,$offset);
        }else
             $arrParam = array($limit,$offset);
    /*    $query   = "select dev.*,".
                            "if(id=ext or id=ext1,if(id/1000<10,'$same, $short','$same'),if(id/1000<10,'$short','')) as mensaje ".
                    "from (".
                            "select id,".
                                "tech,".
                                "description ".
                            "from devices ".
                            "where (tech = 'sip' ".
                                "or tech = 'iax2') and $where) dev ".
                    "left join (".
                            "select data as ext ".
                            "from sip ".
                            "where keyword = 'secret' ".
                            "group by data) s ".
                    "on (dev.id=s.ext ".
                            "and tech ='sip') ".
                    "left join (".
                            "select data as ext1 ".
                            "from iax ".
                            "where keyword = 'secret' ".
                            "group by data) iax ".
                    "on (dev.id=iax.ext1 ".
                            "and tech ='iax2' ) ".
                    "group by id order by id limit $limit offset $offset;";
*/
        //For SIP
        $query = "select devices.id,description,data from devices,sip where devices.id=sip.id and tech = 'sip' and keyword = 'secret' and $where LIMIT ? OFFSET ?";
        $result=$this->_DB->fetchTable($query, true, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
        }
        $result2 = array();
        if(count($result) < $limit){
            $arrParam = "";
            $where = "true";
            if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
                $where = "devices.id like ?";
                $arrParam = array("$filter_value%");
            }
            $query = "select count(*) from devices where tech = 'sip' and $where";

            $total=$this->_DB->getFirstRowQuery($query,false,$arrParam);
            if($total==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return 0;
            }
            $totalSIP = $total[0];
            if($offset <= $totalSIP)
                $offset = 0;
            else
                $offset = $offset-$totalSIP;
            $limit = $limit - count($result);
            if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
                $where = "devices.id like ?";
                $arrParam = array("$filter_value%",$limit,$offset);
            }else
                $arrParam = array($limit,$offset);
            //For IAX
            $query = "select devices.id,description,data from devices,iax where devices.id=iax.id and tech = 'iax2' and keyword = 'secret' and $where LIMIT ? OFFSET ?";
            $result2= $this->_DB->fetchTable($query, true, $arrParam);
            if($result2==FALSE){
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        $final[] = $result;
        $final[] = $result2;
        return $final;
    }

    /**
     * Function that searches in the database an existing device with its respective key
     *
     * @param string     $id                id of the device to be searched
     *
     * @return mixed     null if an error occurs or an array with all the data of the device
     */
    function getWeakKeyById($id)
    {
        $arrParam = array($id);
        $query = "SELECT id,tech FROM devices WHERE id=?";
    
        $result=$this->_DB->getFirstRowQuery($query,true,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        $device = $result;
        if($device['tech'] == "sip"){
            $arrParam = array($id);
            $query = "select devices.id,description,tech,data from devices,sip where devices.id=sip.id and keyword = 'secret' and devices.id = ?";
            $result=$this->_DB->fetchTable($query, true, $arrParam);
            if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return array();
            }
            return $result[0];
        }
        if($device['tech'] == "iax2"){
            $arrParam = array($id);
            $query = "select devices.id,description,tech,data from devices,iax where devices.id=iax.id and keyword = 'secret' and devices.id = ?";
            $result= $this->_DB->fetchTable($query, true, $arrParam);
            if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return array();
            }
            return $result[0];
        }
        return array();
    }

    /**
     * Function that updates the key of an existing device in the database 
     *
     * @param array     $arrValues        array that contains the new key and the id of the device
     * @param string    $tech             string that contains the technology of the device (sip or iax2)
     *
     * @return bool     false if an error occurs or true if the key is correctly updated
     */
    function saveNewKey($arrValues, $tech)
    {
        $arrParam = array($arrValues['new_key'],$arrValues['id']);
        if($tech == "sip"){
            $query = "update sip set data = ? where id = ? and keyword = 'secret'";
            $result = $this->_DB->genQuery($query,$arrParam);
            if( $result == false ){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }    
            return true;
        }
        if($tech == "iax2"){
            $query = "update iax set data = ? where id = ? and keyword = 'secret'";
            $result = $this->_DB->genQuery($query,$arrParam);
            if( $result == false ){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }    
            return true;
        }
    }
}
?>