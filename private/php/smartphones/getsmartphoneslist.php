<?php
declare(strict_types=1);

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
require_once $path_private_class .'/smartphones/evaluationsm.class.php';
require_once 'utilsm.php';


$result = ['status' => '1', 'msg'=>[], 'data'=>[]];
$marque         = getGetValue('marque',null);
$modele         = getGetValue('modele',null);
if ($marque == null) {
    $result['status'] = '0';
    $result['msg']    = 'paramètre [marque] non renseigné';
}
if ($modele == null) {
    $result['status'] = '0';
    $result['msg']    = 'paramètre [modele] non renseigné';
}
if ( $result['status'] == "1" ){
    $dbInstance = DbManagement::getInstance();
    $db = $dbInstance->openDb();
    $tableName = $dbInstance->tableName('smartphones');
    $sqlQuery = "SELECT * from $tableName 
        where marque=:marque and modele = :modele";
    $stmt = $db->prepare($sqlQuery);
    $stmt->execute([
        'marque' =>formatKey($marque,true),
        'modele' =>formatKey($modele,true),
        ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) != 0) {
        $evaluationSm = EvaluationSm::getInstance();
        foreach($rows as $row) {
            $categorie = $evaluationSm->calculCategorie($row['ram'], $row['stockage'], $row['indice']);
            $row['categorie'] = $categorie;
            array_push($result['data'], $row);
        }
    }
}
echo json_encode($result);

