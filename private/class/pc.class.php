<?php
declare(strict_types=1);

require_once __DIR__.'/util01.class.php';

class PC {
	
	private string $pcModel="";
	
	/**
     * tableau contenant une liste de CPU.
	 * c'est le premier dont on trouve le résultat qui est utilisé
	 */
	private array $cpuTextInputArray;
	
	private ?string $tailleRam;
	private ?string $tailleRamUniteSetParDefaut;
	private array   $diskArray; // initialisé à l'instanciation
	private ?string $uniteParDefaut; // initialisé à l'instantiation
	private ?string $typeDiskParDefaut; // initialisé à l'instantiation
	private $logger; // initialisé à l'instantiation

	//format du text cpu
	//private $fmtCpu;

    private function __construct()
    {
    }

    public static function getInstance() : PC
    {
        $c = new PC();
		$c->resetPc();
		$c->logger = LoggerRec::getInstance();
        return $c;
    }

    // raz  ==============================	
	public function resetPc() : PC {
		$this->pcModel="";
		$this->cpuTextInputArray = [];
		$this->diskArray =[];
		$this->uniteParDefaut = ""; // GO TO <div class=""></div>
		$this->typeDiskParDefaut = ""; // HDD ...
		return $this;
	}

    // pcModel ==============================	
	public function setPcModel(string $pcModel) : PC {
		$this->pcModel = preg_replace( '/[^[:print:]]/', ' ',$pcModel);
		return $this;
	}
	public function getPcModel() : ?string
	{
		return $this->pcModel;
	}

    // uniteParDefaut ==============================	
	public function setUniteParDefaut(string $uniteParDefaut) : PC {
		$this->uniteParDefaut = $uniteParDefaut;
		return $this;
	}
	public function getUniteParDefaut() : ?string
	{
		return $this->uniteParDefaut;
	}

	// type disk pardefaut ==============================	
	public function setTypeDiskParDefaut(string $typeDiskParDefaut) : PC {
		$this->typeDiskParDefaut = $typeDiskParDefaut;
		return $this;
	}
	public function getTypeDiskParDefaut() : ?string
	{
		return $this->typeDiskParDefaut;
	}

    /**
	 * nettoie et stocke la liste des textes CPU
	 * @param array $cpuTextInputArray 
	 * @return PC
	 */	
	public function setCpuTextInputArray(array $cpuTextInputArray) : PC {
		for($x = 0; $x < count($cpuTextInputArray); $x++) {
			$cpuTextInputArray[$x] = Util01::cleanString($cpuTextInputArray[$x]);
		}
		$this->cpuTextInputArray = $cpuTextInputArray;
		return $this;
	}
	public function getCpuTextInputArray() :?array
	{
		return $this->cpuTextInputArray;
	}

    /**
	 * nettoie et stocke la taille RAM du PC
	 * @param string $tailleRam
	 * @return PC
	 */	
	public function setTailleRam(string $tailleRam) : PC {
		$tailleRam = Util01::cleanString($tailleRam);
		if (is_numeric($tailleRam) && $this->uniteParDefaut!= '') {
			$tailleRam .= $this->uniteParDefaut;
			$this->tailleRamUniteSetParDefaut = "Y";
		}
		$this->tailleRam =$tailleRam;
		return $this;
	}
	public function getTailleRam() :?string
	{
		return $this->tailleRam;
	}

    // disk ==============================
	/**
	 * nettoie et stocke la taille et le type d'un disque
	 * @param integer $n
	 * @param string $tailleDisk
	 * @param string $type
	 * @return PC
	 */
	public function setDisk(int $n, string $tailleDisk, string $type) : PC {
		$typeDiskSetToDefault = "N";
		$uniteSetToDefault    = "N";
		$tailleDisk = Util01::cleanString($tailleDisk);
		$type = Util01::cleanString($type);
		// le disque 1 est obligatoire
		// pour les autres disques, ils ne sont traités que si taille ou type sont renseignés
		if ($n==1 or ($tailleDisk !="" or $type != "")) {
			$msg = "disk n [$n] tailleDisk [$tailleDisk] type [$type]";
			$this->logger->addLogDebugLine($msg, 'setDisk');
			if ($tailleDisk != "") {
				if ($type=='' && $this->typeDiskParDefaut != '') {
					$type = $this->typeDiskParDefaut;
					$typeDiskSetToDefault = "Y";
				}
				$typeUpper =strtoupper($type);
				if (stripos($typeUpper, "HDD") !== false) {
					$type = "HDD";
				}else if  (stripos($typeUpper, "NVME") !== false) {
					$type = "NVME";
				}else if (stripos($typeUpper, "SSD") !== false) {
					$type = "SSD";
				}
			}
			
			if (is_numeric($tailleDisk) && $this->uniteParDefaut!= '') {
				$tailleDisk .= $this->uniteParDefaut;
				$uniteSetToDefault = "Y";
			}
			// correction de l'erreur de frappe qui met un zéro à la place d'un "O" dans l'unité
			if ( substr($tailleDisk,-1 ) == "0") {
				$tailleDisk = substr($tailleDisk,0, -1 )."O";
			}

			$this->diskArray[$n] = ["taille" => $tailleDisk, "type" => $type,
				 'typeDiskSetToDefault'=> $typeDiskSetToDefault, 'uniteSetToDefault'=> $uniteSetToDefault];
		}
		return $this;
	}
	
	public function getDisk($n) : ?array
	{
		if (isset($this->diskArray[$n])) {
			return $this->diskArray[$n];
		}else{
			return null;
		}
	}

	public function toString() {
		$retour  = "pcModel = [$this->pcModel] |";
		$retour .= "tailleRam = [$this->tailleRam] |";
		$retour .= "uniteParDefaut = [$this->uniteParDefaut] |";
		$retour .= "cpuTextInputArray = \n";
		foreach ($this->cpuTextInputArray as $cputext) {
			$retour .= $cputext."\n";
		}
		$retour .= "cpuTextInputArray = \n";
		foreach ($this->diskArray as $disk) {
			foreach($disk as $key => $val) {
				$retour .= "$key => $val";
			}
		}
	}


	//******************************************************************* */
	function __call($name, $arguments)
    {
        throw new Exception("Appel de la méthode non statique inconnue : '$name'");
    }

    static function __callStatic($name, $arguments)
    {
        throw new Exception("Appel de la méthode statique inconnue : '$name'");
    }

    function __set($name, $value)
    {
        throw new Exception("Set d'une propriété inconnue : '$name'");
    }

    function __get($name)
    {
        throw new Exception("Get d'une propriété inconnue : $name");
    }	
}
?>