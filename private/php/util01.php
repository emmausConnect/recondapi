<?php
declare(strict_types=1);

function debugDir() : string {
	$retour  = '<pre>';
	$retour .= '__DIR__            : '.__DIR__.'<br>';
	$retour .= '__FILE__           : '.__FILE__.'<br>';
	$retour .= 'getcwd()           : '.getcwd().'<br>';
	$retour .= 'dirname(__FILE__)  : '.dirname(__FILE__).'<br>';
	$retour .= 'basename(__FILE__) : '.basename(__FILE__).'<br>';
	$retour .= '</pre>';
	return $retour;
}

function listDir($dir) : string {
	$handle = opendir($dir);
	$retour = '<pre>dirname : '.$dir.'<br>';
	if ($handle) {
		while (($entry = readdir($handle)) !== FALSE) {
			$retour .= $entry.'<br>';
		}
	}
	$retour .= '</pre>';
	closedir($handle);
	return $retour;

}

function getProtocole () {
	$protocole = "";
	if (array_key_exists("REQUEST_SCHEME", $_SERVER)) {
		$protocole = $_SERVER["REQUEST_SCHEME"];
	}else if (array_key_exists("SCRIPT_URI", $_SERVER)) {
		preg_match('/(.*):\/\/(.*)/', $_SERVER["SCRIPT_URI"], $a);
		if (count($a) >=2) {
			$protocole = $a[1];
		}
	}
	if ($protocole == "") {
		echo __FILE__ ." : impossible de savoir si http ou https";
        print_r($_SERVER);
		die;
	}
    if ($protocole != "http" && $protocole != "https") {
		echo __FILE__ ." : protocole doit être http ou https : [$protocole]";
		die;
	}
	return $protocole;
}

/**
 * Ajoute un enreg au fichier de log des traitment
 * 
 * file : chemin du fichier ayant appeler la fonction (__FILE__)
 * msg : texte à écritre
 */
function logexec($file, $msg="", $outputType = "") {
	$user="";
	if (isset($_SESSION)) {
		if(array_key_exists('emmaususerconnected',$_SESSION) && $_SESSION['emmaususerconnected'] == 'Y') {
			$user= $_SESSION['email'];
		}
	}
	$outFile = '../work/logfiles/exec' .$outputType. '.log';


	$execlog = "\n".date('d/m/y H:i:s') .' | '. $_SERVER['REMOTE_ADDR'] .' | '.basename($file).' | user = ' . $user ." | ".$msg.' | ';
	file_put_contents($outFile, $execlog,  FILE_APPEND | LOCK_EX);
}

?>