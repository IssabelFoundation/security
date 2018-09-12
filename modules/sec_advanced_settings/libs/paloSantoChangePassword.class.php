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
  $Id: paloSantoChangePassword.class.php, Wed 12 Sep 2018 07:34:42 PM EDT, nicolas@issabel.com
  */  
  
class paloSantoAdvancedSecuritySettings{
    var $_DB;
    var $errMsg;
    var $arrConf;

    function paloSantoAdvancedSecuritySettings($arrConf, &$pDB=null)
    {
	$this->arrConf = $arrConf;
        // Se recibe como parámetro una referencia a una conexión paloDB
	if(isset($pDB)){
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
    }
    
    function changeIssabelPBXPassword($ipbx_password, $arrConf)
    {
      //--------------------------- Begin Transaction --------------------------------
      //Paso #1: Actualizar la clave del usuario Admin.
      $this->_DB->beginTransaction();
      $resultUpdatePass = $this->updateIssabelPBXPasswordAdmin($ipbx_password);
      if(!$resultUpdatePass){
	  $this->_DB->rollBack();
          return false;
      }
      //Paso #2: Crear el usuario asteriskuser y asignarle el password ingresado.
      $resultCreateUser = $this->createAsteriskUser($ipbx_password);
      if(!$resultCreateUser){
	  $this->_DB->rollBack();
          return false;
      }
      //Paso #3: Actualizar los archivos de configuración.
      $resultUpdateConfFiles = $this->updateConfFiles($ipbx_password,$arrConf);
      if(!$resultUpdateConfFiles['result']){
	 $this->_DB->rollBack();
         return $arrResult;
      }
      $this->_DB->commit();
      //--------------------------- End Transaction --------------------------------
      return $resultUpdateConfFiles;
    }

    private function updateIssabelPBXPasswordAdmin($ipbx_password)
    {
      $arrParam[] = $ipbx_password;
      $query = "UPDATE ampusers SET password_sha1=sha1(?) WHERE username = 'admin' ";

      $result=$this->_DB->genQuery($query,$arrParam);
      if($result == FALSE){
	  $this->errMsg = $this->_DB->errMsg;
      }
      return $result;
    }
    
   private function createAsteriskUser($ipbx_password)
   {
        $query = "GRANT USAGE ON *.* TO 'asteriskuser'@'localhost' IDENTIFIED BY '$ipbx_password' ";

        $result=$this->_DB->genExec($query);
        if($result == FALSE){
            $this->errMsg = $this->_DB->errMsg;
        }
        return $result;
   }
    
   private function updateConfFiles($ipbx_password,$arrConf){
        $output = $retval = NULL;
        exec('/usr/bin/issabel-helper setadminpwd '.escapeshellarg($ipbx_password).' 2>&1', 
            $output, $retval);
        $arrResult = array(
            'result'            => ($retval == 0),
            'arrUpdateFiles'    =>  array(),
        );        
        foreach ($output as $sLinea) {
            $regs = NULL;
            if (preg_match('/^CHANGED (.+)/', trim($sLinea), $regs))
        	   $arrResult['arrUpdateFiles'][] = $regs[1]; 
        }
        return $arrResult;
   }

   function updateStatusIssabelPBXFrontend($status_ipbx_frontend)
   {
      //Actualizar la clave ActivatedIssabelPBX.
      $pDBSettings = new paloDB($this->arrConf['issabel_dsn']["settings"]);
      return (set_key_settings($pDBSettings,"activatedIssabelPBX",$status_ipbx_frontend));
   }

   function isActivatedIssabelPBXFrontend()
   {
      $pDBSettings = new paloDB($this->arrConf['issabel_dsn']["settings"]);
      return (get_key_settings($pDBSettings,"activatedIssabelPBX"));
   }

    function isActivatedAnonymousSIP()
    {
        $bValorPrevio = TRUE;   // allowguest es yes hasta encontrar seteo
        foreach (file('/etc/asterisk/sip_general_additional.conf') as $sLinea) {
            $regs = NULL;
            if (preg_match('/^allowguest\s*=\s*(\S+)$/', trim($sLinea), $regs)) {
                $bValorPrevio = in_array(strtolower($regs[1]), array('yes', '1', 'true'));
            }
        }
        return $bValorPrevio;
    }
    
    function updateStatusAnonymousSIP($bNuevoEstado)
    {
    	$output = $retval = NULL;
        exec('/usr/bin/issabel-helper anonymoussip '.($bNuevoEstado ? '--enable' : '--disable'),
            $output, $retval);
        return ($retval == 0);
    }
}
?>
