<?php
declare(strict_types=1);
require_once __DIR__.'/smartphone.class.php';
require_once __DIR__.'/evaluationsm.class.php';
require_once __DIR__.'/../paramini.class.php';

//include the file that loads the PhpSpreadsheet classes
require __DIR__.'/../../../libraries/spreadsheet/vendor/autoload.php';
//include the file that loads the PhpSpreadsheet classes//include the classes needed to create and write .xlsx file
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Traitement d'un fichier Excel
 */
class TrtExcelSm {
    private $log=""; // 1 => le log est joint à la réponse (pour debug)
    private $logger; // initialisé à l'instantiation
    private $timeStampStart;
    private $debug;
    private function __construct(){ }

    /**
     * retrun a new instance
     *
     * @param string $uploadType
     * @param string $debug
     * @return TrtExcelSm
     */
    public static function getInstance(string $uploadType, string $debug) : TrtExcelSm
    {
        $c = new TrtExcelSm();
        $c->logger = LoggerRec::getInstance();
        //$c->uploadType = $uploadType;
        $c->debug = $debug;
        return $c;
    }

    /** trtExcel()
     *  pour le trt d'un fichier Excel :
     *  ligneentete     : n° de la ligne en-tête de colonne    dft = 5
     *  ...
     *  recalculcategorie
     */
    public function trtExcelSm() {
        GLOBAL $g_environnement;
        $uploadType = $_GET["upload"];
        $this->logger->addLogDebugLine('>>> execUpload  uploadType = "'.$uploadType.'" __LINE__');

        //=========== Traitement de $_POST =================================================
        // $inMap contient contient les données du formulaire
        $inMap = [];
        if (array_key_exists("ligneentete", $_POST)) {
            $ligneentete=$_POST['ligneentete'] ;
        }else{ 
            $ligneentete="";
        }
        $inMap["ligneentete"] = $ligneentete;

        if (array_key_exists("colnumlot", $_POST)) {
            $colnumlot=strtoupper($_POST['colnumlot'] );
        }else{ 
            $colnumlot="";
        }
        $inMap["colnumlot"] = $colnumlot;

        if (array_key_exists("colidentifiantunique", $_POST)) {
            $colidentifiantunique=strtoupper($_POST['colidentifiantunique']) ;
        }else{ 
            $colidentifiantunique="";
        }
        $inMap["colidentifiantunique"] = $colidentifiantunique;
            
        if (array_key_exists("coltypemateriel", $_POST)) {
            $coltypemateriel=strtoupper($_POST['coltypemateriel']) ;
        }else{ 
            $coltypemateriel="";
        }
        $inMap["coltypemateriel"] = $coltypemateriel;

        if (array_key_exists("colconstructeur", $_POST)) {
            $colconstructeur=strtoupper($_POST['colconstructeur']) ;
        }else{ 
            $colconstructeur="";
        }
        $inMap["colconstructeur"] = $colconstructeur;

        if (array_key_exists("colmodel", $_POST)) {
            $colmodel=strtoupper($_POST['colmodel']) ;
        }else{ 
            $colmodel="";
        }
        $inMap["colmodel"] = $colmodel;

        if (array_key_exists("colimei", $_POST)) {
            $colimei=strtoupper($_POST['colimei']) ;
        }else{ 
            $colimei="";
        }
        $inMap["colimei"] = $colimei;

        if (array_key_exists("colcpu", $_POST)) {
            $colcpu=strtoupper($_POST['colcpu']) ;
        }else{ 
            $colcpu="";
        }
        $inMap["colcpu"] = $colcpu;

        if (array_key_exists("colos", $_POST)) {
            $colos=strtoupper($_POST['colos']) ;
        }else{ 
            $colos="";
        }
        $inMap["colos"] = $colos;

        if (array_key_exists("coltaillestockage", $_POST)) {
            $coltaillestockage=strtoupper($_POST['coltaillestockage']) ;
        }else{ 
            $coltaillestockage="";
        }
        $inMap["coltaillestockage"] = $coltaillestockage;

        if (array_key_exists("coltailleram", $_POST)) {
            $coltailleram=strtoupper($_POST['coltailleram']) ;
        }else{ 
            $coltailleram="";
        }
        $inMap["coltailleram"] = $coltailleram;

        if (array_key_exists("colbatterie", $_POST)) {
            $colbatterie=strtoupper($_POST['colbatterie']) ;
        }else{ 
            $colbatterie="";
        }
        $inMap["colbatterie"] = $colbatterie;

        if (array_key_exists("colecran", $_POST)) {
            $colecran=strtoupper($_POST['colecran']) ;
        }else{ 
            $colecran="";
        }
        $inMap["colecran"] = $colecran;

        if (array_key_exists("colecranresolution", $_POST)) {
            $colecranresolution=strtoupper($_POST['colecranresolution']) ;
        }else{ 
            $colecranresolution="";
        }       
        $inMap["colecranresolution"] = $colecranresolution;

        if (array_key_exists("colchargeur", $_POST)) {
            $colchargeur=strtoupper($_POST['colchargeur']) ;
        }else{ 
            $colchargeur="";
        }       
        $inMap["colchargeur"] = $colchargeur;

        if (array_key_exists("coloperateur", $_POST)) {
            $coloperateur=strtoupper($_POST['coloperateur']) ;
        }else{ 
            $coloperateur="";
        }
        $inMap["coloperateur"] = $coloperateur;

        if (array_key_exists("colstatut", $_POST)) {
            $colstatut=strtoupper($_POST['colstatut']) ;
        }else{ 
            $colstatut="";
        }
        $inMap["colstatut"] = $colstatut;

        if (array_key_exists("colremarque", $_POST)) {
            $colremarque=strtoupper($_POST['colremarque']) ;
        }else{ 
            $colremarque="";
        }
        $inMap["colremarque"] = $colremarque;

        if (array_key_exists("colcouleur", $_POST)) {
            $colcouleur=strtoupper($_POST['colcouleur']) ;
        }else{ 
            $colcouleur="";
        }
        $inMap["colcouleur"] = $colcouleur;

 
        if (array_key_exists("colgradeesthetique", $_POST)) {
            $colgradeesthetique=strtoupper($_POST['colgradeesthetique']) ;
        }else{ 
            $colgradeesthetique="";
        }        
        $inMap["colgradeesthetique"] = $colgradeesthetique;

        if (array_key_exists("colcategorie", $_POST)) {
            $colcategorie=strtoupper($_POST['colcategorie']) ;
        }else{ 
            $colcategorie="";
        }
        $inMap["colcategorie"] = $colcategorie;
        
        if (array_key_exists("colerreur", $_POST)) {
            $colerreur=strtoupper($_POST['colerreur']) ;
        }else{ 
            $colerreur=$colcategorie;
        }
        if ($colerreur == "") {
            // si col erreur n'est pas renseignée, on met le texte d'erreur dans la catégorie 
            $colerreur=$colcategorie;
        }
        $inMap["colerreur"] = $colerreur;

        // coldebug est une checkbox
        if (array_key_exists("coldebug", $_POST)) {
            $coldebug=strtoupper($_POST['coldebug']) ;
        }else{ 
            $coldebug="";
        }
        $inMap["coldebug"] = $coldebug;
        
        $recalculcategorie = true;
        if (array_key_exists("recalculcategorie", $_POST)) {
            $recalculcategorie=$_POST['recalculcategorie'];
        }
        if ($recalculcategorie == 'yes') {
            $recalculcategorie = true;
        }else{ 
            $recalculcategorie = false;
        }
        $inMap["recalculcategorie"] = $recalculcategorie;       

        if (array_key_exists("unitepardefaut", $_POST)) {
            $unitepardefaut=$_POST['unitepardefaut'];
        }
        if ($unitepardefaut == 'yes') {
            $unitepardefaut = "Go";
        }else{ 
            $unitepardefaut = "";
        }
        $inMap["unitepardefaut"] = $unitepardefaut;

        //=========== Traitement du fichier =================================================
        $source = $_FILES["upfile"]["tmp_name"]; // D:\xampp\tmp\phpDDAB.tmp
        $horodate = time();
        $this->timeStampStart = $horodate;
        $fileNameInput = $_FILES["upfile"]["name"];
        $this->logger->addLogDebugLine($fileNameInput, '==== trt du fichier ======================================');
        //$fileNameInputExt = pathinfo($fileNameInput, PATHINFO_EXTENSION);
        $fileNameOrig = $horodate.'_'.$fileNameInput;
        $destDir  = __DIR__."/../../../public/upload/";
        $destUrl  = "upload/";
        $destFileOrg = $destDir.$fileNameOrig;
        //$progressId=$_POST['id'];
        move_uploaded_file($source, $destFileOrg);
        //$progressFileName =__DIR__.'/../../work/progressfiles/'.$progressId.".txt";
        //$_SERVER['REMOTE_ADDR'].
        //date('d/m/y H-i-s');
        logexec(basename(__FILE__), $fileNameInput . " param =" .json_encode($inMap));
        $debugColHeader = ["cpuTextInput",
                "cputextnorm",
                "cpuWebName",
                "indiceCPU",
                "origine",
                "categorieCPU",
                "tailleDisk01",
                "typeDisk01",
                "categorieDisk01",
                "tailleDisk02",
                "typeDisk02",
                "categorieDisk02",
                "categorieDisk",
                "tailleRam",
                "categorieRam",
                "categorieTotal",
                "categoriePCcodeNormale",
                "categoriePCnormale",
                "categoriePCcodeMaxi",
                "categoriePCcode",
                "categoriePCCorrigée"
            ];
        
        try {
            //create directly an object instance of the IOFactory class, and load the xlsx file
            //echo "load spreadsheet     ";
            //$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($destFileOrg);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($destFileOrg);

            //read excel data and store it into an array
            $spreadsheet->setActiveSheetIndex(0);
            
            // NULL,        // Value that should be returned for empty cells
            // TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
            // TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
            // TRUE         // Should the array be indexed by cell row and cell column
            //echo "spreadsheet->getActiveSheet()->toArray(null, false, false, true)";
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
            $xls_data  = $worksheet->toArray(null, false, false, true);

            $nbrows = count($xls_data); //number of rows
            $this->logger->addLogDebugLine("highestRow : $highestRow  highestColumn : $highestColumn     highestColumnIndex : $highestColumnIndex", 'Sheet size ');
    
            //$this->addProgress($progressFileName, '0', $fileNameInput);
                
            if ($coldebug != "") {
                $spreadsheet->setActiveSheetIndex(0)->fromArray(
                    $debugColHeader ,
                    null, $coldebug.$ligneentete);
            }

            //$ceSM      = Smartphone::getInstance();
            $firstLine = $ligneentete + 1;

            // ***********************************************************************************
            // ********* Calcul de la catégorie pour chaque ligne de l'excel *********************
            // ***********************************************************************************
            $lineTrt   = 0;
            for($i=$firstLine; $i<=$nbrows; $i++){
                ++$lineTrt;
                $this->logger->addLogDebugLine('début SM '.$i. "=============================================================");
                //$ceSM->resetPc();
                $ceSM = Smartphone::getInstance();
                $ceSM->setUniteParDefaut($unitepardefaut);
                if ($recalculcategorie or $xls_data[$i][$colcategorie] == "") {
                    if ($xls_data[$i][$colconstructeur] != "" And $xls_data[$i][$colconstructeur] != null) {
                        // on vérifie que les champs importants ne sont pas des formules
                        $msg =''; // messages d'erreur
                        //$a = 


                        if (($colmodel != "" and static::isFormula($xls_data[$i][$colmodel]))
                                or (static::isFormula($xls_data[$i][$colmodel]))
                                or (static::isFormula($xls_data[$i][$coltaillestockage]))
                                or (static::isFormula($xls_data[$i][$coltaillestockage]))
                                ) {
                            $categoriePCToPrint = "erreur";
                            $msg ="[Une des colonnes du tableau inital contient une formule ou RAM ou STOCKAGE ne pont pas numérique: $i]";
                            $this->logger->addLogDebugLine($msg, 'Erreur  ');
                        }else{
                            $ceSM->setMarque(  "".$xls_data[$i][$colconstructeur]);
                            $ceSM->setModele(  "".$xls_data[$i][$colmodel]);
                            $ceSM->setRam(     "".$xls_data[$i][$coltailleram]);
                            $ceSM->setStockage("".$xls_data[$i][$coltaillestockage]) ;

                            $this->logger->addLogDebugLine($ceSM->toString(), 'ceSM '.$i. "=========================================");
                            $evaluationSmClInstance = EvaluationSm::getInstance($ceSM);
                            $evaluationSmCl         = $evaluationSmClInstance->evalSmartphone();
                            if ($evaluationSmCl->getErrMsg() == "" ) {
                                $categorieSm            = $evaluationSmCl->getCategoriePondereAlpha();
                            }else{
                                $categorieSm            = 'err';
                            }
                            $categorieSmToPrint     = $categorieSm;
                            if ($g_environnement != 'PROD') {
                                $categorieSmToPrint .= " test";
                            }
                            $msg = $evaluationSmCl->getErrMsg();

                            if ($coldebug != "") {
                                $evaluationSmClasArray=$evaluationSmCl->convertToArray();
                                $arrayDebug = 
                                    [
                                        $evaluationSmClasArray["cpuTextInput"],
                                        $evaluationSmClasArray["cputextnorm"],
                                        $evaluationSmClasArray["cpuWebName"],
                                        $evaluationSmClasArray["indiceCPU"],
                                        $evaluationSmClasArray["origine"],
                                        $evaluationSmClasArray["categorieCPU"],
                                        $evaluationSmClasArray["tailleDisk01"],
                                        $evaluationSmClasArray["typeDisk01"],
                                        $evaluationSmClasArray["categorieDisk01"],
                                        $evaluationSmClasArray["tailleDisk02"],
                                        $evaluationSmClasArray["typeDisk02"],
                                        $evaluationSmClasArray["categorieDisk02"],
                                        $evaluationSmClasArray["categorieDisk"],
                                        $evaluationSmClasArray["tailleRam"],
                                        $evaluationSmClasArray["categorieRam"],
                                        $evaluationSmClasArray["categorieTotal"],
                                        $evaluationSmClasArray["categoriePCcodeNormal"],
                                        $evaluationSmClasArray["categoriePCnormale"],
                                        $evaluationSmClasArray["categoriePCcodeMaxi"],
                                        $evaluationSmClasArray["categoriePCcode"],
                                        $evaluationSmClasArray["categoriePCCorrigée"]
                                    ];
    
                                try {
                                    $spreadsheet->setActiveSheetIndex(0)->fromArray(
                                        $arrayDebug,
                                        null, $coldebug.$i);
                                }catch (Exception $ex) {
                                    echo "erreur lors de l'écriture du résultat excel<br>";
                                    echo 'Exception : '.__LINE__;
                                    echo $ex->getMessage().'<br>';
                                    echo $ex->getTraceAsString();
                                }
                            }
                        }
                        $spreadsheet->setActiveSheetIndex(0)
                            ->setCellValue($colcategorie.$i, $categorieSmToPrint);
                        
                        if ($colerreur == $colcategorie) {
                            $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue($colcategorie.$i, $categorieSm . $msg );
                        }else{
                            $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue($colerreur.$i, $msg);
                        }
    
 
                    }
                }
            } // fin du FOR traitement des lignes de l'excel
            
            // ***********************************************************************************
            // ********* MAJ de l'excel soumis ***************************************************
            // ***********************************************************************************
            $writer = new Xlsx($spreadsheet);
            $fileResultName = $horodate.'_r_sm_'.$fileNameInput;
            //$fileResult = $destDir. $fileResultName;
            $writer->save($destDir. $fileResultName);
 
            // ***********************************************************************************
            // ********* Crt d'un Excel au format BOLC *******************************************
            // ***********************************************************************************
            $spreadsheetNorm = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__."/../../data/sm_modele_BOLC_v1.xlsx");
            //spreadsheetNorm = new Spreadsheet();
            $sheetNorm       = $spreadsheetNorm->getActiveSheet();
            $fileNorm        = __DIR__."/../../data/exceltemplatescstsm.json";
            $dataNorm        = file_get_contents($fileNorm);
            $dataNormJson    = json_decode($dataNorm, true);
            $xlsNormJsonCol     = $dataNormJson['*BOLC']['data'];
            $xlsNormJsonHeader  = $dataNormJson['*BOLC']['header'];
            $lineHeaderNorm     = $xlsNormJsonCol["ligneentete"];
            // écriture de la ligne en-tête
            foreach ($xlsNormJsonCol as $key => $colNorm) {
                if (str_starts_with($key, 'col')) {
                    $valHeader = $xlsNormJsonHeader[$key];
                    $sheetNorm->setCellValue($colNorm.$lineHeaderNorm,$valHeader);
                }
            }
            // écriture des données
            $lineTrtNorm = $lineHeaderNorm;
            // parcour des lignes du tableau soumis résultat
            for($i=$firstLine; $i<=$nbrows; $i++){
                ++$lineTrtNorm;
                foreach ($xlsNormJsonCol as $key => $colNorm) {
                    // xls_data est le tableau contenant la feuiile calculé
                    if (str_starts_with($key, 'col')) {
                        if ($inMap[$key] != "") {
                            //on ne fait que si la colonne existe en entrée
                            $val1 = $spreadsheet->getActiveSheet()->getCell($inMap[$key].$i)->getValue();
                            if (static::isFormula($val1)) {
                                $val1 = "'".$val1;
                                $sheetNorm->getStyle($colNorm.$lineTrtNorm)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                            }
                            $sheetNorm->setCellValue($colNorm.$lineTrtNorm,$val1);
                       }
                    }
                }
            }

            // ajout des infos de débug s'il y a lieu
            // on écrit également l'en-tête débug si elle existe

            if ($coldebug != "") {
               // l'entête débug
                $colIn = $coldebug;
                $nbColDebug = count($debugColHeader); // nombre de colonnes à copier
                $colOut = "V";
                for ($c=1 ; $c <= $nbColDebug ; $c++) {
                    $val1 = $spreadsheet->getActiveSheet()->getCell($colIn.$ligneentete)->getValue();
                    $sheetNorm->setCellValue($colOut.$lineHeaderNorm,$val1);
                    ++$colIn;
                    ++$colOut;
                }
                // les données débug
                $firstLine   = $ligneentete + 1;
                $lineTrtNorm = $lineHeaderNorm;
                for($l=$firstLine; $l<=$nbrows; $l++){
                    ++$lineTrtNorm;
                    $colIn  = $coldebug;
                    $colOut = "V";
                    for ($c=1 ; $c <= $nbColDebug ; $c++) {
                        $val1 = $spreadsheet->getActiveSheet()->getCell($colIn.$l)->getValue();
                        $sheetNorm->setCellValue($colOut.$lineTrtNorm,$val1);
                        ++$colIn;
                        ++$colOut;
                    }
                }
            }

            $writerNorm = new Xlsx($spreadsheetNorm);
            $fileResultNameNorm = $horodate.'_n_sm_'.$fileNameInput;
            //$fileResult = $destDir. $fileResultName;
            $writerNorm->save($destDir. $fileResultNameNorm);

            // ***********************************************************************************
            // ********* envoi de la réponse avec le statut du résultat **************************
            // ***********************************************************************************
            $retour = array(
                'status' => "OK",
                "url"    => $destUrl.$fileResultName,
                "url2"   => $destUrl.$fileResultNameNorm,
                "log"    => $this->logger->getLog(),
                "duree"  => time() - $this->timeStampStart,
                //'progressid' => $progressId,
                'highestRow'       => $highestRow,
                'highestColumn'    => $highestColumn,
                'nbrows'           => $nbrows,
                'entetecpu'        => $xls_data[$ligneentete][$colcpu],
                'entetetailleram'  => $xls_data[$ligneentete][$coltailleram]
                );
            if ($uploadType ==  "1") {
                $this->logger->addLogDebugLine("envoi de la réponse Json ".__LINE__);
                echo json_encode($retour);
            }else {
                $htmlPage  = '<!DOCTYPE html><HTML><HEAD></HEAD><body class="body_flex">';
                $htmlPage .= 'cliquez <a href="' . $retour['url']. '">ici</a> pour charger le résultat.';
                echo $htmlPage;
            }

        }catch (Exception $ex) {
            $this->logger->addLogDebugLine($ex->getTrace(),'Stacktrace '.__LINE__);
            var_dump($ex);
            $errMsg = 'traitement du fichier ' .$fileNameInput. ' impossible';
            if ($uploadType ==  "1") {
                $retour = array(
                    'status' => "KO",
                    "url" => ''  ,
                    "log" => $this->logger->getLog(),
                    "duree" => time() - $this->timeStampStart,
                    //'progressid' => $progressId,
                    'errmsg'=>$errMsg);
                echo json_encode($retour);
            }else {
                $htmlPage  = '<!DOCTYPE html><HTML><HEAD></HEAD><body class="body_flex">';
                $htmlPage .= __FILE__ + " " + __LINE__ +"<br>";
                $htmlPage .= $errMsg;
                echo $htmlPage;
            }
        }
    }

    static private function isFormula($cellValue) {
        return (!is_null($cellValue) and  is_string($cellValue) and str_starts_with($cellValue,"="));
    }

    // **********************************************
    // *** MAJ progression
    // **********************************************
    // function addProgress($progressFileName, $value, $msg) {
    //     $progressFile = fopen($progressFileName, "w") or die("Unable to open progress file : [$progressFileName]");
    //     fwrite($progressFile, $value."\t".$msg.PHP_EOL);
    //     fclose($progressFile);
    // }
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

?>