<?php
declare(strict_types=1);
require_once __DIR__.'/indicecpu.class.php';
require_once __DIR__.'/loggerrec.class.php';
require_once __DIR__.'/cpubenchmarkresult.class.php';
class Cpubenchmarkread {
	private CpuBenchmarkResult $cpuBenchmarkResult;
	private LoggerRec $logger;


	private function __construct() {
		
	}

	public static function getInstance() : Cpubenchmarkread{
		$c = new Cpubenchmarkread();
		$c->logger = LoggerRec::getInstance();
		$c->cpuBenchmarkResult = CpuBenchmarkResult::getInstance();
		return $c;
	}

	/** essaye de lire l'indice CPU depuis le web
	 * 
	 * si trouvé :
	 * 	 * set IndiceCpu dans $this->cpuBenchmarkResult
	 * 
	 * @param string $cpuTextNorm
	 * @return self
	 */
	function getCpuNormindiceFromWeb(string $cpuTextNorm ) : self {
		$this->logger->addLogDebugLine(">>> getCpuNormindiceFromWeb(" . $cpuTextNorm. ")");
		$indiceClw = IndiceCpu::getInstance();
		$indiceClw->setOrigine('WEB');
		$indiceClw->setDateMaj(date('Ymd'));
		$this->readCpuPage($cpuTextNorm);
		
		if($this->cpuBenchmarkResult->getWebpage() !== null) {
			$this->extractCpuindiceFromWebPage();
			if($this->cpuBenchmarkResult->getStatus() == "OK") {
				// indice WEB trouvé
				$indiceClw->setIndice($this->cpuBenchmarkResult->getIndiceCl()->getIndice());
				$indiceClw->setCpuWebName($this->cpuBenchmarkResult->getIndiceCl()->getCpuWebName());

				$indiceClw->setIndice($this->cpuBenchmarkResult->getIndiceCl()->getIndice());
				$this->cpuBenchmarkResult->setIndiceCl($indiceClw);
			}else{
				// indice WEB NON trouvé dans la page
				$this->cpuBenchmarkResult->getEvaluationErrorsCl()->addErrorMsg("", "Ligne indice CPU non trouvée : '" .$this->cpuBenchmarkResult->getCpuTextNorm(). '"');
			}
			$this->logger->addLogDebugLine("---- getCpuNormindiceFromWeb");
		}else{
			// page non trouvée
			$this->cpuBenchmarkResult->getEvaluationErrorsCl()->addErrorMsg("", "Page wen non trouvée pour le CPU : '" .$this->cpuBenchmarkResult->getCpuTextNorm(). '"');
		}
		return $this;
	}

	/** renseigne URL, CpuTextNorm , Webpage , Errors
	 * @param string $cpuTextNorm
	 * @return self
	 */
	private function readCpuPage(string $cpuTextNorm) : self {
		$this->logger->addLogDebugLine(">>>> readCpuPage    cpuTextNorm = '$cpuTextNorm'");
		$this->cpuBenchmarkResult->setCpuTextNorm($cpuTextNorm);
		try {	
			$cpuTextNormUrl = preg_replace("/ /", "+", $cpuTextNorm );
			$cpuTextNormUrl = preg_replace("/@/", "%40", $cpuTextNormUrl );
			$url = "https://www.cpubenchmark.net/cpu.php?cpu=" . $cpuTextNormUrl;
			$this->cpuBenchmarkResult->setUrl($url);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			//for debug only : https://www.php.net/manual/en/function.curl-setopt.php
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			$resp = curl_exec($curl);
			$this->cpuBenchmarkResult->setWebpage($resp);
		}catch (Exception $e) {
			$this->cpuBenchmarkResult->getEvaluationErrorsCl()->addErrorMsg("",$e->getMessage());
		}finally {
			curl_close($curl);
		}
		return $this;
	}

	/** extrait l'indice CPU de la page WEB et place le résultat dans $cpuBenchmarkResult
	 *   
	 *  positionne $cpuBenchmarkResult à "OK" ou "KO"
	 * *  si trouvé : $cpuBenchmarkResult->setIndiceCl($ind); : uniquement l'indice
	 * @return self
	 */
	private function extractCpuindiceFromWebPage() : self {
		$this->logger->addLogDebugLine(">>>> extractCpuindiceFromWebPage");
		$pattern = "/.*<div class=speedicon>/m";
		// <span class="cpuname">
		$lines = explode("\n", $this->cpuBenchmarkResult->getWebpage());
		$resp2 = "";
		$divOk=false;
		// extraction de la ligne **qui suit** les "<div ...>"
		foreach ($lines as $line) {
			if ($divOk) {
				$resp2 = $line;
				break;
			}
			if (strpos($line, 'speedicon') !== FALSE) {
				$divOk=true;
			}
		}
		if ($resp2 == "") {
			$this->cpuBenchmarkResult->setStatus('KO');
		}else{
			$this->cpuBenchmarkResult->setStatus('OK');
			// recherche de la chaîne indice
			preg_match('/^\s*<span.*>(\d*)<\/span>/', $resp2, $resp2array);
			if (count($resp2array)<2) {
				$this->cpuBenchmarkResult->getEvaluationErrorsCl()->addErrorMsg(""
						,'Chaîne indice non trouvée dans la page web');
			}else{
				$indweb = trim($resp2array[1]);
				if (ctype_digit($indweb)) {
					//c'est bien numérique
					$indiceCPUCl = IndiceCPU::getInstance();
					$indiceCPUCl->setIndice($resp2array[1]);
					//$this->cpuBenchmarkResult->setIndiceCl($indiceCPUCl);
					// recherche du texte du cpu trouvé
					$ligCpuNameArray = []; // contiendra 1 tableau par ligne de lapage web
					preg_match('/.*<span class="cpuname">(.*)<\/span>.*/', $this->cpuBenchmarkResult->getWebpage(), $ligCpuNameArray);
					if (count($ligCpuNameArray) > 0) {
						//parcour pour trouver le tableau non vide
						// foreach ( $ligCpuNameArray as $ligneArray) {
						// 	if (count($ligneArray) == 2) {
						// 		$this->cpuBenchmarkResult->setCpuWebName($ligneArray[1]);
						// 		$indiceCPUCl->setCpuWebName($ligneArray[1]);
						// 	}
						// }
						if (count($ligCpuNameArray) == 2) {
							//$this->cpuBenchmarkResult->setCpuWebName($ligCpuNameArray[1]);
							$indiceCPUCl->setCpuWebName($ligCpuNameArray[1]);
						}
					}
					$this->cpuBenchmarkResult->setIndiceCl($indiceCPUCl);
				}else{
					$this->cpuBenchmarkResult->getEvaluationErrorsCl()->addErrorMsg(""
							,'Chaîne indice trouvée dans la page web n\'est pas numérique : "' .$resp2array[1]. '"');
				}
			}
		}
		return $this;
	}

	/** Get the value of puBenchmarkResult
	 * @return  CpuBenchmarkResult
	 */
	public function getCpuBenchmarkResult() : CpuBenchmarkResult {
		return $this->cpuBenchmarkResult;
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
