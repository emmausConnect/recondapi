<?php

declare(strict_types=1);
/**
 * conteneur d'un message d'erreur
 */

class EvaluationError
{
        private $code;
        private $msg;


        private function __construct()
        {
        }

        public static function getInstance(): EvaluationError
        {
                $c = new EvaluationError();
                return $c;
        }

        /** Get the value of code
         * @return string
         */
        public function getCode(): string
        {
                return $this->code;
        }
        /** set Code
         * @param string $code
         * @return self
         */
        public function setCode(string $code): self
        {
                $this->code = $code;
                return $this;
        }

        /** get msg
         * @return string
         */
        public function getMsg(): string
        {
                return $this->msg;
        }
        /** set MSG
         * @param String $msg
         * @return self
         */
        public function setMsg(String $msg): self
        {
                $this->msg = $msg;
                return $this;
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
