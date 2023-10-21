<?php
declare(strict_types=1);
require_once __DIR__.'/../loggerrec.class.php';
require_once __DIR__.'/../../php/pageheaderhtml.php';
require_once __DIR__.'/../../php/pageexcelhtml.php';

/**
 * affichage du formulaire de soumission d'excels SM
 */
class FormExcelSm {
    private LoggerRec $logger;
    private string    $debug;
    
    private function __construct(){ }

    public static function getInstance(string $debug) : FormExcelSm 
    {
        $c = new FormExcelSm();
        $c->logger = LoggerRec::getInstance();
        $c->debug = $debug;
        return $c;
    }

    function displayForm() {
        $debug = $this->debug;
        $this->logger->addLogDebugLine(">>>> displayForm");
        // formulaire de chargement d'un Excel
        echo getUploadHtmlHead();
        echo '<body class="body_flex" onload="execInitPage()"><main>';
        echo getHtmlExcel('sm');
        echo '</main></body></html>';
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