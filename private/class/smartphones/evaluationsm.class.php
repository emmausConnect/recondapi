<?php
declare(strict_types=1);
require_once 'smartphone.class.php';
$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class.'/paramini.class.php';
require_once $path_private_class.'/db/dbmanagement.class.php';

class EvaluationSm {

    private Smartphone $sm;
    private bool   $supressSpacesBool = true;
    private array  $paramArray;
    private int    $noteRam;
    private int    $noteStockage;
    private int    $noteIndice;
    private int    $noteTotale;
    private int    $categorie;
    private string $categorieApha;
    private int    $ponderation;
    private int    $notePondere;
    private int    $categoriePondere;
    private string $categoriePondereAlpha;
    private string $errMsg;
    private bool   $simulation = false;
    private bool   $smRowFound = false;
    private array  $smRow = [];

    private function __construct() {}

    public static function getInstance(Smartphone $sm = null, bool $supressSpacesBool = true) : EvaluationSm
    {
        $c = new EvaluationSm();
        $c->sm = $sm;
        $c->supressSpacesBool = $supressSpacesBool;
        $paramArray = ParamIni::getInstance(__DIR__.'/../../config/param.ini')->getParam();
        $c->paramArray = $paramArray;
        return $c;
    }
    
    function evalSmartphone() {
        $errMsg = "";
        if ($this->sm->getMarque() != "EMMAUSCONNECT") {
            // recherhce dans la BBD
            $dbInstance = DbManagement::getInstance();
            $db = $dbInstance->openDb();
            $tableName = $dbInstance->tableName('smartphones');
            $sqlQuery = "SELECT * from $tableName 
                where marque=:marque and modele=:modele and ram=:ram and stockage=:stockage;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->execute([
                'marque'    =>formatKey($this->sm->getMarque(),$this->supressSpacesBool),
                'modele'   => formatKey($this->sm->getModele(),$this->supressSpacesBool),
                'ram'      => formatKey($this->sm->getRam(),$this->supressSpacesBool),
                'stockage' => formatKey($this->sm->getStockage(),$this->supressSpacesBool)
                ]);
            $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($smRow) {
                $this->smRowFound  = true;
                $this->smRow = $smRow;
                $indice      = $smRow['indice'];
                $this->calculCategorie($this->sm->getRam(), $this->sm->getStockage(), $indice, $this->sm->getPonderationValue() );
            }else{
                $errMsg .= "Il n'y a aucun modèle dans la base avec les critères spécifiés<br>.";
                $errMsg .= "Pensez à cocher la case 'Supprimer les espaces en trop<br>";
                $errMsg .= "Pensez aussi à changer les chiffres romains en chiffres arabes.";
            }
        }else{
            // on utilise un indice en constante qui est dans le modèle
            $this->simulation = true;
            $this->smRowFound = true;
            $indice = $this->sm->modele;
            $smRow['marque']   = $this->sm->marque;
            $smRow['modele']   = $this->sm->modele;
            $smRow['ram']      = $this->sm->ram;
            $smRow['stockage'] = $this->sm->stockage;
            $smRow['indice']   = $this->sm->modele;
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
            $this->calculCategorie($this->sm->ram, $this->sm->stockage, $this->sm->indice, $this->sm->ponderationValue );
        }
        $this->errMsg = $errMsg;
    }
   
    function calculCategorie($ram, $stockage, $indice, $ponderation = 0) {
        //$plages = getSmPlages($this->paramArray);
        $ramPlages            = $this->paramArray['smram'];
        $stockagePlages       = $this->paramArray['smstockage'];
        $indicePlages         = $this->paramArray['smindice'];
        $categoriePlages      = $this->paramArray['smcategorie'];
        $categorieAlphaPlages = $this->paramArray['smcategoriealpha'];
    
        $noteRam      = searchIndice($ramPlages, $ram);
        $noteStockage = searchIndice($stockagePlages, $stockage);
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

    function getSmPlages($paramArray) {
        $ramPlages       = $paramArray['smram'];
        $stockagePlages  = $paramArray['smstockage'];
        $indicePlages    = $paramArray['smindice'];
        $categoriePlages = $paramArray['smcategorie'];
        $categoriePlagesAlpha = $paramArray['smcategoriealpha'];
    
        return [$ramPlages, $stockagePlages, $indicePlages, $categoriePlages, $categoriePlagesAlpha];
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
}