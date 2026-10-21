#!/usr/bin/php
<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 2017 - Issabel Foundation                              |
  +----------------------------------------------------------------------+
  +----------------------------------------------------------------------+
  | Autor: Nicolas Gudino  <asternic@gmail.com>                          |
  +----------------------------------------------------------------------+
*/

if(!isset($argv[1]))
    exit(1);

switch($argv[1]){
    case "insertservername":
        if(isset($argv[2])) {
            insertServerName($argv[2]);
        }
        break;
    case "editservername":
        if(isset($argv[2])) {
            editServerName($argv[2]);
        }
        break;
    case "newcertificate":

        // newcertificate test@email.com my.dominio.com [staging]

        if ($argc < 4 || $argc > 5) {
            fputs(STDERR, "ERR: invalid number of arguments\n");
            exit(1);
        }

        $email = $argv[2];
        $domain = strtolower($argv[3]);

        if (strlen($email) > 254 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fputs(STDERR, "ERR: invalid email\n");
            exit(1);
        }

        // Un dominio DNS ASCII, sin comodines.
        if (strlen($domain) > 253 ||
            !preg_match(
                '/\A(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+' .
                '[a-z](?:[a-z0-9-]{0,61}[a-z0-9])?\z/',
                $domain
            )) {
            fputs(STDERR, "ERR: invalid domain\n");
            exit(1);
        }

        if (isset($argv[4]) && $argv[4] !== 'staging') {
            fputs(STDERR, "ERR: invalid staging argument\n");
            exit(1);
        }

        if (!is_executable('/usr/bin/certbot')) {
            fputs(
                STDERR,
                "ERR: Could not find executable /usr/bin/certbot. " .
                "Be sure to install the certbot package.\n"
            );
            exit(1);
        }

        $params = array(
            '/usr/bin/certbot',
            '-n',
            '-i', 'apache',
            '-a', 'webroot',
            '-w', '/var/www/html',
            '--agree-tos',
            '--email', $email,
            '-d', $domain
        );

        if (isset($argv[4])) {
            $params[] = '--test-cert';
        }

        // Escapar cada argumento individualmente.
        $comando = implode(
            ' ',
            array_map('escapeshellarg', $params)
        );

        $output = array();
        $ret = 1;

        exec($comando, $output, $ret);

        if ($ret != 0) {
            fputs(
                STDERR,
                "ERR: failed to run /usr/bin/certbot. Output follows:\n"
            );
            fputs(STDERR, implode("\n", $output) . "\n");
            exit(1);
        }

        writeAsteriskPEMfile(1);

        echo implode("\n", $output);
        exit(0);

        break;
    case "renewcertificate":
        $comando = "/usr/bin/certbot renew";
        $output = $ret = NULL;

        exec($comando, $output, $ret);

        if ($ret != 0) {
            fputs(STDERR, "ERR: failed to run /usr/bin/certbot. Output follows:\n");
            fputs(STDERR, implode('', $output));
            exit(1);
        } else {

            writeAsteriskPEMfile(1);

            echo implode("\n",$output);
            exit(0);
        }
        break;
    case "writevars":
        if(isset($argv[2]) && isset($argv[3])) {
            $email  = $argv[2];
            $domain = $argv[3];
            $valuesc="/etc/letsencrypt/values";
            file_put_contents($valuesc, "");
            //create the new vars File
            $fp = fopen($valuesc, "w");
            fputs($fp,"domain=${domain}\n");
            fputs($fp,"email=${email}\n");
            fclose($fp);
        }
        break;
    case "writeasteriskcert":
        generateAsteriskCert();
        break;
    default:
        exit(1);
        break;
}


function insertServerName($sHostname) {
    // Insert new line, we must do it after <VirtualHost _default_:443>
    $sslfile = "/etc/httpd/conf.d/ssl.conf";
    $contenidoNuevo = array();
    foreach (file($sslfile) as $sLinea) {
        if (preg_match('/^<VirtualHost _default_:443/', $sLinea)) {
            $contenidoNuevo[] = $sLinea;
            $contenidoNuevo[] = "ServerName $sHostname\n";
        } else {
            $contenidoNuevo[] = $sLinea;
        }
    }
    file_put_contents($sslfile, $contenidoNuevo);
}

function editServerName($sHostname) {
    $sslfile = "/etc/httpd/conf.d/ssl.conf";
    $contenidoNuevo = array();
    foreach (file($sslfile) as $sLinea) {
        if (preg_match('/^[\s]*?ServerName\s*/', $sLinea)) {
            $contenidoNuevo[] = "ServerName $sHostname\n";
        } else {
            $contenidoNuevo[] = $sLinea;
        }
    }
    file_put_contents($sslfile, $contenidoNuevo);
}

function generateAsteriskCert() {
    if (!file_exists('/etc/asterisk/keys/asterisk.pem')) {
        writeAsteriskPEMfile(0);
    }
}

function writeAsteriskPEMfile($reload=1) {

    if (!file_exists('/etc/asterisk/keys')) {
        mkdir('/etc/asterisk/keys', 0751, true);
    }

    exec(">/etc/asterisk/keys/asterisk.pem", $output, $ret);
    exec("for A in `grep '^SSLCert' /etc/httpd/conf.d/ssl.conf | awk '{print $2}' | sed 's/cert.pem/fullchain.pem/g'`; do cat \$A >>/etc/asterisk/keys/asterisk.pem; done", $output, $ret);
    if($reload==1) {
       system('/usr/sbin/amportal a r');
    }

}
