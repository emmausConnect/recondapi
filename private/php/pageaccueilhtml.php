<?php
declare(strict_types=1);
require_once __DIR__.'/pageheaderhtml.php';
/****************************************************************************
 * HTML page Accueil
**************************************************************************** */
function getHtmlAccueil() {
	$ctx = Contexte::getInstance();
	$paramPhpArray  = $ctx->getParamPhpIniCls()->getParam();
	$isConnected    = false;
	$isConnected    = $ctx->isConnected();
	$emailConnected = $ctx->getEmailConnected();
	$retour  = getHtmlHead();
	$retour .= '<script src="util01.js"></script>';
	$retour .= <<<'EOT'
	<script>
	// si debug = true dans cooky => on coche la case
	function doOnLoad() {
		let debug = getCookieDebug();
		if (debug == "1") {
			let checkbox = document.getElementById('checkdebug');
			checkbox.checked = true;
		}
		let htmlElements = document.getElementsByClassName("ec-btn");
		Array.from(htmlElements).forEach(item => {
			item.addEventListener("click", openUrlOnClick);
		})
	}

	// la case débug à été cliquée => maj du cooky
	function checkdebugClicked() {
		let checkbox = document.getElementById('checkdebug');
		if (checkbox.checked) {
			setCookieDebug("1");
		}else{
			setCookieDebug("0");
		}
	}

	function openUrlOnClick(e) {
		openUrl(e);
		e.stopPropagation()
	}

	// clic sur un bouton pour ouvrir une URL
	// l'URL est dans l'attribut "dataset.goto"
	// si la touche Ctrl est appuyée => dans un nouvel onglet
	function openUrl(e) {
		let elem = e.target;
		let url  = elem.dataset.goto;
EOT;
		$retour .= 'let qstring = "'.$_SERVER["QUERY_STRING"].'";'."\n";
		$retour .= <<<'EOT'
		let url2 = url+"?"+qstring;
		if(e.ctrlKey) {
			window.open(url2); 
		}else{
			window.location.assign(url2); 
		}	
	}

	</script>
	</head>
    <body class="body_flex" onload="doOnLoad()">
	<main>
EOT;
	$retour .= getHtmlHeader();

	$retour .= <<<"EOT"
		<article>
			<h3 class="menutitre">PC</h2>
			<div class="menuoption" style="padding:0px 0px 5px 20px;">
				<a class="ec-btn" data-goto="exformtableau.php">Catégorisation d'un PC</a><br>
				<br>
				<a class="ec-btn" data-goto="exformexcel.php">Traitement d'un Excel PC</a><br>
				<br>
				<a class="ec-btn" href="{$paramPhpArray['fichiers']['pc_modele_BOLC_xlsx_download']}">Télécharger l'Excel PC BOLC modèle</a><br>
				<br>			
				<a class="ec-btn" href="exdisplayvideoaide.htm">Vidéo d'aide</a><br>

			</div>
		</article>
EOT;
	$retour .= <<<"EOT"
		<article>
			<h3 class="menutitre">Smartphone</h2>
			<div class="menuoption" style="padding:0px 0px 5px 20px;">
				<a class="ec-btn" data-goto="exsearchsmartphone.php">Catégorisation d'un Smartphone</a><br>
				<br>
				<a class="ec-btn" data-goto="exformexcelsm.php">Traitement d'un Excel SM</a><br>
				<br>
				<a class="ec-btn" data-goto="exdisplaysmartphonebd.php" target="_blank">Afficher la BDD smartphones</a><br>
				<br>
				<a class="ec-btn" href="{$paramPhpArray['fichiers']['sm_modele_BOLC_xlsx_download']}">Télécharger l'Excel SM BOLC modèle</a><br>
				<br>
				<a class="ec-btn" data-goto="exdisplayvideoaidesm.htm" target="_blank">Vidéo d'aide'</a><br>
			</div>
		</article>
EOT;
	$retour .= <<<'EOT'
 		<article>
 			<h2 class="menutitre">Menu gestionnaires</h2>
EOT;
	if($isConnected) {
		$retour .= <<<'EOT'
			<div class="menuoption" style="padding:0px 5px 0px 20px;">
				<a class="ec-btn" data-goto="exgestion.php">Menu de gestion</a><br><br>
			</div>
		</article>
EOT;
		if($emailConnected == 'mben@emmaus-connect.org') {
		$retour .= <<<'EOT'
		<article>
			activer le debug : <input type="checkbox" id="checkdebug" onclick="checkdebugClicked()">
			( à n'utiliser que pour de la mise au point des programmes car peut faire planter le serveur)
		</article>
EOT;
		}
	}else{
		$retour .= <<<"EOT"
			<div class="menuoption" style="padding:0px 5px 0px 20px;">
				<p>Pour accéder aux options avancées, veuillez vous connecter avec votre compte <strong>@Emmaus-Connect.org</strong><br>
				vous pourrez :<br>
					* choisir le colonnage des Excels<br>
					* afficher les différents fichiers de paramètres<br>
				</p>
				<a class="ec-btn menuoption" href="exgoogleconnectscreen.php">Ecran de connexion</a>
			</div>
			</article>
EOT;
	}
	$retour .= getFooter();
	$retour .= '</main>';
	return $retour;
}