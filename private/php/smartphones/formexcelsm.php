<?php
declare(strict_types=1);
require_once __DIR__.'/../../class/smartphones/formexcelsm.class.php';

$trt = FormExcelSm::getInstance($debug);
$trt->displayForm();

?>