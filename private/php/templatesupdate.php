<?php
declare(strict_types=1);
/**
 * maj d'un template Excel utilisateur
 */
$reponse = [];
$reponse['status'] = 'KO';
$reponse['msg'] = 'Erreur inconnue';
$emailConnected = "inconnu";
$isConnected = false;
if(array_key_exists('emmaususerconnected',$_SESSION) && $_SESSION['emmaususerconnected'] == 'Y') {
    $isConnected = true;
    $emailConnected = $_SESSION['email'];
}

$errParm = false;
if (! array_key_exists('typemat', $_GET)) {
    $errParm = true;
    $reponse['msg']    = "templateupdate : param [typemat] non trouvé";
}else{
    if ( $_GET['typemat'] != 'pc' && $_GET['typemat'] != 'sm') {
        $errParm = true;
        $reponse['msg'] = "templateupdate : param [typemat] invalide [".$_GET['typemat']."]";
    }
}
if ($errParm) {
    $reponse['status'] = 'KO';
    echo json_encode($reponse);
    exit(0);
}
$typeMat = strtolower($_GET['typemat']);

$filename = $_SERVER['DOCUMENT_ROOT']."/../work/workingfiles/exceltemplates$typeMat.json";
$template = file_get_contents('php://input');
$templateJson = json_decode($template, true);

$templateName = $templateJson["templatename"];

if ( $templateJson['operation']== 'update') {
    // update
    if (preg_match('/[^A-Za-z_\-0-9\s]/', $templateName) OR str_starts_with($templateName,'*') OR str_starts_with($templateName,'_')) {
        // erreur
        $reponse['status'] = 'KO';
        $reponse['msg']    = "Le nom ne peut contenir que de minuscules, majuscules, chiffres, des espaces et des '-' et '_'. Il ne doit pas commencer par '-' ni '_' ni '*'";
    }else{
        $templateJson["updatedby"] = $emailConnected ;
        $templateJson["updatedtime"] = date("Y-m-d H:i:s");

        @$data = file_get_contents($filename);
        if (!$data) {$data='[]';}
        $dataJson = json_decode($data, true);
        $dataJson[$templateName] = $templateJson;
        $dataText = json_encode($dataJson);

        $fileopen=(fopen("$filename",'w'));
        fwrite($fileopen,$dataText);
        fclose($fileopen);
        $reponse['status'] = 'OK';
        $reponse['msg'] = 'Mise à jour du modèle "' .$templateJson["templatename"]. '" effectuée.';
    }
}else{
    // delete
    if (str_starts_with($templateName,'*') OR str_starts_with($templateName,'_')) {
        $reponse['status'] = 'KO';
        $reponse['msg']    = "Les modèles commençant par '*' ou '_' ne peuvent pas être supprimés";
    }else{
    @$data = file_get_contents($filename);
        if (!$data) {$data='[]';}
        $dataJson = json_decode($data, true);
        unset($dataJson[$templateName]);
        $dataText = json_encode($dataJson);
        $fileopen=(fopen("$filename",'w'));
        fwrite($fileopen,$dataText);
        fclose($fileopen);
        $reponse['status'] = 'OK';
        $reponse['msg'] = '"'.$templateName. '" supprimé.';
    }
}
echo json_encode($reponse);