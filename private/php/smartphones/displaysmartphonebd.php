<?php

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
require_once $path_private_class .'/smartphones/smartphone.class.php';
require_once $path_private_class .'/smartphones/evaluationsm.class.php';

$path_private_php = $g_contexte_instance->getPath('private/php');
require_once $path_private_php .'/pageheaderhtml.php';

require_once 'utilsm.php';

$errmsg = "";

$displayCategorie = true;
// if (strtolower(trim(getGetValue('dspcat','n'))) == 'y') {
//     $displayCategorie = true;
// }

$dbInstance = DbManagement::getInstance();
$db = $dbInstance->openDb();
$tableName = $dbInstance->tableName('smartphones');

$sqlQuery = "SELECT * from $tableName ORDER BY marque, modele, ram, stockage;";
$stmt = $db->prepare($sqlQuery);
$stmt->execute([]);
$rowsForTitle = [];
//$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rowsForTitle = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rowsForTitle) == 0) {
     $errmsg = "table vide";
}

$htmlpage  = getHtmlHead();;
$htmlpage .= <<<"EOT"

<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<!-- <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet"> -->
<!-- <link href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.dataTables.min.css" rel="stylesheet"> -->
<!-- <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet"> -->
<!-- <link href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css" rel="stylesheet"> -->
 
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script> -->
<!-- script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script> -->
<!-- <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script> -->

<link rel="stylesheet" href="style/stylesm.css" />

<script>
    $(document).ready( function () {
        tableDb = $('#tableDb').DataTable( {
            searching: true,
            ordering:  true,
            aLengthMenu: [[15, 25, 50, 75, -1], [15, 25, 50, 75, "All"]],
            pageLength: 15,

           // buttons:        [ 'colvis' ],
            // fixedColumns:   {
            //     left: 4
            // }
        } );

        tableDb.columns()
        .every(function () {
            // pour chaque colonne
            let column = this;
            let title = column.footer().textContent;

            // Create input element
            let children1 = column.footer().children;
            if (children1.length >0) {
                let firstLevelchildren = column.footer().children[0];
                let children2 = firstLevelchildren.children;
                if (children1.length >0) {
                    let input = children2[0];
                    input.placeholder = 'liste de mots';


                    // Event listener for user input
                    input.addEventListener('keyup', () => {
                        if (column.search() !== this.value) {
                            if (input.value =='  ') {
                                column.search('^$', true, false).draw();
                            }else{
                                column.search(input.value).draw();
                            }
                        }
                    });
                }
            }
        });

    } );
    //var table = $('#example').DataTable();
 
    function toggle(colType) {
        let colNbrs = [];
        if (colType == 'crt') {
            colNbrs = [8, 9, 10, 11, 12];
        }
        if (colType == 'upd') {
            colNbrs = [13, 14, 15, 16];
        }
        let tableDb = $('#tableDb').DataTable();
        colNbrs.forEach(col => {
            tableDb.column(col).visible(! tableDb.column(col).visible());
        });
    }

</script>
EOT;
$htmlpage .= '</head>';
$htmlpage .= '<body>';
$htmlpage .= '<div style="width: 800px;">';
$htmlpage .= getHtmlHeader();
$htmlpage .= '</div>';

$htmlpage .= <<<"EOT"
<h1>liste de la BDD</h1>
<button onclick="toggle('crt');">Afficher/cacher les colonnes crt</button>
<button onclick="toggle('upd');">Afficher/cacher les colonnes upd</button>
Pour n'afficher que les <b>cellules vides</b>, mettre <b>2 espaces</b> '&nbsp;&nbsp;'. <!--Pour n'afficher que les <b>cellules <u>non</u> vides</b>, mettre <b>1 espace</b>--> ' '.
Pour un texte exact, mettre entre guillemets "galaxy A01".<br>
La colonne "modèle_ns" contint le modèle snas espaces, cela facilite la recherche.
<table  id="tableDb" class="table table-sm table-striped" style="width:50%">
<thead>
    <tr>
        <th>marque</th>
        <th>modèle</th>
        <th title "sans les espaces">modèle_ns</th>
        <th>mod origine</th>
        <th style="font-weight: bold;">ram</th>
        <th style="font-weight: bold;">stockage</th>
        <th>indice</th>
EOT;
        if ($displayCategorie) {
            $htmlpage .= '<th>categorie</th>';
        }
$htmlpage .= <<<"EOT"
        <th>url</th>
        <th>crtorigine</th>
        <th>crtby</th>
        <th>crtdate</th>
        <th>crttype</th>
        <th>updorigine</th>
        <th>updby</th>
        <th>upddate</th>
        <th>updtype</th>
        <th>tocheck</th>
    </tr>
</thead>
<tfoot>
    <tr>
EOT;
$htmlpage .= makeInput('marqueWidth');
$htmlpage .= makeInput('modeleWidth');
$htmlpage .= makeInput('modeleWidth');
$htmlpage .= makeInput('modeleWidth');
$htmlpage .= makeInput('ramWidth');
$htmlpage .= makeInput('stockageWidth');
$htmlpage .= makeInput('indiceWidth');
if ($displayCategorie) {
    $htmlpage .= makeInput('categorieWidth');
}
$htmlpage .= '<th>&nbsp;</th>';  // url
$htmlpage .= makeInput('origineWidth');
$htmlpage .= makeInput('crtbyWidth');
$htmlpage .= makeInput('crtdateWidth');
$htmlpage .= makeInput('crttypeWidth');
$htmlpage .= makeInput('origineWidth');
$htmlpage .= makeInput('crtbyWidth');
$htmlpage .= makeInput('crtdateWidth');
$htmlpage .= makeInput('crttypeWidth');
$htmlpage .= makeInput('tocheckWidth');

$htmlpage .= <<<"EOT"
    </tr>
</tfoot>
<tbody>
EOT;
$evalInstance = EvaluationSm::getInstance();
foreach($rowsForTitle as $m) {
    // $ceSM = Smartphone::getInstance();
    // $ceSM->setMarque(  "".$m['marque']);
    // $ceSM->setModele(  "".$m['modele']);
    // $ceSM->setRam(     "".$m['ram']);
    // $ceSM->setStockage("".$m['stockage']) ;
    // $evaluationSmClInstance = EvaluationSm::getInstance($ceSM);
    // $evaluationSmCl         = $evaluationSmClInstance->evalSmartphone();
    // if ($evaluationSmCl->getErrMsg() == "" ) {
    //     $categorieSm            = $evaluationSmCl->getCategoriePondereAlpha();
    // }else{
    //     $categorieSm            = 'err';
    // }
    //                            calculCategorie($ramIn,    $stockageIn,        $indice, int $ponderation = 0, string $unitepardefaut='G')
    $categorieSm = $evalInstance->calculCategorie($m['ram'], $m['stockage'], $m['indice'], 0, 'G');
    $htmlpage .= '<tr>';
    $htmlpage .= '<td>'.$m['marque'].'</td>';
    $htmlpage .= '<td>'.$m['modele'].'</td>';
    $htmlpage .= '<td>'.$m['modele_ns'].'</td>';
    $htmlpage .= '<td>'.$m['modele_synonyme'].'</td>';
    $htmlpage .= '<td>'.$m['ram'].'</td>';
    $htmlpage .= '<td>'.$m['stockage']."</td>";
    $htmlpage .= '<td>'.$m['indice']."</td>";
    if ($displayCategorie) {
        $htmlpage .= '<td>'.$categorieSm['categoriePondereAlpha']."</td>";
    }
    $htmlpage .= '<td><a href="'.$m['url'].'" target="_blank">'.'go'.'</td>';
    $htmlpage .= '<td>'.$m['crtorigine']."</td>";
    $htmlpage .= '<td>'.$m['crtby'].'</td><td>'.$m['crtdate'].'</td><td>'.$m['crttype'].'</td>';
    $htmlpage .= '<td>'.$m['updorigine'].'</td>';
    $htmlpage .= '<td>'.$m['updby'].'</td><td>'.$m['upddate']."</td><td>".$m['updtype'].'</td>';
    $htmlpage .= '<td>'.$m['tocheck'].'</td>';
    $htmlpage .= '</tr>';
}

$htmlpage .= <<<'EOT'
</tbody>
</table>
</body>
</html>
EOT;

echo $htmlpage;

function makeInput($className) {
    $retour  = '<th>';
    $retour .= '  <div class="'.$className.'" style="border: 1px solid; resize: horizontal; overflow: auto;">';
    $retour .= '     <input type="text" style="width:100%">';
    $retour .= '  </div>';  
    $retour .= '</th>';
    return $retour;
}