<?php
declare(strict_types=1);
require_once __DIR__.'/evaluationindicecpu.class.php';
require_once __DIR__.'/util01.class.php';
require_once __DIR__.'/evaluationerrors.class.php';
require_once __DIR__.'/pc.class.php';
//require_once __DIR__.'/paramini.class.php';
require_once __DIR__.'/contexte.class.php';
require_once 'loggerrec.class.php';

/**
 * Calcul le code catégorie d'un objet Pc
 */
class EvaluationPc
{
    /** String contenant la demande, pour stockage et debug */
    private string  $demande = "";
    /** objet PC contenanbt la description du PC à évaluer */
    private PC      $pc;             // initialisé à l'instanciation
    /** n'est plus utilisé */
    private string  $fmtCpu = "";
    /** si un PC contient 2 disques, indiquele max de catégorie disque à prendre */
    /**  contient "err" si une erreur est survenue */
    private ?string $status = null;
    /** contient les erreurs rencontrées */
    private EvaluationErrors    $evaluationErrorsCl;
    /** résultat du calcul de l'indice CPU (avec calculs intermédiaires) */
    private EvaluationIndiceCpu $EvaluationIndiceCpuCl;
    /** code catégorie du CPU en nombre de points (1 2 3 ..) */
    private ?string $categorieCPU = null;
    /** code catégorie du RAM en nombre de points (1 2 3 ..) */
    private ?string $categorieRam = null;
    /** tableau contenant les codes catégorie (1 2 ...) des disques en nombre de points (1 2 3 ..).
     *  commence à l'indice 1.
     *  $categorieDiskArr[1] contient le code catégorie (1 2 ...) du disque 1
    */
    private ?array  $categorieDiskArr = null;
    /** somme des catégories (1 2 ...) des disques */
    private ?string $categorieDiskTotal = null;
    /** catégorie (1 2 ...) de l'ensemble des disques.
     *  ce n'est pas la sommes des catégories des disques qui est retenu.
    */
    private ?string $categorieDisk = null;
    /** Catégorie totale (1 2 ...) du PC, avant application de règles spécifiques. */
    private ?string $categorieTotal = null;
    /** code catégorie (A B ...) du PC avant application de règles spécifiques.*/
    private ?string $categoriePCcodeNormale = null;
    private ?string $categoriePCnormale = null;
    private ?string $categoriePCcodeMaxi = null;
    private ?string $categoriePCcode = null;
    private ?string $categoriePC = null;
    private $paramArray;
    private ?LoggerRec $logger;

    private function __construct()
    {
    }

    public static function getInstance( PC $pc) : EvaluationPc
    {
        $c = new EvaluationPc();
        $c->setPc($pc);
        $c->categorieDiskArr = [];
        $c->setEvaluationErrorsCl(EvaluationErrors::getInstance());
        $ctx = Contexte::getInstance();
        $c->paramArray = $ctx->getParamIniCls()->getParam();
        //$c->paramArray = ParamIni::getInstance(__DIR__.'/../config/param.ini')->getParam();
        
        $c->logger = LoggerRec::getInstance();
        return $c;
    }


    /** Calcule l'évaluation du PC
     * @return self
     */
    public function getEvalPc() : self
    {
        $erreurCalcul = false;
        $categoriePCcodeNormale = "";
        $categoriePCnormale = "";
        $categoriePCcodeMaxi = "";
        $categoriePCcode = "";
        $categoriePC = "";
        // ==== Evaluation du CPU ====================================
        $indiceCPU = "";
        $categorieCPU = "";
        $evalCPUcl = EvaluationIndiceCpu::getInstance($this->pc);
        $evalCPUcl = $evalCPUcl->calcCpuIndice();
        if ($evalCPUcl->getStatus() == "OK") {
            $indiceCPU = $evalCPUcl->getIndiceCpuCl()->getIndice();
            $categorieCPU = self::getItemNote($indiceCPU, $this->paramArray["seuilsCPU"]);
        } else {
            $erreurCalcul = true;
            $this->evaluationErrorsCl->mergeErrorArray($evalCPUcl->getEvaluationErrorsCl());
            $categorieCPU = "Non trouvée";
            $this->evaluationErrorsCl->addErrorMsg('', 'CPU non trouvée');
        }
        $this->setCategorieCPU($categorieCPU);
        $this->setEvaluationIndiceCpuCl($evalCPUcl);
 
        //==== DISK =======================================
        $catDisk01 = $this->evalCategorieDiskUnit(1);
        if ($catDisk01 == "-1") {
            $erreurCalcul = true;
        }
        $catDisk02 = $this->evalCategorieDiskUnit(2);
        if ($catDisk02 == "-1") {
            $erreurCalcul = true;
        }
        $categorieDisk="";
        $catDiskTotal = $catDisk01;
        if ($catDisk01 != "-1" and $catDisk02 != "-1") {
            // 2 disques
            // $typeDisk = $this->pc->getDisk(1)["type"];
            // $maxCategorieDisk01 = $this->maxCatDisk[$typeDisk];
            // $maxCategorieDisk02 = null;
            // if ($this->pc->getDisk(2) !=  null) {
            //     $typeDisk = $this->pc->getDisk(2)["type"];
            //     $maxCategorieDisk02 = $this->maxCatDisk[$typeDisk];
            // }
            $catDiskTotal = $catDisk01 + $catDisk02;
            //$maxCategorieDisk = max($maxCategorieDisk01, $maxCategorieDisk02);    // max("1", null) = 1
            //$categorieDisk    = min($maxCategorieDisk , $catDisk01 + $catDisk02); // 1 + null = 1
        }
        // La note est plafonnée à 4 (version de novmebre 2023)
        $categorieDisk    = min((int) $this->paramArray["seuilsMaxi"]["disks"] , $catDiskTotal);
        $this->setCategorieDiskTotal("".$catDiskTotal);
        $this->setCategorieDisk("".$categorieDisk);

        //==== RAM ==========================================
        $tailleRamCvt = Util01::convertUnit($this->pc->getTailleRam(), "g", $this->pc->getUnitepardefaut());
        $categorieRam = "-9999999";
        if (is_string($tailleRamCvt)) {
            $erreurCalcul = true;
            $this->evaluationErrorsCl->addErrorMsg('', 'Taille RAM : ' . $tailleRamCvt);
        } else {
            $tailleRam = (int) $tailleRamCvt;
            if ($tailleRam == $tailleRamCvt) {
                $categorieRam = self::getItemNote($tailleRam, $this->paramArray["seuilsRAM"]);
            } else {
                $erreurCalcul = true;
                $this->evaluationErrorsCl->addErrorMsg('', 'Taille RAM : doit être un multiple entier de Goctets "' . $tailleRamCvt . '"');
            }
        }
        $this->setCategorieRam($categorieRam);

        //===== PC Total ==============================================
        if (!$erreurCalcul) {
            $categorieTotal = $categorieCPU + $categorieRam + $categorieDisk;
            $categoriePCcodeNormale = self::getItemNote($categorieTotal, $this->paramArray["seuilsCodeCatPC"]);
            $categoriePCnormale     = self::getCatPcText($categoriePCcodeNormale, $this->paramArray["seuilsCatPC"]);

            // Un PC avec un indice inférieur à 2500 (=> catégorie 1) et
            //    dont le disque dur est de type HDD est classé au mieux en catégorie C (code 3)
            //    dont le disque dur est de type SSD est classé au mieux en catégorie B (code 4)
            $categoriePCcodeMaxi = "";
            if ($categorieCPU > 1) {
                $categoriePCcode = $categoriePCcodeNormale;
            } else {
                if ($this->pc->getDisk(1)["type"] != "HDD" 
                    or ($this->pc->getDisk(2) != null and $this->pc->getDisk(2)["type"] != "HDD")) {
                    // SSD ou NVME
                    $categoriePCcodeMaxi = "4"; // cat "B"
                }else{
                    // il n'y a pas mieux que HDD
                    $categoriePCcodeMaxi = "3"; // cat "C"
                }
                $categoriePCcode = min($categoriePCcodeNormale, $categoriePCcodeMaxi);
            }
            $categoriePC     = self::getCatPcText($categoriePCcode, $this->paramArray["seuilsCatPC"]);
        } else {
            $categorieTotal = "erreur";
            $categoriePC    = "erreur";
            $this->status   = "err";
        }
        $this->categorieTotal         = "".$categorieTotal;
        $this->categoriePCcodeNormale = $categoriePCcodeNormale;
        $this->categoriePCnormale     = $categoriePCnormale;
        $this->categoriePCcodeMaxi    = $categoriePCcodeMaxi;
        $this->categoriePCcode        = $categoriePCcode;
        $this->categoriePC            = $categoriePC;
        return $this;
    }
    /**
     * Evaluation de la catégorie d'un disque      
     * @param integer $ndisk : numéro du disque (commence à 1)
     * @return string|null 
     *          null si le disque n'existe pas
     *          "-1" en cas d'erreur
     */
    private function evalCategorieDiskUnit(int $ndisk) : ?string {
        $categorieDisk  = null;
        $erreurCalcul   = false;
        $diskArray = $this->pc->getDisk($ndisk);
        if ($diskArray !=  null) {
            $typeDisk = $diskArray["type"];
            $tailleDiskIn = $diskArray["taille"];
            // le disque 1 est obligatoire
            if ($ndisk == 1 or ($typeDisk != "" || $tailleDiskIn != "")) {
                $tailleDisk = 0;
                if($typeDisk == "") {
                    $typeDisk = $this->pc->getTypeDiskParDefaut();
                }
                if ($typeDisk != "HDD" and $typeDisk != "SSD" and $typeDisk != "NVME") {
                    $erreurCalcul = true;
                    $this->evaluationErrorsCl->addErrorMsg('', 'Erreur syntaxe type disque '.$ndisk.' : mettre "HDD" ou "SSD ou "NVME"');
                } else {
                    if ($tailleDiskIn != "")  {
                        // if(is_numeric($tailleDiskIn) and $this->pc->getUnitepardefaut() != "") {
                        //     $tailleDiskIn .= $this->pc->getUnitepardefaut();
                        // }
                        $tailleDiskCvt = Util01::convertUnit($tailleDiskIn, "g", $this->pc->getUnitepardefaut());
                        if (is_string($tailleDiskCvt)){
                            $erreurCalcul = true;
                            $this->evaluationErrorsCl->addErrorMsg('', 'Taille Disk '.$ndisk.' : ' .$tailleDiskCvt);
                        }else{
                            $tailleDisk = (int) $tailleDiskCvt;
                            if ($tailleDisk == $tailleDiskCvt) {
                                $nomSeuilDisk = "seuils" . $typeDisk;
                                $categorieDisk = self::getItemNote($tailleDisk, $this->paramArray[$nomSeuilDisk]);
                            }else{
                                $erreurCalcul = true;
                                $this->evaluationErrorsCl->addErrorMsg('', 'Taille Disk '.$ndisk.' : doit être un multiple entier de Goctets "'.$tailleDiskCvt.'"');
                            }
                        }
                    }else{
                        $erreurCalcul = true;
                        $this->evaluationErrorsCl->addErrorMsg('', 'Taille Disk '.$ndisk.' doit être renseignée.');
                    }
                }
                if ($erreurCalcul) {
                    $categorieDisk  = "-1";
                }
            }
        }
        $this->setCategorieDiskUnit($ndisk, $categorieDisk);
        return $categorieDisk;
    }


    /**
     * @param string $item : valeur à convertie en note, ex : 2Go
     * @param array $param : tableau associatif trié en ascendant ['valeur de comparaison' => 'note')
     * @return string
     */
    static function getItemNote(string|int $item, array $param): string
    {
		//this->logger->addLogDebugLine(">>> getItemNote(" . $item. ")");
        $note = "non trouvé";
        $item = "".$item;
        foreach ($param as $key => $val) {
			//this->logger->addLogDebugLine(".... getItemNote :" . $key. " / " .$val);
            if ($item < $key) {
				//this->logger->addLogDebugLine(".... getItemNote : OK");
                $note = $val;
                break;
            }
        }
        return $note;
    }

    static function getItemNoteMax(array $param): string
    {
        $note = "-99999999999999";
        foreach ($param as $key => $val) {
            if ($note < $val) {
                $note = $val;
            }
        }
        return $note;
    }

    /** transforme le code cat PC en Catégorie PC
     * @param string $code
     * @param array  $param : tableau associatif [code => catégorie]
     * @return string
     */
    static function getCatPcText(string $code, array $param) : string
    {
        $PCtxt = "non trouvé";
        if (array_key_exists($code, $param)) {
            $PCtxt = $param[$code];
        }
        return $PCtxt;
    }

    public function hasErrors() {
		if ($this->evaluationErrorsCl->hasErrors()) {
			return true;
		}else{
			return false;
		}
    }

    /** Get the value of evaluationErrorsCl
     * @return  EvaluationErrors
     */
    public function getEvaluationErrorsCl() : EvaluationErrors
    {
        return $this->evaluationErrorsCl;
    }
    /** Set the value of evaluationErrorsCl
     * @param   EvaluationErrors  $evaluationErrorsCl  
     * @return  self
     */
    public function setEvaluationErrorsCl(EvaluationErrors $evaluationErrorsCl)
    {
        $this->evaluationErrorsCl = $evaluationErrorsCl;
        return $this;
    }

    //===
    /** retourne l'évaluation comme un tableau associatif
     * 
     * "cpuTextInput"
     * "cputextnorm"
     * "indiceCPU"
     * "origine"
     * "categorieCPU"
     * "tailleDisk01"
     * "typeDisk01"
     * "categorieDisk01"
     * "tailleDisk02"
     * "typeDisk02"
     * "categorieDisk02"
     * "categorieDiskTotal"
     * "categorieDisk"
     * "tailleRam"
     * "categorieRam"
     * "categorieTotal"
     * "categoriePCcodeNormal"
     * "categoriePCcodeMaxi"
     * "categoriePCcode"
     * "categoriePCCorrigée"
     * "demande"
     * "fmtCpu"
     * @return array|null
     */
	public function convertToArray() : ?array{
		$retour = [];
        $retour["cpuTextInput"]=$this->getEvaluationIndiceCpuCl()->getCpuTextInputArray()[0];
        $retour["cputextnorm"] =$this->getEvaluationIndiceCpuCl()->getCputextnorm();
        if ($this->getEvaluationIndiceCpuCl()->getIndiceCPUCl() != null) {
            $retour["indiceCPU"] =$this->getEvaluationIndiceCpuCl()->getIndiceCPUCl()->getIndice();
            $retour["origine"]   =$this->getEvaluationIndiceCpuCl()->getIndiceCPUCl()->getOrigine();
            $retour["cpuWebName"]=$this->getEvaluationIndiceCpuCl()->getIndiceCPUCl()->getCpuWebName();
        }else{
            $retour["indiceCPU"] ="erreur";
            $retour["origine"]   ="erreur";
            $retour["cpuWebName"]="";
        }
        $retour["categorieCPU"]   =$this->getCategorieCPU();
        $retour["tailleDisk01"]   =($this->getPc()->getDisk(1) !== null) ? $this->getPc()->getDisk(1)["taille"]:"";
        $retour["typeDisk01"]     =($this->getPc()->getDisk(1) !== null) ? $this->getPc()->getDisk(1)["type"]:"";
        $retour["categorieDisk01"]=($this->getPc()->getDisk(1) !== null) ? $this->getCategorieDiskUnit(1):"";


        $retour["tailleDisk02"]   =($this->getPc()->getDisk(2) !== null) ? $this->getPc()->getDisk(2)["taille"]:"";
        $retour["typeDisk02"]     =($this->getPc()->getDisk(2) !== null) ? $this->getPc()->getDisk(2)["type"]:"";
        $retour["categorieDisk02"]=($this->getPc()->getDisk(2) !== null) ? $this->getCategorieDiskUnit(2):"";

        $retour["categorieDiskTotal"]=$this->getCategorieDiskTotal();

        $retour["categorieDisk"]        =$this->getCategorieDisk();
        $retour["tailleRam"]            =$this->getPc()->getTailleRam();
        $retour["categorieRam"]         =$this->getCategorieRam();
        $retour["categorieTotal"]       =$this->getCategorieTotal();
        $retour["categoriePCcodeNormal"]=$this->getCategoriePCcodeNormale();
        $retour["categoriePCnormale"]   =$this->getCategoriePCnormale();
        $retour["categoriePCcodeMaxi"]  =$this->getCategoriePCcodeMaxi();
        $retour["categoriePCcode"]      =$this->getCategoriePCcode();
        $retour["categoriePCCorrigée"]  =$this->getCategoriePC();
        $retour["demande"]              =$this->getDemande();
        $retour["fmtCpu"]               =$this->getFmtCpu();
        return $retour;
	}

    /**
     * @param string $sep
     * @param bool   $detail
     * @return string : contenant les caratéristiques séparées par $sep
     */
    public function convertToText(string $sep, bool $detail) : String
    {
        $this->logger->addLogDebugLine('>>>> convertReponseToText : $evalPC');
        $result = "";
        if ($this->hasErrors()) {
            $result .= $this->getEvaluationErrorsCl()->getErrorsMsgAsString();
        } else {
            $result .= $result . $this->getCategoriePC();
        }

        if ($detail) {
            $temp = $this->convertToArray();
            $result .=  $sep . 'cpuTextInput = "' . $temp["cpuTextInput"] .'"';
            $result .=  $sep . 'cputextnorm = "' . $temp["cputextnorm"] .'"';
            $result .=  $sep . 'indiceCPU = "' . $temp["indiceCPU"] .'"';
            $result .=  $sep . 'origine = "' . $temp["origine"] .'"';
            $result .=  $sep . 'cpuWebName = "' . $temp["cpuWebName"] .'"';
            $result .=  $sep . 'categorieCPU = "' . $temp["categorieCPU"] .'"';
            $result .=  $sep . 'tailleDisk01 = "' . $temp["tailleDisk01"] .'"';
            $result .=  $sep . 'typeDisk01 = "' . $temp["typeDisk01"] .'"';
            $result .=  $sep . 'categorieDisk01 = "' . $temp["categorieDisk01"] .'"';
            $result .=  $sep . 'tailleDisk02 = "' . $temp["tailleDisk02"] .'"';
            $result .=  $sep . 'typeDisk02 = "' . $temp["typeDisk02"] .'"';
            $result .=  $sep . 'categorieDisk02 = "' . $temp["categorieDisk02"] .'"';
            $result .=  $sep . 'categorieDiskTotal = "' . $temp["categorieDiskTotal"] .'"';
            $result .=  $sep . 'categorieDisk = "' . $temp["categorieDisk"] .'"';
            $result .=  $sep . 'tailleRam = "' . $temp["tailleRam"] .'"';
            $result .=  $sep . 'categorieRam = "' . $temp["categorieRam"] .'"';
            $result .=  $sep . 'categorieTotal = "' . $temp["categorieTotal"] .'"';
            $result .=  $sep . 'categoriePCcodeNormal = "' . $temp["categoriePCcodeNormal"] .'"';
            $result .=  $sep . 'categoriePCnormale = "' . $temp["categoriePCnormale"] .'"';
            $result .=  $sep . 'categoriePCcodeMaxi = "' . $temp["categoriePCcodeMaxi"] .'"';
            $result .=  $sep . 'categoriePCcode = "' . $temp["categoriePCcode"] .'"';
            $result .=  $sep . 'categoriePCCorrigée = "' . $temp["categoriePCCorrigée"] .'"';
            //print_r($temp);
        }
        $this->logger->addLogDebugLine($result, '<<<< convertReponseToText : $result');
        return $result;
    }

        /**
     * @param string $sep
     * @param bool   $detail
     * @return string : contenant les caratéristiques séparées par $sep
     */
    public function convertToTable(bool $detail) : String
    {
        $this->logger->addLogDebugLine('>>>> convertReponseToText : $evalPC');
        $result = '';
        if ($this->hasErrors()) {
            $result .= $this->getEvaluationErrorsCl()->getErrorsMsgAsString();
        } else {
            $result .= $result . $this->getCategoriePC();
        }

        if ($detail) {
            $temp = $this->convertToArray();
            $result .= "<table>";
            $result .=  '<tr><td>' . 'cpuTextInput</td><td>' . $temp["cpuTextInput"] . '</td></tr>';
            $result .=  '<tr><td>' . 'cputextnorm</td><td>' . $temp["cputextnorm"] . '</td></tr>';
            $result .=  '<tr><td>' . 'indiceCPU</td><td>' . $temp["indiceCPU"] . '</td></tr>';
            $result .=  '<tr><td>' . 'origine</td><td>' . $temp["origine"] . '</td></tr>';
            $result .=  '<tr><td>' . 'cpuWebName</td><td>' . $temp["cpuWebName"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieCPU</td><td>' . $temp["categorieCPU"] . '</td></tr>';
            $result .=  '<tr><td>' . 'tailleDisk01</td><td>' . $temp["tailleDisk01"] . '</td></tr>';
            $result .=  '<tr><td>' . 'typeDisk01</td><td>' . $temp["typeDisk01"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieDisk01</td><td>' . $temp["categorieDisk01"] . '</td></tr>';
            $result .=  '<tr><td>' . 'tailleDisk02</td><td>' . $temp["tailleDisk02"] . '</td></tr>';
            $result .=  '<tr><td>' . 'typeDisk02</td><td>' . $temp["typeDisk02"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieDisk02</td><td>' . $temp["categorieDisk02"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieDiskTotal</td><td>' . $temp["categorieDiskTotal"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieDisk</td><td>' . $temp["categorieDisk"] . '</td></tr>';
            $result .=  '<tr><td>' . 'tailleRam</td><td>' . $temp["tailleRam"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieRam</td><td>' . $temp["categorieRam"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categorieTotal</td><td>' . $temp["categorieTotal"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categoriePCcodeNormal</td><td>' . $temp["categoriePCcodeNormal"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categoriePCnormale</td><td>' . $temp["categoriePCnormale"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categoriePCcodeMaxi</td><td>' . $temp["categoriePCcodeMaxi"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categoriePCcode</td><td>' . $temp["categoriePCcode"] . '</td></tr>';
            $result .=  '<tr><td>' . 'categoriePCCorrigée</td><td>' . $temp["categoriePCCorrigée"] . '</td></tr>';
            $result .=  '</table>';
        }
        $this->logger->addLogDebugLine($result, '<<<< convertReponseToText : $result');
        return $result;
    }

	/** Get the value of demande
     * @return string|null
     */
	public function getDemande() : ?string{
		return $this->demande;
	}
	/** Set the value of demande
     * @param string $demande
     * @return self
     */
	public function setDemande(string $demande) : self {
		$this->demande = $demande;
		return $this;
	}

	/** Get the value of pc
     * @return PC
     */
	public function getPc() : PC {
		return $this->pc;
	}
	/** Set the value of pc
     * @param PC $pc
     * @return self
     */
	public function setPc(PC $pc) : self {
		$this->pc = $pc;
		return $this;
	}

	/** Get the value of fmtCpu
     * @return string|null
     */
	public function getFmtCpu() : ?string{
		return $this->fmtCpu;
	}
	/** Set the value of fmtCpu
     * @param string $fmtCpu
     * @return self
     */
	public function setFmtCpu(string $fmtCpu) : self {
		$this->fmtCpu = $fmtCpu;
		return $this;
	}

	/** Get the value of status
	 * @return  string
	 */
	public function getStatus() : ?string{
		return $this->status;
	}
	/** Set the value of status
	 * @param   string  $status  
	 * @return  self
	 */
	public function setStatus(string $status) : self {
		$this->status = $status;
		return $this;
	}

	/** Get the value of categorieCPU
	 * @return  mixed
	 */
	public function getCategorieCPU() : ?string{
		return $this->categorieCPU;
	}

	/** Set the value of categorieCPU
	 * @param   mixed  $categorieCPU  
	 * @return  self
	 */
	public function setCategorieCPU(string $categorieCPU) {
		$this->categorieCPU = $categorieCPU;
		return $this;
	}

	/** Get the value of categorieRam
	 * @return  mixed
	 */
	public function getCategorieRam() : ?string{
		return $this->categorieRam;
	}

	/** Set the value of categorieRam
	 * @param   mixed  $categorieRam  
	 * @return  self
	 */
	public function setCategorieRam(string $categorieRam) {
		$this->categorieRam = $categorieRam;
		return $this;
	}
   /**
     * Get the value of categorieDiskArr
     * @param [type] $n
     * @return string|null
     */
	public function getCategorieDiskUnit($n) : ?string{
        if (isset($this->categorieDiskArr[$n])) {
		    return $this->categorieDiskArr[$n];
        }else{
            return null;
        }
	}

	/** Set the value of categorieDiskArr
	 * @param   mixed  $categorieDiskArr  
	 * @return  self|null
	 */
	public function setCategorieDiskUnit(int $n, ?string $categorieDisk)  : self {
		$this->categorieDiskArr[$n] = $categorieDisk;
		return $this;
	}
	/** Get the value of categorieDisk
	 * @return  mixed
	 */
	public function getCategorieDisk() : ?string{
		return $this->categorieDisk;
	}

	/** Set the value of categorieDisk
	 * @param   mixed  $categorieDisk  
	 * @return  self
	 */
	public function setCategorieDisk(string $categorieDisk) {
		$this->categorieDisk = $categorieDisk;
		return $this;
	}

	/** Get the value of CatDiskTotal
     *
     * @return string|null : Cathérogorie total des disques avant limitation
     */
	public function getCategorieDiskTotal() : ?string {
		return $this->categorieDiskTotal;
	}

	/** Set the value of CatDiskTotal
	 * @param   string  $CatDiskTotal  
	 * @return  self
	 */
	public function setCategorieDiskTotal(string $CategorieDiskTotal) {
		$this->categorieDiskTotal = $CategorieDiskTotal;
		return $this;
	}

	/** Get the value of categorieTotal
     * @return string|null
     */
	public function getCategorieTotal() : ?string{
		return $this->categorieTotal;
	}
	/** Set the value of categorieTotal
     * @param string $categorieTotal
     * @return self
     */
	public function setCategorieTotal(string $categorieTotal) : self {
		$this->categorieTotal = $categorieTotal;
		return $this;
	}

	/** Get the value of categoriePCcodeNormale
     * @return string|null
     */
	public function getCategoriePCcodeNormale() : ?string{
		return $this->categoriePCcodeNormale;
	}
	/** Set the value of categoriePCcodeNormale
	 * @param   string  $categoriePCcodeNormale  
	 * @return  self
	 */
	public function setCategoriePCcodeNormale(string $categoriePCcodeNormale) : self {
		$this->categoriePCcodeNormale = $categoriePCcodeNormale;
		return $this;
	}

	/** Get the value of categoriePCnormale
     * @return string|null
     */
	public function getCategoriePCnormale() : ?string {
		return $this->categoriePCnormale;
	}
	/** Set the value of categoriePCnormale
     * @param string $categoriePCnormale
     * @return self
     */
	public function setCategoriePCnormale(string $categoriePCnormale) : self {
		$this->categoriePCnormale = $categoriePCnormale;
		return $this;
	}

	/** Get the value of categoriePCcodeMaxi
     * @return string|null
     */
	public function getCategoriePCcodeMaxi() : ?string{
		return $this->categoriePCcodeMaxi;
	}
	/** Set the value of categoriePCcodeMaxi
     * @param string $categoriePCcodeMaxi
     * @return self
     */
	public function setCategoriePCcodeMaxi(string $categoriePCcodeMaxi) : self {
		$this->categoriePCcodeMaxi = $categoriePCcodeMaxi;
		return $this;
	}

	/** Get the value of categoriePCcode
     * @return string|null
     */
	public function getCategoriePCcode() : ?string{
		return $this->categoriePCcode;
	}
	/** Set the value of categoriePCcode
     * @param string|null $categoriePCcode
     * @return self
     */
	public function setCategoriePCcode(?string $categoriePCcode) : self {
		$this->categoriePCcode = $categoriePCcode;
		return $this;
	}

	/** Get the value of categoriePC
     * @return string|null
     */
	public function getCategoriePC() : ?string{
		return $this->categoriePC;
	}
	/** Set the value of categoriePC
     * @param string $categoriePC
     * @return self
     */
	public function setCategoriePC(string $categoriePC) : self {
		$this->categoriePC = $categoriePC;
		return $this;
	}

	/** Get the value of EvaluationIndiceCpuCl
     * @return EvaluationIndiceCpu
     */
	public function getEvaluationIndiceCpuCl() : ?EvaluationIndiceCpu{
		return $this->EvaluationIndiceCpuCl;
	}
	/** Set the value of EvaluationIndiceCpuCl
     * @param EvaluationIndiceCpu $EvaluationIndiceCpuCl
     * @return self
     */
	public function setEvaluationIndiceCpuCl(EvaluationIndiceCpu $EvaluationIndiceCpuCl) : self {
		$this->EvaluationIndiceCpuCl = $EvaluationIndiceCpuCl;
		return $this;
	}


	/** Get the value of paramArray
	 * @return  mixed
	 */
	public function getParamArray(){
		return $this->paramArray;
	}

	/** Set the value of paramArray
	 * @param   mixed  $paramArray  
	 * @return  self
	 */
	public function setParamArray($paramArray) {
		$this->paramArray = $paramArray;
		return $this;
	}

    /**
     * pour debug, retourne les valeurs des propriétés
     */
    function __toString() : string {
        $retour  = "";
        $retour .= "[demande : $this->demande]\n";
        $retour .= "[fmtCpu : $this->fmtCpu]\n";
        $retour .= "[status : $this->status]\n";
        $retour .= "[categorieCPU : $this->categorieCPU]\n";
        $retour .= "[categorieRam : $this->categorieRam]\n";
        $retour .= "[categorieDiskTotal : $this->categorieDiskTotal]\n";
        $retour .= "[categorieDisk : $this->categorieDisk]\n";
        $retour .= "[categorieTotal : $this->categorieTotal]\n";
        $retour .= "[categoriePCcodeNormale : $this->categoriePCcodeNormale]\n";
        $retour .= "[categoriePCnormale : $this->categoriePCnormale]\n";
        $retour .= "[categoriePCcodeMaxi : $this->categoriePCcodeMaxi]\n";
        $retour .= "[categoriePCcode : $this->categoriePCcode]\n";
        $retour .= "[categoriePC : $this->categoriePC]\n";
        $retour .= "[EvaluationErrors : $this->evaluationErrorsCl]\n";
        return $retour;
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
