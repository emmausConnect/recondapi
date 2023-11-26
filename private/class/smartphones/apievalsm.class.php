<?php

declare(strict_types=1);

require_once __DIR__ . '/smartphone.class.php';
require_once __DIR__ . '/evaluationsm.class.php';
require_once __DIR__ . '/../util01.class.php';
require_once __DIR__ . '/../contexte.class.php';

/**
 * gère une requête API
 */
class APIevalSm
{
    private string       $debug;    // init à l'instanciation
    private LoggerRec    $logger;   // init à l'instanciation
    private Contexte     $contexte;
    private Smartphone   $ceSM;
    private EvaluationSm $evaluationSmCl;
    private array        $errMsg = [];

    private function __construct()
    {
    }

    public static function getInstance(string $debug): APIevalSm
    {
        $c = new APIevalSm();
        $c->logger = LoggerRec::getInstance();
        $c->contexte = Contexte::getInstance();
        $c->debug  = $debug;
        return $c;
    }

    /**
     * Undocumented function
     * 
     * en entrée $_GET
     *   marque
     *   modele
     *   ram
     *   stockage
     *   outfmt : json / text
     *   outdata : 'i' => indice, 'c' => catégorie. Dgt = 'c'
     *   
     *  
     * @return self
     */
    public function execGet(): self
    {
        $debug = $this->debug;;
        $this->logger->addLogDebugLine(">>>> execGet xx");
        $outdata = 'c';
        if (array_key_exists("outdata", $_GET)) {
            if (in_array(strtolower($_GET['outdata']), ['c', 'i', 'in'])) {
                $outdata = strtolower($_GET['outdata']);
            }else{
                $this->errMsg[] = __FILE__."[param 'outdata' invalide : {$_GET['outdata']}]";
            }
        }

        if (!array_key_exists("marque", $_GET)) {
            $this->errMsg[] = "[Il manque le paramètre 'marque']";
        }else{
            $marque   = $_GET['marque'];
        }

        if (!array_key_exists("modele", $_GET)) {
            $this->errMsg[] = "[Il manque le paramètre 'modele']";
        }else{
            $modele   = $_GET['modele'];
        }
        $ram = '';
        $stockage = '';
        if ($outdata == 'c') {
            if (!array_key_exists("ram", $_GET)) {
                $this->errMsg[] = "[Il manque le paramètre 'ram']";
            }else{
                $ram      = $_GET['ram'];
            }

            if (!array_key_exists("stockage", $_GET)) {
                $this->errMsg[] = "[Il manque le paramètre 'stockage']";
            }else{
                $stockage = $_GET['stockage'];
            }
        }

        if (count($this->errMsg) == 0) {
             $this->execSm($marque, $modele, $ram, $stockage, $outdata);
        }
        return $this; // ->evaluationSmCl;
    }

    private function execSm($marque, $modele, $ram, $stockage, $outdata)
    {
        $this->logger->addLogDebugLine(">>>> exec");
        $ceSM = Smartphone::getInstance();
        $ceSM->setMarque("" . $marque);
        $ceSM->setModele("" . $modele);
        $ceSM->setRam("" . $ram);
        $ceSM->setStockage("" . $stockage);
        $evaluationSmClInstance = EvaluationSm::getInstance($ceSM);
        if ($outdata == 'c') {
            $this->evaluationSmCl   = $evaluationSmClInstance->evalSmartphone();
        }else{
            $this->evaluationSmCl   = $evaluationSmClInstance->evalIndice();
        }
        $err = $this->evaluationSmCl->getErrMsg();
        if ($err != "") {
            $this->errMsg[] = $err;
        }
    }

    // ****************************************************************************

    /**
     * Get the value of ceSM
     */
    public function getCeSM(): Smartphone
    {
        return $this->ceSM;
    }

    /**
     * Get the value of evaluationSmCl
     */
    public function getEvaluationSmCl(): EvaluationSm
    {
        return $this->evaluationSmCl;
    }

    /**
     * Get the value of errMsg
     */
    public function getErrMsg(): array
    {
        return $this->errMsg;
    }

    //******************************************************************* */
    function __call($name, $arguments)
    {
        throw new Exception("Appel de la méthode non statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
    }

    static function __callStatic($name, $arguments)
    {
        throw new Exception("Appel de la méthode statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
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
