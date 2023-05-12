<?php
declare(strict_types=1);
//require_once __DIR__ . '/../class/paramini.class.php';
require_once __DIR__ . '/../class/loggerrec.class.php';



// ============== affichage de la textarea contenant la trace
function getHtmlDebug($result) {
	$retour = <<<'EOT'
	<article style="border-style: inset;width:850px">
	<h2>Débug</h2>
	<div id="divlog">
	<pre>
	<textarea id="log" rows="200" cols="100">
	<pre>
EOT;
	$logger = LoggerRec::getInstance();
	$retour .= $logger->getLog();
	$retour .= <<<'EOT'
	<pre>
	</textarea>
	</pre>
	</div>
	</article>
EOT;
	return $retour;
}
?>