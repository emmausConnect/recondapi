<?php
declare(strict_types=1);

/**
 * gère le fichier cache dans lequel on a mémorisé
 * les indices des CPU
 * cela évite des accès au service web
 */
class CpuIndiceFileCache {
	private static $indiceCpuJson = "";
	
	public static function getIndice(string $cpuName) : ?IndiceCPU {
		//$logger = LoggerRec::getInstance();
		if (static::$indiceCpuJson == "") {
			static::$indiceCpuJson = static::getIndiceCpuJson();
		}
		$indiceCl = NULL;
		if (array_key_exists("data", static::$indiceCpuJson )) {
			if (array_key_exists($cpuName, static::$indiceCpuJson["data"])) {
				$indice   = static::$indiceCpuJson["data"][$cpuName]["indice"];
				$dateMaj  = static::$indiceCpuJson["data"][$cpuName]["date"];
				$indiceCl = IndiceCPU::getInstance();
				//$indiceCl->setStatus('OK');
				$indiceCl->setIndice($indice);
				$indiceCl->setDateMaj($dateMaj);
				$indiceCl->setOrigine('fileCache');
			}
		}
		return $indiceCl;
	}

	private static function getIndiceCpuJson() : array {
		$data = file_get_contents(__DIR__."/../data/cpuindicecachedata.json");
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