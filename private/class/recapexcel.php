<?php
/**
 * utilitaire créé en janvier 2023 pour contrôler tous les excels déjà traités
 *  et vérifier que la catégorie y était bonne
 */

// cd D:\users\Mick5\Desktop\temp\upload\dest_r
// D:\xampp-windows-x64-8.1.4-1-VS16\php\php.exe D:\Users\Mick5\Documents\GitHub\EC-recondapi.git\private\class\recapexcel.php

declare(strict_types=1);
echo "start\n";
require __DIR__.'/../../libraries/spreadsheet/vendor/autoload.php';
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

$listCat = array("INVENDABLE" => "1", "HC" => "2", "C" => "3", "B" => "4", "A" => "5", "PREMIUM" =>"6");

// "F ... ": 1675881292_r_20230208BOLCmaterielextractionrecond.ASF300.xlsx
//   F Nom (E Categorie)
// 1647337576_r_PC dis pour moulinette.xlsx  -> HDD
// 1669398515_r_1669043122_All-in-one-MP2_ ASF - LCT.xlsx -> DD et "Type DD"
$labelCPU = array("processeur","processor");
$labelCategorie = array("catégorie","categorie");
$labelTypeDisk1 = array("type disque","type disque 1","type disque dur","Storage1Type","Type DD");
$labelSizeDisk1 = array("taille","taille disque 1","F Disque Dur ","disque dur","Storage1Size", "HDD", "DD","CAPACITE DISQUE");
$labelTypeDisk2 = array("type disque 2","type disque dur 2");
$labelSizeDisk2 = array("taille disque 2","F Disque Dur 2","Storage2Size");
$labelRAM       = array("RAM","F Memoire Vive","mémoire vive","CAPACITE MÉMOIRE");

//$fileNameOrig = "1668012795_r_Audit le boncoin1.xlsx";
include "recapexcelList.php";

$fileNameEntete = "recapexcel_entete.txt";
$fileNameDetail = "recapexcel_detail.txt";
$fileNameErreur = "recapexcel_erreur.txt";
$detailColTitle = "\"date\"\t\"fichier\"\t\"ligne\"\t\"cpu\"\t\"type disk 1\"\t\"taille disk 1\"\t\"type disk2\"\t\"taille dsk2\"\t\"RAM\"\t\"cat\"";
file_put_contents($fileNameEntete, "");
file_put_contents($fileNameDetail, "");
file_put_contents($fileNameErreur, "");


$numFichier = 0;
foreach ($listExcel as $excelFileName => $typeExcel) {
    ++$numFichier;
    echo($numFichier. "    " .$excelFileName."\n");
    trtExcel($excelFileName, $typeExcel, $reader);
}
exit();

/**
 * copie la ligne netete d'un excel dans un fichier csv et les détails dans un autre.
 *
 * @param String $fileNameOrig
 * @param string $typeExcel type d'excel "N" normal "B" export Bolc
 * @param [type] $reader
 * @return void
 */
function trtExcel(String $fileNameOrig, string $typeExcel, $reader) {
    global $labelCategorie, $labelTypeDisk1, $labelSizeDisk1, $labelTypeDisk2, 
        $labelSizeDisk2, $labelRAM, $listCat, $fileNameEntete, $fileNameDetail ;

        $fentete = fopen($fileNameEntete, "a");
        $fdetail = fopen($fileNameDetail, "a");

    $spreadsheet  = $reader->load($fileNameOrig);
    //read excel data and store it into an array
    $spreadsheet->setActiveSheetIndex(0);

    // NULL,        // Value that should be returned for empty cells
    // TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
    // TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
    // TRUE         // Should the array be indexed by cell row and cell column
    //$xls_data = $spreadsheet->getActiveSheet()->toArray(null, false, false, true);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow(); // e.g. 10
    $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    echo("    ".$fileNameOrig." highestRow : ".$highestRow."   highestColumnIndex : ".$highestColumnIndex."\n");
    //$nbrows = count($xls_data); //number of rows

    // recherche de la ligne en-tête en cherchant le libellé processeur
    $msgResult = "";
    $ligneentete = 0;
    $colProc = 0;
    $nbColProc = 0;
    $lastHeaderString = 0;
    searchHeaderLine($worksheet, $ligneentete, $colProc, $nbColProc, $lastHeaderString, $highestRow, $highestColumnIndex);
    msglog("ligneentete : ".$ligneentete."  colProc : ".$colProc."   nbColProc : ".$nbColProc."  lastString : ".$lastHeaderString."\n");

    // sauvegarde de la ligne en-tête en CSV
    // fwrite($fentete,$fileNameOrig."\t");
    // fwrite($fentete,$colProc."\t".$nbColProc."\t");
    // for($col=1; $col<=$highestColumnIndex; $col++){
    //     $value = rtvCellValue($worksheet, $col, $ligneentete);
    //     // $worksheet->getCellByColumnAndRow($col, $ligneentete)->getValue();
    //     fwrite($fentete,$value."\t");
    // }
    // fwrite($fentete,"\n");

    if(true) {
        if ($ligneentete == 0) {
            // entete non trouvée
            $msgResult .= "". $fileNameOrig. "\ten-tete non trouvée <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<";
            msglogErr($msgResult);
            fwrite($fdetail, $msgResult."\r\n");
        }else{
            // en-tête trouvée
            // recherche catégorie
            $colCat      = 0; // contiendra la colonne à utiliser pour la catégorie
            $nbColCat    = 0; 
            $firtsColCat = 0;
            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelCategorie, 
                    $nbColCat, $colCat);
            // $colcat contient la première col candidate
            msglog("=== recherhce catégorie === "."  ");
            msglog("colCat : ".$colCat."  ");
            msglog("nbColCat    : ".$nbColCat."  ");
            msglog("ligneentete : ".$ligneentete."  ");
            msglog("highestRow  : ".$highestRow."  ");
            msglog("highestColumnIndex : ".$highestColumnIndex."\n");

            $colCatLast = 0;
            $debut = 0;
            if ($nbColCat <> 1) {
                // cat non trouvée ou plusieurs candidates, parcours des dernières colonnes pour trouver
                if ($colCat<> 0) {
                    // on a trouvé au moins une colonne CAT, on commence à partir d'elle
                    $debut  = $firtsColCat;
                }else{
                    // on a PAS trouvé de colonne CAT, on commence à partir de la première sans titre'elle
                    $debut  = $lastHeaderString;
                }
                msglog("recherche cat à partir de la colonne debut :".$debut);
                // parccours des cellules de chaque ligne jusqu'à trouver une valeur de catégorie
                // on suppose alors que c'est la colonne catégorie 
                for($lig=$ligneentete+1; $lig<=$highestRow; $lig++){
                    for($col=$debut; $col<=$highestColumnIndex; $col++){
                        $value = rtvCellValue($worksheet, $col, $lig);
                        if (is_string($value)) {
                            if ( array_key_exists(strtoupper($value), $listCat)) {
                                // 
                                $colCatLast = $col;
                                msglog("colCatLast : ".$colCatLast."\n");
                                break;
                            }
                        }
                    }
                    if ($colCatLast <> 0) {
                        break;
                    }
                }
                if ( $colCatLast != 0) {
                    $colCat = $colCatLast;
                    msglog("catégorie en colonne : $colCatLast\n");
                }else{
                    $colCat = 0;
                    msglog("catégorie NON TROUVEE\n");
                }
            }

            // trt des lignes
            // fichier de sortie :
            // date et heure du fichier
            // nom du fichier
            // processeur
            // catégorie du PC
            // cat last col
            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelTypeDisk1, $nbColTypeDisk1, $firtsColTypeDisk1Found);
            msglog("firtsColTypeDisk1Found $firtsColTypeDisk1Found    ");
            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelSizeDisk1, $nbColSizeDisk1, $firtsColSizeDisk1Found);
            msglog("firtsColSizeDisk1Found $firtsColSizeDisk1Found\n");

            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelTypeDisk2, $nbColTypeDisk2, $firtsColTypeDisk2Found);
            msglog("firtsColTypeDisk2Found $firtsColTypeDisk2Found    ");
            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelSizeDisk2, $nbColSizeDisk2, $firtsColSizeDisk2Found);
            msglog("firtsColSizeDisk2Found $firtsColSizeDisk2Found\n");

            searchColInHeader($worksheet, $highestColumnIndex, $ligneentete, $labelRAM      , $nbColRAM,        $firtsColRAMFound);
            msglog("firtsColRAMFound      $firtsColRAMFound\n");
            $timeFile = substr($fileNameOrig,0,10);
            $dateFile = date("Y/m/d H:i:s",(int) $timeFile);
            $msgEntete  = "$dateFile\t\"$fileNameOrig\"\t\"$typeExcel\"\t\"$colProc\"\t\"$firtsColTypeDisk1Found\"\t\"$firtsColSizeDisk1Found";
            $msgEntete .= "\"\t\"$firtsColTypeDisk2Found\"\t\"$firtsColSizeDisk2Found\"\t\"$firtsColRAMFound\"\t\"$colCat\"";

            fwrite($fentete, $msgEntete."\r\n");
            $msgResult = "";
            for($lig=$ligneentete+1; $lig<=$highestRow; $lig++){
                $proc = rtvCellValue($worksheet, $colProc, $lig);
                if (is_string($proc)) {
                    if ($proc <> "" ) {
                        $msgResult = "$dateFile\t\"$fileNameOrig\"\t\"$typeExcel\"\t\"$lig\"\t\"$proc\"";
                        $cat  = rtvCellValue($worksheet, $colCat, $lig);
                        if (is_string($cat)) {
                            $cat = strtoupper($cat);
                        }


                        //$msgResult .= "$firtsColTypeDisk1Found\t$firtsColSizeDisk1Found\t$firtsColTypeDisk2Found\t$firtsColSizeDisk2Found\t$firtsColRAMFound\t";
                        //rtvCellValue($worksheet, $col, $lig);
                        $msgResult .= "\t\"".rtvCellValue($worksheet, $firtsColTypeDisk1Found, $lig)."\"";
                        $msgResult .= "\t\"".rtvCellValue($worksheet, $firtsColSizeDisk1Found, $lig)."\"";
                        $msgResult .= "\t\"".rtvCellValue($worksheet, $firtsColTypeDisk2Found, $lig)."\"";
                        $msgResult .= "\t\"".rtvCellValue($worksheet, $firtsColSizeDisk2Found, $lig)."\"";
                        $msgResult .= "\t\"".rtvCellValue($worksheet, $firtsColRAMFound, $lig)."\"";
                        $msgResult .= "\t\"".$cat."\"";
                        //$msgResult .= "$firtsColTypeDisk1Found\t$firtsColSizeDisk1Found\t$firtsColTypeDisk2Found\t$firtsColSizeDisk2Found\t$firtsColRAMFound\t$cat";
                        fwrite($fdetail, $msgResult."\r\n");
                    }
                }
            }
        }
    }
    fclose($fdetail);
    fclose($fentete);
    flush();
}

/**
 * recherche de la ligne en-tête 
 * @param worksheet la feuille
 * @param ligneentete n° de ligne recherchée
 * @param colProc n° de la première colonne contenant le processeur
 * @param nbColProc nbr de colonne pouvant contenuir le processeurs
 * @param lastHeaderStringIndex dernière colonne de la ligne en-tête contenant une string
 * @param highestRow dernière ligne du worksheet
 * @param highestColumnIndex dernière colonne du worksheet
 * 
*/
function searchHeaderLine(&$worksheet, &$ligneentete, &$colProc, &$nbColProc,&$lastHeaderStringIndex, $highestRowIndex, $highestColumnIndex ) {
    global $labelCPU;
    $ligneentete = 0;
    $colProc = 0;
    $nbColProc = 0;
    $lastHeaderStringIndex = 0;
    msglog(">>>>>>>> searchHeaderLine  ligneentete : ".$ligneentete."   colProc : ".$colProc);
    msglog("  highestRowIndex : ".$highestRowIndex."   highestColumnIndex : ".$highestColumnIndex."\n");
    for($ligne=1; $ligne<=$highestRowIndex; $ligne++){
        // recherche de la ligne en-tête par "processor"
        //msglog("ligne :" .$ligne);
        for($col=1; $col<=$highestColumnIndex; $col++){
            //msglog("  col :" .$col);
            $value = rtvCellValue($worksheet, $col, $ligne);
            //msglog("  val :" .$value. "\n");
            if (is_string($value)) {
                $lastHeaderStringIndex = $col;
                foreach ($labelCPU as $txtCPU) {
                    //msglog("      txtCPU : $txtCPU \n");
                    if ( $value == $txtCPU) {
                        //msglog("         contenu\n");
                        $ligneentete = $ligne;
                        if ($colProc == 0) {
                            $colProc = $col;
                        }
                        ++$nbColProc;
                        //msglog("  >>>>>>>> en-tête trouvée ligneentete : ".$ligneentete."  colProc : ".$colProc."\n");
                    }
                }
            }
        }
        if ($colProc <> 0) {
            break; // ligne en-tête trouvée
        }
    }
}

/** 
 *  recherche d'une colonne dans la ligne en-tête
 * @param worksheet la feuille
 * @param highestColumnIndex dernier n° de colonne
 * @param ligneentete n° ligne en-tête
 * @param arrayVal liste des valeurs à rechercher
 * @param nbColFound nbr de colonne contenant une de ces valeurs
 * @param firtsColFound première colonne contenant une des valeurs recherchées
 * @return self
*/
function searchColInHeader(&$worksheet, $highestColumnIndex, $ligneentete, $arrayVal, 
        &$nbColFound, &$firtsColFound) {
   msglog(">>>>>> searchColInHeader\n");
    $nbColFound    = 0;
    $firtsColFound = 0;

    for($col=1; $col<=$highestColumnIndex; $col++){
        $value = rtvCellValue($worksheet, $col, $ligneentete);
        msglog("value '$value'\n");
        if (is_string($value)) {
            foreach ($arrayVal as $searchVal) {
                msglog("  searchVal '$searchVal'\n");
                if ( $value == strtolower($searchVal) ) {
                    if ($firtsColFound == 0 ) {
                        $firtsColFound = $col;
                    }
                    ++$nbColFound;
                   msglog("    colFound : ".$firtsColFound."\n");
                }
            }
            
        }
    }
    msglog("searchColInHeader >>>>>> cherché : '".$arrayVal[0]."'    colFound : $firtsColFound      nbColFound : $nbColFound\n");
}

/**
 * lit une cellule et la convertie en minuscule si c'est une string
 */
function rtvCellValue($worksheet, $col, $ligne) {
    if ($col == 0) {
        $value = "";
    }else{
        $value = $worksheet->getCellByColumnAndRow($col, $ligne)->getValue();
        if (is_string($value)) {
            $value = strtolower(cleanString($value));
        }
    }
    return $value;
}
function msglog($message)
{
    //echo($message);
}
function msglogErr($message)
{
    global $fileNameErreur ;
    $ferreurs = fopen($fileNameErreur, "a");
    fwrite($ferreurs, $message);
    fclose($ferreurs);
    echo($message);
}

function cleanString(String $string) : String {
    $string = trim($string);
    $string = preg_replace( '/[\x00-\x1F\x7F]/u', ' ',$string);
    $string = preg_replace( '!\s+!', ' ',$string);
    return $string;
}
