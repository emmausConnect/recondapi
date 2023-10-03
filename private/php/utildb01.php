<?php
declare(strict_types=1);
// require_once __DIR__."/../class/paramini.class.php";
// require_once __DIR__."/../class/contexte.class.php";

// function openDb() {

//     $contexte      = Contexte::getInstance();
//     $environnement = $contexte->getEnvironnement();

//     if (! in_array($environnement, ['PROD','TEST','LOCAL'])) {
//         echo "le type d'environnement [$environnement] n'est pas défini dans la classe contexte. Ouverture de db impossible";
//         exit(1);
//     }

//     $extDbParam = strtolower($environnement);
//     $paramDbArray = ParamIni::getInstance('*paramconfidentiel.ini')->getParam();

//     if (! array_key_exists('db'.$extDbParam, $paramDbArray)) {
//         echo "param BDD non trouvé pour [$extDbParam]";
//         exit(1);
//     }
//     $paramThisDbArray = $paramDbArray['db'.$extDbParam];
//     $msg = "";
//     if (! array_key_exists('env', $paramThisDbArray)) {
//         $msg .= "[env] non trouvé dans le param de la BDD";
//     }
//     if (! array_key_exists('servername', $paramThisDbArray)) {
//         $msg .= " | [servername] non trouvé dans le param de la BDD";
//     }
//     if (! array_key_exists('dbname', $paramThisDbArray)) {
//         $msg .= " | [dbname] non trouvé dans le param de la BDD";
//     }
//     if (! array_key_exists('username', $paramThisDbArray)) {
//         $msg .= " | [username] non trouvé dans le param de la BDD";
//     }
//     if (! array_key_exists('password', $paramThisDbArray)) {
//         $msg .= " | [password] non trouvé dans le param de la BDD";
//     }
//     if (! array_key_exists('tprefix', $paramThisDbArray)) {
//         $msg .= " | [tprefix] non trouvé dans le param de la BDD";
//     }

//     $env        = $paramThisDbArray['env'];
//     $servername = $paramThisDbArray['servername'];
//     $dbname     = $paramThisDbArray['dbname'];
//     $username   = $paramThisDbArray['username'];
//     $password   = $paramThisDbArray['password'];
//     $tprefix    = $paramThisDbArray['tprefix'];

//     if(strtoupper($env) !== strtoupper($environnement)) {
//         $msg .= " | Il y a incohérence entre les noms d'environnement du param de la BD est du fichier environnement";
//     }

//     if ($msg != "") {
//         echo $msg;
//         exit(1);
//     }
//     $contexte->setTprefix($tprefix);
// 	$db = new PDO(
// 		"mysql:host=$servername;dbname=$dbname",
// 		"$username",
// 		"$password"
// 	);
// 	return $db;
// }

// function tableName($table) {
//     $context = Contexte::getInstance();
//     $tprefix = $context->getTprefix();
//     return $tprefix.$table;
// }