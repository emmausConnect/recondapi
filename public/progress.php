<?php
declare(strict_types=1);
exit();
$debug = false;
$dureeMax = 180;   // secondes
$numberOfErrorMax = 10;
if (array_key_exists('debug',$_GET)) {
	if ($_GET['debug']==1) {
		$debug = true;
		$dureeMax = 20;   // secondes
		$numberOfErrorMax = 2;
		echo "<html></html>===== DEBUG ========<br>";
	}
}
if (! $debug) {
	header('Content-Type: text/event-stream');
}
// recommended to prevent caching of event data.
header('Cache-Control: no-cache'); 

$progressId=$_GET['id'];
$progressFile = "../work/progressfiles/$progressId.txt";

//LONG RUNNING TASK
$i = 1;
$fin = time() + $dureeMax;
$numberOfError = 0;

while (time() < $fin) {
	$erreur = "";
	$progress = 0;
	$msgFichierSuivi = "";
	@$lines = file($progressFile);
	if (! $lines) {
		$erreur = "FileNotFound $progressFile";
	}elseif (count($lines) == 0) {
		$erreur = "FileEmpty $progressFile";
	}
	if ($erreur != "") {
		$message = $erreur;
		++$numberOfError;
		if ($numberOfError > $numberOfErrorMax) {
			break;
		}
	}else{
		$progressArray = explode("\t", $lines[0]);
		$progress = $progressArray [0];
		$msgFichierSuivi = ((count($progressArray)>1) ? $progressArray[1] : "");
		if ($debug) {
			$msgFichierSuivi  .= " $progressId";
		}
		$message  = "trt en cours : ". $msgFichierSuivi;
	}
	$message = str_pad($message,1000,' ');
	send_message($i, $message, $progress);

	if ($progress >= 100) {
		break;
	}
    sleep(3);
	$i = $i+1;
}
if (time() > $fin) {
	send_message('CLOSE', "progression impossible à suivre ... trt trop long  $msgFichierSuivi",0);
}else{
	send_message('CLOSE', "fin du suivi $msgFichierSuivi",100);
}

function send_message($id, $message, $progress) {
	GLOBAL $debug;
	if ($debug) {
		echo "$id   $message   $progress\n";
	}else{
		send_message_event($id, $message, $progress);
	}
}

function send_message_event($id, $message, $progress) {
    $d = array('message' => $message , 'progress' => $progress);
    echo "id: $id" . PHP_EOL;  // transmet le close
    echo "event: progress" . PHP_EOL;
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;
     
    ob_flush();
    flush();
}