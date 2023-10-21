<?php
declare(strict_types=1);
class Util01
{
    /**
     * Convertit en GB
     * @param [string] $text qt à convertir
     * @param [type] $dest unité destination
     * @param [type] $uniteParDefaut unité de $textpar défaut
     * @return string|integer
     *     message d'erreur ou taille convertie
     */
    public static function convertUnit($text, $dest, string $uniteParDefaut) : string|float
    {
        if(! is_numeric($text)) { 
            $text = preg_replace('/\s/', '', $text); // suppression des espaces
            $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text); // sup cara non imprimable
        }
        // le texte est de la forme
        // ddG, ddGo ..
        // extraire dd, puis regarder par quoi commence l'unité et la convertie en G
        if(is_numeric($text)) {
            // si'il n'y a pas d'unité, on prend celle par défaut
            $text .= $uniteParDefaut;
        }
        $unit = array(
            "O" => 1, "K" => 1000, "M" => 1000000, "G" => 1000000000, "T" => 1000000000000
        );
        // seul Giga et Tera sont acceptés
        //preg_match('/^(\d*)(O|K|M|G|T)/i', $text, $tailleArray);

        preg_match('/^(\d+)([G|T])+[a-z]*$/iU', $text, $tailleArray);
        if (count($tailleArray) != 3 or !ctype_digit($tailleArray[1])) {
            $retour = 'convertUnit : "'.$text.'"erreur de syntaxe, mettre un nombre suivi de "GB", "GO" ou "G" ou "T" ... Exemple  "4GB", "512GO", "2T"';
        } else {
            $tailleOctets = $tailleArray[1] * $unit[strtoupper($tailleArray[2])];
            $retour = $tailleOctets / $unit[strtoupper($dest)];
        }
        return $retour;
    }


    /** 
     * return string to href the defaul getDefaultPage
     * 
     * @return 
     * 
     */
    public static function getDefaultPage()
    {
        // HTTP_HOST: "localhost:8080"
        // REQUEST_URI: "/public/excalculexcel.php"
        // QUERY_STRING: "a=b&debug=1"

        // REQUEST_URI: "/"
        // REQUEST_URI: "/excalculexcel.php"

        // (.*)/(.*)
        //
        // "/public/2/excalculexcel.php"    désiré : /public/2
        // array(
        // 	0=>/public/2/excalculexcel.php
        // 	1=>/public/2
        // 	2=>excalculexcel.php
        //
        // "/excalculexcel.php"             désiré : ""
        // array(
        // 	0=>/excalculexcel.php
        // 	1=>
        // 	2=>excalculexcel.php
        //
        // "/"                              désiré : ""
        // array(
        // 	0=>/
        // 	1=>
        // 	2=>
        $req = [];
        preg_match('/(.*)\/(.*)/', $_SERVER['REQUEST_URI'], $req);
        return $req[1];
    }

    /**
     * Construction q'une queryString : /!\ ne gère pas les paramètre en tableau du genre ?"cpu[]=1&cpu[]=2
     * 
     * @$qStringArray tableau associatif comme donné par $_GET
     * @$toExclude  : tableau de clefs à ne pas reprendre : ["toto", "tata", ...]
     * @$toInclude  : table 
     *                 "dft" => ["dfttrt" => $dfttrt]
     *                          $dfttrt = "ADD": on ajoute la clef avec sa valeur
     *                                    "IGNORE" : on l'ignore
     *                    si "dft" n'existe pas ou "dfttrt" n'existe pas ou sa veleur est <> add => on ignore
     * 
     *                 "data" => [ $key=>["trt"=> $trt, "dft"=> $dft], ... ]
     *                             $key=>["trt"=> $trt, "dft"=> $dft] à reprendre
     *                                  les valeurs par défaut sont : "trt"=> "use", "dft"=> ""
     *                                si trt = "USE" dft  :  alors la clef est ajoutée si elle existe
     *                                      avec sa valeur existant dans $qStringArray
     *                                si trt = "REPLACE"  :  alors la clef est ajoutée si elle existe
     *                                      avec la valeur $dft
     *                                si trt = "FORCE"    :  alors la clef est ajouté même si elle n'existe pas
     *                                      avec la valeur "$dft
     *               dft : ["dft" => ["dfttrt" => "ignore"]
     * @encodeVal   : si true => urlencode
     * 
     * @retour : chaîne avec la syntaxe "&....."; donc pas de "?" au début
     */
    public static function buildQueryStringAsArray($qStringArray, $toExclude = [], $toInclude = [], $encodeVal = true)
    {
        $toInclude = $toInclude + ["dft" => ["dfttrt" => "IGNORE"]]; // ajout val par défaut de "dft"
        $toInclude["dft"]["dfttrt"] = strtoupper($toInclude["dft"]["dfttrt"]); // en majuscule
        $toInclude = $toInclude + ["data" => []]; // ajout val par défaut de "data"

        $retour = "";
        $qStringArrayW = $qStringArray;
        // on supprime les clefs à ne pas reprendre 
        foreach ($toExclude as $key) {
            unset($qStringArrayW[$key]);
        }
        // on change les valeurs par celle par défaut si c'est "REPLACE"
        foreach ($qStringArrayW as $key => $val) {
            if (array_key_exists($key, $toInclude["data"])) {
                $todoDft = ["trt" => "USE", "dft" => ""];
                $todo = $toInclude["data"][$key] + $todoDft; // ajoute les valeurs non définies pour cette clef
                $trtTodo = strtoupper($toInclude["data"][$key]["trt"]);
                switch ($trtTodo) {
                        // case "USE":
                        // 	$keyRetour = $key;
                        // 	$valRetour = $val;
                        // 	break;
                    case "REPLACE":
                        $qStringArrayW[$key] = $toInclude["data"][$key]["dft"];
                        break;
                }
            }
        }
        // on ajoute les clefs manquantes
        foreach ($toInclude["data"] as $key => $val) {
            $toInclude["data"][$key] = $toInclude["data"][$key] + ["val" => "", "trt" => "USE", "dft" => ""];
            if (strtoupper($toInclude["data"][$key]["trt"]) == "FORCE") {
                $qStringArrayW[$key] = $toInclude["data"][$key]["dft"];
            }
        }

        return $qStringArrayW;
    }

    public static function buildQueryStringAsString($qStringArray, $toExclude = [], $toInclude = [], $encodeVal = true)
    {
        $qStringArrayW = self::buildQueryStringAsArray($qStringArray, $toExclude, $toInclude, $encodeVal);
        $retour = "";
        foreach ($qStringArrayW as $key => $val) {
            $retour .= '&' . $key . "=";
            $retour .= ($encodeVal) ? urlencode($val) : $val;
        }
        return $retour;
    }

    /**
     * Convert Excel style column names into numbers.
     * @param string $column Excel style column name like AA or CF
     * @return integer Number with A being 1 and AA being 27
     */
    static function alpha2num($column)
    {
        $number = 0;
        foreach (str_split($column) as $letter) {
            $number = ($number * 26) + (ord(strtolower($letter)) - 96);
        }
        return $number;
    }

    // hex_dump : utiliser pour débuguer
    static function hex_dump($data, $newline = "\n")
    {
        static $from = '';
        static $to = '';
        static $width = 16; # number of bytes per line
        static $pad = '.'; # padding for non-visible characters

        if ($from === '') {
            for ($i = 0; $i <= 0xFF; $i++) {
                $from .= chr($i);
                $to   .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex   = str_split(bin2hex($data), $width * 2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line) {
            echo sprintf('%6X', $offset) . ' : ' . implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }

    /**
     * remplace les caractère non imprimable par un espace
     * et remplace les espaces multiples par un seul espace
     * et supprime les espaces en début et fin de chaîne
     * @param String $string
     * @return String
     */
    static function cleanString(String $string) : String {
        $string = trim($string);
        $string = preg_replace( '![^[:print:]]!', ' ',$string);
        $string = preg_replace( '!\s+!', ' ',$string);
        return $string;
    }

    function defaultValAsString($var) {
        $retour=$var;
        if (!isset($var)) {
            $retour="isNotSetOrNull";
        }
        return $retour;
    }

    /**
     * returns the value of var_dump instead of outputting it
     */
    static function var_dump_ret($mixed = null) {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
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
