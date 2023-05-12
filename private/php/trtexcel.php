<?php
declare(strict_types=1);
require_once __DIR__.'/../class/trtexcel.class.php';
$uploadType = "";
if (array_key_exists("upload", $_GET)) {
    $uploadType=$_GET['upload'] ;
}else{ 
    echo 'Type de chargement invalide ('.__LINE__.')';
    exit();
}

$trt = TrtExcel::getInstance($uploadType, $debug);
$trt->trtExcel();
?>