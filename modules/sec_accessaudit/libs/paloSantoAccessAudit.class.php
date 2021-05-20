<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0.3                                                |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2021 Issabel Foundation                                |
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
  $Id: paloSantoAccessAudit.class.php, Thu 20 May 2021 03:45:02 PM EDT, nicolas@issabel.com
*/

include_once "modules/asterisk_log/libs/LogParser_Full.class.php";

class paloSantoAccessaudit{
    var $_DB;
    var $errMsg;
    var $astLog;

    function __construct()
    {
        $this->astLog = new LogParser_Full("/var/log/issabel","audit");
    }

    function ObtainNumAccessLogs($sFecha)
    {
        $total = $this->astLog->numeroBytesMensajesFecha($sFecha);
        return array($total);
    }

    function ObtainAccessLogs($limit, $offset, $sFecha)
    {
        $iBytesLeidos = 0;
        $lineas = array();
        $this->astLog->posicionarMensaje($sFecha, $offset);
        $bContinuar = TRUE;
        while ($bContinuar) {
            $pos = $this->astLog->obtenerPosicionMensaje();
            $s = $this->astLog->siguienteMensaje();
            // Se desactiva la condición porque ya no todas las líneas empiezan con corchete
            if (!(count($lineas) == 0 && !is_null($s) && $s[0] != '[')) {
                $regs = NULL;
                if (preg_match('/^\[([[:alnum:][:space:]\:]+)\][[:space:]]+([[:alpha:]]+)[[:space:]]+([^[:space:]]+):[[:space:]]+(.*)$/', $s, $regs)) {
                    $l = array(
                        'offset'=> $pos[1],
                        'fecha' => $regs[1],
                        'tipo' => $regs[2],
                        'origen' => $regs[3],
                        'linea' => $regs[4],
                    );
                } else {
                    $l = array(
                        'offset'=> $pos[1],
                        'fecha' =>  '',
                        'tipo'  =>  '',
                        'origen'=> '',
                        'linea' =>  $s,
                    );
                }
                $lineas[] = $l;
            }
            $pos = $this->astLog->obtenerPosicionMensaje();
            $iBytesLeidos = $pos[1] - $offset;
            $bContinuar = (!is_null($s) && $iBytesLeidos < $limit);
        }
        return $lineas;
    }
}
?>
