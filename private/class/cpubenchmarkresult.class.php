<?php
declare(strict_types=1);
require_once __DIR__.'/evaluationerrors.class.php';
require_once __DIR__.'/evaluationerror.class.php';
require_once __DIR__.'/indicecpu.class.php';

/**
 * resultat de la recherche sur le web de l'indce CPU
 */
class CpuBenchmarkResult{
    private string $status = "";
    private EvaluationErrors $evaluationErrorsCl;
    private ?IndiceCPU $indiceCl = null;
	private ?string    $webpage = null;
	private ?string    $url = null;
	private ?string    $cpuTextNorm = null;
	//private ?string    $cpuWebName  = null;
 
	private function __construct() {
	}
	
	/** retourne une nouvelle instance de la classe
	 * @return CpuBenchmarkResult
	 */
	public static function getInstance() : CpuBenchmarkResult{
	  $c = new CpuBenchmarkResult();
	  $c->setEvaluationErrorsCl(EvaluationErrors::getInstance());

	  return $c;
	}

	/* ===== Getters et Setters =================================================== */
	/** Get the value of status
	 * @return  string
	 */
	public function getStatus(): string{
		return $this->status;
	}
	/** Set the value of status
	 * @param   string  $status  
	 * @return  self
	 */
	public function setStatus(string $status) : self {
		$this->status = $status;
		return $this;
	}

	/** Get the value of errors
	 * @return EvaluationErrors
	 */
	public function getEvaluationErrorsCl(): EvaluationErrors {
		return $this->evaluationErrorsCl;
	}
	/**
	 * @param EvaluationErrors $evaluationErrorsCl
	 * @return self
	 */
	private function setEvaluationErrorsCl(EvaluationErrors $evaluationErrorsCl) {
		$this->evaluationErrorsCl = $evaluationErrorsCl;
		return $this;
	}

	/** Get the value of indiceCl
	 * @return  ?IndiceCPU
	 */
	public function getIndiceCl() : ?IndiceCPU {
		return $this->indiceCl;
	}
	/** Set the value of indice
	 * @param   IndiceCPU  $indiceCl  
	 * @return  self
	 */
	public function setIndiceCl(IndiceCPU $indiceCl) : self {
		$this->indiceCl = $indiceCl;
		return $this;
	}

	/** Get the value of webpage
	 * @return  ?string 
	 */
	public function getWebpage() : ?string {
		return $this->webpage;
	}
	/** set le contenu de la page web lue
	 * @param string $webpage
	 * @return self
	 */
	public function setWebpage(string $webpage) : self {
		$this->webpage = $webpage;
		return $this;
	}

	/** retourne l'URL utilisée pour recherche l'indice CPU
	 * @return string|null
	 */
	public function getUrl() : ?string{
		return $this->url;
	}
	/**  Set the value of url
	 * @param string|null $url URL utilisée pour lire les infos du CPU
	 * @return self
	 */
	public function setUrl(string $url) : self {
		$this->url = $url;
		return $this;
	}

	/** Get the value of cpuTextNorm
	 * @return  string|null texte du CPU utilisé pour faire la recherhce
	 */
	public function getCpuTextNorm() : ?string{
		return $this->cpuTextNorm;
	}
	/** Set the value of cpuTextNorm
	 * @param   string  $cpuTextNorm  
	 * @return  self
	 */
	public function setCpuTextNorm( string $cpuTextNorm) : self {
		$this->cpuTextNorm = $cpuTextNorm;
		return $this;
	}

	// /** Get the value of cpuWebName
	//  * @return  string|null texte du CPU dans la page Web
	//  */
	// public function getCpuWebName() : ?string{
	// 	return $this->cpuWebName;
	// }
	// /** Set the value of cpuTextNorm
	//  * @param   string  $cpuWebName  
	//  * @return  self
	//  */
	// public function setCpuWebName( string $cpuWebName) : self {
	// 	$this->cpuWebName= $cpuWebName;
	// 	return $this;
	// }

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
