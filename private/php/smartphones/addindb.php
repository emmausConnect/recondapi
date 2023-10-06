<?php
declare(strict_types=1);

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
require_once 'utilsm.php';

try {

    $retour = ['status' => '1', 'msg'=>[], 'data'=>[]];
    $action     = getPostValue('action',null);

    // marque ... smartphone à insérer
    $marque     = getPostValue('marque',null);
    $modele     = getPostValue('modele',null);
    $ram        = getPostValue('ram',null);
    $stockage   = getPostValue('stockage',null);
    $indice     = getPostValue('indice',null);
    $os         = getPostValue('os',null);
    $url        = getPostValue('url',null);
    $origine    = getPostValue('origine',null);
    $tocheck    = getPostValue('crtorigine','Y');


    // marque2 ... sont le modèle
    $marque2    = getPostValue('marque2',null);
    $modele2    = getPostValue('modele2',null);
    $ram2       = getPostValue('ram2',null);
    $stockage2  = getPostValue('stockage2',null);

    $username   = getPostValue('username',null);

    if ($action == null) {
        $retour['status'] = 0;
        $retour['msg']    = "le param action est null";
        echo json_encode($retour);
        exit();
    }

    if ( ! in_array($action, ['copy', 'insert'])) {
        $retour['status'] = 0;
        $retour['msg']    = "le param action est invalide : [$action]. Il faut 'copy', 'insert'";
        echo json_encode($retour);
        exit();
    }

    if ($action == 'copy') {
        if ( 
               $marque === null 
            || $modele === null 
            || $ram === null 
            || $stockage === null 
            || $marque2 === null 
            || $modele2 === null 
            || $ram2 === null 
            || $stockage2 === null 
            || $username === null
            )
        {
            $retour['status'] = 0;
            $retour['msg']    = "Un des paramètres pour la copie est null";
            echo json_encode($retour);
            exit();
        }
    }
    if ($action == 'insert') {
        if ( 
               $marque === null 
            || $modele === null 
            || $ram === null 
            || $stockage === null
            || $indice === null 
            || $username === null
            )
        {
            $retour['status'] = 0;
            $retour['msg']    = "Un des paramètres pour la copie est null";
            echo json_encode($retour);
            exit();
        }
    }

    $supressSpacesBool = true;
    $dbInstance = DbManagement::getInstance();
    $db = $dbInstance->openDb();
    $tableName = $dbInstance->tableName('smartphones');

    // recherche des infos du modèle
    if ($action == 'copy') {
        $sqlQuery = "SELECT * from $tableName 
            where marque=:marque and modele=:modele and ram=:ram and stockage=:stockage;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque'   => formatKey($marque2,$supressSpacesBool),
            'modele'   => formatKey($modele2,$supressSpacesBool),
            'ram'      => formatKey($ram2,$supressSpacesBool),
            'stockage' => formatKey($stockage2,$supressSpacesBool)
            ]);
        $rowModele = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rowModele == false) {
            $retour['status'] = 0;
            $retour['msg']    = "smartphone modèle non trouvé";
            echo $retour;
            exit();
        }
        $indice = $rowModele['indice'];
        $os     = $rowModele['os'];
        $url    = $rowModele['url'];
        $crtorigine = $origine.'['.$marque2.'] ['.$modele2.'] ['.$ram2.'] ['.$stockage2.']';
        $crttype = 'duplic';

    }else{
        $crttype = 'manuel';
        $crtorigine = $origine;
    }

    // insertion du nouveau smartphone
    $sqlQueryInsert = "INSERT INTO $tableName(marque, modele, ram, stockage, indice, os, url, 
    crtorigine, crtby, crtdate, crttype, tocheck ) 
    VALUES (:marque, :modele, :ram, :stockage, :indice, :os, :url, :crtorigine, :crtby, :crtdate, :crttype, :tocheck);";
    $insertRecipe = $db->prepare($sqlQueryInsert);
    $insertRecipe->execute([
        'marque'   => $marque,
        'modele'   => $modele,
        'ram'      => $ram,
        'stockage' => $stockage,
        'indice'   => $indice,
        'os'       => $os,
        'url'      => $url,
        'crtorigine'  => $crtorigine,
        'crtby'    => $username,
        'crtdate'  => date ('Y-m-d H:i:s'),
        'crttype'  => $crttype,
        'tocheck'  => $tocheck
    ]);

    $retour['status'] = 1;
    $retour['msg']    = "Smartphone ajouté";

} catch (Exception $e) {
    $retour['status'] = 0;
    $retour['msg']    = 'Exception reçue : ' .  $e->getMessage();
}
echo json_encode($retour);
