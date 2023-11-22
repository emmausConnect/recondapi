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
    private array $param = array(); // tableau 2 dim du fichier ini


    private function __construct() { }

    /** retourne une nouvelle instance de la classe
     * @return ParamIni
     */
    public static function getInstance(string $file): ParamIni
    {
        if ($file == '*param.ini') {
            $file = $_SERVER["DOCUMENT_ROOT"].'/../private/config/param.ini';
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

    /**
     * retourne le contenu du fichier ini sous fprme de tableau 2 dim
     *
     * @return array
     */
    public function getParam() : array
    {
        return $this->param;
    }

    /**
     * retour la valeur associée ou null si non trouvé 
     *
     * @param string $titre : titre
     * @param string $paramName : paramètre
     * @return void
     */
    public function getValue(string $titre, string $paramName) : null | string
    {
        $param = $this->getParam();
        $retour = null;
        if (array_key_exists($titre, $param)) {
            if (array_key_exists($paramName, $param[$titre])) {
                $retour = $param[$titre][$paramName];
            }
        }
        return $retour;
    }
    /**
     * retourne le paramname ou null si non trouvé
     *
     * ex : 
     *     $ctx = Contexte::getInstance();
     *     $c = $ctx->getParamIniCls();
     *     $b = $c->getParamName('seuilsCPU','1');
     * 
     * @param string $titre
     * @param string $value
     * @param boolean $caseSensitive
     * @return void
     */
    public function getParamName(string $titre, string $value, $caseSensitive = false) : null | string | int
    {
        $param = $this->getParam();
        $retour = null;
        if (! $caseSensitive) {
            $value = strtolower($value);
        }
        if (array_key_exists($titre, $param)) {
            foreach($param[$titre] as $paramName => $val) {
                if (! $caseSensitive) {
                    $val = strtolower($val);
                }
                if ($val == $value) {
                    $retour = $paramName;
                    break;
                }
            }
        }
        return $retour;
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
