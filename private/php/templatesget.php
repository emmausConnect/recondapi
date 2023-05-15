<?php
declare(strict_types=1);
/**
 * gestion des templates excel des utilisateurs
 */
$reponse = [];
$reponse['status'] = 'KO';
$reponse['msg'] = 'Erreur inconnue';

$filename = $_SERVER['DOCUMENT_ROOT']."/../private/data/exceltemplatescst.json";
@$data2 = file_get_contents($filename);
if (!$data2) {$data2='[]';}
$data2Json = json_decode($data2, true); // string -> var

$filename = $_SERVER['DOCUMENT_ROOT']."/../work/workingfiles/exceltemplates.json";
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