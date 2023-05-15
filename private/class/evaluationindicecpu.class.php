<?php

declare(strict_types=1);
require_once __DIR__.'/indicecpu.class.php';
require_once __DIR__.'/cpuindiceramcache.class.php';
require_once __DIR__.'/cpuindicefilecache.class.php';
require_once __DIR__.'/cpubenchmarkread.class.php';
require_once __DIR__.'/pc.class.php';
require_once __DIR__.'/util01.class.php';
require_once __DIR__.'/loggerrec.class.php';

/**
 * fait l'évaluation d'un CPU contenu dans un objet "Pc"
 */
class EvaluationIndiceCpu
{
  private array      $cpuTextInputArray = array();     // renseigné à l'instentiation
  private ?string    $status            = null;
  private EvaluationErrors   $evaluationErrorsCl;      // renseigné à l'instentiation
  private ?string    $cputextnorm       = null;        // renseigné lors de l'évaluation
  private ?IndiceCPU $indiceCPUCl       = null;        // renseigné lors de l'évaluation
  private ?CpuBenchmarkResult $cpuBenchmarkResultCl;   // renseigné lors de l'évaluation si recherche sur le web|null
  //private ?string    $categorieCPU    = null;
  private LoggerRec  $logger;

  private function __construct()
  {
  }
  /**
   * @param PC $pc
   * @return EvaluationIndiceCpu
   */
  public static function getInstance(PC $pc): EvaluationIndiceCpu
  {
    $c = new EvaluationIndiceCpu();
    $c->logger = LoggerRec::getInstance();
    $c->setEvaluationErrorsCl(EvaluationErrors::getInstance());
    $c->setCpuTextInputArray($pc->getCpuTextInputArray());
	  $c->setIndiceCPUCl(IndiceCPU::getInstance());
    //$c->calcCpuIndice();
    return $c;
  }

  /**
   * Evaluation du CPU
   *  
   *  * utilise $cpuTextInputArray : tableau de noms de CPU
   *  * Normalise le nom du CPU puis recherche son indice MARK
   *  * pour 1 PC il est possible d'avoir plusieurs syntaxe de CPU
   * @return self
   */
  public function calcCpuIndice(): self
  {
    $cpuRamCache = CpuIndiceRamCache::getInstance();
    $cpuName      = $this->cpuTextInputArray[0];
    $this->calcCpuNormIndice($cpuName);
    if ($this->getStatus() == "OK") {
      $cpuRamCache->addCpuIndice($cpuName, $this->indiceCPUCl);
    }else{
      // indice non trouvé, on essaye en normalisant le nom
      $cpuTextNorm  = $this->normalyseCpuText($cpuName);
      $this->calcCpuNormIndice($cpuTextNorm);
      if ($this->getStatus() == "OK") {
        $cpuRamCache->addCpuIndice($cpuName, $this->indiceCPUCl);
      }else{
        $this->logger->addLogDebugLine("indice CPU non trouvé. Ajout dans 'cpunotfound.txt'");
        $myfile = fopen("../work/workingfiles/cpunotfound.txt", "a");
        $temp   = fwrite($myfile, date("Y-m-d H:i:s") . "\t" . $cpuName . "\t" . $cpuTextNorm . "\n");
        $this->logger->addLogDebugLine("[$temp]", "résultat de l'ajout à 'cpunotfound.txt'");
		    // pour ne pas le chercher une seconde fois durant ce traitement
		    $cpuRamCache->addCpuIndice($cpuTextNorm, $this->indiceCPUCl);
      }

    }

    return $this;
  }

  /**
   * Evaluation du CPU
   *  
   *  * utilise $cpuTextInputArray : tableau de noms de CPU
   *  * Normalise le nom du CPU puis recherche son indice MARK
   *  * pour 1 PC il est possible d'avoir plusieurs syntaxe de CPU
   * @return self
   */
  private function calcCpuIndice2(String $cpuTextInput, String $cpuTextNorm): self
  {
    $this->logger->addLogDebugLine(">>>> calcCpuIndice2 ('$cpuTextInput' , '$cpuTextNorm' )");
    $this->setStatus("KO");
	  $cpuRamCache = CpuIndiceRamCache::getInstance();
    // pour chaque texte de CPU on fait une recherche d'indice
    //foreach ($this->cpuTextInputArray as $cpuTextInput) {
      //$cpuTextNorm  = $this->normalyseCpuText($cpuTextInput);
      $this->calcCpuNormIndice($cpuTextNorm);
      if ($this->getStatus() != "OK") {
        $this->logger->addLogDebugLine("indice CPU non trouvé. Ajout dans 'cpunotfound.txt'");
        $myfile = fopen("../work/workingfiles/cpunotfound.txt", "a");
        $temp   = fwrite($myfile, date("Y-m-d H:i:s") . "\t" . $cpuTextInput . "\t" . $cpuTextNorm . "\n");
        $this->logger->addLogDebugLine("[$temp]", "résultat de l'ajout à 'cpunotfound.txt'");
		    //$cpuRamCache[$cpuTextNorm] = $this; // pour ne pas le chercher une seconde fois durant ce traitement
		    $cpuRamCache->addCpuIndice($cpuTextNorm, $this->indiceCPUCl);
      } else {
        //$cpuRamCache[$cpuTextNorm] = $this; // pour ne pas le chercher une seconde fois durant ce traitement
		    $cpuRamCache->addCpuIndice($cpuTextNorm, $this->indiceCPUCl);
        //break;
      }
    //}
    return $this;
  }

  /**
   * $cpuTextNorm doit être normalisé
   * renseigne 
   *   $this->setStatus
   *   $this->setIndiceCPUCl
   * @return void
   */
  function calcCpuNormIndice($cpuTextNorm): void
  {
    $this->logger->addLogDebugLine(">>> calcCpuNormIndice(" . $cpuTextNorm . ")");
    $this->setCpuTextNorm($cpuTextNorm);
    $this->setStatus("KO");
    // RAM : l'indice a-t'il déjà été recherché pour ce CPU
    $cpuRamCache = CpuIndiceRamCache::getInstance();
    $indiceClRam = $cpuRamCache->getCpuIndice($cpuTextNorm);
    if ($indiceClRam != null) {
      // indice trouvé dans le cache RAM
      $this->logger->addLogDebugLine($indiceClRam, "CPU repris du cache RAM PHP");
      if($indiceClRam->getIndice() !="") {
        $this->setStatus("OK");
      }
      $this->indiceCPUCl = $indiceClRam;
      $this->indiceCPUCl = $this->indiceCPUCl->getOrigine() . " RamCache";
    } else {
      // EMMAUSCONNECT nnnn  : le texte contient-il l'indice ?
      $indiceKs = $this->getCpuIndiceFromConstante($cpuTextNorm);
      if ($indiceKs !== null) {
        if (!ctype_digit($indiceKs)) {
          // la constante n'est pas numérique
          $this->setStatus("KO");
          $this->getEvaluationErrorsCl()->addErrorMsg("", "L'indice cpu saisi n'est pas numérique : '" . $indiceKs . "'");
        } else {
          // indice en constante OK
          $this->setStatus("OK");
          $this->indiceCPUCl->setOrigine("constante");
          $this->indiceCPUCl->setIndice($indiceKs);
        }
      } else {
        // l'indice a-t'il déjà été cherché lors d'un précédent traitement ?
        $indiceCl = CpuIndiceFileCache::getIndice($cpuTextNorm);
        if ($indiceCl !== NULL) {
          $this->setStatus("OK");
          $this->setIndiceCPUCl($indiceCl);
        } else {
          // recherche sur le web
          $cbench = Cpubenchmarkread::getInstance();
          $retour = $cbench->getCpuNormIndiceFromWeb($cpuTextNorm);
          $this->setCpuBenchmarkResultCl($retour->getCpuBenchmarkResult());
          if ($this->getCpuBenchmarkResultCl()->getStatus() != "OK") {
            // erreur sur le WEB
            $this->setStatus('KO');
            $this->setEvaluationErrorsCl($this->getCpuBenchmarkResultCl()->getEvaluationErrorsCl());
          } else {
            // trouvé sur le WEB
            $this->setStatus('OK');
            $this->setIndiceCPUCl($this->getCpuBenchmarkResultCl()->getIndiceCl());
          }
        }
      }
    }
    $this->logger->addLogDebugLine('<<< getCpuNormIndice  : retour');
  }



  function getCpuIndiceFromConstante($cpuText): ?string
  {
    $this->logger->addLogDebugLine(">>> getCpuNormIndiceFromConstante(" . $cpuText . ")");
    $indice = null;
    preg_match('/(EMMAUSCONNECT.* )(.*)/i', $cpuText, $ind);
    if (!empty($ind) && count($ind) > 2) {
      $indice = $ind[2]; // 2ème partie du preg_match
    }
    return $indice;
  }

  /**
   * Normalyse le nom du CPU
   * 
   * * supprime les caratère non imprimables
   * * supprime la fin de ligne
   * * supprime les espaces au début et à la fin
   * * complète le texte comme il peut
   * @param string $cpu
   * @param string $fmtcpu
   * @return string
   */
  function normalyseCpuText(string $cpu, $fmtcpu = "1") : string
  {
    $this->logger->addLogDebugLine(">>> normalyseCpuText(" . $cpu . ")");
    $cpuNorm = strtolower(Util01::cleanString($cpu));
    $cpuType = "";
    if (str_starts_with($cpuNorm, "intelcore")) {
        $cpuNorm = Util01::cleanString(str_replace("intelcore","intel core ",$cpuNorm));
    }
    if (str_starts_with($cpuNorm, "core")) {
        $cpuNorm = 'intel ' . $cpuNorm;
    }
    if (str_starts_with($cpuNorm, "i5")) {
        $cpuNorm = 'intel core ' . $cpuNorm;
    }
    if (str_starts_with($cpuNorm, "i7")) {
        $cpuNorm = 'intel core ' . $cpuNorm;
    }

    if (str_starts_with($cpuNorm, "intel")) {
        if (str_starts_with($cpuNorm, "intel core i5")) {
            $cpuType = "intel i5";
        }
        if (str_starts_with($cpuNorm, "intel core i7")) {
            $cpuType = "intel i7";
        }
        // "INTEL CORE I5/7200 U"   => "INTEL CORE I5 7200 U"                              I5/ -> I5-
        //$cpuNorm = Util01::cleanString(str_replace("intel core i5/","intel core i5-",$cpuNorm));
        $cpuNorm = preg_replace('/(intel core i5)\/\s*(.*)/', '$1-$2', $cpuNorm);
        //$cpuNorm = Util01::cleanString(str_replace("intel core i7/","intel core i7-",$cpuNorm));
        $cpuNorm = preg_replace('/(intel core i7)\/\s*(.*)/', '$1-$2', $cpuNorm);
        // "INTEL CORE I5/  7200 U" => "INTEL CORE I5 7200 U"                                  ajoute INTEL
        $cpuNorm = Util01::cleanString(str_replace("intel core i5 ","intel core i5-",$cpuNorm));
        $cpuNorm = Util01::cleanString(str_replace("intel core i7 ","intel core i7-",$cpuNorm));
        // "intel core i5-8365/u @ 1.60ghz" => "intel core i5-8365u @ 1.60ghz"                 sup "/" après N°
        $cpuNorm = preg_replace('/(intel core i5-)(\d{4})\/(.*)/', '$1$2$3', $cpuNorm); 
        $cpuNorm = preg_replace('/(intel core i7-)(\d{4})\/(.*)/', '$1$2$3', $cpuNorm);
        // "intel core i5-8365/u @ 1.60ghz" => "intel core i5-8365u @ 1.60ghz"                 sup "-"" après N°
        $cpuNorm = preg_replace('/(intel core i5-)(\d{4})-(.*)/', '$1$2$3', $cpuNorm); 
        $cpuNorm = preg_replace('/(intel core i7-)(\d{4})-(.*)/', '$1$2$3', $cpuNorm);
        // "INTEL CORE I5-7200 U	" => "INTEL CORE I5-7200U	                                   colle la lettre du processeur
        $cpuNorm = preg_replace('/^(intel core i5-)(\d{4})(\s)(.)$/', '$1$2$4', $cpuNorm);
        $cpuNorm = preg_replace('/^(intel core i7-)(\d{4})(\s)(.)$/', '$1$2$4', $cpuNorm);
        $cpuNorm = preg_replace('/(intel core i5-\d{4}) (.) (.*)/', '$1$2$3', $cpuNorm);
        $cpuNorm = preg_replace('/(intel core i7-\d{4}) (.) (.*)/', '$1$2$3', $cpuNorm);
        // "INTEL CORE I5-3570 3.40 GHZ" => ""INTEL CORE I5-3570 3.40GHZ"  sup espace avant 2.20GHz
        $cpuNorm = Util01::cleanString(str_replace(" ghz","ghz ",$cpuNorm));
        // "intel core i5-3570 3.40ghz" => "intel core i5-3570 @ 3.40ghz"                      ajoute le @
        if ( !str_contains($cpuNorm, '@') ) {
          $cpuNorm = Util01::cleanString(preg_replace('/(.*)(\d.\d\dghz)/', '$1 @ $2', $cpuNorm));
        }
        // "INTEL CORE I5-3570 3,40 GHZ" => ""INTEL CORE I5-3570 3.40GHZ"                      remplace la virgule par un point
        if (substr($cpuNorm,-3 ) == "ghz"   and substr($cpuNorm, -6 , 1 ) == ",") {
          $cpuNorm= substr_replace($cpuNorm, ".", -6, 1 );
        }
        
    }
    return $cpuNorm;
  }

  // ==================================================================
  /** Get the value of cpuTextInputArray
   * @return array|null
   */
  public function getCpuTextInputArray(): ?array
  {
    return $this->cpuTextInputArray;
  }
  /** Set the value of cpuTextInputArray
   * @return  self
   */
  public function setCpuTextInputArray(array|string $cpuTextInputArray)
  {
    if (!is_array($cpuTextInputArray)) {
      $cpuTextInputArray = array($cpuTextInputArray);
    }
    $this->cpuTextInputArray = $cpuTextInputArray;
    return $this;
  }

  /**
   * Get the value of status
   */
  public function getStatus(): ?string
  {
    return $this->status;
  }
  /** Set the value of status
   * @return  self
   */
  public function setStatus(string $status)
  {
    $this->status = $status;
    return $this;
  }

  /** Get the value of EvaluationErrorsCl
   * @return EvaluationErrors
   */
  public function getEvaluationErrorsCl(): EvaluationErrors
  {
    return $this->evaluationErrorsCl;
  }
  /** Set the value of EvaluationErrorsCl
   * @return  self
   */
  public function setEvaluationErrorsCl(?EvaluationErrors $errors)
  {
    $this->evaluationErrorsCl = $errors;
    return $this;
  }

  /** Get the value of cputextnorm
   * @return string|null
   */
  public function getCputextnorm(): ?string
  {
    return $this->cputextnorm;
  }
  /** Set the value of cputextnorm
   * @param string $cputextnorm
   */
  public function setCputextnorm(string $cputextnorm)
  {
    $this->cputextnorm = $cputextnorm;
    return $this;
  }

  /** get indiceCPUCL
   * @return IndiceCPU
   */
  public function getIndiceCPUCl(): ?IndiceCPU
  {
    return $this->indiceCPUCl;
  }
  /** set indiceCPUCL
   * @param IndiceCPU $indiceCPUCl
   */
  public function setIndiceCPUCl(IndiceCPU $indiceCPUCl)
  {
    $this->indiceCPUCl = $indiceCPUCl;
    return $this;
  }

  /** Get the value of cpuBenchmarkResult
   * @return  ?CpuBenchmarkResult
   */
  public function getCpuBenchmarkResultCl(): ?CpuBenchmarkResult
  {
    return $this->cpuBenchmarkResultCl;
  }
  /**
   * @param CpuBenchmarkResult $cpuBenchmarkResult
   * @return self
   */
  private function setCpuBenchmarkResultCl(CpuBenchmarkResult $cpuBenchmarkResult): self
  {
    $this->cpuBenchmarkResultCl = $cpuBenchmarkResult;
    return $this;
  }

  //******************************************************************* */
  function __call($name, $arguments)
  {
    throw new Exception("Appel de la méthode non statique inconnue : '$name'\n");
  }

  static function __callStatic($name, $arguments)
  {
    throw new Exception("Appel de la méthode statique inconnue : '$name'\n");
  }

  function __set($name, $value)
  {
    throw new Exception("Set d'une propriété inconnue : '$name'\n");
  }

  function __get($name)
  {
    throw new Exception("Get d'une propriété inconnue : '$name'\n");
  }
}
