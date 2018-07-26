<?php
require_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    require_once "modules/$module_name/configs/default.conf.php";
    include_once "libs/paloSantoForm.class.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConf['templates_dir'])) ? $arrConf['templates_dir'] : 'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // Check to see if we have ServerName defined in ssl.conf, if not, add it
    $modify=0;
    $sslfile = "/etc/httpd/conf.d/ssl.conf";

    // Check to see if setting is already set on file
    foreach (file($sslfile) as $sLinea) {
        if (preg_match('/^[\s]*?ServerName\s*/', $sLinea)) {
            $modify=1;
        }
    }

    if(isValidIP($_SERVER['HTTP_HOST'])) {
        $smarty->assign("NO_HOSTNAME_NOTICE", _tr("Please be sure to access the Web UI via a valid name configured in your DNS. That would be the same domain you will pass to Let's Encrypt to validate the SSL certificate. You should not access the site via IP address."));
    } else {
        $smarty->assign("NO_HOSTNAME_NOTICE", "");
    }

    $sHostname = file_get_contents("/etc/hostname");
    $sHostname = trim(preg_replace('/\s\s+/', ' ', $sHostname));
    $smarty->assign("valuedomain", $sHostname);

    // There is no ServerName in ssl.conf, let's add it, otherwise certbot won't be able to do its magic
    if($modify==0) {
        // Insert new line, we must do it after <VirtualHost _default_:443>
        exec("/usr/bin/issabel-helper ssl_certbot insertservername ".escapeshellarg($sHostname), $respuesta, $retorno);
        if($retorno==1) { $error=1; }
    }

    // Check if we already have a letsencrypt certificate
    $valuesc="/etc/letsencrypt/values";
    if ( file_exists($valuesc) ) {
        $ffvalues = file_get_contents($valuesc);
        preg_match("/email=(.*)/i",$ffvalues,$email1);
        $smarty->assign("valueemail", $email1[1]);
        preg_match("/domain=(.*)/i",$ffvalues,$domain2);
        $smarty->assign("valuedomain", $domain2[1]);
    } 

    $smarty->assign("INSTALLNEW", _tr("Install New Certificate from Let's Encrypt"));
    $smarty->assign("INSTALL",    _tr("Install"));
    $smarty->assign("RENEW",      _tr("Renew"));
    $smarty->assign("USAGE",      _tr("Usage"));
    $smarty->assign("RENEWCERT",  _tr("Renew Certificate"));
    $smarty->assign("HASDATA",    _tr("This is your actual account and domain configuration"));
    $smarty->assign("DOMAIN",     _tr("Domain"));
    $smarty->assign("EMAIL",      _tr("Email"));
    $smarty->assign("STAGING",    _tr("Staging Certificate(Use this for testing)"));
    $smarty->assign("STEP1",      _tr("1.- Create a Valid Domain by purchasing it with you preferred Vendor or get one from free DDNS service like No-Ip.org"));
    $smarty->assign("STEP2",      _tr("2.- Be sure that you domain is redirected to your PBX Service."));
    $smarty->assign("STEP3",      _tr("3.- Open the 443 and 80 ports in your firewall and redirect to your PBX."));
    $smarty->assign("STEP4",      _tr("4.- In the field DOMAIN enter your domain to obtain a valid certificate. You can add many domains comma separated"));
    $smarty->assign("STEP5",      _tr("5.- In the field EMAIL enter your email to register your account."));
    $smarty->assign("STEP6",      _tr("6.- To renew your certificate press the Renew button."));
    $smarty->assign("STEP7",      _tr("7.- For test certificates enable the Staging checkbox."));
    $smarty->assign("STEP8",      _tr("8.- After the process finish, reload your window with the Domain name(make sure the DNS is set internally too)."));
    $smarty->assign("STEP9",      _tr("9.- You can enable the firewall rules again for ports 443 and 80."));
    $smarty->assign("STEP10",     _tr(" Enjoy. ;)"));
    $smarty->assign("STEP11",     _tr("<small>Visit <strong>https://letsencrypt.org</strong> to know more about free certification process their TOS and License Agreement, this is a basic GUI for the APACHE CERTBOT and is provided as is and without warranty.If you have any questions please provide it in the forum.Don't forget to Donate to the Let's Encrypt Project</small>"));

    $contenidoModulo = $smarty->fetch("$local_templates_dir/new.tpl");

    return $contenidoModulo;
}

function isValidIP($str) {
    return (bool)preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $str);
}
