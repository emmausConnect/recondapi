<?php

declare(strict_types=1);

$path_private_class = $g_contexte_instance->getPath('private/class');
$path_private       = $g_contexte_instance->getPath('private');
require_once $path_private_class.'/db/dbmanagement.class.php';
require_once $path_private_class.'/util01.class.php';
//require_once $path_private_class.'/contexte.class.php';
require_once $path_private.'/php/smartphones/utilsm.php';

class UtilSm
{
    /**
     * Cherche la ligne smartphone en BDD.
     * Les clefs sont mise en forme avec formatKey
     *
     * @param [type] $marque
     * @param [type] $modele
     * @param [type] $ram
     * @param [type] $stockage
     * @param [type] $supressSpacesBool
     * @return void
     */
    static public function getSmartphoneRow($marque, $modele, $ram, $stockage, $supressSpacesBool)
    {
        $dbInstance = DbManagement::getInstance();
        $db = $dbInstance->openDb();
        $tableName = $dbInstance->tableName('smartphones');
        $sqlQuery = "SELECT * from $tableName 
            where marque=:marque and modele=:modele and ram=:ram and stockage=:stockage;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque'   => formatKey($marque, $supressSpacesBool),
            'modele'   => formatKey($modele, $supressSpacesBool),
            'ram'      => formatKey($ram, $supressSpacesBool),
            'stockage' => formatKey($stockage, $supressSpacesBool)
        ]);
        $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$smRow) {
            // on recherche sur le modèle sans espaces
            $sqlQuery = "SELECT * from $tableName 
            where marque=:marque and modele_ns=:modele_ns and ram=:ram and stockage=:stockage;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->execute([
                'marque'   => formatKey($marque, $supressSpacesBool),
                'modele_ns'=> str_replace(" ", "", $modele),
                'ram'      => formatKey($ram, $supressSpacesBool),
                'stockage' => formatKey($stockage, $supressSpacesBool)
            ]);
            $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $smRow;
    }

    static public function getIndice($marque, $modele, $supressSpacesBool)
    {
        $dbInstance = DbManagement::getInstance();
        $db = $dbInstance->openDb();
        $tableName = $dbInstance->tableName('smartphones');
        $sqlQuery = "SELECT * from $tableName 
            where marque=:marque and modele=:modele;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque'   => formatKey($marque, $supressSpacesBool),
            'modele'   => formatKey($modele, $supressSpacesBool),
        ]);
        $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$smRow) {
            // on recherche sur le modèle sans espaces
            $sqlQuery = "SELECT * from $tableName 
            where marque=:marque and modele_ns=:modele_ns;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->execute([
                'marque'   => formatKey($marque, $supressSpacesBool),
                'modele_ns'   => str_replace(" ", "", 'modele'),
            ]);
            $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if ($smRow) {
            return $smRow;
        }else{
            return '';
        }

    }

    //******************************************************************* */
    function __call($name, $arguments)
    {
        throw new Exception("Appel de la méthode non statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
    }

    static function __callStatic($name, $arguments)
    {
        throw new Exception("Appel de la méthode statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
    }

    function __set($name, $value)
    {
        throw new Exception("Set d'une propriété inconnue : $name, param : $value");
    }

    function __get($name)
    {
        throw new Exception("Get d'une propriété inconnue : $name");
    }
    //******************************************************************* */

}
