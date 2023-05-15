<?php
declare(strict_types=1);
/**
 * affiche le menu d'accueil
 */
require __DIR__."/pageaccueilhtml.php";

$page = getHtmlAccueil();
$page .= "</body></html>";
echo $page;
