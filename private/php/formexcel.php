<?php
declare(strict_types=1);
require_once __DIR__.'/../class/formexcel.class.php';

$trt = FormExcel::getInstance($debug);
$trt->displayForm();

?>