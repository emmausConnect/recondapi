<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 'On');

//require_once __DIR__.'/../utildb01.php';
$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/contexte.class.php';
require_once $path_private_class .'/db/dbmanagement.class.php';
//$context = Contexte::getInstance();

if ($_GET['pw'] != '220556') {
    echo 'transaction interdite';
    exit(1);
}

try {
    $dbInstance = DbManagement::getInstance();
    $db = $dbInstance->openDb();
    $tableName = $dbInstance->tableName('smartphones');

    $sqlQuery = "DROP TABLE IF EXISTS `$tableName`";

    $preparedSql = $db->prepare($sqlQuery);
    $preparedSql->execute();

        //    `title` VARCHAR(300) NOT NULL , 
    $sqlQuery = "CREATE TABLE `$tableName` (
        `marque`     VARCHAR(300) NOT NULL ,
        `marque_ns`  VARCHAR(300) NOT NULL ,
        `modele`     VARCHAR(300) , 
        `modele_ns`  VARCHAR(300) ,
        `modele_synonyme`  VARCHAR(300) ,
        `ram`        INT          NOT NULL ,
        `stockage`   INT          NOT NULL , 
        `indice`     INT          NOT NULL , 
        `os`         VARCHAR(50) , 
        `url`        VARCHAR(2048),
        `crtorigine` VARCHAR(100),
        `crtby`      VARCHAR(100),
        `crtdate`    DATETIME,
        `crttype`    VARCHAR(10),
        `updorigine` VARCHAR(100),
        `updby`      VARCHAR(100),
        `upddate`    DATETIME,
        `updtype`    VARCHAR(10),
        `tocheck`    CHAR(1) NOT NULL ,
        CONSTRAINT tocheck_YN CHECK (tocheck ='Y' || tocheck ='N')
         )
        ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci;";
        // crttype    crtorigine 
        // 'excel'     nom excel
        // 'duplic'    nom du sm
        // 'manuel'    text
    $preparedSql = $db->prepare($sqlQuery);
    $preparedSql->execute();
    echo "table $tableName crée";
    //ALTER TABLE `connexiobeta`.`rc_smartphones` ADD UNIQUE `ui_01` (`title`, `modele`, `ram`, `stockage`); 

    $sqlQuery = "CREATE UNIQUE INDEX ui_01
        ON `$tableName` (`marque`, `modele`, `ram`, `stockage`);";
    $preparedSql = $db->prepare($sqlQuery);
    $preparedSql->execute();

    echo "<br>index table $tableName crée";

}catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

