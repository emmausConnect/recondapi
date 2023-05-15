<?php
declare(strict_types=1);

/**
 * gestion du log
 * 
 * peut écrire dans un fichier
 * peut mémoriser en RAM (avec gestion d'une taille maxi)
 */
class LoggerRec {
	private  $log = ""; // concaténation de tous les message de log reçus
	private  $debugLevel = "0";
	private  $outFile = "";
	private  $ramOverflowFlag = false; // true si le messgae "dépassement de capacité" à déjà été ajouté à $log
	private  $ramMax = 100000;  // taille maximum de $log
	private  $numLigLog = 0;    // numérotation des ligne du log
	private static $instance = null;
	
    private function __construct() { }

	public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new LoggerRec();
        }
        return self::$instance;
    }

	public  function setOutfile($filePath) {
		$this->outFile = $filePath;
	}
	public  function addLogDebugLine($msg, $entete="") {
		$this->addLogDebugLineExec($msg, $entete, $force="");
	}

	public  function addLogDebugLineForce($msg, $entete="", $force="noram") {
		$this->addLogDebugLineExec($msg, date('d/m/Y G:i:s'). 'force ' .$entete, $force);
	}

	/**
	 * Undocumented function
	 * @param [type] $msg 
	 * @param string $entete
	 * @param string $force :
	 *     si != "" => fait le log même si on est pas en debug
	 *     si == "noram" => ne met pas le message en ram
	 */
	private  function addLogDebugLineExec($msg, $entete, $force) {
		if( $this->debugLevel == 1 or $force !="" ) {
			if (is_array($msg)) {
				$this->addLogLine(print_r($msg, true), $entete, $force);
			}else if (is_object($msg)){
				$this->addLogLine(var_export($msg, true), $entete, $force);
			}else{
				$this->addLogLine($msg, $entete, $force);
			}
		}
	}

	private function addLogLine($msg, $entete, $force) {
		$msgOut = "\n" . date("Y-m-d H:i:s").' | '. $this->numLigLog. ' | ';
		++$this->numLigLog;
		if($entete != "") {
			$msgOut .= "---- " .$entete. "----  ";
		}
		$msgOut .= $msg;
		if ($force != "noram") {
			if (strlen($this->log) < $this->ramMax) {
				$this->log .=  $msgOut;
			}else{
				if (! $this->ramOverflowFlag) {
					$this->log .=  "taille du log en ram atteinte : $this->ramMax <<<<<<";
				}
				$this->ramOverflowFlag = true;
			}
		}

		if ($this->outFile != "") {
			if (! file_exists($this->outFile)) {
				$f = fopen($this->outFile, "x+");
				fclose($f);
			}
			$f = fopen($this->outFile, 'a');
			if (!$f) {
				echo "Impossible d'ouvrir le fichier ($this->outFile)";
				exit;
			}
			$result = fwrite($f, $msgOut);
			if ($result === FALSE) {
				echo "Impossible d'écrire dans le fichier ($this->outFile)";
				fclose($f);
				exit;
			}
			fclose($f);

		}
	}

	public function getLog() {
		$debug   = $this->debugLevel;
		$retour  = "debugLevel = $debug \n";
		$retour .= $this->log;
		return $retour;
	}
	
	public function setDebugLevel($debugLevel) {
		$this->debugLevel = $debugLevel;
	}
	//******************************************************************* */
	function __call($name, $arguments)
    {
        throw new Exception("Appel de la méthode non statique inconnue : $name, param : ". implode(', ', $arguments). "\n");
    }

    static function __callStatic($name, $arguments)
    {
        throw new Exception("Appel de la méthode statique inconnue : $name, param : ". implode(', ', $arguments). "\n");
    }

    function __set($name, $value)
    {
        throw new Exception("Set d'une propriété inconnue : $name, param : $value");
    }

    function __get($name)
    {
        throw new Exception("Get d'une propriété inconnue : $name");
    }
}