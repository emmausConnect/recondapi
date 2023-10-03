<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 'On');

$debugDev=""; // utiliser pour conditionner la sortie de debug ponctuel de dev
// if (array_key_exists('debugdev', $_GET)) {
//     $debugDev=$_GET['debugdev'];
// }

session_set_cookie_params(['samesite' => 'Lax']);
session_start();
require_once __DIR__.'/initcontexte.php';
$path_private_php = $g_contexte_instance->getPath('private/php');
require_once $path_private_php . '/cookies.php';
require_once $path_private_php . '/errormanagement.php';
require_once $path_private_php . '/util01.php';

$path_private_php_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_php_class . '/loggerrec.class.php';
require_once $path_private_php_class . '/util01.class.php';
require_once $path_private_php_class . '/paramini.class.php';
require_once $path_private_php_class . '/contexte.class.php';

// check qu'on est en https, sauf si localhost
if (!str_starts_with($_SERVER['HTTP_HOST'],'localhost')) {
    if (!isset($_SERVER['HTTPS']) || isset($_SERVER['HTTPS']) && !$_SERVER['HTTPS'] === 'on') {
        echo "Veuillez-vous connecter en https<br>";
        $link = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        echo '<a href="' .$link. '">' .$link. '</a>';
        exit();
    }
}   

$contexte = Contexte::getInstance();

// le fichier environnement contient soit "PROD" soit "TEST"
// s'il ne contient pas PROD :
//    * une banière "!! environnement de test" est affichée
//    * certains traitemenent ont un comportement différent
// $fileEnvirName = '../environnement.ini';
// $g_environnement = ""; // global : environnement prod ou test
// if (! file_exists($fileEnvirName)) {
//     echo "Fichier '$fileEnvirName' non trouvé";
//     exit();
// }else{
//     $txt_file = fopen('../environnement.ini','r');
//     $g_environnement = fgets($txt_file);
//     fclose($txt_file);
//     if ($g_environnement != 'PROD' and $g_environnement != 'TEST' and $g_environnement != 'LOCAL') {
//         echo "Valeur environnement invalide : '$g_environnement'";
//         exit();
//     }
// }
//$contexte->setEnvironnement($g_environnement);

$debug = getDebugFromBrowserSessionCookie();
if ($debug == null) {
    $debug = "0";
}
$contexte->setDebugLevel($debug);

$logger = LoggerRec::getInstance();
$logger->setDebugLevel($debug);
$logger->setOutfile('supererror.log');

// à faire avant toute sortie vers le browser
// création du cookies s'il n'existe pas
if (!checkFromBrowserSessionCookie()) {
    //session_set_cookie_params(['samesite' => 'Lax']);
    initCookieBrowserSession(); 
}

header('Cache-Control: no-cache');

//$paramPhpArray = getParamPhp();

/**
 * URL = http://localhost:8080/EC-recondapi.git/public
 * [REQUEST_URI] => /EC-recondapi.git/public/
 * [SCRIPT_NAME] => /EC-recondapi.git/public/index.php
 * 
 * URL = http://localhost:8080/EC-recondapi.git/public?a=b
 * [REQUEST_URI] => /EC-recondapi.git/public/?a=b
 * [SCRIPT_NAME] => /EC-recondapi.git/public/index.php
 * 
 * URL = http://localhost:8080/EC-recondapi.git/public/?a=b
 * [REQUEST_URI] => /EC-recondapi.git/public/?a=b
 * [SCRIPT_NAME] => /EC-recondapi.git/public/index.php
 * 
 * URL = http://localhost:8080/EC-recondapi.git/public/index.php
 * [REQUEST_URI] => /EC-recondapi.git/public/index.php
 * [SCRIPT_NAME] => /EC-recondapi.git/public/index.php
 * 
 * URL = http://localhost:8080/EC-recondapi.git/public/excalculexcel.php
 * [REQUEST_URI] => /EC-recondapi.git/public/excalculexcel.php
 * [SCRIPT_NAME] => /EC-recondapi.git/public/excalculexcel.php
 *
 * URL = http://localhost:8080/EC-recondapi.git/public/excalculexcel.php?a=b
 * [REQUEST_URI] => /EC-recondapi.git/public/excalculexcel.php?a=b
 * [SCRIPT_NAME] => /EC-recondapi.git/public/excalculexcel.php
 */
// echo '<pre>';
// echo $_SERVER["REQUEST_URI"].'<br>';
// echo $_SERVER["SCRIPT_NAME"].'<br>';
// print_r($_SERVER);

// Extraction du nom du script
preg_match('/^(.*\/)(.*)/i', $_SERVER["SCRIPT_NAME"], $a);

// 0 : /EC-recondapi.git/public/calculexcel.php
// 1 : /EC-recondapi.git/public/
// 2 : calculexcel.php
if (count($a) <3 ) {
    echo "erreur:[impossible de détecter l'action à exécuter. Vérifier l'URL utilisée]";
    exit();
}
$scriptName = strtolower($a[2]);
$maintenanceFileName = "../maintenance.html";

$logger->addLogDebugLine('=================================================');
$logger->addLogDebugLine('=================================================');
$logger->addLogDebugLine("==================  $scriptName  =====================");
$logger->addLogDebugLine('=================================================');
$logger->addLogDebugLine('=================================================');

$_GET = array_change_key_case($_GET, CASE_LOWER); 
$logger->addLogDebugLine($_GET,'$_GET = ');
$logger->addLogDebugLine($_POST,'$_POST =');

if ($debug != "1") {
    if (file_exists($maintenanceFileName)) {
        if ($scriptName == 'exapieval.php') {
            echo "erreur:[api indisponible]";
        }else{
            $maintenanceFile  = fopen($maintenanceFileName,'r');
            echo fgets($maintenanceFile);
        }
        if (! array_key_exists("forceexec", $_GET) ) {
            exit();
        }

    }
}
// Extraction des paramètres
//$param = $_SERVER["QUERY_STRING"];

$actiontodo = [
    "index.php"          => '../private/php/accueil.php',
    "exaccueil.php"      => '../private/php/accueil.php',
    
    "exapieval.php"      => '../private/php/apieval.php',    
    "exformtableau.php"  => '../private/php/formtableau.php',

    "exformexcel.php"    => '../private/php/formexcel.php',
    //"excalculexcel.php"  => '../private/php/formexcel.php', // pour rétro compatibilité
    "extrtexcel.php"     => '../private/php/trtexcel.php',
    "extemplatesupdate.php"   => '../private/php/templatesupdate.php',
    "extemplatesget.php"      => '../private/php/templatesget.php',

    "*******exgoogleconnect.php"=> '*******../private/php/googleconnect.php',
    "exgoogleconnectscreen.php" => '../private/php/googleconnectscreen.php',
    "exgoogleconnectdone.php"   => '../private/php/googleconnectdone.php',
    "exgoogledisconnect.php"    => '../private/php/googledisconnect.php',
    
    "exgestion.php"      => '../private/php/gestion.php',
    "exgentest.php"      => '../private/php/gentest.php',
    "exdisplaydoc.php"   => '../private/php/displaydoc.php',

    "excrtsmartphonestables.php"  => '../private/php/install/crtsmartphonestables.php',
    "exsearchsmartphone.php"      => '../private/php/smartphones/searchsmartphone.php',
    "exloadsmartphonesexcel.php"  => '../private/php/smartphones/loadsmartphonesexcel.php',
    "exdisplaysmartphonebd.php"   => '../private/php/smartphones/displaysmartphonebd.php',
    'exgetsmartphoneslist.php'    => '../private/php/smartphones/getsmartphoneslist.php',
    'exaddindb.php'               => '../private/php/smartphones/addindb.php'
];

// echo "<pre>";
// print_r($_SERVER);
// echo "<br>__FILE__ : " .__FILE__;
// echo "</pre>";
//exit();
if (! array_key_exists($scriptName, $actiontodo)) {
	echo '<pre>';
    echo "action non définie. Vérifier l'URL utilisée";
    echo $_SERVER["SCRIPT_NAME"].'<br>';
    print_r($_SERVER);
    print_r($a);

}else{
    require_once $actiontodo[$scriptName];
}
