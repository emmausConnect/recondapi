<?php
declare(strict_types=1);
require_once __DIR__.'/pageheaderhtml.php';
require_once __DIR__.'/pagegoogleconnecthtml.php';

$page = getHtmlGoogleConnect();
$page .= "</body></html>";
echo $page;
