<?php

declare(strict_types=1);
/** stocke en tableau les classes IndiceCPU déjà trouvés */
require_once __DIR__.'/loggerrec.class.php';


class CpuIndiceRamCache
{
	private  $cpuCache = array();
	private static $instance = null;
	private LoggerRec  $logger;

	private function __construct()
	{
	}

	public static function getInstance(): CpuIndiceRamCache
	{
		if (self::$instance == null) {
			$c = new CpuIndiceRamCache();
			$c->logger = LoggerRec::getInstance();
			self::$instance = $c;
		}
		return self::$instance;
	}

	/** Ajoute l'indice d'un CPU dans le cache RAM
	 * 
	 * @param string     $cpuName
	 * @param IndiceCPU  $indice
	 */
	public  function addCpuIndice(string $cpuName, ?IndiceCPU $indice)
	{
		$this->logger->addLogDebugLine(">>>> CpuIndiceRamCache addCpuIndice $cpuName");
		$this->logger->addLogDebugLine($indice, "indice");
		$this->cpuCache[$cpuName] = $indice;
		$origine = $this->cpuCache[$cpuName]->getOrigine();
		// $stringCache = " CacheRam";
		// if (strpos($origine, $stringCache) === false) {
		// 	$this->cpuCache[$cpuName]->setOrigine($origine .$stringCache);
		// }
	}

	/** retourne la classe IndiceCPU si elle a été méorisée, sinon null
	 * @param  string $cpuName
	 * @return ?IndiceCPU
	 */
	public function getCpuIndice(string $cpuName): ?IndiceCPU
	{
		$this->logger->addLogDebugLine(">>>> CpuIndiceRamCache getCpuIndice $cpuName)");
		$retour = null;
		if (array_key_exists($cpuName, $this->cpuCache)) {
			$retour = $this->cpuCache[$cpuName];
		}
		$this->logger->addLogDebugLine("<<<<< CpuIndiceRamCache getCpuIndice )");
		$this->logger->addLogDebugLine($retour, "<<<<< CpuIndiceRamCache getCpuIndice )");
		return $retour;
	}
	
	function __toString() : string {
		$retour = "";
		foreach ($this->cpuCache as $key => $val) {
			$retour .= "[$key]".$val."\n"; 
		} 
		return $retour;
	}
	
	//******************************************************************* */
	function __call($name, $arguments)
	{
		throw new Exception("Appel de la méthode non statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
	}

	static function __callStatic($name, $arguments)
	{
		throw new Exception("Appel de la méthode statique inconnue : $name, param : " . implode(', ', $arguments) . "\n");
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
