<?php

// cd D:\users\Mick5\Desktop\temp\upload\dest_r
// D:\xampp-windows-x64-8.1.4-1-VS16\php\php.exe D:\Users\Mick5\Documents\GitHub\EC-recondapi.git\private\class\trtcsv.php

declare(strict_types=1);

require_once __DIR__.'/pc.class.php';
require_once __DIR__.'/evaluationpc.class.php';
require_once __DIR__.'/paramini.class.php';
/*
 0 : date du fichier
 1 : nom du fichier
 2 : type d'excel : "N" normal, "B" extract Bolc
 3 : n° ligne dans l'excel
 4 : cpu
 5 : col type disk 1
 6 : col taille disk 1
 7 : col type disk 2
 8 : col taille disk 2
 9 : col RAM
 10 : col cat
*/

$fileNameInput    = "recapexcel_detail.txt";
$fileNameOutput   = "trtcsv_detail_calculé.txt";
$fileNameErreurs2 = "trtcsv_erreur.txt";

file_put_contents($fileNameOutput, "");
file_put_contents($fileNameErreurs2, "");

$finput    = fopen($fileNameInput,  "r");
$lignetrt = 0;

while ($ligne = fgets($finput)) {
    ++$lignetrt;
    $foutput   = fopen($fileNameOutput, "a");
    $ferreurs2 = fopen($fileNameErreurs2, "a");
    echo "$lignetrt   $ligne";
    // str_getcsv(
    //     string $string,
    //     string $separator = ",",
    //     string $enclosure = "\"",
    //     string $escape = "\\"
    // )
    $parse = str_getcsv(
        $ligne,
        "\t",
        "\"",
        "\\"
    );
    // for ($i=0; $i<count($parse); ++$i) {
    //      echo $i." : ".$parse[$i]."\n";
    // }
    $cePC = PC::getInstance();
    $cePC->setUniteParDefaut('HDD');
    $cePC->setTypeDiskParDefaut('GB');
    $cePC->setCpuTextInputArray([$parse[4]]);
    $cePC->setDisk(1,$parse[6],$parse[5]);
    $cePC->setDisk(2,$parse[8],$parse[7]);
    $cePC->setTailleRam($parse[9]);
    $evaluationPcClInstance = EvaluationPc::getInstance($cePC);
    $evaluationPcCl = $evaluationPcClInstance->getEvalPc();
    $categoriePC    = $evaluationPcCl->getCategoriePC();
    $l = str_replace("\r","",$ligne);
    $l = str_replace("\n","",$l);
    $output = "$l\t\"$categoriePC\"\t\"\""; // une colonne vide pour la comparaison

    $evaluationPcClasArray=$evaluationPcCl->convertToArray();
    $output .="\t\"".$evaluationPcClasArray["cpuTextInput"]."\""; // L
    $output .="\t\"".$evaluationPcClasArray["cputextnorm"]."\"";  // M
    $output .="\t\"".$evaluationPcClasArray["indiceCPU"]."\"";    // N
    $output .="\t\"".$evaluationPcClasArray["origine"]."\"";      // O
    $output .="\t\"".$evaluationPcClasArray["categorieCPU"]."\""; // P
    $output .="\t\"".$evaluationPcClasArray["tailleDisk01"]."\""; // Q
    $output .="\t\"".$evaluationPcClasArray["typeDisk01"]."\"";   // R
    $output .="\t\"".$evaluationPcClasArray["categorieDisk01"]."\""; // S
    $output .="\t\"".$evaluationPcClasArray["tailleDisk02"]."\""; // T
    $output .="\t\"".$evaluationPcClasArray["typeDisk02"]."\"";   // U
    $output .="\t\"".$evaluationPcClasArray["categorieDisk02"]."\""; // V
    $output .="\t\"".$evaluationPcClasArray["categorieDisk"]."\"";   // W
    $output .="\t\"".$evaluationPcClasArray["tailleRam"]."\"";       // X
    $output .="\t\"".$evaluationPcClasArray["categorieRam"]."\"";    // Y
    $output .="\t\"".$evaluationPcClasArray["categorieTotal"]."\"";  // Z
    $output .="\t\"".$evaluationPcClasArray["categoriePCcodeNormal"]."\""; // AA
    $output .="\t\"".$evaluationPcClasArray["categoriePCnormale"]."\"";    // AB
    $output .="\t\"".$evaluationPcClasArray["categoriePCcodeMaxi"]."\"";   // AC
    $output .="\t\"".$evaluationPcClasArray["categoriePCcode"]."\"";       // AD
    $output .="\t\"".$evaluationPcClasArray["categoriePCCorrigée"]."\"";   // AE

// echo "parse[9] $parse[9]\n";
// echo 'evaluationPcClasArray["categoriePCCorrigée"]'."  ". $evaluationPcClasArray["categoriePCCorrigée"]."\n";
// exit;
    fwrite($foutput,$output."\t\"$parse[10]\"\r\n");
    if (strtoupper($parse[10]) != strtoupper($evaluationPcClasArray["categoriePCCorrigée"])) {
        fwrite($ferreurs2,$output."\r\n");
    }
    fclose($foutput);
    fclose($ferreurs2);
}
fclose($finput);

exit();