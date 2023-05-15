<?php
declare(strict_types=1);

// tentative de générer un fichier pour les tests unitaires
// pas terminé ;-)


require 'loggerrec.class.php';
$debug = 0;
header('Cache-Control: no-cache');

$logger = LoggerRec::getInstance();
$logger->setDebugLevel(1);
$param  = getParam();

$logger->addLogDebugLine($param);
$logger->addLogDebugLine($param["seuilsCPU"], 'param["seuilsCPU"]');
$date = new DateTime;
$myFileName = "testfile_".$date->getTimestamp().".txt";
$myfile = fopen("../public/upload/jeudetest".$myFileName, "a");

$data = "<table>";
$data .= getTest('HDD');
$data .= getTest('SSD');
$data .= "</table>";

echo $data;

function getTest($disk) {
	GLOBAL $param, $myfile;
	$retour = "";
	foreach ($param["seuilsCPU"] as $cpuindice => $val) {
		for ($cpui = -1; $cpui <=1; $cpui++) {
			$cpuival =$cpuindice+$cpui;
			//$temp = fwrite($myfile, "<tr><td>CPU $cpuival</td><td></td><td> Go</td><td> Go</td></tr>");
			foreach ($param['seuils'.$disk] as $HDDindice => $val) {
				for ($HDDi = -1; $HDDi <=1; $HDDi++) {
					$HDDival =$HDDindice+$HDDi;
					foreach ($param['seuilsRAM'] as $RAMindice => $val) {
						for ($RAMi = -1; $RAMi <=1; $RAMi++) {
							$RAMival =$RAMindice+$RAMi;
							$line = "<tr><td>EMMAUSCONNECT $cpuival</td><td>$disk</td><td>$HDDival Go</td><td>$RAMival Go</td></tr>";
							$retour .= $line;
							fwrite($myfile,$line);
						}
					}
				}
			}
		}
	}
	return $retour;
}


/**
 * lit me fichier param.ini
 */
function getParam() {
	$f = file_get_contents('../private/param.ini');
	$param = parse_ini_string($f, true);
	ksort($param["seuilsCPU"], SORT_NUMERIC);
	ksort($param["seuilsRAM"], SORT_NUMERIC);
	ksort($param["seuilsSSD"], SORT_NUMERIC);
	ksort($param["seuilsHDD"], SORT_NUMERIC);
	ksort($param["seuilsCatPC"], SORT_NUMERIC);
	return $param;
}