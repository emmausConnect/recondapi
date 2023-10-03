<?php
declare(strict_types=1);

require_once 'utilsm.php';

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/contexte.class.php';
require_once $path_private_class .'/db/dbmanagement.class.php';

$path_libraries = $g_contexte_instance->getPath('libraries');
require_once $path_libraries. '/spreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**  Define a Read Filter class implementing \PhpOffice\PhpSpreadsheet\Reader\IReadFilter  
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-files/#loading-a-spreadsheet-file
*/
class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow   = 0;
    private $columns  = [];

    /**  Get the list of rows and columns to read  */
    public function __construct($startRow, $endRow, $columns) {
        $this->startRow = $startRow;
        $this->endRow   = $endRow;
        $this->columns  = $columns;
    }

    public function readCell($columnAddress, $row, $worksheetName = '') {
        //  Only read the rows and columns that were configured
        if ($row >= $this->startRow && $row <= $this->endRow) {
            if (in_array($columnAddress,$this->columns)) {
                return true;
            }
        }
        return false;
    }
}
$filterSubset = new MyReadFilter(2, 5000, range('A','G'));
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly('data');
$reader->setReadFilter($filterSubset);


$uploaDir  = __DIR__."/../../../public/upload/";
$excelFileName = "export kilmovil.xlsx";
$truncate = false;
if (array_key_exists('truncate',$_GET)) {
    if ($_GET['truncate'] === 'Y') {
        $truncate = true;
    }
}


try {
    $nbLignesInserted = trtExcel($uploaDir, $excelFileName, $reader, $truncate);
    echo "chargement terminé, $nbLignesInserted lignes ajoutées ou mises à jour";
}catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}

function trtExcel(String $uploaDir, String $fileNameOrig, $reader, $truncate) {
    $dbInstance = DbManagement::getInstance();
    $db = $dbInstance->openDb();
    $tableName = $dbInstance->tableName('smartphones');
    if ($truncate) {
        $sqlQuerytruncate = "TRUNCATE $tableName;";
        $truncateRecipe = $db->prepare($sqlQuerytruncate);
        $truncateRecipe->execute();
        echo "<br> table $tableName effacée";
    }


    $spreadsheet  = $reader->load($uploaDir.$fileNameOrig);
    //read excel data and store it into an array
    //$spreadsheet->setActiveSheetIndex(0);

    // NULL,        // Value that should be returned for empty cells
    // TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
    // TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
    // TRUE         // Should the array be indexed by cell row and cell column
    // $xls_data = $spreadsheet->getActiveSheet()->toArray(null, false, false, true);
    $worksheet          = $spreadsheet->getActiveSheet();
    $highestRowIndex    = $worksheet->getHighestRow(); // e.g. 10
    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    echo("<br>    ".$fileNameOrig." highestRowIndex : ".$highestRowIndex."   highestColumnIndex : ".$highestColumnIndex."\n");
    
    $sqlQueryInsert = "INSERT INTO $tableName( marque, modele, ram, stockage, indice, os, url, 
        crtorigine, crtby, crtdate, crttype ) 
        VALUES (:marque, :modele, :ram, :stockage, :indice, :os, :url, :origine, :crtby, :crtdate, :crttype)
        ON DUPLICATE KEY UPDATE indice =:indice, os=:os, url=:url, 
            updorigine =:origine, upddate=:crtdate, updby=:crtby, updtype=:crttype
        
        ;";

    $insertRecipe = $db->prepare($sqlQueryInsert);
    $mysqltime = date ('Y-m-d H:i:s');
    $nbLignesInserted = 0;

    for($ligne=2; $ligne<=$highestRowIndex; $ligne++){
        $title    = formatKey($worksheet->getCellByColumnAndRow(1, $ligne)->getValue(), true);
        $marque   = strtok($title.' ', ' ');
        $modeles  = formatKey($worksheet->getCellByColumnAndRow(2, $ligne)->getValue(), true);

        $ram      = formatKey($worksheet->getCellByColumnAndRow(3, $ligne)->getValue(), true);
        $stockage = formatKey($worksheet->getCellByColumnAndRow(4, $ligne)->getValue(), true);
        $indice   = formatKey($worksheet->getCellByColumnAndRow(5, $ligne)->getValue(), true);
        $os       = formatKey($worksheet->getCellByColumnAndRow(6, $ligne)->getValue(), true);
        $url      = formatKey($worksheet->getCellByColumnAndRow(7, $ligne)->getValue(), true);
        //echo "<br><b>[$ligne][$title][$marque][$modeles][$ram][$stockage]</b>";
        if ($title != "" && $title != null) {
            $modelesArray = [];
            if ($modeles != "" && $modeles != null) {
                $modelesArray = explode(",", $modeles);
            }
            $modeleTitle = str_replace($marque, '', $title);
            $modeleTitle = trim($modeleTitle . ' ');
            array_push($modelesArray, $modeleTitle);
            foreach($modelesArray as $modele) {
                $modele = trim($modele);
                $exp = "/$marque(.*)/";
                $modele = trim(preg_replace($exp, '$1', $modele));
                //echo "<br>[$ligne][$title][$marque][$modele][$ram][$stockage]";
                //'title'    => $marque .' '.trim($modele." ") ,

                $insertRecipe->execute([
                    'marque'   => $marque,
                    'modele'   => trim($modele." "),
                    'ram'      => $ram,
                    'stockage' => $stockage,
                    'indice'   => $indice,
                    'os'       => $os,
                    'url'      => $url,
                    'origine'  => $fileNameOrig,
                    'crtby'    => basename(__FILE__),
                    'crtdate'  => $mysqltime,
                    'crttype'  => 'excel'
                ]);
                ++$nbLignesInserted;
            }
        }

    }
    return $nbLignesInserted;
}

// function mb_basename($path) {
//     if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
//         return $matches[1];
//     } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
//         return $matches[1];
//     }
//     return '';
// }