<?php
declare(strict_types=1);
/**
 * gestion des templates excel des utilisateurs
 */
$reponse = [];
$reponse['status'] = 'KO';
$reponse['msg'] = 'Erreur inconnue';
$typeMat = "";
$errParm = false;
if (! array_key_exists('typemat', $_GET)) {
    $errParm = true;
    $reponse['msg']    = "param [typemat] non trouvé";
}else{
    if ( $_GET['typemat'] != 'pc' && $_GET['typemat'] != 'sm') {
        $errParm = true;
        $reponse['msg'] = "param [typemat] non trouvé ou invalide [".$_GET['typemat']."]";
    }
}
if ($errParm) {
    $reponse['status'] = 'KO';
    echo json_encode($reponse);
    exit(0);
}

$typeMat = strtolower($_GET['typemat']);

$filename = $_SERVER['DOCUMENT_ROOT']."/../private/data/exceltemplatescst".$typeMat.".json";
@$data2 = file_get_contents($filename);
if (!$data2) {$data2='[]';}
$data2Json = json_decode($data2, true); // string -> var

$filename = $_SERVER['DOCUMENT_ROOT']."/../work/workingfiles/exceltemplates".$typeMat.".json";
@$data1 = file_get_contents($filename);
if (!$data1) {$data1='[]';}
$data1Json = json_decode($data1, true); // string -> var

$dataMergeJson= 
    array_merge(
        $data1Json,  // string -> var
        $data2Json)  // string -> var
;


$reponse['status'] = 'OK';
$reponse['msg'] = '';
$reponse['data'] = $dataMergeJson;

echo json_encode($reponse);