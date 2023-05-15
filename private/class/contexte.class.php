<?php
declare(strict_types=1);

require_once __DIR__.'/loggerrec.class.php';

/**
 * stocke des infos de contexte d'exécution, tel le niveau de log
 * et des valeurs à utiliser par défaut
 */
class Contexte {
    private LoggerRec $logger;
    private string  $debugLevel;
    private bool    $debugBool;
    private bool    $useDefaultUnit;
    private bool    $useDefaultDiskType;
    private string  $environnement;  // "PROD" ou autre
    
    private function __construct(){ }

    public static function getInstance() : Contexte 
    {
        $c = new Contexte();
        $c->logger = LoggerRec::getInstance();
        $c->debugLevel           = "";
        $c->debugBool            = false;
        $c->useDefaultUnit       = false;
        $c->useDefaultDiskType   = false;
        $c->environnement        = "";
        return $c;
    }

    /**
     * debugLevel "0" ou "" : pas de debug
     *      sinon, debug actif
     */
    function setDebugLevel(string $debugLevel) {
        $this->debugLevel = $debugLevel;
    }
    function getDebugLevel() : string {
        return $this->debugLevel;
    }

    //function setDebugSts(bool $debugBool) {
    //   $this->debugBool = $debugBool;
    //}

    function getDebugSts() : bool {
        if ($this->debugLevel === "0")
        return $this->debugBool;
    }

    function setUseDefaultUnit(bool $useDefaultUnit) {
        $this->useDefaultUnit = $useDefaultUnit;
    }
    function getUseDefaultUnit() : bool {
        return $this->useDefaultUnit;
    }

    function setUseDefaultDiskType(bool $useDefaultDiskType) {
        $this->useDefaultDiskType = $useDefaultDiskType;
    }
    function getUseDefaultDiskType() : bool {
        return $this->useDefaultDiskType;
    }

    function setEnvironnement(string $environnement) {
        $this->environnement = strtoupper($environnement);
    }
    function getEnvironnement() : String {
        return $this->environnement;
    }
    function environnementIsProd() : Bool {
        if ($this->environnement == "PROD") {
            return true;
        }else{
            return false;
        }
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