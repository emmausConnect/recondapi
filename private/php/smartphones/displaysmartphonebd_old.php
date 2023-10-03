<?php

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';
echo "liste de la BDD<hr>";
$errmsg = "";

$dbInstance = DbManagement::getInstance();
$db = $dbInstance->openDb();
$tableName = $dbInstance->tableName('smartphones');

$sqlQuery = "SELECT * from $tableName ORDER BY title, modele, ram, stockage;";
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
    <link href="style/style.css" rel="stylesheet" type="text/css">

<style>
.key {
    font-weight: bold;
}
</style>

</head>
<body>
<table class="table table-sm table-striped" id="table">
<thead>
    <tr>
        <th>marque</th>
        <th>modèle</th>    
        <th class="font-weight: bold;">titre</th>
        <th class="font-weight: bold;">ram</th>
        <th class="font-weight: bold;">stockage</th>
        <th>indice</th>
        <th>url</th>
        <th>origine</th>
        <th>crtby</th>
        <th>crtdate</th>
        <th>crttype</th>
        <th>updby</th>
        <th>upddate</th>
        <th>updtype</th>
    </tr>
</thead>
<tbody>
EOT;

foreach($rowsForTitle as $m) {
    $htmlpage .= '<tr>';
    $htmlpage .= '<td>'.$m['marque'].'</td>';
    $htmlpage .= '<td>'.$m['modele'].'</td>';
    $htmlpage .= '<td>'.$m['title'].'</td>';
    $htmlpage .= '<td>'.$m['ram'].'</td>';
    $htmlpage .= '<td>'.$m['stockage']."</td><td>".$m['indice']."</td>";
    $htmlpage .= '<td><a href="'.$m['url'].'" target="_blank">'.'cliquez ici'.'</td>';
    $htmlpage .= '<td>'.$m['origine']."</td>";
    $htmlpage .= '<td>'.$m['crtby'].'</td><td>'.$m['crtdate'].'</td><td>'.$m['crttype'].'</td>';
    $htmlpage .= '<td>'.$m['updby']."</td><td>".$m['upddate']."</td><td>".$m['updtype']."</td>";
    $htmlpage .= '</tr>';
}

$htmlpage .= <<<'EOT'
</tbody>
</table>

<script type="module">
import {DataTable} from "https://cdn.jsdelivr.net/npm/simple-datatables/dist/module.js"
window.dt = new DataTable("table", {
    searchItemSeparator : ',',
    perPageSelect: [5, 10, 15, ["All", -1]],
    columns: [
        {
            select: 2,
            sortSequence: ["desc", "asc"]
            
        },
        {
            select: 3,
            sortSequence: ["desc"]
        },
        {
            select: 4,
            cellClass: "green",
            headerClass: "red"
        }
    ],
    tableRender: (_data, table, type) => {
        if (type === "print") {
            return table
        }
        const tHead = table.childNodes[0]
        const filterHeaders = {
            nodeName: "TR",
            childNodes: tHead.childNodes[0].childNodes.map(
                (_th, index) => ({nodeName: "TH",
                    childNodes: [
                        {
                            nodeName: "INPUT",
                            attributes: {
                                class: "datatable-input",
                                type: "search",
                                "data-columns": `[${index}]`
                            }
                        }
                    ]})
            )
        }
        tHead.childNodes.push(filterHeaders)
        return table
    }
})

</script>

</body>
</html>
EOT;

echo $htmlpage;
