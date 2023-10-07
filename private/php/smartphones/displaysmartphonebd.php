<?php

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';

$errmsg = "";

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
// if (count($rows) != 0) {
//     foreach($rows as $row) {
//         array_push($rowsForTitle, $row);
//     }
// }else{
//     $errmsg = "table vide";
// }

$htmlpage ="";
$htmlpage .= <<<"EOT"

<!DOCTYPE html>
<head>

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
            //let input = document.createElement('input');
            let children1 = column.footer().children;
            if (children1.length >0) {
                let firstLevelchildren = column.footer().children[0];
                let children2 = firstLevelchildren.children;
                if (children1.length >0) {
                    let input = children2[0];
                    input.placeholder = 'liste de mots';
                    //input.classList.add('searchInput');
                    //column.footer().replaceChildren(input);

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
            colNbrs = [6, 7, 8, 9];
        }
        if (colType == 'upd') {
            colNbrs = [10, 11, 12, 13];
        }
        let tableDb = $('#tableDb').DataTable();
        colNbrs.forEach(col => {
            tableDb.column(col).visible(! tableDb.column(col).visible());
        });
    }

</script>

</head>
<body>
<h1>liste de la BDD</h1>
<button onclick="toggle('crt');">Afficher/cacher les colonnes crt</button>
<button onclick="toggle('upd');">Afficher/cacher les colonnes upd</button>
Pour n'afficher que les <b>cellules vides</b>, mettre <b>2 espaces</b> '&nbsp;&nbsp;'. Pour n'afficher que les <b>cellules <u>non</u> vides</b>, mettre <b>1 espaces</b> ' '.
Pour un texte exact, mettre entre guillemets "galaxy A01".
<table  id="tableDb" class="table table-sm table-striped" style="width:50%">
<thead>
    <tr>
        <th>marque</th>
        <th>modèle</th>    
        <th style="font-weight: bold;">ram</th>
        <th style="font-weight: bold;">stockage</th>
        <th>indice</th>
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
$htmlpage .= makeInput('ramWidth');
$htmlpage .= makeInput('stockageWidth');
$htmlpage .= makeInput('indiceWidth');
$htmlpage .= '<th>&nbsp;</th>';
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

foreach($rowsForTitle as $m) {
    $htmlpage .= '<tr>';
    $htmlpage .= '<td>'.$m['marque'].'</td>';
    $htmlpage .= '<td>'.$m['modele'].'</td>';
    $htmlpage .= '<td>'.$m['ram'].'</td>';
    $htmlpage .= '<td>'.$m['stockage']."</td><td>".$m['indice']."</td>";
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