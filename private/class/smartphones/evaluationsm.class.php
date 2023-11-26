<?php
declare(strict_types=1);
require_once 'smartphone.class.php';
require_once 'utilsm.class.php';
$path_private_class = $g_contexte_instance->getPath('private/class');
$path_private       = $g_contexte_instance->getPath('private');
//require_once $path_private_class.'/paramini.class.php';
require_once $path_private_class.'/db/dbmanagement.class.php';
require_once $path_private_class.'/util01.class.php';
require_once $path_private_class.'/contexte.class.php';
require_once $path_private.'/php/smartphones/utilsm.php';

class EvaluationSm {

    private Smartphone $sm;
    private bool   $supressSpacesBool = true; // indique s'il faut suppriler les espaces multiple pour la rcherche en BDD
    private Contexte $ctx;
    private array  $paramArray; // contient les seuils
    private int    $noteRam = 0;
    private int    $noteStockage = 0;
    private int    $indice = 0;
    private int    $noteIndice = 0;
    private int    $noteTotale = 0;
    private int    $categorie = 0;
    private string $categorieApha = "";
    private int    $ponderation = 0;
    private int    $notePondere = 0;
    private int    $categoriePondere = 0;
    private string $categoriePondereAlpha = "";
    private string $errMsg;
    private bool   $simulation = false; // true => la marque est EMMAUSCONNECT et l'indice est dans MODELE
    private bool   $smRowFound = false; // smartphne trouvé dans la BDD
    private array  $smRow = [];         // enregistrement de la BDD

    private function __construct() {}

    /**
     * Undocumented function
     *
     * @param Smartphone|null $sm l'instance Smartphone à catégoriser
     * @param boolean $supressSpacesBool 
     * @return EvaluationSm
     */
    public static function getInstance(Smartphone $sm = null, bool $supressSpacesBool = true) : EvaluationSm
    {
        $c = new EvaluationSm();
        if ($sm != null) {
            $c->sm = $sm;
        }
        $c->supressSpacesBool = $supressSpacesBool;
        $ctx = Contexte::getInstance();
        $c->ctx = $ctx;
        $c->paramArray = $ctx->getParamIniCls()->getParam();
        return $c;
    }

    /**
     * UCatégorise un smartphone.
     * Si RAM et Stockage sont nul => retrouve uniquement l'indice du modèle
     *
     * @return self
     */
    function evalSmartphone()  : self {
        $errMsg = "";
        $tailleRamCvt      = $this->sm->getRamGo();
        $tailleStockageCvt = $this->sm->getStockageGo();
        // il arrive que le stockage soit de la forme "26.0 GB"
        if(is_string($tailleRamCvt) || is_string($tailleStockageCvt)) {
            $errMsg = "Ram ou Stockage incorrect";
        }else{
            if ($this->sm->getMarque() != "EMMAUSCONNECT") {
                // un vrai smartphone
                $smRow = UtilSm::getSmartphoneRow($this->sm->getMarque(), $this->sm->getModele(), $this->sm->getRam(), $this->sm->getStockage(), $this->supressSpacesBool);
                if ($smRow) {
                    $this->smRowFound  = true;
                    $this->smRow = $smRow;
                    $this->indice = $smRow['indice'];
                    $this->calculCategorie($this->sm->getRam(), $this->sm->getStockage(), $this->getIndice(), $this->sm->getPonderationValue() );
                }else{
                    $errMsg .= "Il n'y a aucun modèle dans la base avec les critères spécifiés<br>.";
                    $errMsg .= 'marque ['.$this->sm->getMarque().'] modele ['.$this->sm->getModele().'] ram ['.$tailleRamCvt.'] stockage ['.$tailleStockageCvt.']<br>';
                    //$errMsg .= "Pensez à cocher la case 'Supprimer les espaces en trop<br>";
                    //$errMsg .= "Pensez aussi à changer les chiffres romains en chiffres arabes.";
                }
            }else{
                // on utilise un indice en constante qui est dans le modèle
                $this->simulation = true;
                $this->smRowFound = true;
                //$indice = $this->sm->getModele();
                $this->indice      = (int) $this->sm->getModele();
                $smRow['marque']   = $this->sm->getMarque();
                $smRow['modele']   = $this->sm->getModele();
                $smRow['ram']      = $this->sm->getRam();
                $smRow['stockage'] = $this->sm->getStockage();
                $smRow['indice']   = $this->sm->getModele();
                $smRow['os'] = '';
                $smRow['url'] = '';
                $smRow['crtorigine'] = '';
                $smRow['crtby'] = '';
                $smRow['crtdate'] = '';
                $smRow['crttype'] = '';
                $smRow['updorigine'] = '';
                $smRow['updby'] = '';
                $smRow['upddate'] = '';
                $smRow['updtype'] = '';
                $this->smRow = $smRow;
                $this->calculCategorie($this->sm->getRam(), $this->sm->getStockage(), $this->getIndice(), $this->sm->getPonderationValue() );
            }
        }
        $this->errMsg = $errMsg;
        return $this;
    }
   
    /**
     * calculCategorie
     *
     * @param [type] $ramIn  
     * @param [type] $stockageIn
     * @param [type] $indice
     * @param integer $ponderation
     * @param string $unitepardefaut
     * @return array
     */
    function calculCategorie($ramIn, $stockageIn, $indice, int $ponderation = 0, string $unitepardefaut='G'): array {
        //$plages = getSmPlages($this->paramArray);
        $ramPlages            = $this->paramArray['smram'];
        $stockagePlages       = $this->paramArray['smstockage'];
        $indicePlages         = $this->paramArray['smindice'];
        $categoriePlages      = $this->paramArray['smcategorie'];
        $categorieAlphaPlages = $this->paramArray['smcategoriealpha'];
        
        $erreurCalcul = false;
        $noteRam      = (int)-9999999;
        $ramCvt = Util01::convertUnit($ramIn, "g", $unitepardefaut);
        if (is_string($ramCvt)){
            $erreurCalcul = true;
            $this->evaluationErrorsCl->addErrorMsg('', 'Taille Ram '.$ramCvt);
        }else{
            $ram = (int) $ramCvt;
            if ($ram == $ramCvt) {
                $noteRam      = searchIndice($ramPlages, $ram);
            }else{
                $erreurCalcul = true;
                $this->evaluationErrorsCl->addErrorMsg('', 'Taille Ram doit être un multiple entier de Goctets "'.$ramCvt.'"');
            }
        }
        //$noteRam      = searchIndice($ramPlages, $ram);
        $noteStockage = (int)-9999999;
        $stockageCvt = Util01::convertUnit($stockageIn, "g", $unitepardefaut);
        if (is_string($stockageCvt)){
            $erreurCalcul = true;
            $this->evaluationErrorsCl->addErrorMsg('', 'stockage  : ' .$stockageCvt);
        }else{
            $stockage = (int) $stockageCvt;
            if ($stockage == $stockageCvt) {
                $noteStockage = searchIndice($stockagePlages, $stockage);
            }else{
                $erreurCalcul = true;
                $this->evaluationErrorsCl->addErrorMsg('', 'Stockage doit être un multiple entier de Goctets "'.$stockageCvt.'"');
            }
        }
        //$noteStockage = searchIndice($stockagePlages, $stockage);

        $noteIndice   = searchIndice($indicePlages, $indice);
        $noteTotale   = $noteRam + $noteStockage + $noteIndice;
        $notePondere  = round($noteTotale * ( 1 + ($ponderation/100)));
        $categorie    = searchIndice($categoriePlages, $noteTotale);
        $categoriePondere    = searchIndice($categoriePlages, $notePondere);

        $this->noteRam= (int) $noteRam;
        $this->noteStockage= (int) $noteStockage;
        $this->noteIndice= (int) $noteIndice;
        $this->noteTotale= (int) $noteTotale;
        $this->categorie= (int) $categorie;
        $this->categorieApha= $categorieAlphaPlages[$categorie];
        $this->ponderation= (int) $ponderation;
        $this->notePondere= (int) $notePondere;
        $this->categoriePondere= (int) $categoriePondere;
        $this->categoriePondereAlpha= $categorieAlphaPlages[$categoriePondere];

        return [
            'noteRam' => $noteRam,
            'noteStockage' => $noteStockage,
            'noteIndice' => $noteIndice,
            'noteTotale' => $noteTotale,
            'categorie' => $categorie,
            'categorieApha' => $categorieAlphaPlages[$categorie],
            'ponderation' => $ponderation,
            'notePondere' => $notePondere,
            'categoriePondere' => $categoriePondere,
            'categoriePondereAlpha' => $categorieAlphaPlages[$categoriePondere]
        ];
    }

    /**
     * recherche uniquement l'indice de $this->sm
     *
     * @return self
     */
    function evalIndice()  : self {
        $errMsg = '';
        $smRow = UtilSm::getIndice($this->sm->getMarque(), $this->sm->getModele(), $this->supressSpacesBool);
        if (! $smRow) {
            $errMsg .= "Indice non trouvé pour la marque [" .$this->marque. '] et le modèle ['.$this->modele.']';
        }else{
            $indice = $smRow['indice'];
            $this->smRow = $smRow;
            $this->indice = $smRow['indice'];
            $indicePlages         = $this->paramArray['smindice'];
            $noteIndice   = searchIndice($indicePlages, $indice);
            $this->noteIndice = $noteIndice;
        }
        $this->errMsg = $errMsg;
        return $this;
    }

    function getSmPlages($paramArray) {
        $ramPlages       = $paramArray['smram'];
        $stockagePlages  = $paramArray['smstockage'];
        $indicePlages    = $paramArray['smindice'];
        $categoriePlages = $paramArray['smcategorie'];
        $categoriePlagesAlpha = $paramArray['smcategoriealpha'];
    
        return [$ramPlages, $stockagePlages, $indicePlages, $categoriePlages, $categoriePlagesAlpha];
    }

    public function getResultAsArray() : array {
        $retour = [];
        $retour['marque']         = $this->getSm()->getMarque();
        $retour['modele']         = $this->getSm()->getModele();
        $retour['ram']            = $this->getSm()->getRam();
        $retour['noteRam']        = $this->getNoteRam();
        $retour['stockage']       = $this->getSm()->getStockage();
        $retour['noteStockage']   = $this->getNoteStockage();
        $retour['indice']         = $this->getIndice();
        $retour['noteIndice']     = $this->getNoteIndice();
        $retour['noteTotale']     = $this->getNoteTotale();
        $retour['categorieApha']  = $this->getCategorieApha();
        $retour['ponderation']    = $this->getPonderation();
        $retour['notePondere']    = $this->getNotePondere();
        $retour['categoriePondereAlpha'] = $this->getCategoriePondereAlpha();
        return $retour;
    }

     /**
     * Get the value of sm
     */
    public function getSm(): Smartphone
    {
        return $this->sm;
    }

    /**
     * Set the value of sm
     */
    public function setSm(Smartphone $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    /**
     * Get the value of supressSpacesBool
     */
    public function isSupressSpacesBool(): bool
    {
        return $this->supressSpacesBool;
    }

    /**
     * Set the value of supressSpacesBool
     */
    public function setSupressSpacesBool(bool $supressSpacesBool): self
    {
        $this->supressSpacesBool = $supressSpacesBool;
        return $this;
    }

    /**
     * Get the value of paramArray
     */
    public function getParamArray(): array
    {
        return $this->paramArray;
    }

    /**
     * Set the value of paramArray
     */
    public function setParamArray(array $paramArray): self
    {
        $this->paramArray = $paramArray;
        return $this;
    }

    /**
     * Get the value of indice
     */
    public function getIndice(): int
    {
        return $this->indice;
    }

    /**
     * Set the value of indice
     */
    public function setIndice(int $indice): self
    {
        $this->indice = $indice;
        return $this;
    }

    /**
     * Get the value of noteRam
     */
    public function getNoteRam(): int
    {
        return $this->noteRam;
    }

    /**
     * Set the value of noteRam
     */
    public function setNoteRam(int $noteRam): self
    {
        $this->noteRam = $noteRam;
        return $this;
    }

    /**
     * Get the value of noteStockage
     */
    public function getNoteStockage(): int
    {
        return $this->noteStockage;
    }

    /**
     * Set the value of noteStockage
     */
    public function setNoteStockage(int $noteStockage): self
    {
        $this->noteStockage = $noteStockage;
        return $this;
    }

    /**
     * Get the value of noteIndice
     */
    public function getNoteIndice(): int
    {
        return $this->noteIndice;
    }

    /**
     * Set the value of noteIndice
     */
    public function setNoteIndice(int $noteIndice): self
    {
        $this->noteIndice = $noteIndice;
        return $this;
    }

    /**
     * Get the value of noteTotale
     */
    public function getNoteTotale(): int
    {
        return $this->noteTotale;
    }

    /**
     * Set the value of noteTotale
     */
    public function setNoteTotale(int $noteTotale): self
    {
        $this->noteTotale = $noteTotale;
        return $this;
    }

    /**
     * Get the value of categorie
     */
    public function getCategorie(): int
    {
        return $this->categorie;
    }

    /**
     * Set the value of categorie
     */
    public function setCategorie(int $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * Get the value of categorieApha
     */
    public function getCategorieApha(): string
    {
        return $this->categorieApha;
    }

    /**
     * Set the value of categorieApha
     */
    public function setCategorieApha(string $categorieApha): self
    {
        $this->categorieApha = $categorieApha;
        return $this;
    }

    /**
     * Get the value of ponderation
     */
    public function getPonderation(): int
    {
        return $this->ponderation;
    }

    /**
     * Set the value of ponderation
     */
    public function setPonderation(int $ponderation): self
    {
        $this->ponderation = $ponderation;
        return $this;
    }

    /**
     * Get the value of notePondere
     */
    public function getNotePondere(): int
    {
        return $this->notePondere;
    }

    /**
     * Set the value of notePondere
     */
    public function setNotePondere(int $notePondere): self
    {
        $this->notePondere = $notePondere;
        return $this;
    }

    /**
     * Get the value of categoriePondere
     */
    public function getCategoriePondere(): int
    {
        return $this->categoriePondere;
    }

    /**
     * Set the value of categoriePondere
     */
    public function setCategoriePondere(int $categoriePondere): self
    {
        $this->categoriePondere = $categoriePondere;
        return $this;
    }

    /**
     * Get the value of categoriePondereAlpha
     */
    public function getCategoriePondereAlpha(): string
    {
        return $this->categoriePondereAlpha;
    }

    /**
     * Set the value of categoriePondereAlpha
     */
    public function setCategoriePondereAlpha(string $categoriePondereAlpha): self
    {
        $this->categoriePondereAlpha = $categoriePondereAlpha;
        return $this;
    }

    /**
     * Get the value of errMsg
     */
    public function getErrMsg(): string
    {
        return $this->errMsg;
    }

    /**
     * Set the value of errMsg
     */
    public function setErrmsg(string $errMsg): self
    {
        $this->errMsg = $errMsg;
        return $this;
    }

    /**
     * Get the value of simulation
     */
    public function isSimulation(): bool
    {
        return $this->simulation;
    }

    /**
     * Set the value of simulation
     */
    public function setSimulation(bool $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }

    /**
     * Get the value of getRowFound
     */
    public function getSmRowFound(): bool
    {
        return $this->smRowFound;
    }

    /**
     * Set the value of smRowFound
     */
    public function setSmRowFound(bool $smRowFound): self
    {
        $this->smRowFound = $smRowFound;
        return $this;
    }

    /**
     * Get the value of smRow
     */
    public function getSmRow(): array
    {
        return $this->smRow;
    }

    /**
     * Set the value of smRow
     */
    public function setSmRow(array $smRow): self
    {
        $this->smRow = $smRow;
        return $this;
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
  //******************************************************************* */


}