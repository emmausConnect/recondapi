<?php
declare(strict_types=1);
require_once __DIR__.'/pc.class.php';
require_once __DIR__.'/evaluationpc.class.php';
require_once __DIR__.'/paramini.class.php';
require_once __DIR__.'/contexte.class.php';

//include the file that loads the PhpSpreadsheet classes
require __DIR__.'/../../libraries/spreadsheet/vendor/autoload.php';
//include the file that loads the PhpSpreadsheet classes//include the classes needed to create and write .xlsx file
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Traitement d'un fichier Excel
 */
class TrtExcelPc {
    private $log=""; // 1 => le log est joint à la réponse (pour debug)
    private $logger; // initialisé à l'instantiation
    private $timeStampStart;
    private Contexte $contexte;
    private $debug;
    private function __construct(){ }

    public static function getInstance(string $uploadType, string $debug) : TrtExcelPc
    {
        $c = new TrtExcelPc();
        $c->logger   = LoggerRec::getInstance();
        $c->contexte = Contexte::getInstance();
        $c->debug = $debug;
        return $c;
    }

    /** trtExcel()
     *  pour le trt d'un fichier Excel :
     *  ligneentete     : n° de la ligne en-tête de colonne    dft = 5
     *  colcpu          : colonne processeur
     *  coltailleram    : colonne taille ram
     *  coltailledisk   : colonne taille disk
     *  coltypedisk     : colonne type disque
     *  coltailledisk2  : colonne taille disk
     *  coltypedisk2    : colonne type disque
     *  colcategorie    : colonne devant recevoir le résultat
     *  colerreur       : par defaut = colcategorie
     *  recalculcategorie
     */
    public function trtExcel() {
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

        if (array_key_exists("colpcmodel", $_POST)) {
            $colpcmodel=strtoupper($_POST['colpcmodel']) ;
        }else{ 
            $colpcmodel="";
        }
        $inMap["colpcmodel"] = $colpcmodel;

        if (array_key_exists("colnumserie", $_POST)) {
            $colnumserie=strtoupper($_POST['colnumserie']) ;
        }else{ 
            $colnumserie="";
        }
        $inMap["colnumserie"] = $colnumserie;

        if (array_key_exists("colcpu", $_POST)) {
            $colcpu=strtoupper($_POST['colcpu']) ;
        }else{ 
            $colcpu="";
        }
        $inMap["colcpu"] = $colcpu;

        if (array_key_exists("coltailledisk", $_POST)) {
            $coltailledisk=strtoupper($_POST['coltailledisk']) ;
        }else{ 
            $coltailledisk="";
        }
        $inMap["coltailledisk"] = $coltailledisk;
        if (array_key_exists("coltypedisk", $_POST)) {
            $coltypedisk=strtoupper($_POST['coltypedisk']) ;
        }else{ 
            $coltypedisk="";
        }
        $inMap["coltypedisk"] = $coltypedisk;

        if (array_key_exists("coltailledisk2", $_POST)) {
            $coltailledisk2=strtoupper($_POST['coltailledisk2']) ;
        }else{ 
            $coltailledisk2="";
        }
        $inMap["coltailledisk2"] = $coltailledisk2;
        if (array_key_exists("coltypedisk2", $_POST)) {
            $coltypedisk2=strtoupper($_POST['coltypedisk2']);
        }else{ 
            $coltypedisk2="";
        }
        $inMap["coltypedisk2"] = $coltypedisk2;

        if (array_key_exists("coltailleram", $_POST)) {
            $coltailleram=strtoupper($_POST['coltailleram']) ;
        }else{ 
            $coltailleram="";
        }
        $inMap["coltailleram"] = $coltailleram;

        if (array_key_exists("coldvd", $_POST)) {
            $coldvd=strtoupper($_POST['coldvd']) ;
        }else{ 
            $coldvd="";
        }
        $inMap["coldvd"] = $coldvd;

        if (array_key_exists("colwebcam", $_POST)) {
            $colwebcam=strtoupper($_POST['colwebcam']) ;
        }else{ 
            $colwebcam="";
        }       
        $inMap["colwebcam"] = $colwebcam;

        if (array_key_exists("colecran", $_POST)) {
            $colecran=strtoupper($_POST['colecran']) ;
        }else{ 
            $colecran="";
        }
        $inMap["colecran"] = $colecran;

        if (array_key_exists("colremarque", $_POST)) {
            $colremarque=strtoupper($_POST['colremarque']) ;
        }else{ 
            $colremarque="";
        }
        $inMap["colremarque"] = $colremarque;

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

        if (array_key_exists("typediskpardefaut", $_POST)) {
            $typediskpardefaut=$_POST['typediskpardefaut'];
        }
        if ($typediskpardefaut == 'yes') {
            $typediskpardefaut = "HDD";
        }else{ 
            $typediskpardefaut = "";
        }
        $inMap["typediskpardefaut"] = $typediskpardefaut;

        //=========== Traitement du fichier =================================================
        $source = $_FILES["upfile"]["tmp_name"]; // D:\xampp\tmp\phpDDAB.tmp
        $horodate = time();
        $this->timeStampStart = $horodate;
        $fileNameInput = $_FILES["upfile"]["name"];
        $this->logger->addLogDebugLine($fileNameInput, '==== trt du fichier ======================================');
        //$fileNameInputExt = pathinfo($fileNameInput, PATHINFO_EXTENSION);
        $fileNameOrig = $horodate.'_pc_o_'.$fileNameInput;
        $destDir  = __DIR__."/../../public/upload/";
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
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($destFileOrg);
            //read excel data and store it into an array
            //echo "setActiveSheetIndex(0)     ";
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

            $cePC      = PC::getInstance();
            $firstLine = $ligneentete + 1;

            // ***********************************************************************************
            // ********* Calcul de la catégorie pour chaque ligne de l'excel *********************
            // ***********************************************************************************
            $lineTrt   = 0;
            for($i=$firstLine; $i<=$nbrows; $i++){
                ++$lineTrt;
                $this->logger->addLogDebugLine('début PC '.$i. "=============================================================");
                $cePC->resetPc();
                $cePC->setUniteParDefaut($unitepardefaut);
                $cePC->setTypeDiskParDefaut($typediskpardefaut);
                if ($recalculcategorie or $xls_data[$i][$colcategorie] == "") {
                    if ($xls_data[$i][$colcpu] != "" And $xls_data[$i][$colcpu] != null) {
                        // on vérifie que les champs importants ne sont pas des formules
                        $msg =''; // messages d'erreur
                        if (($colpcmodel != "" and static::isFormula($xls_data[$i][$colpcmodel]))
                                or (static::isFormula($xls_data[$i][$colcpu]))
                                or (static::isFormula($xls_data[$i][$coltailleram]))
                                or (static::isFormula($xls_data[$i][$coltailledisk]))
                                or (static::isFormula($xls_data[$i][$coltypedisk]))
                                or ($coltailledisk2 != "" and static::isFormula($xls_data[$i][$coltailledisk2]))
                                or ($coltypedisk2   != "" and static::isFormula($xls_data[$i][$coltypedisk2]))
                                ) {
                            $categoriePCToPrint = "erreur";
                            $msg ="[Une des colonnes du tableau inital contient une formule : $i]";
                            $this->logger->addLogDebugLine($msg, 'Erreur  ');
                        }else{
                            if ($colpcmodel != "") {
                                $cePC->setPcModel(            "".$xls_data[$i][$colpcmodel]);
                            }
                            $cePC->setCpuTextInputArray(array("".$xls_data[$i][$colcpu]));
                            $cePC->setTailleRam(              "".$xls_data[$i][$coltailleram]);
                            $cePC->setDisk(                1, "".$xls_data[$i][$coltailledisk],"".$xls_data[$i][$coltypedisk]) ;
                            if ($coltailledisk2 != "") {
                                $cePC->setDisk(            2, "".$xls_data[$i][$coltailledisk2],"".$xls_data[$i][$coltypedisk2]) ;
                            }
                            $this->logger->addLogDebugLine($cePC->toString(), 'cePC '.$i. "=========================================");
                            $evaluationPcClInstance = EvaluationPc::getInstance($cePC);
                            $evaluationPcCl         = $evaluationPcClInstance->getEvalPc();
                            $categoriePC            = $evaluationPcCl->getCategoriePC();
                            $categoriePCToPrint     = $categoriePC;
                            // if (! $this->contexte->environnementIsProd()) {
                            //     $categoriePCToPrint .= " test";
                            // }
                            $msg = $evaluationPcCl->getEvaluationErrorsCl()->getErrorsMsgAsString();

                            if ($coldebug != "") {
                                $evaluationPcClasArray=$evaluationPcCl->convertToArray();
                                $arrayDebug = 
                                    [
                                        $evaluationPcClasArray["cpuTextInput"],
                                        $evaluationPcClasArray["cputextnorm"],
                                        $evaluationPcClasArray["cpuWebName"],
                                        $evaluationPcClasArray["indiceCPU"],
                                        $evaluationPcClasArray["origine"],
                                        $evaluationPcClasArray["categorieCPU"],
                                        $evaluationPcClasArray["tailleDisk01"],
                                        $evaluationPcClasArray["typeDisk01"],
                                        $evaluationPcClasArray["categorieDisk01"],
                                        $evaluationPcClasArray["tailleDisk02"],
                                        $evaluationPcClasArray["typeDisk02"],
                                        $evaluationPcClasArray["categorieDisk02"],
                                        $evaluationPcClasArray["categorieDisk"],
                                        $evaluationPcClasArray["tailleRam"],
                                        $evaluationPcClasArray["categorieRam"],
                                        $evaluationPcClasArray["categorieTotal"],
                                        $evaluationPcClasArray["categoriePCcodeNormal"],
                                        $evaluationPcClasArray["categoriePCnormale"],
                                        $evaluationPcClasArray["categoriePCcodeMaxi"],
                                        $evaluationPcClasArray["categoriePCcode"],
                                        $evaluationPcClasArray["categoriePCCorrigée"]
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
                            ->setCellValue($colcategorie.$i, $categoriePCToPrint);
                        
                        if ($colerreur == $colcategorie) {
                            $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue($colcategorie.$i, $categoriePC . $msg );
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
            $fileResultName = $horodate.'_pc_r_'.$fileNameInput;
            //$fileResult = $destDir. $fileResultName;
            $writer->save($destDir. $fileResultName);
 
            // ***********************************************************************************
            // ********* Crt d'un Excel au format BOLC *******************************************
            // ***********************************************************************************
            $xlsModelFile    = $this->contexte->getParamPhpIniCls()->getParam()['fichiers']['pc_modele_BOLC_xlsx_gen'];
            //$spreadsheetNorm = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__."/../data/pc_modele_BOLC_v2.xlsx");
            $spreadsheetNorm = \PhpOffice\PhpSpreadsheet\IOFactory::load( $xlsModelFile);
            //spreadsheetNorm = new Spreadsheet();
            $sheetNorm       = $spreadsheetNorm->getActiveSheet();
            $fileNorm        = __DIR__."/../data/exceltemplatescstpc.json";
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
            $fileResultNameNorm = $horodate.'_pc_n_'.$fileNameInput;
            //$fileResult = $destDir. $fileResultName;
            $writerNorm->save($destDir. $fileResultNameNorm);

            // ***********************************************************************************
            // ********* envoi de la réponse avec le statut du résultat **************************
            // ***********************************************************************************
            $cpuRamCache = CpuIndiceRamCache::getInstance();

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
                'entetetailleram'  => $xls_data[$ligneentete][$coltailleram],
                'entetetailledisk' => $xls_data[$ligneentete][$coltailledisk],
                'entetetypedisk'   => $xls_data[$ligneentete][$coltypedisk],
                'cpuramcache'      => $cpuRamCache->__toString()
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