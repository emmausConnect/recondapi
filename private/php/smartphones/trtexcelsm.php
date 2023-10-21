<?php
declare(strict_types=1);
require_once __DIR__.'/../../class/smartphones/trtexcelsm.class.php';
$uploadType = "";
if (array_key_exists("upload", $_GET)) {
    $uploadType=$_GET['upload'] ;
}else{ 
    echo 'Type de chargement invalide ('.__LINE__.')';
    exit();
}

$trt = TrtExcelSm::getInstance($uploadType, $debug);
$trt->trtExcelSm();
?>