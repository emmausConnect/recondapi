<?php
declare(strict_types=1);
/**
 * affcihele menu de gestion
 */

require_once __DIR__.'/pageheaderhtml.php';

//$debugValue = getDebugFromBrowserSessionCookie();
//echo $debugValue.'<br>';
echo getHtmlHead();

echo '</head><body class="body_flex">';
echo "<main>";
echo getHtmlHeader();
echo '<hr>';
echo 'Pour avoir la description du calcul, <a href="exdisplaydoc.php">cliquez ici</a><br>';
echo '<hr>';
$msg= "Ce fichier contient les mêmes informations que le programme d'Audit";
afficherFichier('../private/config/param.ini', true, $msg);

$msg  = <<<'EOT'
Ce fichier permet de transformer un texte de CPU en un autre afin de pouvoir en trouver les caractéristiques<br>
Ceci est parfois nécessaire car les contructeurs de PC respectent rarement les normes de nommage des CPU<br>
Par exemple, un PC fournit <b>'CORE I5 M 520 2.40 GHZ'</b> comme nom de CPU, qui ne permet pas d'en trouver l'indice dans la base<br>"
il devrait s'appeler <b>'Intel Core i5-520M @ 2.40GHz'</b> qui lui est bien dans la base<br>
EOT;
afficherFichier('../private/data/cputranscodagedata.json', true, $msg);

$msg  = <<<'EOT'
Ce fichier contient les nom de CPU pour lesquels on n'a pas trouvé d'info dans la base<br>
Il faut chercher manuellement son nom normalisé et le rajouter au fichier 'cputranscodagedata.json'<br>
EOT;

//afficherFichier(__DIR__.'/../../work/workingfiles/cpunotfound.txt', true, $msg);
afficherFichier('../work/workingfiles/cpunotfound.txt', true, $msg);

$msg  = <<<'EOT'
Ce fichier est utilisé pour éviter :<br>
* de faire un accès au site web afin d'avoir les caractéristique du CPU<br>
* de ne pas fonctionner lorsque le site est en panne ou a changé sa mise en page<br>
Pour chaque CPU on y stocke :<br>
* son indice<br>
* la date d'ajout<br>
Si lors du traitement on s'apperçoit que la date est trop ancienne :<br>
* on lit le site web<br>
* on met à jour ce fichier<br>
EOT;

afficherFichier('../private/data/cpuindicecachedata.json', true, $msg);

echo getFooter();
echo "</main>";
?>


</body>

<?php
function afficherFichier($f, $pre=true, $msg="") {
	echo '<article style="clear: left;">';
	echo "<p><b>$f</b></p>";
	if ($msg!="") {
		echo "<p>$msg</p>";
	}
	if (!file_exists($f)) {
		$data = "fichier non trouvé";
	}else{
		try {
			@$data = file_get_contents($f);
		}catch (Exception $ex) {
			$data = "fichier non trouvé";
		}
	}
	echo '<article class="article2" style="clear: left;">';
	if ($pre) {
		echo "<pre>".htmlentities($data)."</pre>";
	}else{
		echo htmlentities($data);
	}
	echo "</article>";
	echo "</article>";
}
