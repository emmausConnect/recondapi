<?php
declare(strict_types=1);
require_once __DIR__.'/../class/apieval.class.php';
require_once __DIR__.'/../class/loggerrec.class.php';

$trt = APIeval::getInstance($debug);
$trt->execGet();
