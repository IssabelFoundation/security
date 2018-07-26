<?php
include("/var/www/html/libs/misc.lib.php");
include("/var/www/html/configs/default.conf.php");
include("/var/www/html/libs/paloSantoACL.class.php");
include_once("/var/www/html/libs/paloSantoDB.class.php");
include_once("/var/www/html/libs/paloSantoNetwork.class.php");

session_name("issabelSession");
session_start();

$pDB  = new paloDB($arrConf["issabel_dsn"]["acl"]);
$pACL = new paloACL($pDB);
if(isset($_SESSION["issabel_user"])) {
    $issabel_user = $_SESSION["issabel_user"];
} else {
    $issabel_user = "";
}

session_commit();

if(!$pACL->isUserAdministratorGroup($issabel_user)){
    die();
}

$pNet = new paloNetwork();
$arrNetwork = $pNet->obtener_configuracion_red();
if(is_array($arrNetwork)) {
    $arrNetworkData['dns_ip_1'] = isset($arrNetwork['dns'][0])?$arrNetwork['dns'][0]:'';
    $arrNetworkData['dns_ip2'] = isset($arrNetwork['dns'][1])?$arrNetwork['dns'][1]:'';
    $arrNetworkData['host'] = isset($arrNetwork['host'])?$arrNetwork['host']:'';
    $arrNetworkData['gateway_ip'] = isset($arrNetwork['gateway'])?$arrNetwork['gateway']:'';
}

$renew=0;
if(isset($_POST['renew'])) {
    $renew = 1;
} else {
    $staging = isset($_POST['staging'])?$_POST['staging']:'';
    $domain  = $_POST['domain'];
    $email   = $_POST['email'];
    $arrNetworkData['host'] = $domain;
}

// Do a renew
if($renew==1) {
    exec("/usr/bin/issabel-helper ssl_certbot renewcertificate", $result, $retorno);
    $result = implode("\n",$result);

    $output = "<strong>Output log: </strong><br><pre>";
    $getvars = array();
        
    $result1 = str_replace("<","",$result);
    $result2 = str_replace(">","",$result1);
    $result3 = str_replace("\n","<br>",$result2);
    $output .= $result3;

    $getvars['result'] = $output;

    echo __json_encode($getvars);

    die();
}

// Install new Cert

// if one value is empty display the required label
if($domain==''){
    echo "0";
} elseif ($email=='') {
    echo "0";
} else {

    // Check to see if we have ServerName defined in ssl.conf, if not, add it
    $modify=0;
    $sslfile = "/etc/httpd/conf.d/ssl.conf";

    // Check to see if setting is already set on file
    foreach (file($sslfile) as $sLinea) {
        if (preg_match('/^[\s]*?ServerName\s*/', $sLinea)) {
            $modify=1;
        }
    }

    if($modify==0) {
        exec("/usr/bin/issabel-helper ssl_certbot insertservername ".escapeshellarg($domain), $respuesta, $retorno);
    } else {
        exec("/usr/bin/issabel-helper ssl_certbot editservername ".escapeshellarg($domain), $respuesta, $retorno);
    }

    exec("/usr/bin/issabel-helper ssl_certbot newcertificate ".escapeshellarg($email)." ".escapeshellarg($domain)." $staging", $result, $retorno);
    $result = implode("\n",$result);

    if($retorno==0) {
        exec("/usr/bin/issabel-helper ssl_certbot writevars ".escapeshellarg($email)." ".escapeshellarg($domain), $out, $rtn);
        $pNet->escribir_configuracion_red_sistema($arrNetworkData);
    }

    $output = "<strong>Output log: </strong><br><pre>";

    //retrieve values to show again
    $valuesc="/etc/letsencrypt/values";

    if( file_exists($valuesc) ){

        $ffvalues = file_get_contents($valuesc);

        preg_match_all("/domain=(.*)/i",$ffvalues,$domain1);
        $domainff = $domain1[1];

        preg_match("/email=(.*)/i",$ffvalues,$email1);
        $emailff = $email1[1];

        $getvars = array();

        $result1 = str_replace("<","",$result);
        $result2 = str_replace(">","",$result1);
        $result3 = str_replace("\r\n","<br>",$result2);
        $output .= $result3;
        $output .= "</pre>";

        $getvars['domain'] = $domainff;
        $getvars['email']  = $emailff;
        $getvars['result'] = $output;

        echo __json_encode($getvars);

    } else {

        $getvars = array();

        $getvars['domain'] = "";
        $getvars['email']  = "";
        echo __json_encode($getvars);

    }

}

//---------------------------------
function __json_encode( $data ) {
    if( is_array($data) || is_object($data) ) {
        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) );

        if( $islist ) {
            $json = '[' . implode(',', array_map('__json_encode', $data) ) . ']';
        } else {
            $items = Array();
            foreach( $data as $key => $value ) {
                $items[] = __json_encode("$key") . ':' . __json_encode($value);
            }
            $json = '{' . implode(',', $items) . '}';
        }
    } elseif( is_string($data) ) {
# Escape non-printable or Non-ASCII characters.
# I also put the \\ character first, as suggested in comments on the 'addclashes' page.
        $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
        $json    = '';
        $len    = strlen($string);
# Convert UTF-8 to Hexadecimal Codepoints.
        for( $i = 0; $i < $len; $i++ ) {

            $char = $string[$i];
            $c1 = ord($char);

# Single byte;
            if( $c1 <128 ) {
                $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
                continue;
            }

# Double byte
            $c2 = ord($string[++$i]);
            if ( ($c1 & 32) === 0 ) {
                $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
                continue;
            }

# Triple
            $c3 = ord($string[++$i]);
            if( ($c1 & 16) === 0 ) {
                $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128));
                continue;
            }

# Quadruple
            $c4 = ord($string[++$i]);
            if( ($c1 & 8 ) === 0 ) {
                $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1;

                $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3);
                $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128);
                $json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
            }
        }
    } else {
# int, floats, bools, null
        $json = strtolower(var_export( $data, true ));
    }
    return $json;
}

