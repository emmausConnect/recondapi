<?php
declare(strict_types=1);
/**
 * traite un appel de type API
 * 
 * ne passe pas par le traitemet "index.php'
 */
require_once __DIR__.'/../class/apieval.class.php';
require_once __DIR__.'/../class/loggerrec.class.php';

$trt = APIeval::getInstance($debug);
$trt->execGet();
