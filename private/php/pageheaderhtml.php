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
		<link rel="stylesheet" href="style/style01.css"/>
		<!-- fin getHtmlHead -->
EOT;
	return $retour;
}

function getHtmlHeader() {
	$ctx = Contexte::getInstance();
	$isConnected = false;
	if(array_key_exists('emmaususerconnected',$_SESSION) && $_SESSION['emmaususerconnected'] == 'Y') {
		$isConnected = true;
	}
	//$paramPhpArray = ParamIni::getInstance(__DIR__.'/../config/paramphp.ini')->getParam();
	$ctx = Contexte::getInstance();
	$paramPhpArray = $ctx->getParamPhpIniCls()->getParam();
	$menuInitial = "/?".$_SERVER['QUERY_STRING'];
	$retour = '<!-- debut getHtmlHeader( -->';
	if (! $ctx->environnementIsProd()) {
		$retour .= '<div style="background-color:red; text-align:center; font-size:150%;">Environnement de test<br>Les résultats peuvent être faux car des tests y sont en cours</div>';
	}
	$retour .=  '<header>';
	$retour .=  '<div class="titre">';	
	$retour .=  ' <div style="display:inline; /* margin-left:50px;*/">';
	$retour .=  '  <a href="'.$menuInitial.'" target="_self" alt="retour au menu initial">';
	$retour .=  '   <img src="images/icones/logo.png" style="width: 100px;border-width:0px">';
	$retour .=  '  </a>';
	$retour .=  ' </div>';
	$retour .=  ' <div style="display:inline; margin-right:50px;">';
	$retour .=  '   Emmaus-Connect Catégorisation';
	$retour .=  ' </div>';
if ($isConnected) {
	$retour .= <<<EOT
	<div class="dropdown" style="font-size: 15px;">
      <img src="images/icones/profil.webp" class="dropbtn" onclick="myFunction()">
      <div id="myDropdown" class="dropdown-content">
EOT;

		$retour .=  '<strong>'.$_SESSION['email'].'</strong>';
		//$retour .=  '  <a class="ec-btn ec-nav menuoption" href="exgoogledisconnect.php">Déconnexion</a>';
		$retour .=  '  <a href="exgoogledisconnect.php">Déconnexion</a>';
	
	$retour .= <<<EOT
     </div>
    </div> <!-- menu -->
EOT;
}
	$retour .=  '</div>'; // titre

	$retour .=  '<div style="border-style: none none solid none; border-color: #00acb0;">';
	$retour .=  '<div class="version">';
	$retour .=  ' <div style="display:inline; margin-left:20px;">';
	$retour .=  '  <span style="text-align:left;">'.date('d/m/Y G:i:s').'</span>';
	$retour .=  ' </div>';
	$retour .=  ' <div style="display:inline; margin-left:10px;">';
	$retour .=  '  <span style="text-align:left;font-size:small;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (version : ' 
					.$paramPhpArray["version"]["num"]. " du " .$paramPhpArray["version"]["date"]. " - " .$paramPhpArray["version"]["text"].")</span>" ;
	$retour .=  ' </div>';					
	if ($isConnected) {
		$retour .=  ' <div style="display:inline; margin-right:10px;">';
		$retour .=  '<strong>'.$_SESSION['email'].'</strong>';
		$retour .=  ' </div>';
	}
	$retour .=  '</div>';
	$retour .=  '<div id="divcontact" class="version" style="text-align:center">';
	$retour .=  '<span style="text-align:center; margin:auto;">pour toutes remarques/questions, envoyez un mail à <b>emmausconnect-web-recond@ab.lespages.info</b></span>';
	$retour .=  '<div>';
	$retour .=  '</div>';
	$retour .=  '</header>';

$retour .= <<<EOT
<script>
/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function myFunction() {
  document.getElementById("myDropdown").classList.toggle("dropdown-show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('dropdown-show')) {
        openDropdown.classList.remove('dropdown-show');
      }
    }
  }
}
</script>
EOT;

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