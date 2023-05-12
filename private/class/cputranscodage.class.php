<?php
declare(strict_types=1);
class CpuTranscodage {
	/**
	 * contient 2 postes :
	 * "description"
	 * "data"
	 *  * "data" est un tableau associatif :
	 *  * "nom cpu" : "nom normalisé"
	 * */
	private static $dataJson = NULL;
	
	public static function getTransCpu($cpuName) {
		if (is_null(static::$dataJson)) {
			static::$dataJson = static::getDataJson();

		}
		$transName = NULL;
		if (array_key_exists("data", static::$dataJson)) {
			if (array_key_exists($cpuName, static::$dataJson["data"])) {
				$transName = static::$dataJson["data"][$cpuName];
			}
		}
		return $transName;
	}

	private static function getDataJson() {
		$data = file_get_contents(__DIR__."/cputranscodagedata.json");
		return json_decode($data, true);		
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
?>