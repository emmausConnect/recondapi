<?php
declare(strict_types=1);

require_once __DIR__ . '/pc.class.php';
require_once __DIR__ . '/evaluationpc.class.php';
require_once __DIR__ . '/util01.class.php';

/**
 * gère une requête API
 */
class APIeval
{
    private string    $debug;    // init à l'instanciation
    private LoggerRec $logger;   // init à l'instanciation

    private function __construct()
    {
    }

    public static function getInstance(string $debug): APIeval
    {
        $c = new APIeval();
        $c->logger = LoggerRec::getInstance();
        $c->debug  = $debug;
        return $c;
    }

    /**
     *      execGet()
     *      =========
     * cette fonction traite une demande faite par un get
     * elle donne une réponse en Json ou en texte
     * 
     * Param du get
     * $_GET ou $_POST
     *  query : "PC" ou rien
     *          "PC" => calcul de la catégorie du pc
     *		    sinon uniquement l'évaluation du CPU
     *  upload : traite le fichier Excel
     * 
     *  ========= Description du PC ===============================	 
     *  pc     : cpu | typedisk1 | tailledisk1 |  typedisk2 | tailledisk2 |tailleram     utilisé pour formule depuis un excel
     *           le format par défaut est "text"
     *		   ex de formule : =SERVICEWEB("http://localhost:8080/ec-recondapi.git/public/recondp.php?pc=core i5 m 560 2.67ghz|ssd|1T|8G")
     *		                   =SERVICEWEB("http://localhost:8080/ec-recondapi.git/public/recondp.php?pc=" &H3& "|" &I3& "|" &J3& "|" &K3)
     *						   =SERVICEWEB("http://localhost:8080/ec-recondapi.git/public/recondp.php?pc=" &JOINDRE.TEXTE("|";0;H3:K3))
     *		   
     *	
  
     *  cpu     text du CPU :: default = ""
     *  cpu[]       :   si on veut mettre plusieurs syntaxes pour le CPU.
     *                  le programme fera la recherche et renverra le résultat pour le premier qui fonctionne
     *  tailledisk1 :   taille du stockage sous forme "4g" "4 Go" ... : default = 0g
     *  typedisk1   :   HDD ou SSD, NVME dft = "HDD"
     *  tailledisk2 :   taille du stockage sous forme "4g" "4 Go" ... : default = 0g
     *  typedisk2   :   HDD ou SSD, dft = "HDD"
     *  tailleram   :   taille de la ram sous forme "4g" "4 Go" ... : default = 0g
     * 
     *  ========== type de sortie =================================
     *  outfmt     :    
     *                  cat  : renvoie la catéggorie du PC ou "erreur:texte erreur"
     *                  text : renvoie la réponse en format texte
     *                  html ; utilisé avec détail = 1 permet d'avoir le détail du calcul
     *                  val par défaut :
     *                     si le paramètre "pc" existe => "cat", sinon "text"
     *  seperateur :   utilisé si format "text". Normalement "\t"
     *                  dft = "\t"
     *  detail     :    1 => le détail du calcul est joint au résultat (pour debug) 
     *                   dft = false 
     *  log        :    1 => le log est joint à la réponse (pour debug)
     *                   dft = false
     *				
     * exemples :
     * ==========
     * ?cpu=CORE I5 M 560 2.67 GHZ
     * ?outfmt=text&cpu=CORE I5 M 560 2.67 GHZ
     * ?pc=CORE+I5+M+560+2.67+GHZ|8g|1T|SSD
     */
    public function execGet()
    {
        $debug = $this->debug;;
        $this->logger->addLogDebugLine(">>>> execGet xx");
        // $query = "pc"; // "PC" => calcul de la catégorie du pc sinon uniquement l'évaluation du CPU
        // if ((array_key_exists("query", $_GET))) {
        //     // "pc" => calcul de la catégorie du pc
        //     $query = strtolower($_GET["query"]);
        // }

        $outfmt = "cat";
        if ((array_key_exists("pc", $_GET))) {
            $outfmt = "text";
        }
        if ((array_key_exists("outfmt", $_GET))) {
            $outfmt = strtolower($_GET["outfmt"]);
        }

        $seperateur = "\t";
        if ((array_key_exists("seperateur", $_GET))) {
            $seperateur = $_GET["seperateur"];
        }

        $detail = false; // 1 => le détail du calcul est joint au résultat
        if ((array_key_exists("detail", $_GET))) {
            if ($_GET["detail"] = "1") {
                $detail = true;
            }
        }

        // $log = false; // 1 => le log est joint à la réponse
        // if ((array_key_exists("log", $_GET))) {
        //     if ($_GET["log"] = "1") {
        //         $log = true;
        //     }
        // }


        $tailleram   = "";
        $tailledisk1 = "";
        $typedisk1   = "";
        $tailledisk2 = "";
        $typedisk2   = "";
        $cpu[]       = array();
        $recondid    = "";
        if ((array_key_exists("pc", $_GET))) {
            // appel de l'API en mode concaténation
            // utile pour mettre en formule excel
            $descriptionPc = explode('|', $_GET['pc']);
            $cpu[0]        = (count($descriptionPc) > 0) ? $descriptionPc[0] : "";
            $tailledisk1   = (count($descriptionPc) > 2) ? $descriptionPc[2] : "";
            $typedisk1     = (count($descriptionPc) > 1) ? $descriptionPc[1] : "";
            $tailledisk2   = (count($descriptionPc) > 4) ? $descriptionPc[4] : "";
            $typedisk2     = (count($descriptionPc) > 3) ? $descriptionPc[3] : "";
            $tailleram     = (count($descriptionPc) > 5) ? $descriptionPc[5] : "";
            $recondid      = (count($descriptionPc) > 6) ? $descriptionPc[6] : "";
        } else {
            // les valeurs sont passées par paramètre individuel
            $errparammsg = [];

            if (  !array_key_exists("cpu", $_GET) ) {
                $errparammsg[] = "[Il manque le paramètre 'cpu']";
            }
            
            if (  !array_key_exists("tailledisk1", $_GET) ) {
                $errparammsg[] = "[Il manque le paramètre 'tailledisk1']";
            }

            if (  !array_key_exists("typedisk1", $_GET) ) {
                $errparammsg[] = "[Il manque le paramètre 'typedisk1']";
            }

            if (  array_key_exists("tailledisk2", $_GET) And !array_key_exists("typedisk2", $_GET)) {
                $errparammsg[] = "['tailledisk2' fournit, il manque le paramètre 'typedisk2']";
            }

            if (  array_key_exists("typedisk2", $_GET) And !array_key_exists("tailledisk2", $_GET)) {
                $errparammsg[] = "['typedisk2' fournit, il manque le paramètre 'tailledisk2']";
            }

            if (  !array_key_exists("tailleram", $_GET) ) {
                $errparammsg[] = "[Il manque le paramètre 'tailleram']";
            }

            if (  !array_key_exists("recondid", $_GET) ) {
//                $errparammsg[] = "[Il manque le paramètre 'recondid']";
            } 

            if (count($errparammsg) != 0) {
                echo 'erreur:'.implode("",$errparammsg).'[query reçu :'.$_SERVER['QUERY_STRING'].']';
            }else{
                if ((array_key_exists("cpu", $_GET))) {
                    $cpu[0] = $_GET["cpu"];
                } elseif ((array_key_exists("cpu[]", $_GET))) {
                    $cpu = $_GET["cpu"];
                }

                if ((array_key_exists("tailledisk1", $_GET))) {
                    $tailledisk1 = strtoupper($_GET["tailledisk1"]);
                }

                if ((array_key_exists("typedisk1", $_GET))) {
                    $typedisk1 = strtoupper($_GET["typedisk1"]);
                }

                if ((array_key_exists("tailledisk2", $_GET))) {
                    $tailledisk2 = strtoupper($_GET["tailledisk2"]);
                }

                if ((array_key_exists("typedisk2", $_GET))) {
                    $typedisk2 = strtoupper($_GET["typedisk2"]);
                }

                if ((array_key_exists("tailleram", $_GET))) {
                    $tailleram = strtoupper($_GET["tailleram"]);
                }

                // TriRA : TRIRHO01
                // ASF : ATELSF01
                if ((array_key_exists("recondid", $_GET))) {
                    $recondid = strtoupper($_GET["recondid"]);
                }

                $this->exec($cpu, $tailledisk1, $typedisk1, $tailledisk2, $typedisk2, $tailleram, $outfmt, $seperateur, $detail, $debug, $recondid);

            }
        }
    }

    private function exec($cpu, $tailledisk1, $typedisk1, $tailledisk2, $typedisk2, $tailleram, $outfmt, $seperateur, $detail, $debug, $recondid="") {
        GLOBAL $g_environnement;
        $this->logger->addLogDebugLine(">>>> exec    environnement = '$g_environnement'");
        $cePC =  PC::getInstance();;
        $cePC->setCpuTextInputArray($cpu);
        $cePC->setTailleRam($tailleram);
        $cePC->setDisk(1, $tailledisk1, $typedisk1);
        if ($tailledisk2 != "") {
            $cePC->setDisk(2, $tailledisk2, $typedisk2);
        }
        logexec(basename(__FILE__), '|demande| |' .$_SERVER["QUERY_STRING"]. '|'  .json_encode($_REQUEST), "api");
        $evalPC = EvaluationPc::getInstance($cePC)->getEvalPc();
        logexec(basename(__FILE__), '|result|'.$evalPC->getCategoriePC().'|' .$_SERVER["QUERY_STRING"]. '|'  .json_encode($_REQUEST). '|' .json_encode($evalPC->convertToArray()), "api");

        switch ($outfmt) {
            case "cat":
                if (!$evalPC->getStatus() == "err") {
                    $categoriePCToSend = $evalPC->getCategoriePC();
                    if ($g_environnement != 'PROD') {
                        $categoriePCToSend = $categoriePCToSend ." test";
                    }
                    echo $categoriePCToSend;

                    if ($debug == 1) {
                        echo nl2br($this->logger->getLog());
                    }
                }else{
                    echo "erreur:" .$evalPC->getEvaluationErrorsCl()->getErrorsMsgAsString().'[query brute reçu :'.$_SERVER['QUERY_STRING'].']';
                }
                break;
            case "text":
                echo $evalPC->convertToText($seperateur, $detail);
                if ($debug == 1) {
                    echo nl2br($this->logger->getLog());
                }
                break;
            case "html":
                echo $evalPC->convertToTable($detail);
                if ($debug == 1) {
                    echo nl2br($this->logger->getLog());
                }
                break;
            case "json":
                echo "le formet JSON n'est pas supporté";
                // header('Content-Type: application/json');
                // if ($log) {
                //     $evalPC["log"] = getLog();
                // }
                // echo json_encode($evalPC);
                break;

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