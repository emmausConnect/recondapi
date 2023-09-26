<?php
declare(strict_types=1);

require_once __DIR__.'/loggerrec.class.php';
require_once __DIR__."/paramini.class.php";

/**
 * stocke des infos de contexte d'exécution, tel le niveau de log
 * et des valeurs à utiliser par défaut
 */
class Contexte {
    private LoggerRec $logger;
    private static   Contexte $instance;
    private string   $debugLevel;
    private bool     $debugBool;
    private bool     $useDefaultUnit;
    private bool     $useDefaultDiskType;
    private string   $environnement;      // "PROD" ou autre, stocké en majuscule
    private string   $tprefix;            // prefix pour les tables de la BDD
    private ParamIni $paramPhpIni;        // prefix pour le tables de la BDD
    
    private function __construct(){ }

    public static function getInstance() : Contexte 
    {
        if (! isset(self::$instance)) {
            $c = new Contexte();
            $c->logger = LoggerRec::getInstance();
            $c->debugLevel           = "";
            $c->debugBool            = false;
            $c->useDefaultUnit       = false;
            $c->useDefaultDiskType   = false;
            $c->environnement        = "";

            // le fichier environnement contient soit "PROD" soit "TEST"
            // s'il ne contient pas PROD :
            //    * une banière "!! environnement de test" est affichée
            //    * certains traitemenent ont un comportement différent
            
            // $fileEnvirName = __DIR__.'/../../environnement.ini';
            // $g_environnement = ""; // global : environnement prod ou test
            // if (! file_exists($fileEnvirName)) {
            //     echo "Fichier '$fileEnvirName' non trouvé";
            //     exit(1);
            // }else{
            //     $txt_file = fopen('../environnement.ini','r');
            //     $g_environnement = fgets($txt_file);
            //     fclose($txt_file);
            //     if ($g_environnement != 'PROD' and $g_environnement != 'TEST' and $g_environnement != 'LOCAL') {
            //         echo "Valeur environnement invalide : '$g_environnement'";
            //         exit(1);
            //     }
            // }
            // $c->environnement = strtoupper($g_environnement);
            $c->environnement = strtoupper(self::getEnvironnementIni());

            $c->paramPhpIni = ParamIni::getInstance('*paramphp.ini');

            self::$instance = $c;
        }
        return self::$instance;
    }

    static function getEnvironnementIni() {
        // le fichier environnement contient soit "PROD" soit "TEST"
        // s'il ne contient pas PROD :
        //    * une banière "!! environnement de test" est affichée
        //    * certains traitemenent ont un comportement différent
        
        $fileEnvirName = __DIR__.'/../../environnement.ini';
        $g_environnement = ""; // global : environnement prod ou test
        if (! file_exists($fileEnvirName)) {
            echo "Fichier '$fileEnvirName' non trouvé";
            exit(1);
        }else{
            $txt_file = fopen('../environnement.ini','r');
            $g_environnement = fgets($txt_file);
            fclose($txt_file);
            if ($g_environnement != 'PROD' and $g_environnement != 'TEST' and $g_environnement != 'LOCAL') {
                echo "Valeur environnement invalide : '$g_environnement'";
                exit(1);
            }
        }
        return $g_environnement;
    }

    function getPath($pathName) {
        $paramArray = $this->paramPhpIni->getParam();
        $path = $paramArray['path'][$pathName];
        $path = $_SERVER['DOCUMENT_ROOT'].$path;
        return $path;
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
    /**
     * retourne le type d'environnemet en MAJUSCULE
     *
     * @return String
     */
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

    function setTprefix(string $tprefix) {
        $this->tprefix = $tprefix;
    }
    function getTprefix() :string {
        return $this->tprefix;
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