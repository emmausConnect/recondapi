<?php
declare(strict_types=1);

class Smartphone {
    private string $marque ="";
    private string $modele ="";
    private int    $ram =0;
    private int    $stockage = 0;
    private string $ponderationKey ="";
    private int    $ponderationValue =0;
    private string $idEc ="";
    private string $statutKey ="";
    private string $statutText ="";
    private string $imei ="";
    private string $os ="";
    private string $batterieStatut ="";

    private function __construct() {}

    public static function getInstance() : Smartphone
    {
        $c = new Smartphone();
        return $c;
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
    public function getRam(): int
    {
        return $this->ram;
    }

    /**
     * Set the value of ram
     */
    public function setRam(int $ram): self
    {
        $this->ram = $ram;

        return $this;
    }

    /**
     * Get the value of stockage
     */
    public function getStockage(): int
    {
        return $this->stockage;
    }

    /**
     * Set the value of stockage
     */
    public function setStockage(int $stockage): self
    {
        $this->stockage = $stockage;

        return $this;
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