<?php
declare(strict_types=1);
require_once __DIR__ . '/pc.class.php';
require_once __DIR__ . '/evaluationpc.class.php';
require_once __DIR__ . '/util01.class.php';
require_once __DIR__ . '/../php/pageheaderhtml.php';
require_once __DIR__ . '/../php/pageformulairehtml.php';
require_once __DIR__ . '/../php/pagedebughtml.php';

/**
 * Affichage du forumulaire permettant d'évaluer 1 PC
 */
class FormTableau
{
    private string    $debug;    // init à l'instanciation
    private LoggerRec $logger;   // init à l'instanciation

    private function __construct()
    {
    }

    public static function getInstance(string $debug): FormTableau
    {
        $c = new FormTableau();
        $c->logger = LoggerRec::getInstance();
        $c->debug  = $debug;
        return $c;
    }

    /**
     *    execInit
     *    =======
     *  affiche le formulaire initial
     */
    function execinit()
    {
        $this->execPost(true);
    }

    /**
     *    execPos
     *    =======
     * cette fonction traite les demandes faites par formulaire
     * et affiche la réponse
     */
    function execPost(bool $init=false)
    {
        $debug = $this->debug;
        $this->logger->addLogDebugLine(">>>> execPost");

        $evaluationPCArray = [];

        if (array_key_exists("inputcpu", $_POST)) {
            // traitement de la textarea de saisie
            $evaluationPCArray = $this->getReponse();
        }

        // formulaire de saisie de PC par texte
        echo getCopieColonnesHead();
        echo '<body class="body_flex"><main>';
        echo getHtmlHeader();
        echo getCopieColonnes($evaluationPCArray, $init);
        if ($debug == 1) {
            echo getHtmlDebug($evaluationPCArray);
        }
        echo  getFooter();
        echo '</main></body></html>';
    }


    /**
     * chaque ligne de PC ainsi que les champs de saisi individuel
     *    donne une ligne de résultat de la textearea
     * @return array : tableau d'objet EvaluationPC
     */
    function getReponse() : array
    {
        $this->logger->addLogDebugLine(">>>> getReponse");

        $detail = false;
        if (isset($_POST['checkboxDetailCalcul'])) {
            $detail = true;
        }
        $result = "";

        // si les champs individuel cpu ... sont renseignés, on les ajoute à la textarea
        $demandeTextArea  = "";
        if ($_POST["inputcpu"] != "") {
            $demandeTextArea .= "\n"   . $_POST["inputcpu"];
            $demandeTextArea .= "\t"   . $_POST["inputtailledisk"];
            $demandeTextArea .= "\t"   . $_POST["inputtypedisk"];
            $demandeTextArea .= "\t"   . $_POST["inputtailledisk2"];
            $demandeTextArea .= "\t"   . $_POST["inputtypedisk2"];
            $demandeTextArea .= "\t"   . $_POST["inputtailleram"];
        }


        $demandeTxt = array("listePCtxt" => $demandeTextArea); //, "fmtcpu" => $_POST["fmtcpu"]);
        $evaluationPCArray = $this->evaluerListePCtxt($demandeTxt, $detail);
        $this->logger->addLogDebugLine($evaluationPCArray, 'getReponse $evaluationPCArray');
        return $evaluationPCArray;
    }



    /**
     * Undocumented function
     * @param array $demandeTxt : tableau associatif :
     *   [listePCtxt] => contenu de la textarea,
     *       une ligne par PC (CPU, taille RAM, taille disk, typeSSD...) séparée par des tabulations
     *   [fmtcpu] => format du texte cpu
     * @param boolean $detail : true => mettre le detail du calcul dans la réponse
     * @return array : tableau d'objet EvaluationPC
     */
    function evaluerListePCtxt(array $demandeTxt, bool $detail) : array
    {
        $this->logger->addLogDebugLine(">>> evaluerListePCtxt");

        $lignesPCtxt = explode("\n", $demandeTxt['listePCtxt']);
        $this->logger->addLogDebugLine($lignesPCtxt, "lignesPCtxt");
        $retour = array();
        foreach ($lignesPCtxt as $lignePCtxt) {
            if (strlen($lignePCtxt) > 1) {
                // la ligne n'est pas vide
                $demandeLigne = explode("\t", $lignePCtxt);
                if (count($demandeLigne) >= 1) {
                    if (strlen(trim($demandeLigne[0])) > 0) {
                        $cePC = PC::getInstance();
                        // CPU
                        $cePC->setCpuTextInputArray(array($demandeLigne[0])); // plusieurs syntaxe possible pour un cpu

                        // Disk : taille  & type
                        $cePC->setDisk(
                            1,
                            count($demandeLigne) > 1 ? $demandeLigne[1] : "-1g",
                            count($demandeLigne) > 2 ? $demandeLigne[2] : "HDD"
                        );
                        $cePC->setDisk(
                            2,
                            count($demandeLigne) > 3 ? $demandeLigne[3] : "-1g",
                            count($demandeLigne) > 4 ? $demandeLigne[4] : "HDD"
                        );
                        // taille RAM
                        $cePC->setTailleRam(
                            count($demandeLigne) > 5 ? $demandeLigne[5] : "-1g"
                        );
                        //$cePC->setFmtCpu($demandeTxt['fmtcpu']);
                        $eval = $this->evaluerUneDemande($cePC, $lignePCtxt);
                        $retour[] = $eval;
                    }
                }
            }
        }
        return $retour;
    }

    /** evalue Une Demande
     * @param PC $cePC
     * @param string $demande
     * @param bool $detail
     * @return EvaluationPc
     */
    private function evaluerUneDemande(PC $cePC, string $demande): EvaluationPc
    {
        $this->logger->addLogDebugLine(">>> evaluerUneDemande");
        $this->logger->addLogDebugLine($cePC, '$cePC');
        $this->logger->addLogDebugLine($demande, '$demande');
        $evaluationPcCl = EvaluationPc::getInstance($cePC)->getEvalPc();
        $this->logger->addLogDebugLine("<<< evaluerUneDemande");
        return $evaluationPcCl;
    }



    /** convertit une chaîne avec de \n et \t en tableau html
     * @param [type] $reponse  la reponse format texte affichée
     * @return string
     */
    static function convertTextToTable($chaine): string
    {
        $table = <<<'EOT'
	<table><tr>
    <th>PC</th>
    <th>CPU</th>
    <th>CPU normalisé</th>
    <th>Indice CPU</th>
    <th>Origine</th
    ><th>Cat CPU</th>
	<th>Ram</th>
    <th>Cat Ram</th>
	<th>Disque</th>
    <th>Type dsk</th>
    <th>Cat Dsk</th>
	<th>Total</th></tr>
EOT;
        $lignesHtml = "";
        $lignes = explode("\n", $chaine);
        foreach ($lignes as $ligne) {
            $ligneHtml = "<tr>";
            $colonnes = explode("\t", $ligne);
            foreach ($colonnes as $col) {
                $ligneHtml .= "<td>$col</td>";
            }
            $ligneHtml .=  "</tr>";
            $lignesHtml .= $ligneHtml;
        }
        $table .= $lignesHtml . '</table>';
        return $table;
    }
	//******************************************************************* */
	function __call($name, $arguments)
    {
        throw new Exception("Appel de la méthode non statique inconnue : '$name'");
    }

    static function __callStatic($name, $arguments)
    {
        throw new Exception("Appel de la méthode statique inconnue : '$name'");
    }

    function __set($name, $value)
    {
        throw new Exception("Set d'une propriété inconnue : '$name'");
    }

    function __get($name)
    {
        throw new Exception("Get d'une propriété inconnue : '$name'");
    }
}
