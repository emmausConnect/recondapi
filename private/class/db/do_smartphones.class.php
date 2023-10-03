<?php
declare(strict_types=1);

require_once __DIR__.'/../loggerrec.class.php';
require_once __DIR__.'/../paramini.class.php';
require_once __DIR__.'/../contexte.class.php';

/**
 * stocke des infos de contexte d'exécution, tel le niveau de log
 * et des valeurs à utiliser par défaut
 */
class Do_Smartphones  extends Do_ {

    private static $tableName = "Smartphones";
    
    private static $fileldsDesc = [
        'title'    => ['mysqlDesc'=>'VARCHAR(300)', 'length' => '300', 'null'=>'NOT null',
            'header'=>'Titre', 'headerMultiLines'=>'', 'description'=>''],
        'modele'   => ['mysqlDesc'=>'VARCHAR(300)', 'length' => '300', 'null'=>'NOT null',
            'header'=>'Modèle', 'headerMultiLines'=>'', 'description'=>''],
        'ram'      => ['mysqlDesc'=>'INT', 'length' => '', 'null'=>'NOT null',
            'header'=>'Taille Ram', 'headerMultiLines'=>'', 'description'=>''],
        'stockage' => ['mysqlDesc'=>'INT', 'length' => '', 'null'=>'NOT null',
            'header'=>'Taille stockage', 'headerMultiLines'=>'', 'description'=>''],
        'indice'   => ['mysqlDesc'=>'INT', 'length' => '', 'null'=>'NOT null',
            'header'=>'Indice', 'headerMultiLines'=>'', 'description'=>''],
        'os'       => ['mysqlDesc'=> 'VARCHAR(50)', 'length' => '50', 'null'=>'',
            'header'=>'OS', 'headerMultiLines'=>'', 'description'=>''],
        'url'      => ['mysqlDesc'=> 'VARCHAR(2048)', 'length' => '2048', 'null'=>'',
            'header'=>'URL', 'headerMultiLines'=>'', 'description'=>''],
        'origine'  => ['mysqlDesc'=>'VARCHAR(100)', 'length' => '100', 'null'=>'',
            'header'=>'Origine', 'headerMultiLines'=>'', 'description'=>'Origine des infos'],
        'crtby'    => ['mysqlDesc'=>'VARCHAR(100)', 'length' => '100', 'null'=>'',
            'header'=>'Créé par', 'headerMultiLines'=>'', 'description'=>''],
        'crtdate'  => ['mysqlDesc'=>'DATETIME', 'length' => '', 'null'=>'',
            'header'=>'Créé le', 'headerMultiLines'=>'', 'description'=>''],
        'updby'    => ['mysqlDesc'=>'VARCHAR(100)', 'length' => '100', 'null'=>'',
            'header'=>'Modifié par', 'headerMultiLines'=>'', 'description'=>''],
        'upddate'  => ['mysqlDesc'=>'DATETIME', 'length' => '', 'null'=>'',
            'header'=>'Modifié le', 'headerMultiLines'=>'', 'description'=>'']
    ];
    private $fileldsData = [
        'title'    => ['defined'=>false, 'value'=>null],
        'modele'   => ['defined'=>false, 'value'=>null],
        'ram'      => ['defined'=>false, 'value'=>null],
        'stockage' => ['defined'=>false, 'value'=>null],
        'indice'   => ['defined'=>false, 'value'=>null],
        'os'       => ['defined'=>false, 'value'=>null],
        'url'      => ['defined'=>false, 'value'=>null],
        'origine'  => ['defined'=>false, 'value'=>null],
        'crtby'    => ['defined'=>false, 'value'=>null],
        'crtdate'  => ['defined'=>false, 'value'=>null],
        'updby'    => ['defined'=>false, 'value'=>null],
        'upddate'  => ['defined'=>false, 'value'=>null]
    ];

    private function __construct(){ }

    /**
     * Undocumented function
     *
     * @return Do_Smartphones
     */
	public static function getInstance() : Do_Smartphones{
        $c = new Do_Smartphones();
        return $c;
      }

    //******************************************************************* */

    public static function getTableName() {
        return self::$tableName;
    }

    public function setValues(array $row) {
        foreach($row as $fieldName => $fieldValue) {
            $this->_setFieldValue($fieldName, $fieldValue);
        }
    }

    public function setTitleValue($value)    { return $this->_setFieldValue('title', $value); }
    public function setModeleValue($value)   { return $this->_setFieldValue('modele', $value); }
    public function setRamValue($value)      { return $this->_setFieldValue('ram', $value); }
    public function setStockageValue($value) { return $this->_setFieldValue('stockage', $value); }
    public function setIndiceValue($value)   { return $this->_setFieldValue('indice', $value); }
    public function setOsValue($value)       { return $this->_setFieldValue('os', $value); }
    public function setUrlValue($value)      { return $this->_setFieldValue('url', $value); }
    public function setOrigineValue($value)  { return $this->_setFieldValue('origine', $value); }
    public function setCrtbyValue($value)    { return $this->_setFieldValue('crtby', $value); }
    public function setCrtdateValue($value)  { return $this->_setFieldValue('ctrdate', $value); }
    public function setUpdbyValue($value)    { return $this->_setFieldValue('updby', $value); }
    public function setUpddateValue($value)  { return $this->_setFieldValue('upddate', $value); }

    public function getTitleValue()    { return $this->_getFieldValue('title'); }
    public function getModeleValue()   { return $this->_getFieldValue('modele'); }
    public function getRamValue()      { return $this->_getFieldValue('ram'); }
    public function getStockageValue() { return $this->_getFieldValue('stockage'); }
    public function getIndiceValue()   { return $this->_getFieldValue('indice'); }
    public function getOsValue()       { return $this->_getFieldValue('os'); }
    public function getUrlValue()      { return $this->_getFieldValue('url'); }
    public function getOrigineValue()  { return $this->_getFieldValue('origine'); }
    public function getCrtbyValue()    { return $this->_getFieldValue('crtby'); }
    public function getCrtdateValue()  { return $this->_getFieldValue('crtdate'); }
    public function getUpdbyValue()    { return $this->_getFieldValue('updby'); }
    public function getUpddateValue()  { return $this->_getFieldValue('upddate'); }

    public function getTitleValueDft($dft) { return $this->_getFieldValueDft('title', $dft); }
    public function getModeleValueDft($dft) { return $this->_getFieldValueDft('modele', $dft); }
    public function getRamValueDft($dft) { return $this->_getFieldValueDft('ram', $dft); }
    public function getStockageValueDft($dft) { return $this->_getFieldValueDft('stockage', $dft); }
    public function getIndiceValueDft($dft) { return $this->_getFieldValueDft('indice', $dft); }
    public function getOsValueDft($dft) { return $this->_getFieldValueDft('os', $dft); }
    public function getUrlValueDft($dft) { return $this->_getFieldValueDft('url', $dft); }
    public function getOrigineValueDft($dft) { return $this->_getFieldValueDft('origine', $dft); }
    public function getCrtbyValueDft($dft) { return $this->_getFieldValueDft('crtby', $dft); }
    public function getCrtdateValueDft($dft) { return $this->_getFieldValueDft('crtdate', $dft); }
    public function getUpdbyValueDft($dft) { return $this->_getFieldValueDft('updby', $dft); }
    public function getUpddateValueDft($dft) { return $this->_getFieldValueDft('upddate', $dft); }

    // --------- Set field

    private function _setFieldValue(String $field, $value): self {
        $this->fileldsData[$field]['defined'] = true;
        $this->fileldsData[$field]['value']   =  $value;
        return $this;
    }

    private function _getFieldValue(String $field) {
        $retour = null;
        if ($this->fileldsData[$field]['defined'] ) {
            $retour = $this->fileldsData[$field]['value'];
        }else{
            throw new Exception("le champ [$field] n\'a pas été initialisé");
        }
        return $retour;
    }

    private function _getFieldValueDft(String $field, $dftVal): self
    {
        $retour = null;
        if ($this->fileldsData[$field]['defined'] ) {
            $retour = $this->fileldsData[$field]['value'];
        }else{
            $retour = $dftVal;
        }
        return $retour;
    }

    // ========== get description ==============================================
    public function getTitleDesc(): array {
        return $this->fileldsDesc['title'];
    }
    public function getModeleDesc(): array {
        return $this->fileldsDesc['modele'];
    }
    public function getRamDesc(): array {
        return $this->fileldsDesc['ram'];
    }
    public function getStockageDesc(): array {
        return $this->fileldsDesc['stockage'];
    }
    public function getIndiceDesc(): array {
        return $this->fileldsDesc['indice'];
    }
    public function getOsDesc(): array {
        return $this->fileldsDesc['os'];
    }
    public function getUrlDesc(): array {
        return $this->fileldsDesc['url'];
    }
    public function getOrigineDesc(): array {
        return $this->fileldsDesc['origine'];
    }
    public function getCrtbyDesc(): array {
        return $this->fileldsDesc['crtby'];
    }
    public function getCrtdateDesc(): array {
        return $this->fileldsDesc['crtdate'];
    }
    public function getUpdbyDesc(): array {
        return $this->fileldsDesc['update'];
    }
    public function getUpddateDesc(): array {
        return $this->_getFieldDesc('update');
    }
    
    private function _getFieldDesc(string $fieldName) {
        $a = $this->fileldsDesc[$fieldName];
        if ($a['header'] == '' || $a['header'] == null) {
            if ($a['headerMultiLines'] != '' || $a['headerMultiLines'] != null) {
                $a['header'] = str_replace(array("\n", "\r\n"), " ", $a['headerMultiLines']);
            }else{
                if ($a['headerMultiLines'] == '' || $a['headerMultiLines'] = null) {
                    $a['headerMultiLines'] = $a['header'];
                }
            }
        }
        return $a;
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