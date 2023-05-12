<?php
declare(strict_types=1);
require_once __DIR__.'/cpubenchmarkresult.class.php';

/**
 * données sur l'indice d'un CPU
 */
class IndiceCPU {
	private string $cpuTextNorm = "";
	private string $cpuWebName="";
    private string $origine = "";
    private string $indice = "";
    private string $dateMaj="";

    //private ?CpuBenchmarkResult $cpuBenchmarkResultCl;
    
    private function __construct() {
    }
  
    public static function getInstance() {
      $c = new IndiceCPU();
      return $c;
    }
 
    /** GETTERS & SETTERS ***************************************************** */
	/** Get the value of cpuTextNorm
	 * @return  string
	 */
	public function getCpuTextNorm(){
		return $this->cpuTextNorm;
	}

	/** Set the value of cpuTextNorm
	 * @param   string  $cpuTextNorm  
	 * @return  self
	 */
	public function setCpuTextNorm(string $cpuTextNorm) {
		$this->cpuTextNorm = $cpuTextNorm;
		return $this;
	}

	/** Get the value of cpuTextNorm
	 * @return  string
	 */
	public function getCpuWebName(){
		return $this->cpuWebName;
	}

	/** Set the value of cpuTextNorm
	 * @param   string  $cpuTextNorm  
	 * @return  self
	 */
	public function setCpuWebName(string $cpuWebName) {
		$this->cpuWebName = $cpuWebName;
		return $this;
	}

	/** Get the value of origine
	 * @return  string
	 */
	public function getOrigine() : string {
		return $this->origine;
	}
	/** Set the value of origine
	 * @param   string  $origine  
	 * @return  self
	 */
	public function setOrigine(string $origine) {
		$this->origine = $origine;
		return $this;
	}

	/** Get the value of indice
	 * @return  string
	 */
	public function getIndice() : string {
		return $this->indice;
	}
	/** Set the value of indice
	 * @param   string  $indice  
	 * @return  self
	 */
	public function setIndice(string $indice) {
		$this->indice = $indice;
		return $this;
	}

	/** Get the value of dateMaj
	 * @return  string
	 */
	public function getDateMaj() : string{
		return $this->dateMaj;
	}
	/** Set the value of dateMaj
	 * @param   string  $dateMaj  
	 * @return  self
	 */
	public function setDateMaj(string $dateMaj) {
		$this->dateMaj = $dateMaj;
		return $this;
	}

	/** Get the value of cpuBenchmarkResultCl
	 * @return  ?CpuBenchmarkResult
	 */
	public function getCpuBenchmarkResultCl() : ?CpuBenchmarkResult{
		return $this->cpuBenchmarkResultCl;
	}

	// /** Set the value of cpuBenchmarkResultCl
	//  * @param   CpuBenchmarkResult  $cpuBenchmarkResultCl  
	//  * @return  self
	//  */
	// public function setCpuBenchmarkResultCl(CpuBenchmarkResult $cpuBenchmarkResultCl) {
	// 	$this->cpuBenchmarkResultCl = $cpuBenchmarkResultCl;
	// 	return $this;
	// }
	
	function __toString() : string {
		$string = "";
		$string .= "[".$this->cpuTextNorm."]";
		$string .= "[".$this->origine."]";
		$string .= "[".$this->indice."]";
		$string .= "[".$this->dateMaj."]";
		return $string;
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