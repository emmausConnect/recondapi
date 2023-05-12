<?php
declare(strict_types=1);
require_once __DIR__.'/../class/formtableau.class.php';
require_once __DIR__.'/../class/loggerrec.class.php';

$trt = FormTableau::getInstance($debug);

$action = getAction();
switch ($action) {
    case 'execInit' :
		$trt->execInit();
		break;
	case 'execGet' :
		$trt->execGet();
		break;
	case 'execPost' :
		$trt->execPost();
		break;
}


function getAction() {
	$action = "";
	$_GET = array_change_key_case($_GET, CASE_LOWER);
	if ($_SERVER["REQUEST_METHOD"] == 'GET') {
		if (array_key_exists("cpu", $_GET) or array_key_exists("pc", $_GET)) {
			//execGet();
			$action = 'execGet';
		}else{
			//affichage initial
			//execPost();
			$action = 'execInit';
		}
	}else{
		//execPost();
			$action = 'execPost';
	}
	return $action;
}



?>