<?php
declare(strict_types=1);
require_once __DIR__ . '/../class/paramini.class.php';
require_once __DIR__ . '/../class/contexte.class.php';

/** **********************************************
 *  page HTML
 * 
************************************************* */

function getHtmlHead() {
	$retour = <<<EOT
	<!DOCTYPE html>
	<HTML>
	<HEAD>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="style01.css"/>
		<!-- fin getHtmlHead -->
EOT;
	return $retour;
}

function getHtmlHeader() {
	$contexte = Contexte::getInstance();
	$isConnected = false;
	if(array_key_exists('emmaususerconnected',$_SESSION) && $_SESSION['emmaususerconnected'] == 'Y') {
		$isConnected = true;
	}
	$paramPhpArray = ParamIni::getInstance(__DIR__.'/../config/paramphp.ini')->getParam();
	$menuInitial = "/?".$_SERVER['QUERY_STRING'];
	$retour = '<!-- debut getHtmlHeader( -->';
	if (! $contexte->environnementIsProd()) {
		$retour .= '<div style="background-color:red; text-align:center; font-size:150%;">Environnement de test<br>Les résultats peuvent être faux car des tests y sont en cours</div>';
	}
	$retour .=  '<header>';
	$retour .=  '<div class="titre">';	
	$retour .=  ' <div style="display:inline; margin-left:50px;">';
	$retour .=  '  <a href="'.$menuInitial.'" target="_self" alt="retour au menu initial">';
	$retour .=  '   <img src="images/icones/logo.png" style="width: 100px;border-width:0px">';
	$retour .=  '  </a>';
	$retour .=  ' </div>';
	$retour .=  ' <div style="display:inline; margin-right:50px;">';
	$retour .=  '   Emmaus-Connect Catégorisation';
	$retour .=  ' </div>';
	$retour .=  '</div>';

	$retour .=  '<div class="version">';
	$retour .=  ' <div style="display:inline; margin-left:20px;">';
	$retour .=  '  <span style="text-align:left;">'.date('d/m/Y G:i:s').'</span>';
	$retour .=  ' </div>';
	$retour .=  ' <div style="display:inline; margin-left:10px;">';
	$retour .=  '  <span style="text-align:left;font-size:small;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (version : ' 
					.$paramPhpArray["version"]["num"]. " du " .$paramPhpArray["version"]["date"]. ")</span>" ;
	$retour .=  ' </div>';					
	if ($isConnected) {
		$retour .=  ' <div style="display:inline; margin-right:10px;">';
		$retour .=  '<strong>'.$_SESSION['email'].'</strong>';
		$retour .=  ' </div>';
		$retour .=  ' <div style="display:inline; margin-right:20px;">';
		$retour .=  '  <a class="ec-btn ec-nav menuoption" href="exgoogledisconnect.php">Déconnexion</a>';
		$retour .=  ' </div>';
	}
	$retour .=  '</div>';
	$retour .=  '</header>';
	$retour .= '<!-- fin getHtmlHeader( -->';
	return $retour;
}

function getFooter() : String {
	$retour = <<<'EOT'

		<div  style="padding:0px 20px 5px 20px; text-align:right; font-size:small; font-style: italic;">
		Michel BEN
		</div>

EOT;
	return $retour;
}