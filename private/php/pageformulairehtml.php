<?php

/** ***************************************************************
 *  Copie Colonnes textarea
 ***************************************************************  */
function getCopieColonnesHead() {
	$retour = getHtmlHead();
$retour .= <<<EOT
	<link rel="stylesheet" href="upload.css"/>
    </head>
EOT;
return $retour;
}


/** HTML de la demande par textarea
 * @param array $result tableau d'objet EvaluationPC
 * @param boolean $initial affichage initial => pas de donnée à récupérer du $_POST
 * @return string
 */
function getCopieColonnes(array $result, bool $initial=false) : string {
GLOBAL $debug;
$retour = <<<'EOT'
<article style="border-style:inset;width:850px">
	<div class="div-saisie">
		<h2>Test de l'algorithme</h2>
		Pour faciliter les tests, vous pouvez forcer l'indice du CPU en le nommant <strong>"EMMAUSCONNECT nnnnn"</strong><br>
		<br>
		<u>exemple :</u> "EMMAUSCONNECT 3160" sera traité comme un CPU d'indice 3160.
	</div>
	<div class="div-saisie">
	<form action="exformtableau.php" method="post">
EOT;
	$retour .= 'Renseignez les champs ci-dessous :<br>';

	$retour .= '<p><label for="inputcpu">cpu :</label>';
	$retour .= '<input id="inputcpu" name="inputcpu" type="text" size="80"   value="'.($initial?"":$_POST["inputcpu"]).'"/><br></p>';

	$retour .= '<p><label for="inputtailledisk">taille disque 1 :</label>';
	$retour .= '<input id="inputtailledisk" name="inputtailledisk" type="text" size="10" value="'.($initial?"":$_POST["inputtailledisk"]).'"/></p>';
	$retour .= '<p><label for="inputtypedisk">type disque 1 :</label>';
	$retour .= '<input id="inputtypedisk" name="inputtypedisk" type="text"     size="5"  value="'.($initial?"":$_POST["inputtypedisk"]).'"/></p>';

	$retour .= '<p><label for="inputtailledisk2">taille disque 2 :</label>';
	$retour .= '<input id="inputtailledisk2" name="inputtailledisk2" type="text" size="10" value="'.($initial?"":$_POST["inputtailledisk2"]).'"/></p>';
	$retour .= '<p><label for="inputtypedisk2">type disque 2 :</label>';
	$retour .= '<input id="inputtypedisk2" name="inputtypedisk2" type="text"     size="5"  value="'.($initial?"":$_POST["inputtypedisk2"]).'"/></p>';

	$retour .= '<p><label for="inputtailleram">taille RAM :</label>';
	$retour .= '<input id="inputtailleram" name="inputtailleram" type="text"   size="10" value="'.($initial?"":$_POST["inputtailleram"]).'"/></p>';


	$retour .= <<<EOT
	<br><input type="checkbox" id="checkboxDetailCalcul" name="checkboxDetailCalcul" value="checked" 
EOT;

	if(isset($_POST['checkboxDetailCalcul'])) {$retour .=  "checked";}
	$retour .= <<<'EOT'
	>
	Afficher le détail du calcul

	<br><input type="submit" value="Calculer"/>  
	</form>  
	</div>
	<div class="div-saisie">
	<h3>Résultat</h3>
	<pre>
	<textarea rows="10" cols="100">
EOT;
	if ($result != null) {
		foreach ($result as $evalPC) {
			$retour .= $evalPC->getCategoriePC() . "\n";
		}
	}

	$retour .= '</textarea></pre>'."\n";

	// affichage des erreurs s'il y en a
	if ($result != null) {
		$retour .='<h4>Erreurs</h4>';
		$listeErreurs = "";
		foreach ($result as $evalPC) {
			$errorsCl = $evalPC->getEvaluationErrorsCl();
			if ( $errorsCl->hasErrors() ) {
				$listeErreurs .= '<b>'.$evalPC->getPc()->getCpuTextInputArray()[0]. " | ";
				$listeErreurs .= $evalPC->getPc()->getTailleRam(). " | ";
				$listeErreurs .= $evalPC->getPc()->getDisk(1)['taille'] . " | ";
				$listeErreurs .= $evalPC->getPc()->getDisk(1)['type'] .'</b><br>';
				$listeErreurs .= $errorsCl->getErrorsMsgAsString('<br>');
			}
		}
		if ($listeErreurs == "") {
			$listeErreurs = 'aucune erreur';
		}
		$retour .= $listeErreurs;
	}


	if(isset($_POST['checkboxDetailCalcul'])) {
		$retour .='<script>'."\n";
		$retour .='  function affichedansonglet() {'."\n";
		$retour .='    var text = \'' .preg_replace("/\r|\n/", "", getHtmlHead()). '\';'."\n";
		$retour .='    text  += document.getElementById("detaillisible").outerHTML;'."\n";
		$retour .='    var w = window.open();'."\n";
		$retour .='    w.document.write(text);'."\n";	
		$retour .='  }'."\n";
		$retour .='</script>'."\n";
		$retour .='<h4> Détail du calcul<h4>'."\n";
		$retour .='<input type="button" value="afficher dans un onglet" onclick="affichedansonglet()">'."\n";
		if (count($result) >0) {
			$retour .= '<div id="detaillisible"><table><thead><tr>';
			$i=0;
			$pcAsArray = $result[0]->convertToArray();
			foreach ($pcAsArray as $key => $val) {
				$col[$i]=$key;
				$retour .= "<th>$key</th>";
				++$i;
			}
			$retour .= '</tr></thead><tbody>';
			foreach ($result as $evalPC) {
				$retour .= '<tr>';
				$evalPCAsArray = $evalPC->convertToArray();
				for ($i=0; $i<count($col); ++$i) {
					$retour .= '<td>'.$evalPCAsArray[$col[$i]].'</td>';
				}
				$retour .= '</tr>';
			}
			$retour .= '</tbody></table></div>';
		}
	}
	$retour .= '</div>';
	$retour .= '</article>';
	return $retour;

}