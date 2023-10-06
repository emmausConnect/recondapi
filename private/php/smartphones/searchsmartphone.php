<?php
declare(strict_types=1);

$path_private_php = $g_contexte_instance->getPath('private/php');
require_once $path_private_php .'/pageheaderhtml.php';

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';

require_once 'utilsm.php';

$supressSpaces = "on";
$marque        = trim(getPostValue('marque',' '));
$modele        = trim(getPostValue('modele',' '));
$ram           = trim(getPostValue('ram',' '));
$stockage      = trim(getPostValue('stockage',' '));
$incsv         = trim(getPostValue('incsv'," "));
if (count($_POST) != 0) {
    $supressSpaces = getPostValue('supressSpaces',"off");
}
$supressSpacesBool = ($supressSpaces == "on"?True : False);
$errmsg = "";
$errInForm = false;
$simulation = false;
$logmsg = "";
$marqueMsg         = "";
$modeleMsg        = "";
$ramMsg           = "";
$stockageMsg      = "";
$indice = 0;
$os     = "";
$url    = "";
//$imei = "";
$smRow = [];
$smRowFound = False;
$rowsForMarqueLikeModel = [];
$modelesForMarque = [];
$modelesForMarqueRamStk = [];
$listeMarque = [];
$helpHtml = ""; // texte qui sera placé dans la div_help

if ($marque == "") {
    if ($incsv != null) {
        $explodeCsv = explode(",", $incsv);
        if (count($explodeCsv) == 4) {
            $marque   = $explodeCsv[0] ;
            $modele   = $explodeCsv[1] ;
            $ram      = $explodeCsv[2] ;
            $stockage = $explodeCsv[3] ;
        }else{
            $errmsg .= "<hr>le champ csv contient " .count($explodeCsv). " postes. Il devrait en contenir 4. Il y a peut être des virgules dans les valeurs :";
            $tempcsv = str_replace(",", '<span style="background-color: red;">,</span>', $incsv );
            $errmsg .= '<br><span style="color:black">' .$tempcsv. '</span><hr>';
        }
    }
}

if ($marque != removeMultipleSpace($marque)) {
    $marqueMsg = 'contient des espaces en double';
}
if ($modele != removeMultipleSpace($modele)) {
    $modeleMsg = 'contient des espaces en double';
}

if (! ctype_digit($ram)) {
    $ramMsg = '<span style="color: red">ne doit contenir que des chiffres</span>';
    $errmsg .= "<br>Ram ne doit contenir que des chiffres";
    $errInForm = true;
}
if (! ctype_digit($stockage)) {
    $stockageMsg = '<span style="color: red">ne doit contenir que des chiffres</span>';
    $errmsg   .= "<br>Stockage ne doit contenir que des chiffres";
    $errInForm = true;
}
if (! $errInForm) {
    if (! $errInForm && $marque != null && $marque != "") {
        if ($marque != "EMMAUSCONNECT") {
            $dbInstance = DbManagement::getInstance();
            $db = $dbInstance->openDb();
            $tableName = $dbInstance->tableName('smartphones');
            $sqlQuery = "SELECT * from $tableName 
                where marque=:marque and modele=:modele and ram=:ram and stockage=:stockage;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->execute([
                'marque'    =>formatKey($marque,$supressSpacesBool),
                'modele'   => formatKey($modele,$supressSpacesBool),
                'ram'      => formatKey($ram,$supressSpacesBool),
                'stockage' => formatKey($stockage,$supressSpacesBool)
                ]);
            $smRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($smRow) {
                $smRowFound = true;
                $indice      = $smRow['indice'];
                $os          = $smRow['os'];
                $url         = $smRow['url'];
                $origine     = $smRow['crtorigine'];
                $note        = calculCategorie($ram, $stockage, $indice );
            }else{
                $errmsg .= "Il n'y a aucun modèle dans la base avec les critères spécifiés<br>.";
                $errmsg .= "Pensez à cocher la case 'Supprimer les espaces en trop<br>";
                $errmsg .= "pensez aussi à changer les chiffres romains en chiffres arabes.";
                $helpHtml .= $errmsg;
            }
        }else{
            // on utilise un indice en constante qui est dans le modèle
            $simulation = true;
            $smRowFound = true;
            $indice = $modele;
            $smRow['marque']   = $marque;
            $smRow['modele']   = $modele;
            $smRow['ram']      = $ram;
            $smRow['stockage'] = $stockage;
            $smRow['indice']   = $modele;
            $smRow['os'] = '';
            $smRow['url'] = '';
            $smRow['crtorigine'] = '';
            $smRow['crtby'] = '';
            $smRow['crtdate'] = '';
            $smRow['crttype'] = '';
            $smRow['updorigine'] = '';
            $smRow['updby'] = '';
            $smRow['upddate'] = '';
            $smRow['updtype'] = '';

            $note  = calculCategorie($ram, $stockage, $indice );
        }

    }else{
        $errmsg .= "<br>Renseignez ou corrigez tous les champs";
    }
    $colorErrMarque = "";
    $colorErrModele = "";
    $colorErrRam = "";
    $colorErrStockage = "";
    if($smRowFound) {
        // un smartphone a été trouvé
        // check si les clefs de la ligne lue sont exactement égale aux critères entrés
        if ($marque != $smRow['marque']) {
            $colorErrMarque = "red";
        }

        if ($modele != $smRow['modele']) {
            $colorErrModele = "red";
        }

        if ($ram != $smRow['ram']) {
            $colorErrRam = "red";
        }
        
        if ($stockage != $smRow['stockage']) {
            $colorErrStockage = "red";
        }

    }else{
        // enreg non trouvé
        // recherche des enregs sur la marque et premier mot de modèle & ram & srockage
        $debutModele = '';
        if ($modele != '') {
            $debutModele = trim(strtok($modele.' ', ' '));
        }
        $sqlQuery  = "SELECT * FROM $tableName ";
        $sqlQuery .= " where marque = :marque and modele like :debutModele "; // and ram = :ram and stockage = :stockage ";
        $sqlQuery .= " ORDER BY modele, ram, stockage;";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque' =>formatKey($marque,true),
            'debutModele' => '%'.$debutModele.'%',
        //    'ram' =>$ram,
        //    'stockage' =>$stockage
            ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) != 0) {
            foreach($rows as $row) {
                array_push($rowsForMarqueLikeModel, $row);
            }
        }

        //========= recherche des enregs sur la marque ram stockage
        $sqlQuery = "SELECT DISTINCT * FROM $tableName where marque =:marque and ram =:ram and stockage =:stockage ORDER BY modele; ";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque' =>formatKey($marque,$supressSpacesBool),
            'ram' =>formatKey($ram,$supressSpacesBool),
            'stockage' =>formatKey($stockage,$supressSpacesBool)
            ]);
        $modelesForMarqueRamStk = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //========= recherche des enregs sur la marque
        $sqlQuery = "SELECT DISTINCT modele FROM $tableName where marque =:marque ORDER BY modele; ";
        //$sqlQuery = "SELECT DISTINCT * FROM $tableName where marque =:marque and ram =:ram and stockage =:stockage ORDER BY modele; ";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque' =>formatKey($marque,$supressSpacesBool)
            ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) != 0) {
            // il existe des modèle pour cette marque
            foreach($rows as $row) {
                array_push($modelesForMarque, $row['modele']);
            }
        }

        // ===== la marque n'a pas été trouvée, recherche des marques existante
        $sqlQuery = "SELECT DISTINCT marque FROM $tableName ORDER BY marque; ";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $row) {
            array_push($listeMarque, $row['marque']);
        }
       
    }
}
$cvt = 'cvtTextToCsv';
$htmlentities = 'cvtToHtmlentities';
$supressSpaces = ($supressSpacesBool ? "checked" : "");
$htmlpage  = getHtmlHead();
$htmlpage .= <<<"EOT"
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
  
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>

<link rel="stylesheet" href="style/stylesm.css" />
<script>
    $(document).ready( function () {
    if (document.getElementById('sm_modele_table') != null) {
        let table_sm_modele_table = $('#sm_modele_table').DataTable( {
            searching: true,
            ordering:  true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "All"]],
            "pageLength": 10
        } );

        table_sm_modele_table.columns()
            .every(function () {
                let column = this;
                let title = column.footer().textContent;

                // Create input element
                //let input = document.createElement('input');
                let children = column.footer().children;
                let columnName = column.header().innerText;
                if (children.length >0) {
                    let input = column.footer().children[0];
                    input.placeholder = title;
                    if (columnName == 'Ram') {
                        column.search('^'+document.getElementById('ram').value+'$', true, false).draw();
                        input.value = document.getElementById('ram').value;
                    }
                    if (columnName == 'Stockage') {
                        column.search('^'+document.getElementById('stockage').value+'$', true, false).draw();
                        input.value = document.getElementById('stockage').value;
                    }
                    // Event listener for user input
                    input.addEventListener('keyup', () => {
                        if (column.search() !== this.value) {
                            column.search(input.value).draw();
                        }
                    });

                }
        });
        $('#table').DataTable().search("value").draw();


    }

    // =================
    if (document.getElementById('sm_modele_ram_stk_table') != null) {
        let sm_modele_ram_stk_table = $('#sm_modele_ram_stk_table').DataTable( {
            searching: true,
            ordering:  true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "All"]],
            "pageLength": 10
        } );

        sm_modele_ram_stk_table.columns()
            .every(function () {
                let column = this;
                let title = column.footer().textContent;
                let children = column.footer().children;
                if (children.length >0) {
                    let input = column.footer().children[0];
                    input.placeholder = title;
                    //input.classList.add('searchInput');
                    //column.footer().replaceChildren(input);

                    // Event listener for user input
                    input.addEventListener('keyup', () => {
                        if (column.search() !== this.value) {
                            column.search(input.value).draw();
                        }
                    });
                }
            });
    }

    // =================
    if (document.getElementById('sm_marque_table') != null) {
        let table_sm_marque_table = $('#sm_marque_table').DataTable( {
            searching: true,
            ordering:  true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "All"]],
            "pageLength": 10
        } );

        table_sm_marque_table.columns()
            .every(function () {
                let column = this;
                let title = column.footer().textContent;
                let children = column.footer().children;
                if (children.length >0) {
                    let input = column.footer().children[0];
                    input.placeholder = title;
                    //input.classList.add('searchInput');
                    //column.footer().replaceChildren(input);

                    // Event listener for user input
                    input.addEventListener('keyup', () => {
                        if (column.search() !== this.value) {
                            column.search(input.value).draw();
                        }
                    });
                }
            });
    }
} );
</script>

<script>
    function setDemoValues() {
        document.getElementById('marque').value = 'Samsung';
        document.getElementById('modele').value = 'Galaxy S3';
        document.getElementById('ram').value = '1';
        document.getElementById('stockage').value = '16';

    }

    function displayDetailModele(bouton, marque, modele) {
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                // Typical action to be performed when the document is ready:
                //let elem = document.querySelector('[data-title="td_'+ title   +'"]');
                let list = JSON.parse(xhttp.responseText);
                let data = list['data'];
                let html = '';
                html += '<thead>';
                html += "<tr><th>modele</th><th>ram</th><th>stockage</th><th>indice</th><th>url</th><th>choix</th></tr>";
                html += '</thead>';
                html += '<tbody>';
                data.forEach(sm => {
                    html += '<tr>';
                    html += '<td>' +sm['modele']  + '</td>';
                    html += '<td>' +sm['ram']     + '</td>';
                    html += '<td>' +sm['stockage']+ '</td>';
                    html += '<td>' +sm['indice']  + '</td>';
                    html += '<td>';

                    if(sm['url'] != '') {          
                        html += '<a href="' +sm['url']+ '">voir</a>';
                    }
                    

                    html += '</td>';
                    html += '<td>';
                    html +=   '<button title="Utiliser ce maodèle de smartphone"';
                    html +=   'onclick="setDuplicationModal(' +'\'' +marque+ '\', \'' +sm['modele']+ '\', \'' +sm['ram']+ '\', \'' +sm['stockage']+ '\', \'' +sm['indice']+ '\', \'' +sm['categorie'] +'\')">ok</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody>';
                let table = document.createElement('table');
                table.innerHTML = html;
                bouton.replaceWith(table);
            }
        }
        xhttp.open("GET", "exgetsmartphoneslist.php?marque=" +marque+ "&modele=" +modele, true);
        xhttp.send();
    }


    // demande les info pour créer un nouveau smartphone, sans copie
    function displayAddInDb() {
        const modalPrefix = 'addindb';
        let smToInsertTable = document.getElementById(modalPrefix+'_saisie');
        let html = '<table><tbody>'
        html += '<tr><td>Marque</td>   <td>';
        html +=    '<input type="text" id="' +modalPrefix+ '_marque" name="' +modalPrefix+ '_marque" ';
        html +=       'value="' +document.getElementById('marque').value+ '" >';
        html +=   '</td></tr>';

        html += '<tr><td>Modèle</td>   <td>';
        html +=    '<input type="text" id="' +modalPrefix+ '_modele" name="' +modalPrefix+ '_modele" ';
        html +=       'value="' +document.getElementById('modele').value+ '" >';
        html +=   '</td></tr>';

        html += '<tr><td>Ram</td>      <td>';
        html +=    '<input type="number" min="0" step="1" id="' +modalPrefix+ '_ram" name="' +modalPrefix+ '_ram" ';
        html +=       'value="' +document.getElementById('ram').value+ '" >';
        html +=   '</td></tr>';
    
        html += '<tr><td>Stockage</td> <td>';
        html +=    '<input type="number" min="0" step="1" id="' +modalPrefix+ '_stockage" name="' +modalPrefix+ '_stockage" ';
        html +=       'value="' +document.getElementById('stockage').value+ '" >';        
        html +=   '</td></tr>';

        html += '<tr><td>Indice</td><td>';
        html +=    '<input type="number" min="0" step="1" id="' +modalPrefix+ '_indice" name="' +modalPrefix+ '_indice" >';
        html += '</td></tr>';

        html += '<tr><td>OS</td><td>'
        html +=    '<input type="text" id="' +modalPrefix+ '_os" name="' +modalPrefix+ '_os" >';
        html += '</td></tr>';

        html += '<tr><td>URL</td><td>';
        html +=    '<input type="text" id="' +modalPrefix+ '_url" name="' +modalPrefix+ '_url" >';
        html += '</td></tr>';

        html += '<tr><td>Votre nom</td><td>';
        html +=    '<input type="text" id="' +modalPrefix+ '_username" name="' +modalPrefix+ '_username" >';
        html += '</td></tr>';
        html += '</tbody></table>'
        html += '<button onclick="addInDb()">ajouter</button><br>';
        smToInsertTable.innerHTML = html;
        openModal(modalPrefix);

    }

    /**
     * Ajoute un smartphone par copie
     *
     * @return void
     */
    function copyInDb() {
        const modalPrefix = 'chooseSm';
        let userName = document.getElementById(modalPrefix+'_username').value;
        if (userName == "" ) {
            document.getElementById(modalPrefix+'_msg').innerText = "merci de saisir votre nom";
            return;
        }
        let dataArray = [];
        dataArray['action']    = 'copy';
        dataArray['marque']    = document.getElementById('marque').value;
        dataArray['modele']    = document.getElementById('modele').value;
        dataArray['ram']       = document.getElementById('ram').value;
        dataArray['stockage']  = document.getElementById('stockage').value;
        dataArray['marque2']   = document.getElementById(modalPrefix+'_marque2').innerText;
        dataArray['modele2']   = document.getElementById(modalPrefix+'_modele2').innerText;
        dataArray['ram2']      = document.getElementById(modalPrefix+'_ram2').innerText;
        dataArray['stockage2'] = document.getElementById(modalPrefix+'_stockage2').innerText;
        dataArray['username']  = document.getElementById(modalPrefix+'_username').value;
        realCopyInDb(dataArray, modalPrefix);
    }

    /**
     * Ajoute un smartphone PAS par copie
     *
     * @return void
     */
    function addInDb() {
        const modalPrefix = 'addindb';
        document.getElementById(modalPrefix+'_msg').innerText = "";
        let userName = document.getElementById(modalPrefix+'_username').value;
        if (userName == "" ) {
            document.getElementById(modalPrefix+'_msg').innerText = "merci de saisir votre nom";
            return;
        }

        let indice = document.getElementById(modalPrefix+'_indice').value;
        if (indice == "" ) {
            document.getElementById(modalPrefix+'_msg').innerText = "merci de saisir un indice";
            return;
        }
        let dataArray = [];
        dataArray['action']    = 'insert';
        dataArray['marque']    = document.getElementById(modalPrefix+'_marque').value;
        dataArray['modele']    = document.getElementById(modalPrefix+'_modele').value;
        dataArray['ram']       = document.getElementById(modalPrefix+'_ram').value;
        dataArray['stockage']  = document.getElementById(modalPrefix+'_stockage').value;
        dataArray['indice']    = document.getElementById(modalPrefix+'_indice').value;
        dataArray['os']        = document.getElementById(modalPrefix+'_os').value;
        dataArray['url']       = document.getElementById(modalPrefix+'_url').value;
        dataArray['username']  = document.getElementById(modalPrefix+'_username').value;
        realCopyInDb(dataArray, modalPrefix);
    }

    function realCopyInDb(dataArray, modalPrefix) {
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                let reponse = JSON.parse(xhttp.responseText);
                if (reponse['status'] == '0') {
                    displayMsg('Erreur : <br>'+reponse['msg']);
                }else{
                    if (modalPrefix != "") {
                        closeModal(modalPrefix);
                    }
                    displayMsg('smartphone ajouté');
                    document.getElementById("form_search").submit();
                }
            }
        }

        xhttp.open("POST", "exaddindb.php", true);
        let data = new FormData();
        for (let elem in dataArray) {
            data.append(elem, dataArray[elem]);
        }
        xhttp.send(data);
    }

    /**
     * categorie2 est l tableau avec le resultata du calcul catégorie
     */
    function setDuplicationModal(marque2, modele2, ram2, stockage2, indice2, categorie2) {
        const modalPrefix = 'chooseSm';
        let   tableDiv = document.getElementById(modalPrefix + '_tab');
        const marque   = document.getElementById('marque').value;
        const modele   = document.getElementById('modele').value;
        const ram      = document.getElementById('ram').value;
        const stockage = document.getElementById('stockage').value;

        // check si les valeurs sont identiques
        let colorMarque = "#000000";
        if (marque != marque2) {colorMarque = "#FF0000"}
        let colorModele = "#000000";
        if (modele != modele2) {colorModele = "#FF0000"}
        let colorRam = "#000000";
        if (ram != ram2) {colorRam = "#FF0000"}
        let colorStockage = "#000000";
        if (stockage != stockage2) {colorStockage = "#FF0000"}

        let html = '<table>';
        html += '<thead>';
        html += '<tr><th>&nbsp;</th><th>Marque</th><th>Modèle</th><th>Ram</th><th>Stockage</th><th>Indice</th><th>Catégorie</th></tr>';
        html += '</thead>';
        html += '<tbody>';
        html += '<tr>';
        html += '<td>cherché</td>';
        html += '<td class="marqueWidth"><b>'+marque+'</b></td>';
        html += '<td class="modeleWidth">'+modele+'</td>';
        html += '<td class="ramWidth"><b>'+ram+'</b></td>';
        html += '<td class="stockageWidth"><b>'+stockage+'</b></td>';
        html += '<td>&nbsp;</td><td>&nbsp;</td>';
        html += '</tr>';
        
        html += '<tr>';
        html += '<td>choisi </td>';
        html += '<td id="'+modalPrefix+'_marque2"   style="color:' +colorMarque+ '">'+marque2+'</td>';
        html += '<td id="'+modalPrefix+'_modele2"   style="color:' +colorModele+ '">'+modele2+'</td>';
        html += '<td id="'+modalPrefix+'_ram2"      style="color:' +colorRam+ '">'+ram2+'</td>';
        html += '<td id="'+modalPrefix+'_stockage2" style="color:' +colorStockage+ '">'+stockage2+'</td>';
        html += '<td id="'+modalPrefix+'_indice2">'+indice2+'</td>';
        html += '<td style="background-color: #74992e">'+categorie2[4]+'</td></td>';
    
        html += '</tbody>';
        html += '</table>';
        tableDiv.innerHTML = html;
        let   buttonDiv = document.getElementById(modalPrefix + '_button');
        if (marque != marque2 || ram != ram2 || stockage != stockage2) {
            
            buttonDiv.innerHTML = '<span style="color: red">Copie impossible : marque, ram et stockage doivent être identiques</span><br>';
        }else{
            let innerHTML = 'Voulez-vous ajouter ce nouveau modèle de '+marque+' dans la base<br>';
            innerHTML    += 'votre nom : <input type="text" id="'+modalPrefix+'_username"><br>';
            innerHTML    += '<button onclick="copyInDb()">ajouter</button><br>';
            buttonDiv.innerHTML = innerHTML;
        }

        openModal(modalPrefix)
    }

    function displayMsg(msg) {
        const modalPrefix = 'msg';
        let msgDiv   = document.getElementById(modalPrefix + '_text');
        msgDiv.innerText = msg;
        openModal(modalPrefix)
    }

    function openModal(modalPrefix) {
        const modal = document.getElementById(modalPrefix + '_div');
        // Get the <span> element that closes the modal
        //const span = document.getElementsByClassName("close")[0];
        const span = document.getElementById(modalPrefix + '_close');

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
        closeModal(modalPrefix);
        }
        modal.style.display = "block";
    }

    function closeModal(modalPrefix) {
        const modal = document.getElementById(modalPrefix + '_div');
        modal.style.display = "none";
    }
</script>
EOT;
$htmlpage .= '</head>';
$htmlpage .= <<<"EOT"
<body>
Cet écran n'est pas celui qui sera mis en production. C'est une maquette pour tester et mieux appréhender le besoin.<br>
Les champs sont préremplis à l'affichage afin de ne pas avoir à les ressaisir à chaque test.
<div id="div_00" style="display: flex;">
<div id="div_01" style="border:1px solid;padding: 10px;width: 700px; background-color: #dddddd;">
La recherche se fait sans tenir compte des majuscules/minuscules.
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<a href="https://docs.google.com/document/d/1yQG_MQC-HUv2MqzjF6HPVwcgJ1D30UhvhWEiTF0Z6TQ" target="_blank">aide</a>
<br>
Les espaces/blancs en début et fin de critère sont supprimés.
<button onclick="setDemoValues()">set demo values</button>
<br>
<form id="form_search" action="exsearchsmartphone.php"  method="post">
<div style="border:1px solid;padding: 10px;width: 600px;" class="input">
<label class="shortLabel" for="marque">Marque</label>
<input type="text" id="marque" name="marque" value="{$htmlentities($marque)}">&nbsp;$marqueMsg<br>
<label class="shortLabel" for="modele">Modèle</label>
<input type="text" id="modele" name="modele" value="{$htmlentities($modele)}">&nbsp;$modeleMsg<br>
<label class="shortLabel" for="ram">Ram</label>
<input type="text" id="ram" name="ram" value="{$htmlentities($ram)}">&nbsp;$ramMsg<br>
<label class="shortLabel" for="Stockage">stockage</label>
<input type="text" id="stockage" name="stockage" value="{$htmlentities($stockage)}">&nbsp;$stockageMsg<br>
</div>
<div style="border:1px solid;padding: 10px;width: 600px;" class="input">
Utilisé si le champ "titre" n'est pas renseigné.<br>
<label class="shortLabel" for="incsv">csv</label>
<input type="text" id="incsv" name="incsv" size="60" value="{$htmlentities($incsv)}"><br>
(séparateur = virgule. Ne marche pas si les textes contiennent une virgule
</div>
<div style="border:1px solid;padding: 10px;width: 600px;" class="input">
<label for="supressSpaces" class="longLabel">Supprimer les espaces en trop des textes :</label>
<input type="checkbox" id="supressSpaces" name="supressSpaces" $supressSpaces />
</div>
<br>
<input type="submit" value="Chercher">
</form>
</div> <!-- div_01 -->
EOT;
if ($helpHtml != "") {
$htmlpage .= <<<"EOT"
    <div id="div_help" style="border:1px solid;padding: 10px;width: 700px; background-color: #eeeeee;">
    $helpHtml<hr>
    Ce smartphone existe peut-être dans la base avec une orthographe légèrement différente.<br>
    3 extractions vous sont présentées ci-dessous :<br>
        <br>
        <b>1) Ceux de la marque recherchée dont le modèle contient le 1er mot du modèle que vous avez indiqué</b><br>
          - cliquez sur "<u><b>ok</b></u>" pour sélectionner celui qui correspond à celui recherché.<br>
          - logiquement, il devrait avoir les même taille de ram et de stockage<br>
           <br>
        <b>2) Tous les modèles de la marque recherchée</b><br>
        Cliquez sur "<u><b>afficher le détail</b></u>", cela affichera les combinaisons ram/stockage connues<br>
        puis cliquez sur "<u><b>ok</b></u>" comme pour le point "1".<br>
        <br>
        <b>3) la liste des marques contenues dans la base</b><br>
        Vérifier qu'il n'y a pas une erreur de votre part dans la saisie de la marque<br>
        <br>
        La colonne "URL" permet d'afficher la page kimovil du smartphone. Le visuel peut aider à valider le choix.
        <hr>
        Vous ne trouvez pas votre smartphone dans notre base, cherchez le sur le site <a href="https://www.kimovil.com/fr/comparatif-smartphone" target="_blank">kimovil</a><br>
        puis ajoutez le à la base de données <button onclick="displayAddInDb()">ajouter</button>
    </div> <!-- div_help -->
EOT;
}
$htmlpage .= <<<"EOT"
</div> <!-- div_00 -->
<hr>
EOT;
if ($errmsg != '') {
    $htmlpage .= '<span style="color:red;">'.$errmsg.'</span><br>';
}

//=========================================================
//=== smartphone trouvé
//=========================================================
if($smRowFound) {
    $htmlpage .= <<<"EOT"
<div id="div_02"style="border:1px solid;padding: 10px;width: 700px; background-color: #aaaaaa;"> 
<h2 style="text-align: center;">Résultat</h2>
<p  style="text-align: center;">Les critères utilisés pour la recherche sont en rouge quand ils sont différents de ceux que vous avez saisis<p>.

<table style=" margin: 0 auto;">
<thead>
<tr><th>&nbsp;</th><th>Résultat du calcul</th><th>&nbsp;</th></tr>
</thead>
<tbody>
<tr><td>Ram</td>      <td style="text-align: right;">{$htmlentities("".$smRow['ram'])}</td>     <td style="text-align: right;">{$note[0]}</td></tr>
<tr><td>Stockage</td> <td style="text-align: right;">{$htmlentities("".$smRow['stockage'])}</td><td style="text-align: right;">{$note[1]}</td></tr>
<tr><td>Indice</td>   <td style="text-align: right;">{$htmlentities("".$smRow['indice'])}</td>  <td style="text-align: right;">{$note[2]}</td></tr>
<tr><td>Total</td>    <td style="text-align: right;">&nbsp;</td>                                <td style="text-align: right;">{$note[3]}</td></tr>
<tr><td><b>Catégorie</b></td><td>&nbsp;</td>                                                           <td style="text-align: right;"><b>{$note[4]}</b></td></tr>
</tbody></table>
<br>
EOT;

if ($simulation) {
    $htmlpage .= "Il s'agit d'une simulation, les données ne viennent pas de la BDD";
}else{
    $htmlpage .= <<<"EOT"
<table style=" margin: 0 auto;">
<thead>
<tr><th>&nbsp;</th><th>Détail de la BDD</th><th>&nbsp;</th></tr>
</thead>
<tbody>
<tr><td>&nbsp;</td><td><b>Critères utilisés</b></td><td>&nbsp;</td></tr>
<tr><td>Marque</td><td style="color:$colorErrMarque;">{$htmlentities("".$smRow['marque'])}</td><td>&nbsp;</td></tr>
<tr><td>Modele</td><td style="color:$colorErrModele;">{$htmlentities("".$smRow['modele'])}</td><td>&nbsp;</td></tr>
<tr><td>Ram</td><td style="color:$colorErrRam;">{$htmlentities("".$smRow['ram'])}</td><td>&nbsp;</td></tr>
<tr><td>Stockage</td><td style="color:$colorErrStockage;">{$htmlentities("".$smRow['stockage'])}</td><td>&nbsp;</td></tr>

<tr><td>&nbsp;</td><td><b>Valeurs trouvées dans la base</b></td><td>&nbsp;</td></tr>
<tr><td>Indice</td><td>{$htmlentities("".$indice)}</td><td>&nbsp;</td></tr>
<tr><td>OS</td><td>{$htmlentities($os)}</td><td>&nbsp;</td></tr>
<tr><td>URL</td><td><a href="$url"  target="_blank">{$htmlentities($url)}</a></td><td>&nbsp;</td></tr>
<tr><td>Crt origine</td><td>{$htmlentities("".$smRow['crtorigine'])}</td><td>&nbsp;</td></tr>
<tr><td>Crt par</td><td>{$htmlentities("".$smRow['crtby'])}</td><td>&nbsp;</td></tr>
<tr><td>Crt le</td><td>{$htmlentities("".$smRow['crtdate'])}</td><td>&nbsp;</td></tr>
<tr><td>Crt type</td><td>{$htmlentities("".$smRow['crttype'])}</td><td>&nbsp;</td></tr>
<tr><td>Maj origine</td><td>{$htmlentities("".$smRow['updorigine'])}</td><td>&nbsp;</td></tr>
<tr><td>Maj par</td><td>{$htmlentities("".$smRow['updby'])}</td><td>&nbsp;</td></tr>
<tr><td>Maj le</td><td>{$htmlentities("".$smRow['upddate'])}</td><td>&nbsp;</td></tr>
<tr><td>Maj type</td><td>{$htmlentities("".$smRow['updtype'])}</td><td>&nbsp;</td></tr>
</tbody></table>
EOT;
}
$htmlpage .= <<<"EOT"

csv<br>
<textarea style="width:100%">
{$cvt("".$smRow['marque'])}\t{$cvt("".$smRow['modele'])}\t$ram\t$stockage\t$indice\t$note[4]\t{$cvt($os)}\t{$cvt($url)}
</textarea>
<hr>
EOT;
$htmlpage .= getPlagesAsTable();
$htmlpage .= '</div>';
}else{
    //=========================================================
    //=== smartphone NON trouvé
    //=========================================================
    if (! $errInForm) {
        // la recherche a échouée affichage des modèles 
        $marqueGrey = setSpaceGrey($marque);
        $htmlpage .= '<hr><h3>Smartphones de la marque <span style="text-decoration: underline;">'.$marque.'</span>';
        //$htmlpage .= ', ram = <span style="text-decoration: underline;">'.$ram.'</span> et';
        //$htmlpage .= ' et stockage = <span style="text-decoration: underline;">'.$stockage.'</span>';
        $htmlpage .= ' dont le modèle contient <span style="text-decoration: underline;">'.$debutModele.'</span>';
        $htmlpage .= '</h3>';
        if (count($rowsForMarqueLikeModel) == 0) {
            $htmlpage .= "... il n'y a aucun smartphone dans la base sur ce seul critère";
        }else{
            //=========================================================
            //=== Smartphones de la marque
            //=========================================================
            $htmlpage .= '<div id="div_03" style="width:1000px; border-style: solid; border-width: 1px;">';
            $htmlpage .= '<table id="sm_modele_table" style="width:820px">';
            $htmlpage .= '<thead>';
            $htmlpage .= "<tr><th>Marque</th><th>Modele</th><th>Ram</th><th>Stockage</th><th>Indice</th><th>Catégorie</th><th>URL</th><th>Choix</th></tr>";
            $htmlpage .= '</thead>';
            $htmlpage .= '<tfoot>';
            $htmlpage .= '<tr>';
            $htmlpage .= '<th><input class="marqueWidth"></th>';
            $htmlpage .= '<th><input class="modeleWidth"></th>';
            $htmlpage .= '<th><input class="ramWidth"></th>';
            $htmlpage .= '<th><input class="stockageWidth"></th>';
            $htmlpage .= '<th><input class="indiceWidth"></th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '</tr>';
            $htmlpage .= '</tfoot>';
            $htmlpage .= '<tbody>';
            foreach($rowsForMarqueLikeModel as $m) {
                $note      = calculCategorie($m['ram'], $m['stockage'], $m['indice'] );
                $htmlpage .= '<tr>';
                $htmlpage .= "<td>".htmlentities($m['marque'])."</td>";
                $htmlpage .= '<td class="marque">'.htmlentities($m['modele']).'</td>';
                $htmlpage .= '<td style="text-align: right;">'.$m['ram']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['stockage']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['indice']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$note[4]."</td>";
                $htmlpage .= '<td>'.getUrlInchor($m['url']).'</td>'; 
                $htmlpage .= '<td>';
                $htmlpage .=  makeSetDuplicationModalButton($m, $note);
                $htmlpage .= '</td>';
                $htmlpage .= '</tr>';
            }
            $htmlpage .= '</tbody>';
            $htmlpage .= '</table>';
            $htmlpage .= '</div>';
        }
        //=========================================================
        //=== Modèles de smartphones pour la marque ram stockage
        //=========================================================
        $htmlpage .= '<hr><h3>Modèle de la marque <span style="text-decoration: underline;">'.$marque.'</span>';
        $htmlpage .= ', ram = <span style="text-decoration: underline;">'.$ram.'</span> et';
        $htmlpage .= ' et stockage = <span style="text-decoration: underline;">'.$stockage.'</span>';
        $htmlpage .= '</h3>';
        $htmlpage .= '<div id="div_03a" style="width:1000px; border-style: solid; border-width: 1px;">';
        $htmlpage .= '<table id="sm_modele_ram_stk_table" style="width:820px">';
        $htmlpage .= '<thead>';
        $htmlpage .= "<tr><th>Marque</th><th>Modele</th><th>Ram</th><th>Stockage</th><th>Indice</th><th>Catégorie</th><th>URL</th><th>Choix</th></tr>";
        $htmlpage .= '</thead>';
        $htmlpage .= '<tfoot>';
        $htmlpage .= '<tr>';
        $htmlpage .= '<th><input class="marqueWidth"></th>';
        $htmlpage .= '<th><input class="modeleWidth"></th>';
        $htmlpage .= '<th><input class="ramWidth"></th>';
        $htmlpage .= '<th><input class="stockageWidth"></th>';
        $htmlpage .= '<th><input class="indiceWidth"></th>';
        $htmlpage .= '<th>&nbsp;</th>';
        $htmlpage .= '<th>&nbsp;</th>';
        $htmlpage .= '<th>&nbsp;</th>';
        $htmlpage .= '</tr>';
        $htmlpage .= '</tfoot>';
        $htmlpage .= '<tbody>';
        foreach($modelesForMarqueRamStk as $m) {
            $note      = calculCategorie($m['ram'], $m['stockage'], $m['indice'] );
            $htmlpage .= '<tr>';
            $htmlpage .= "<td>".htmlentities($m['marque'])."</td>";
            $htmlpage .= '<td class="marque">'.htmlentities($m['modele']).'</td>';
            $htmlpage .= '<td style="text-align: right;">'.$m['ram']."</td>";
            $htmlpage .= '<td style="text-align: right;">'.$m['stockage']."</td>";
            $htmlpage .= '<td style="text-align: right;">'.$m['indice']."</td>";
            $htmlpage .= '<td style="text-align: right;">'.$note[4]."</td>";
            $htmlpage .= '<td>'.getUrlInchor($m['url']).'</td>'; 
            $htmlpage .= '<td>';
            $htmlpage .=  makeSetDuplicationModalButton($m, $note);
            $htmlpage .= '</td>';
            $htmlpage .= '</tr>';
        }
        $htmlpage .= '</tbody>';
        $htmlpage .= '</table>';
        $htmlpage .= '</div>';
    

        //=========================================================
        //=== Modèles de smartphones pour la marque 
        //=========================================================
        $htmlpage .= '<br><hr><h3>Modèles de smartphones pour la marque <span style="text-decoration: underline;">'.$marque.'</span>';
        $htmlpage .= ' </h3>';

        if (count($modelesForMarque) != 0) {
       
            //$htmlpage .= "<br><b>enregistrements trouvés dans la base pour la marque $marque</b>";
            $htmlpage .= '<div id="div_04" style="width:1000px; border-style: solid; border-width: 1px;">';
            $htmlpage .= '<table id="sm_marque_table" style="width:820px">';
            $htmlpage .= "<thead>";
            $htmlpage .= '<tr><th>Modèle</th><th>Détail</th></tr>';
            $htmlpage .= "</thead>";
            $htmlpage .= '<tfoot>';
            $htmlpage .= '<tr>';
            $htmlpage .= '<th><input  class="modeleWidth"></th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '</tr>';
            $htmlpage .= '</tfoot>';
            $htmlpage .= "<tbody>";

            foreach($modelesForMarque as $modele) {
                $htmlpage .= "<tr>";
                $htmlpage .= "<td>".htmlentities($modele)."</td>";
                $htmlpage .= '<td data-title="td_'.htmlentities($modele).'"><button ';
                $htmlpage .= ' onclick="displayDetailModele(this,  \''.htmlentities($marque).'\', \''.htmlentities($modele).'\')">Afficher le détail</button> </td>';
                $htmlpage .= "</tr>";
            }
            $htmlpage .= "</tbody></table>";
            $htmlpage .= '</div>';
        }
            $htmlpage .= "<h3><b>Liste des marques</b></h3>";
            $htmlpage .= '<div id="div_05" style="width:1000px; border-style: solid; border-width: 1px;">';
            $htmlpage .= '<table style="width:820px">';
            $htmlpage .= "<thead>";
            $htmlpage .= '<tr><th>Marque</th></tr>';
            $htmlpage .= "</thead>";
            $htmlpage .= "<tbody>";

            foreach($listeMarque as $t_marque) {
                $htmlpage .= "<tr>";
                $htmlpage .= "<td>".htmlentities($t_marque)."</td>";
               $htmlpage .= "</tr>";
            }
            $htmlpage .= "</tbody></table>";
            $htmlpage .= '</div>';
      
    }

}
// div duplication d'un smartphone
$htmlpage .= <<<"EOT"
<div id="chooseSm_div" class="modal">
    <!-- Modal content -->
    <div  class="modal-content">
        <span id = "chooseSm_close" class="close">&times;</span>
        <hr>
        Vous avez cherché un smartphone inconnu dans la base et vous en avez sélectionné un pour remplacer votre sélection.<br>
        Vous pouvez :
        <ul>
            <li>simplement utiliser l'indice et la catégorie de celui que vous avez trouvé</li>
            <li>compléter la base en y ajoutant celui que vous avez recherché complété des infos de celui que vous avez trouvé<br>
            la copie n'est possible que si marque, ram et stockage sont identiques</li>
        </ul>
        <div id="chooseSm_tab">
        </div>
        <div id="chooseSm_button">
            Voulez-vous ajouter ce nouveau smartphone dans la base<br>
            votre nom : <input type="text" id="username"><br>
            <button onclick="copyInDb()">ajouter</button><br>
        </div>
        <span id="chooseSm_msg"></span>
    </div>
</div>

<!-- modal pour afficher un message -->
<div id="msg_div" class="modal">
    <!-- Modal content -->
    <div  class="modal-content">
        <span id = "msg_close" class="close">&times;</span>
        <div id="msg_text">
        </div>
    </div>
</div>

<!--  modal pour ajouter un nouveau smartphone sans duplication -->
<div id="addindb_div" class="modal">
    <!-- Modal content -->
    <div  class="modal-content">
        <span id = "addindb_close" class="close">&times;</span>
        Vous allez ajouter ce smartphone :
        <div id="addindb_saisie">
        </div>
        <div id="addindb_msg" style="color:red">
        </div>
    </div>
</div>
</body>
</html>
EOT;

echo $htmlpage;

function setSpaceGrey($text) {
    $retour = str_replace(" ", '<span style="background-color: #030303;"> </span>', $text);
    return $retour;
}

function removeMultipleSpace($text) {
    return preg_replace('/ +/', ' ', $text);
}

function makeSetDuplicationModalButton($from, $note) {
    
    $retour = '<button  title="Utiliser ce maodèle de smartphone" onclick="setDuplicationModal(' .'\'' 
        .$from['marque']. '\', \'' 
        .$from['modele']. '\', \'' 
        .$from['ram']. '\', \'' 
        .$from['stockage']. '\', \'' 
        .$from['indice']. '\', \'' 
        .'['.implode(',',$note).']' 
        .'\')">ok</button>';
    return $retour;
}

function getUrlInchor($url) {
    $retour = "";
    if ($url != '') {
        $retour = '<a href="'.$url.'" target="_blank">voir';
    }
    return $retour;
}
