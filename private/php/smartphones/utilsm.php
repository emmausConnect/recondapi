<?php
declare(strict_types=1);


function getPostValue(string $htmlName, null|string $default = Null): null|string {
    $retour = "";
    if (array_key_exists($htmlName, $_POST)) {
        $retour = $_POST[$htmlName];
    } else{
        if ($default != null) {
            $retour = $default;
        }else{
            $retour = null;
        }
    }
    return $retour;
}

function getGetValue(string $htmlName, null|string $default = Null): null|string {
    $retour = "";
    if (array_key_exists($htmlName, $_GET)) {
        $retour = $_GET[$htmlName];
    } else{
        if ($default != null) {
            $retour = $default;
        }else{
            $retour = null;
        }
    }
    return $retour;
}

function cvtTextToCsv(string $text, $sep='"'): string {
    $text = str_replace($sep, $sep.$sep, $text );
    $text = htmlentities($text);
    $retour = '"'.$text.'"';
    return $retour;
}

function cvtToHtmlentities(null|string $text): string {
    $retour = "";
    if ($text != Null) {
        $retour = htmlentities($text);
    }
    return $retour;
}

/**
 * fait un trim et enlève les multspace si demandé
 *
 * @param [type] $text
 * @param boolean $supSpaces
 * @return void
 */
function formatKey($text, bool $supSpaces): string {
    $retour = "";
    $retour = trim($text ." ");
    if ($supSpaces) {
        $retour = preg_replace('/\s+/', ' ', $retour);
    }
    return $retour;
}

function getSmPlages() {
    $ramPlages = [];
    array_push($ramPlages , [1 , 30]);
    array_push($ramPlages , [2 , 40]);
    array_push($ramPlages , [3 , 54]);
    array_push($ramPlages , [4 , 73]);
    array_push($ramPlages , [6 , 99]);
    array_push($ramPlages , [8 , 133]);
    array_push($ramPlages , [12 , 180]);
    array_push($ramPlages , [16 , 243]);

    $stockagePlages = [];
    array_push($stockagePlages , [0, -9999]);
    array_push($stockagePlages , [16, 31]);
    array_push($stockagePlages , [32, 45]);
    array_push($stockagePlages , [64, 66]);
    array_push($stockagePlages , [128, 96]);
    array_push($stockagePlages , [256, 141]);
    array_push($stockagePlages , [512, 207]);
    array_push($stockagePlages , [1000, 304]);

    $indicePlages =[];
    array_push($indicePlages , [     0 , 40]);
    array_push($indicePlages , [ 50000 , 44]);
    array_push($indicePlages , [100000 , 49]);
    array_push($indicePlages , [150000 , 54]);
    array_push($indicePlages , [200000 , 60]);
    array_push($indicePlages , [250000 , 67]);
    array_push($indicePlages , [300000 , 74]);
    array_push($indicePlages , [350000 , 82]);
    array_push($indicePlages , [400000 , 91]);
    array_push($indicePlages , [450000 , 101]);
    array_push($indicePlages , [500000 , 112]);
    array_push($indicePlages , [550000 , 125]);
    array_push($indicePlages , [600000 , 138]);
    array_push($indicePlages , [650000 , 153]);
    array_push($indicePlages , [700000 , 170]);
    array_push($indicePlages , [750000 , 189]);
    array_push($indicePlages , [800000 , 209]);
    array_push($indicePlages , [850000 , 232]);
    array_push($indicePlages , [900000 , 257]);
    array_push($indicePlages , [950000 , 286]);

    $categoriePlages = [];
    array_push($categoriePlages , [0 , 1]);
    array_push($categoriePlages , [90 , 2]);
    array_push($categoriePlages , [165 , 3]);
    array_push($categoriePlages , [255 , 4]);
    array_push($categoriePlages , [375 , 5]);

    return [$ramPlages, $stockagePlages, $indicePlages, $categoriePlages];
}

function calculCategorie($ram, $stockage, $indice) {
    $plages = getSmPlages();
    $ramPlages = $plages[0];
    $stockagePlages = $plages[1];
    $indicePlages =$plages[2];
    $categoriePlages = $plages[3];

    $noteRam      = searchIndice($ramPlages, $ram);
    $noteStockage = searchIndice($stockagePlages, $stockage);
    $noteIndice   = searchIndice($indicePlages, $indice);
    $noteTotale   = $noteRam + $noteStockage + $noteIndice;
    $categorie    = searchIndice($categoriePlages, $noteTotale);
    return [$noteRam, $noteStockage, $noteIndice, $noteTotale, $categorie];
}

function searchIndice($arr, $value) {
    $retour = null;
    $nbPostes = count($arr);
    for ($i = 1; $i < $nbPostes-1; $i++) {
        if ($arr[$i][0] > $value) {
            $retour = $arr[$i-1][1];
            break;
        }
    }
    if ($retour === null) {
        $retour = $arr[$nbPostes -1][1];
    }
    return $retour;
}

function getPlagesAsTable() {
    $plages = getSmPlages();
    $ramPlages = $plages[0];
    $stockagePlages = $plages[1];
    $indicePlages =$plages[2];
    $categoriePlages = $plages[3];
    $ligneVide ='<td>&nbsp;</td><td>&nbsp;</td>';
    $nbLig = max (count($ramPlages), count($stockagePlages), count($indicePlages), count($categoriePlages));
    $html  = '<table  style=" margin: 0 auto;"><thead><tr>';
    $html .= '<th>Ram</th><th>&nbsp;</th>';
    $html .= '<th>Stockage</th><th>&nbsp;</th>';
    $html .= '<th>Indice</th><th>&nbsp;</th>';
    $html .= '<th>Catégorie</th><th>&nbsp;</th>';
    $html .= '</thead><tbody>';
    for ($l = 0; $l < $nbLig; ++$l) {
        $html .= '<tr>';
        if ($l < count($ramPlages)) {
            $html .= '<td style="text-align: right;">'.$ramPlages[$l][0].'</td><td style="text-align: right;">'.$ramPlages[$l][1].'</td>';
        }else{
            $html .= $ligneVide;
        }

        if ($l < count($stockagePlages)) {
            $html .= '<td style="text-align: right;">'.$stockagePlages[$l][0].'</td><td style="text-align: right;">'.$stockagePlages[$l][1].'</td>';
        }else{
            $html .= $ligneVide;
        }

        if ($l < count($indicePlages)) {
            $html .= '<td style="text-align: right;">'.$indicePlages[$l][0].'</td><td style="text-align: right;">'.$indicePlages[$l][1].'</td>';
        }else{
            $html .= $ligneVide;
        }

        if ($l < count($categoriePlages)) {
            $html .= '<td style="text-align: right;">'.$categoriePlages[$l][0].'</td><td style="text-align: right;">'.$categoriePlages[$l][1].'</td>';
        }else{
            $html .= $ligneVide;
        }
        $html .= '</tr>';
    }
    $html .= '<tbody></table>';
    return $html;
}