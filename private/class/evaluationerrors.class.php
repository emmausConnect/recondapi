<?php
declare(strict_types=1);
//use phpDocumentor\Reflection\PseudoTypes\False_;

require_once 'evaluationerror.class.php';
/**
 * conteneur pour une liste de message d'erreur
 */
class EvaluationErrors {
	/** tableau d'object EvaluationError
	 * @var array EvaluationError
	 */
    private array $evaluationErrorArray;

    private function __construct() {
	}
	
	/** retourne une nouvelle instance de la classe
	 * @return EvaluationErrors
	 */
	public static function getInstance() : EvaluationErrors{
	  $c = new EvaluationErrors();
      $c->evaluationErrorArray = [];
	  return $c;
	}

    public function hasErrors() {
		if (count($this->evaluationErrorArray) > 0) {
			return true;
		}else{
			return false;
		}
    }


    public function addErrorMsg(string $code, string $msg) {
		$error = EvaluationError::getInstance();
		$error->setCode($code);
		$error->setMsg($msg);
        $this->evaluationErrorArray[] = $error;
    }

    public function addError(EvaluationError $error) {
        $this->evaluationErrorArray[] = $error;
    }

    public function getErrorsMsgAsString($sep="") : string {
        $msg = "";
        foreach ($this->evaluationErrorArray as $error) {
            if ($error->getMsg()  != '') {
                $msg .= "[" .$error->getMsg(). "]".$sep;
            }
        }
        return $msg;
    }


	/** Get the value of evaluationErrorArray
	 * @return  array
	 */
	public function getErrorArray() : array {
		return $this->evaluationErrorArray;
	}

	/** Set the value of evaluationErrorArray
	 * @param   array  $evaluationErrorArray  
	 * @return  self
	 */
	public function setErrorArray(array $errorArray) {
		$this->evaluationErrorArray = $errorArray;
		return $this;
	}

	public function mergeErrorArray(EvaluationErrors $evaluationErrors) {
		$this->evaluationErrorArray = array_merge($this->evaluationErrorArray, $evaluationErrors->getErrorArray());
	}

	function __toString() : string {
		$retour = $this->getErrorsMsgAsString("\n");
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
        throw new Exception("Set d'une propriété inconnue : '$name'");
    }

    function __get($name)
    {
        throw new Exception("Get d'une propriété inconnue : '$name'");
    }
}