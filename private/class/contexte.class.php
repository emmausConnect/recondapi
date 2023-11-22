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
    private bool     $connected;
    private string   $emailConnected;
    private string   $debugLevel;
    private bool     $debugBool;
    private bool     $useDefaultUnit;
    private bool     $useDefaultDiskType;
    private string   $environnement;      // "PROD" ou autre, stocké en majuscule
    private string   $tprefix;            // prefix pour les tables de la BDD
    private ParamIni $paramIniCls;        // fichier param.ini
    private ParamIni $paramPhpIniCls;     // fichier paramphp.ini
    
    private function __construct(){ }

    public static function getInstance() : Contexte 
    {
        if (! isset(self::$instance)) {
            $c = new Contexte();
            $c->logger = LoggerRec::getInstance();
            $c->connected            = false;
            $c->emailConnected       = "";
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
            $c->environnement  = strtoupper(self::getEnvironnementIni());
            $c->paramIniCls    = ParamIni::getInstance('*param.ini');
            $c->paramPhpIniCls = ParamIni::getInstance('*paramphp.ini');

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
        $paramArray = $this->paramPhpIniCls->getParam();
        $path = $paramArray['path'][$pathName];
        $path = $_SERVER['DOCUMENT_ROOT'].$path;
        return $path;
    }

    /**
     * Get the value of connectedBool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Set the value of connected
     */
    public function setConnected(bool $connected): self
    {
        $this->connected = $connected;
        return $this;
    }

    /**
     * Get the value of emailConnected
     */
    public function getEmailConnected(): string
    {
        return $this->emailConnected;
    }

    /**
     * Set the value of emailConnected
     */
    public function setEmailConnected(string $emailConnected): self
    {
        $this->emailConnected = $emailConnected;
        return $this;
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
    /**
     * prefix pour les tables de la BDD
     *
     * @param string $tprefix
     * @return void
     */
    function setTprefix(string $tprefix) {
        $this->tprefix = $tprefix;
    }
    /**
     * prefix pour les tables de la BDD
     *
     * @return string
     */
    function getTprefix() :string {
        return $this->tprefix;
    }

     /**
     * Get Class 'paramIni' avec fichier param.ini
     * use getparam() to retreive data
     * 
     * ex : 
     *     $ctx = Contexte::getInstance();
     *     $val = $ctx->getParamIniCls()->getParam()['...']['...']
     * or
     *     $ctx = Contexte::getInstance();
     *     $paramArray = $ctx->getParamIniCls()->getParam();
     *     $val = $paramArray['...']['...']
     */
    public function getParamIniCls(): ParamIni
    {
        return $this->paramIniCls;
    }

    /**
     * Get Class 'paramIni' avec fichier paramphp.ini
     * use getparam() to retreive data
     * 
     * ex : 
     *     $ctx = Contexte::getInstance();
     *     $val = $ctx->getParamPhpIniCls()->getParam()['...']['...']
     * or
     *     $ctx = Contexte::getInstance();
     *     $paramPhpArray = $ctx->getParamPhpIniCls()->getParam();
     *     $val = $paramArray['...']['...']
     */
    public function getParamPhpIniCls(): ParamIni
    {
        return $this->paramPhpIniCls;
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