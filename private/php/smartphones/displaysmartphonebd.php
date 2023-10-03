<?php

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
echo "liste de la BDD<hr>";
$errmsg = "";

$dbInstance = DbManagement::getInstance();
$db = $dbInstance->openDb();
$tableName = $dbInstance->tableName('smartphones');

$sqlQuery = "SELECT * from $tableName ORDER BY marque, modele, ram, stockage;";
$stmt = $db->prepare($sqlQuery);
$stmt->execute([]);
$rowsForTitle = [];
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) != 0) {
    foreach($rows as $row) {
        array_push($rowsForTitle, $row);
    }
}else{
    $errmsg = "table vide";
}

$htmlpage ="";
$htmlpage .= <<<"EOT"

<!DOCTYPE html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>

<link rel="stylesheet" href="style/stylesm.css" />

<script>
    $(document).ready( function () {
        let tableDb = $('#tableDb').DataTable( {
            searching: true,
            ordering:  true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "All"]],
            "pageLength": 10
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
                            column.search(input.value).draw();
                        }
                    });
                }
            }
        });

    } );
    //var table = $('#example').DataTable();
 


</script>

</head>
<body>
<table class="table table-sm table-striped" id="tableDb">
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
    $htmlpage .= '<td>'.$m['updorigine']."</td>";
    $htmlpage .= '<td>'.$m['updby']."</td><td>".$m['upddate']."</td><td>".$m['updtype']."</td>";
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
    $retour  = "<th>";
    $retour .= '  <div class="'.$className.'" style="border: 1px solid; resize: horizontal; overflow: auto;">';
    $retour .= '     <input type="text" style="width:100%">';
    $retour .= '  </div>';  
    $retour .= '</th>';
    return $retour;
}