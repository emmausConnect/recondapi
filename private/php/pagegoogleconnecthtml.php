<?php
declare(strict_types=1);
require_once __DIR__.'/googleconnect.php';

/****************************************************************************
 * HTML page GoogleConnect
**************************************************************************** */
function getHtmlGoogleConnect() {
	$retour  = getHtmlHead();
	$retour  .= <<<"EOT"
	</head>
    <body class="body_flex" onload="doOnLoad()">
	<main>
EOT;
	$retour .= getHtmlHeader();

	$connectUrl = googleConnect('geturl');
	$retour .= <<<"EOT"
	<article>
	<h2 class="menutitre">Identification par Google</h2>
		<div class="menuoption" style="padding:0px 5px 0px 20px;">
			<p>Pour accéder aux optionx de gestion, veuillez vous connecter avec votre compte <strong>@Emmaus-Connect.org</strong><br>
			<br>
			<i>Si vous utilisez une adresse autre que @Emmaus-Connect.org vous aurez un message d'erreur de la part de Google
			et vous devrez certainement relancer votre navigateur.</i>
			</p>
			<br>
			<a class="ec-btn menuoption" href="$connectUrl">Connexion avec Google</a>
			<div class="div-menu-bas">
				<a class="ec-btn ec-nav" href="/" style="float: left;">Retour</a><br>
			</div>
		</div>
	</article>
EOT;
	$retour .= getFooter();
	$retour .= '</main>';
	return $retour;
}