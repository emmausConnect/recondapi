<?php
declare(strict_types=1);

require __DIR__."/pageaccueilhtml.php";

$page = getHtmlAccueil();
$page .= "</body></html>";
echo $page;
