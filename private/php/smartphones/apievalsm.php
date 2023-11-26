<?php
declare(strict_types=1);
/**
 * traite un appel de type API
 * 
 * ne passe pas par le traitemet "index.php'
 */
require_once __DIR__.'/../../class/smartphones/apievalsm.class.php';
require_once __DIR__.'/../../class/loggerrec.class.php';

$retour = [];
$retour['status'] = "";
$retour['msg']    = "";
$retour['data']   = [];

$outfmt = 'json';
if (  array_key_exists("outfmt", $_GET) ) {
    $outfmt = $_GET['outfmt'];
}

$outdata = 'c';
if (array_key_exists("outdata", $_GET)) {
    if (in_array(strtolower($_GET['outdata']), ['c', 'i', 'in'])) { 
        $outdata = strtolower($_GET['outdata']);
    }else{
        $errMsg[] = __FILE__."[param 'outdata' invalide : {$_GET['outdata']}]";
    }
}

$trt = APIevalSm::getInstance($debug);
$result = $trt->execGet();

$categorieSm = "";

if ($outfmt == 'json') {
    if (count($result->getErrMsg()) == 0 ) {
        //$evalresult  = $result->getEvaluationSmCl();
        $retour = [];
        $retour['status'] = "0";
        $retour['msg']    = "";
        $retour['data']   = $result->getEvaluationSmCl()->getResultAsArray();
    }else{
        //$categorieSm = 'erreur :' . implode("|",$result->getErrMsg());
        $retour['status'] = "1";
        $retour['msg']    = 'erreur :' . implode("|",$result->getErrMsg());
    }
    echo json_encode($retour);
}else{
    $text = "";
    switch ($outdata) {
        case 'c' :
            if (count($result->getErrMsg()) == 0 ) {
                $text = $result->getEvaluationSmCl()->getCategoriePondereAlpha();
            }else{
                $text = 'erreur :' . implode("|",$result->getErrMsg());
            }
            break;
        case 'i' :
            // uniquement l'indice
            if (count($result->getErrMsg()) == 0 ) {
                $text = $result->getEvaluationSmCl()->getIndice();
            }else{
                $text = 'erreur :' . implode("|",$result->getErrMsg());
            }
            break;
        case 'in' :
            // uniquement l'indice et note indice
            if (count($result->getErrMsg()) == 0 ) {
                $text = $result->getEvaluationSmCl()->getIndice() . "|" . $result->getEvaluationSmCl()->getNoteIndice();
            }else{
                $text = 'erreur :' . implode("|",$result->getErrMsg());
            }
            break;
    }
    echo $text;
}
