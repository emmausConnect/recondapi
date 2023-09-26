<?php
declare(strict_types=1);

/**
 * Lecture de fichiers .ini
 */
class ParamIni
{
    /** contient un poste instance par fichier param.ini lu
     * @var array
     */
    private static array $paramCl = array();
    private string $paramFileName;
    private array $param = array();


    private function __construct() { }

    /** retourne une nouvelle instance de la classe
     * @return ParamIni
     */
    public static function getInstance(string $file): ParamIni
    {
        if ($file == '*paramphp.ini') {
            $file = $_SERVER["DOCUMENT_ROOT"].'/../private/config/paramphp.ini';
        }
        if ($file == '*paramphp.ini') {
            $file = $_SERVER["DOCUMENT_ROOT"].'/../private/config/paramphp.ini';
        }

        if ($file == '*paramconfidentiel.ini') {
            $file = $_SERVER["DOCUMENT_ROOT"].'/../confidentiel/paramconfidentiel.ini';
        }

        if (!array_key_exists($file, self::$paramCl)) {
            $dir = __DIR__;
            $c = new ParamIni();
            $c->paramFileName = $file;
            $f = file_get_contents($file);
            $c->param = parse_ini_string($f, true);
            if (array_key_exists("seuilsCPU", $c->param)) {
                // si le fichier contient des seuils, on les trie
                @ksort($c->param["seuilsCPU"], SORT_NUMERIC);
                @ksort($c->param["seuilsRAM"], SORT_NUMERIC);
                @ksort($c->param["seuilsSSD"], SORT_NUMERIC);
                @ksort($c->param["seuilsHDD"], SORT_NUMERIC);
                @ksort($c->param["seuilsCatPC"], SORT_NUMERIC);
            }
            self::$paramCl[$file] = $c;
        }
        return self::$paramCl[$file];
    }

    // **********************************************
    // *** gestion du fichier PARAM
    // **********************************************
    public function getParam()
    {
        return $this->param;
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
