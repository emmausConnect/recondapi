<?php
declare(strict_types=1);

require_once __DIR__.'/../loggerrec.class.php';
require_once __DIR__.'/../paramini.class.php';
require_once __DIR__.'/../contexte.class.php';

/**
 * stocke des infos concernat la BDD : servernama, dbname user, password, tables prefix ...
 * instance is unique 
 * 
 */
class DbManagement {
    private static  DbManagement $instance;
    private LoggerRec $logger;
    private Contexte  $contexte;
    private Array     $paramDbArray;
    private string    $env;
    private string    $servername;
    private string    $dbname;
    private string    $username;
    private string    $password;
    private string    $tprefix;
    private PDO       $db;

    private function __construct(){ }

    /**
     * Undocumented function
     *
     * @return DbManagement : unique instance
     */
    public static function getInstance() : DbManagement {
        if (! isset(self::$instance)) {
            $c = new DbManagement();
            $c->logger       = LoggerRec::getInstance();
            $c->contexte     = Contexte::getInstance();
            $c->paramDbArray = ParamIni::getInstance('*paramconfidentiel.ini')->getParam();
            $c->initParamDb();
            self::$instance = $c;

        }
        return self::$instance;
    }

    /**
     * return 'new PDO(...)
     *
     * @return PDO
     */
    public function openDb(): PDO {
        $db = new PDO(
            "mysql:host=$this->servername;dbname=$this->dbname",
            "$this->username",
            "$this->password"
        );
        // pour éviter que tout soit en string
        // https://write.corbpie.com/how-to-return-integers-and-floats-from-mysql-with-php-pdo/
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        return $db;
    }

    /**
     * return $table with prefix use
     * 
     * ex :"smartphones" -> "rc_smartphones"
     *
     * @param String $table
     * @return String
     */
    public function tableName(String $table): String {
        return $this->tprefix.$table;
    }

    public function insertRow(Do_ $doObject){

    }

    public function getRows(string $query, array $param, Do_ $doData): array {
        $retour = [];
        $stmt = $this->db->prepare($query);
        $stmt->execute($param);
        $rows= $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) != 0) {
            foreach($rows as $row) {
                $do = new $doData;
                $do->setValues($row);
                array_push($retour, $do);
            }
        }
        return $retour;
    }
    // ============ PRIVATE CLASS
    private function initParamDb() {
        $environnement = $this->contexte->getEnvironnement();

        if (! in_array($environnement, ['PROD','TEST','LOCAL'])) {
            echo "le type d'environnement [$environnement] n'est pas défini dans la classe contexte. Ouverture de db impossible";
            exit(1);
        }

        $extDbParam   = strtolower($environnement);
        $paramDbArray = $this->paramDbArray;

        if (! array_key_exists('db'.$extDbParam, $paramDbArray)) {
            echo "param BDD non trouvé pour [$extDbParam]";
            exit(1);
        }
        $paramThisDbArray = $paramDbArray['db'.$extDbParam];
        $msg = "";
        if (! array_key_exists('env', $paramThisDbArray)) {
            $msg .= "[env] non trouvé dans le param de la BDD";
        }
        if (! array_key_exists('servername', $paramThisDbArray)) {
            $msg .= " | [servername] non trouvé dans le param de la BDD";
        }
        if (! array_key_exists('dbname', $paramThisDbArray)) {
            $msg .= " | [dbname] non trouvé dans le param de la BDD";
        }
        if (! array_key_exists('username', $paramThisDbArray)) {
            $msg .= " | [username] non trouvé dans le param de la BDD";
        }
        if (! array_key_exists('password', $paramThisDbArray)) {
            $msg .= " | [password] non trouvé dans le param de la BDD";
        }
        if (! array_key_exists('tprefix', $paramThisDbArray)) {
            $msg .= " | [tprefix] non trouvé dans le param de la BDD";
        }

        $this->env        = $paramThisDbArray['env'];
        $this->servername = $paramThisDbArray['servername'];
        $this->dbname     = $paramThisDbArray['dbname'];
        $this->username   = $paramThisDbArray['username'];
        $this->password   = $paramThisDbArray['password'];
        $this->tprefix    = $paramThisDbArray['tprefix'];

        if(strtoupper($this->env) !== strtoupper($environnement)) {
            $msg .= " | Il y a incohérence entre les noms d'environnement du param de la BD est du fichier environnement";
        }

        if ($msg != "") {
            echo $msg;
            exit(1);
        }

        $this->contexte->setTprefix($this->tprefix);
    }

    //******************************************************************* */
	function __call($name, $arguments)
    {
        //$msg = "Appel de la méthode non statique inconnue : $name, param : ". implode(', ', $arguments). "\n";
        $msg = "Appel de la méthode non statique inconnue : $name";
        throw new Exception($msg);
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