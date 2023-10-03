<?php
declare(strict_types=1);

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
require_once 'utilsm.php';

try {

    $retour = ['status' => '1', 'msg'=>[], 'data'=>[]];

    $marque     = getPostValue('marque',null);
    $modele     = getPostValue('modele',null);
    $ram        = getPostValue('ram',null);
    $stockage   = getPostValue('stockage',null);
    $marque2    = getPostValue('marque2',null);
    $modele2    = getPostValue('modele2',null);
    $ram2       = getPostValue('ram2',null);
    $stockage2  = getPostValue('stockage2',null);
    $username   = getPostValue('username',null);

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
        $retour['msg']    = "un des param est null";
        echo json_encode($retour);
        exit();
    }
    $supressSpacesBool = true;
    $dbInstance = DbManagement::getInstance();
    $db = $dbInstance->openDb();
    $tableName = $dbInstance->tableName('smartphones');
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

    $sqlQueryInsert = "INSERT INTO $tableName(marque, modele, ram, stockage, indice, os, url, 
    origine, crtby, crtdate, crttype ) 
    VALUES (:marque, :modele, :ram, :stockage, :indice, :os, :url, :origine, :crtby, :crtdate, :crttype);";
    $insertRecipe = $db->prepare($sqlQueryInsert);
    $insertRecipe->execute([
        'marque'   => $marque,
        'modele'   => $modele,
        'ram'      => $ram,
        'stockage' => $stockage,
        'indice'   => $rowModele['indice'],
        'os'       => $rowModele['os'],
        'url'      => $rowModele['url'],
        'origine'  => '['.$marque2.'] ['.$modele2.'] ['.$ram2.'] ['.$stockage2.']',
        'crtby'    => $username,
        'crtdate'  => date ('Y-m-d H:i:s'),
        'crttype'  => 'duplic'

    ]);

    $retour['status'] = 1;
    $retour['msg']    = "Smartphone ajouté";

} catch (Exception $e) {
    $retour['status'] = 0;
    $retour['msg']    = 'Exception reçue : ' .  $e->getMessage();
}
echo json_encode($retour);

