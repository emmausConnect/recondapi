<?php
declare(strict_types=1);

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class.'/paramini.class.php';

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

/**
 * retourne l'indice
 *
 * @param [type] $arr liste des plages 
 * @param [type] $value valeur à évaluer
 * @return void
 */

 function searchIndice($arr, $value): int {
    $retour = -9999;
    foreach ($arr as $key => $val) {
        if ($key <= $value) {
            $retour = (int) $val;
        }else{
            break;
        }
    }
    return $retour;
}

function getPlagesAsTable() {
    $paramArray = ParamIni::getInstance(__DIR__.'/../../config/param.ini')->getParam();
    $ramPlagesA            = $paramArray['smram'];
    $stockagePlagesA       = $paramArray['smstockage'];
    $indicePlagesA         = $paramArray['smindice'];
    $categoriePlagesA      = $paramArray['smcategorie'];
    $categorieAlphaPlagesA = $paramArray['smcategoriealpha'];
    $ramPlages = [];
    forEach($ramPlagesA as $key=>$val) {array_push($ramPlages, [$key, $val]);}
    $stockagePlages = [];
    forEach($stockagePlagesA as $key=>$val) {array_push($stockagePlages, [$key, $val]);}
    $indicePlages =[];
    forEach($indicePlagesA as $key=>$val) {array_push($indicePlages, [$key, $val]);}
    $categoriePlages = [];
    forEach($categoriePlagesA as $key=>$val) {array_push($categoriePlages, [$key, $val]);}
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
            $html .= '<td style="text-align: right;">'.$categoriePlages[$l][0].'</td><td style="text-align: right;">'.$categorieAlphaPlagesA[$categoriePlages[$l][1]].'</td>';
        }else{
            $html .= $ligneVide;
        }
        $html .= '</tr>';
    }
    $html .= '<tbody></table>';
    return $html;
}

/**
 * Retourne les textes <option> des  statut
 *
 * @param string $dftKey : le code à préselectionner
 * @return string
 */
function getStatutSelect($dftKey = ""): string {
    global  $paramArray;
    if ($dftKey == "") {$dftKey = "0";}
    $retour = "";
    forEach($paramArray['smselectstatut'] as $key => $val) {
        // <option value="DEEE">DEEE</option>
        $retour .= '<option value="'.htmlentities($key."").'" ';
        if ($key == $dftKey) {
            $retour .= 'selected';
        }
        $retour .= '>'.htmlentities($val).'</option>';
    }
    return $retour;
}

/**
 * Retourne les textes <option> des codes podération
 *
 * @param string $dftKey
 * @return string
 */
function getPonderationSelect($dftKey = ""): string {
    global  $paramArray;
    //if ($dftKey == "") { $dftKey = "0";}
    $retour = "";
    forEach($paramArray['smselectponderation'] as $key => $val) {
        // <option value="DEEE">DEEE</option>
        $retour .= '<option value="'.htmlentities($key."").'" ';
        if ($dftKey != "") {
            if ($key == $dftKey) {
                $retour .= 'selected';
            }
        }else{
            if ($val == 0) {
                $retour .= 'selected';
            }
        }
        $retour .= '>'.htmlentities($val).'</option>';
    }
    return $retour;
}

function getStatutText(string $statutKey): string | null {
    global  $paramArray;
    $retour = null;
    if (array_key_exists($statutKey, $paramArray['smselectstatut'])) {
        $retour = $paramArray['smselectstatut'][$statutKey];
    }
    return $retour;
}

function getPonderationValue(string $ponderationKey): int | null {
    global  $paramArray;
    $retour = null;
    if (array_key_exists($ponderationKey, $paramArray['smselectponderation'])) {
        $retour = (int)$paramArray['smselectponderation'][$ponderationKey];

    }
    return $retour;
}