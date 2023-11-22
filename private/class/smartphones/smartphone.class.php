<?php
declare(strict_types=1);
require_once __DIR__.'/../util01.class.php';
class Smartphone {
    private string $marque ="";
    private string $modele ="";
    private string $ram = "";
    private string $ramUniteSetParDefaut = "N";
    private float  $ramGo = 0;
    private string $stockageInput = "";
    private string $stockage = "";
    private bool   $stockageArrondi = false;
    private string $stockageUniteSetParDefaut = "N";
    private float  $stockageGo = 0;
    private string $ponderationKey ="";
    private int    $ponderationValue =0;
    private string $idEc ="";
    private string $statutKey ="";
    private string $statutText ="";
    private string $imei ="";
    private string $os ="";
    private string $batterieStatut ="";
    private string $uniteParDefaut = "GB";
    private bool   $arrondirStockageDone = false;

    private function __construct() {}

    public static function getInstance() : Smartphone
    {
        $c = new Smartphone();
        return $c;
    }

	public function toString() : string {
        $retour = "marque =[$this->marque] |";
        $retour = "modele =[$this->modele] |";
        $retour = "ram =[$this->ram] |";
        $retour = "stockage =[$this->stockage] |";
        $retour = "ponderationValue =[$this->ponderationValue] |";
        return $retour;
    }


    /**
     * Get the value of marque
     */
    public function getMarque(): string {
        return $this->marque;
    }

    /**
     * Set the value of marque
     */
    public function setMarque(string $marque): self {
        $this->marque = $marque;
        return $this;
    }

    /**
     * Get the value of modele
     */
    public function getModele(): string {
        return $this->modele;
    }

    /**
     * Set the value of modele
     */
    public function setModele(string $modele): self {
        $this->modele = $modele;
        return $this;
    }

    /**
     * Get the value of ram
     */
    public function getRam(): string {
        return $this->ram;
    }

    /**
     * Set the value of ram, if num => concat uniteParDefaut
     */
    public function setRam(string $ram): self {
        $ram = Util01::cleanString($ram);
		if (is_numeric($ram) && $this->uniteParDefaut!= '') {
			$ram .= $this->uniteParDefaut;
			$this->ramUniteSetParDefaut = "Y";
		}
        $this->ram = $ram;
        return $this;
    }

    /**
     * Return float RamGo or string error
     *
     * @return void
     */
    public function getRamGo(): string | float  {
        return Util01::convertUnit($this->ram, "g", $this->uniteParDefaut);
    }

    /**
     * Get the value of stockage
     */
    public function getStockage(): string {
        return $this->stockage;
    }

    /**
     * Set the value of stockage, if num => concat uniteParDefaut
     */
    public function setStockage(string $stockage): self {
        $stockage = Util01::cleanString($stockage);
        $this->stockageInput = $stockage;
        // is_numeric Retourne true si value est un nombre ou une chaîne numérique, false sinon.
        if (!is_numeric($stockage)) {
            // il arrive que le stockage soit de la forme "26.0 GB"
            //il faut isoler la taille et l'arrondir au supérieur (28 ->32)
            $sa = [];
            // 123.4567 GB   ["123.4567 GB", "123.4567", "", " " , "GB"]
            // 123.4567GB    ["123.4567 GB", "123.4567", "", ""  , "GB"]
            // 123 GB        ["123 GB"     , "123"     , "", " " , "GB"]
            // 123GB         ["123 GB"     , "123"     , "", ""  , "GB"]
            preg_match('/([0-9.]+)(\d*)(\s*)(\D*)/', $stockage, $sa);
            if (count($sa) == 5 ) {

                // on prend le poste 1 que l'on arrondit au dessus (28->32)
                // et on lui ajoute le poste 4 (l'unité)
                $m = [0, 1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096];
                $stkNorm = 0;
                for ($i = 0; count($m); $i++) {
                    if ($sa[1] <= $m[$i]) {
                        $stkNorm = $m[$i];
                        break;
                    }
                }

                // 28.05 a été transformé en 32
                // il faut ajouter le "." décimal et autant de 0 qu'il y avait de décimale
                //                0         1     2    3
                // 123         ["123"    ,"123", "" , ""]
                // 123.        ["123."   ,"123", ".", ""]
                // 123.4       ["123.4"  ,"123", ".", "4"]
                // 123.45      ["123.45" ,"123", ".", "45"]
                preg_match('/(\d*)(\.*)(\d*)/', $sa[1], $temp);
                $stkNorm .= $temp[2] . str_repeat("0", strlen($temp[3]));
                $stockageNew = $stkNorm . $sa[2]. $sa[3] .$sa[4];
                if ($stockageNew != $stockage) {
                    $this->arrondirStockageDone = true;
                }
                $this->stockage = $stockageNew;
            }
        }

        if (is_numeric($stockage) && $this->uniteParDefaut!= '') {
			$stockage .= $this->uniteParDefaut;
			$this->stockageUniteSetParDefaut = "Y";
            $this->stockage = $stockage;
		}
        
        return $this;
    }

    /**
     * Get the value of float stockageGo or string error
     */
    public function getStockageGo(): string | float {
        return Util01::convertUnit($this->stockage, "g", $this->uniteParDefaut);
    }

    /**
     * Get the value of stockageInput : stockage fourni à setStockage
     */
    public function getStockageInput(): string
    {
        return $this->stockageInput;
    }

    /**
     * Get the value of ponderationKey
     */
    public function getPonderationKey(): string {
        return $this->ponderationKey;
    }

    /**
     * Set the value of ponderationKey */
    public function setPonderationKey(string $ponderationKey): self {
        $this->ponderationKey = $ponderationKey;
        return $this;
    }

    /**
     * Get the value of ponderationValue
     */
    public function getPonderationValue(): int {
        return $this->ponderationValue;
    }

    /**
     * Set the value of ponderationValue
     *
     * @param integer $ponderationValue
     * @return self
     */
    public function setPonderationValue(int $ponderationValue): self {
        $this->ponderationValue = $ponderationValue;
        return $this;
    }

    /**
     * Get the value of idEc (Id Emmaus-connect)
     */
    public function getIdEc(): string {
        return $this->idEc;
    }

    /**
     * Set the value of idEc (Id Emmaus-connect)
     */
    public function setIdEc(string $idEc): self {
        $this->idEc = $idEc;
        return $this;
    }

    /**
     * Get the value of statutKey (statut : A VENDRE ...)
     */
    public function getStatutKey(): string {
        return $this->statutKey;
    }
    /**
     * Set the value of statutKey (statut : A VENDRE ...)
     *
     * @param string $statutKey
     * @return self
     */
    public function setStatutKey(string $statutKey): self {
        $this->statutKey = $statutKey;
        return $this;
    }

    /**
     * Get the value of statutText (statut : A VENDRE ...)
     */
    public function getStatutValue(): string {
        return $this->statutText;
    }

    /**
     * Set the value of statutText (statut : A VENDRE ...)
     *
     * @param string $statutText
     * @return self
     */
    public function setStatutText(string $statutText): self {
        $this->statutText = $statutText;
        return $this;
    }

    /**
     * Get the value of imei
     *
     * @return string
     */
    public function getImei(): string {
        return $this->imei;
    }
    /**
     * Set the value of imei
     *
     * @param string $imei
     * @return self
     */
    public function setImei(string $imei): self {
        $this->imei = $imei;
        return $this;
    }

    /**
     * Get the value of os
     *
     * @return string
     */
    public function getOs(): string {
        return $this->os;
    }
    /**
     * Set the value of os
     *
     * @param string $os
     * @return self
     */
    public function setOs(string $os): self {
        $this->os = $os;
        return $this;
    }

    /**
     * Get the value of batterieStatut
     *
     * @return string
     */
    public function getBatterieStatut(): string {
        return $this->batterieStatut;
    }
    /**
     * Set the value of batterieStatut
     *
     * @param string $batterieStatut
     * @return self
     */
    public function setBatterieStatut(string $batterieStatut): self {
        $this->batterieStatut = $batterieStatut;
        return $this;
    }

    /**
     * Get the value of unitepardefaut
     *
     * @return string
     */
    public function getUniteParDefaut(): string {
        return $this->uniteParDefaut;
    }

    /**
     * Set the value of unitepardefaut (ajouté à RAM et STOCKAGE s'ils sont numérique)
     *
     * @param string $uniteParDefaut
     * @return self
     */
    public function setUniteParDefaut(string $uniteParDefaut): self {
        $this->uniteParDefaut = $uniteParDefaut;

        return $this;
    }

    /**
     * Get the value of arrondirStockageDone
     *
     * @return boolean
     */
    public function isArrondirStockageDone(): bool {
        return $this->arrondirStockageDone;
    }
    /**
     * Set the value of arrondirStockageDone. Si TRUE => 56 -> 64 ...
     *
     * @param boolean $arrondirStockageDone
     * @return self
     */
    private function setArrondirStockageDone(bool $arrondirStockageDone): self {
        $this->arrondirStockageDone = $arrondirStockageDone;
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






}