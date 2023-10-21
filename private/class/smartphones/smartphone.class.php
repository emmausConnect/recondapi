<?php
declare(strict_types=1);
require_once __DIR__.'/../util01.class.php';
class Smartphone {
    private string $marque ="";
    private string $modele ="";
    private string $ram = "";
    private string $ramUniteSetParDefaut = "N";
    private float  $ramGo = 0;
    private string $stockage = "";
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
    public function getMarque(): string
    {
        return $this->marque;
    }

    /**
     * Set the value of marque
     */
    public function setMarque(string $marque): self
    {
        $this->marque = $marque;
        return $this;
    }

    /**
     * Get the value of modele
     */
    public function getModele(): string
    {
        return $this->modele;
    }

    /**
     * Set the value of modele
     */
    public function setModele(string $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    /**
     * Get the value of ram
     */
    public function getRam(): string
    {
        return $this->ram;
    }

    /**
     * Set the value of ram
     */
    public function setRam(string $ram): self
    {
        $ram = Util01::cleanString($ram);
		if (is_numeric($ram) && $this->uniteParDefaut!= '') {
			$ram .= $this->uniteParDefaut;
			$this->ramUniteSetParDefaut = "Y";
		}
        $this->ram   = $ram;
        return $this;
    }

    /**
     * Get the value of ramGo
     */
    public function getRamGo(): string | float
    {
        return Util01::convertUnit($this->ram, "g", $this->uniteParDefaut);
    }

    /**
     * Get the value of stockage
     */
    public function getStockage(): string
    {
        return $this->stockage;
    }

    /**
     * Set the value of stockage
     */
    public function setStockage(string $stockage): self
    {
        $stockage = Util01::cleanString($stockage);
        if (is_numeric($stockage) && $this->uniteParDefaut!= '') {
			$stockage .= $this->uniteParDefaut;
			$this->stockageUniteSetParDefaut = "Y";
		}
        $this->stockage = $stockage;
        return $this;
    }

    /**
     * Get the value of stockageGo
     */
    public function getStockageGo(): string | float
    {
        return Util01::convertUnit($this->stockage, "g", $this->uniteParDefaut);
    }

    /**
     * Get the value of ponderationKey
     */
    public function getPonderationKey(): string
    {
        return $this->ponderationKey;
    }

    /**
     * Set the value of ponderationKey
     */
    public function setPonderationKey(string $ponderationKey): self
    {
        $this->ponderationKey = $ponderationKey;
        return $this;
    }

    /**
     * Get the value of ponderationValue
     */
    public function getPonderationValue(): int
    {
        return $this->ponderationValue;
    }

    /**
     * Set the value of ponderationValue
     */
    public function setPonderationValue(int $ponderationValue): self
    {
        $this->ponderationValue = $ponderationValue;
        return $this;
    }

    /**
     * Get the value of idEc
     */
    public function getIdEc(): string
    {
        return $this->idEc;
    }

    /**
     * Set the value of idEc
     */
    public function setIdEc(string $idEc): self
    {
        $this->idEc = $idEc;
        return $this;
    }

    /**
     * Get the value of statutKey
     */
    public function getStatutKey(): string
    {
        return $this->statutKey;
    }

    /**
     * Set the value of statutKey
     */
    public function setStatutKey(string $statutKey): self
    {
        $this->statutKey = $statutKey;
        return $this;
    }

    /**
     * Get the value of statutText
     */
    public function getStatutValue(): string
    {
        return $this->statutText;
    }

    /**
     * Set the value of statutText
     */
    public function setStatutText(string $statutText): self
    {
        $this->statutText = $statutText;
        return $this;
    }

    /**
     * Get the value of imei
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    /**
     * Set the value of imei
     */
    public function setImei(string $imei): self
    {
        $this->imei = $imei;
        return $this;
    }

    /**
     * Get the value of os
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * Set the value of os
     */
    public function setOs(string $os): self
    {
        $this->os = $os;
        return $this;
    }


    /**
     * Get the value of batterieStatut
     */
    public function getBatterieStatut(): string
    {
        return $this->batterieStatut;
    }

    /**
     * Set the value of batterieStatut
     */
    public function setBatterieStatut(string $batterieStatut): self
    {
        $this->batterieStatut = $batterieStatut;

        return $this;
    }

    /**
     * Get the value of unitepardefaut
     */
    public function getUniteParDefaut(): string
    {
        return $this->uniteParDefaut;
    }

    /**
     * Set the value of unitepardefaut
     */
    public function setUniteParDefaut(string $uniteParDefaut): self
    {
        $this->uniteParDefaut = $uniteParDefaut;

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