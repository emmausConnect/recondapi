<?php
declare(strict_types=1);

$path_private_php = $g_contexte_instance->getPath('private/php');
require_once $path_private_php .'/pageheaderhtml.php';

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class.'/db/dbmanagement.class.php';
require_once $path_private_class.'/paramini.class.php';
require_once $path_private_class.'/smartphones/smartphone.class.php';
require_once $path_private_class.'/smartphones/evaluationsm.class.php';

$path_private_config = $g_contexte_instance->getPath('private/config');
$path_public_images  = $g_contexte_instance->getPath('public/images');

require_once 'utilsm.php';

function fct($data) {
    return $data;
}
$fct = 'fct';

//$paramArray    = ParamIni::getInstance($path_private_config.'/param.ini')->getParam();
//$paramPhpArray = ParamIni::getInstance($path_private_config.'/paramphp.ini')->getParam();
$paramArray    = $g_contexte_instance->getParamIniCls()->getParam();
$paramPhpArray = $g_contexte_instance->getParamPhpIniCls()->getParam();

$supressSpaces = "on";
$marque        = trim(getPostValue('marque',' '));
$modele        = trim(getPostValue('modele',' '));
$ram           = trim(getPostValue('ram',' '));
$stockage      = trim(getPostValue('stockage',' '));
$ponderationKey= trim(getPostValue('ponderationKey','5'));
$idec          = trim(getPostValue('idec',' '));
$statutKey     = trim(getPostValue('statutKey','0'));
$imei          = trim(getPostValue('imei',' '));
$osf           = trim(getPostValue('osf',' '));
$batterieStatut= trim(getPostValue('batterie',' '));

$incsv         = trim(getPostValue('incsv'," "));
if (count($_POST) != 0) {
    $supressSpaces = getPostValue('supressSpaces',"on");
}
$supressSpacesBool = ($supressSpaces == "on"?True : False);
$errmsg = "";
$errInForm = false;
$simulation = false;
$logmsg = "";
$marqueMsg      = "";
$modeleMsg      = "";
$ramMsg         = "";
$stockageMsg    = "";
$ponderationMsg = "";
$idecMsg        = "";
$statutKeyMsg   = "";
$imeiMsg        = "";
$osfMsg         = "";
$batterieMsg    = "";

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
        // 0 Marque
        // 1 Modèle
        // 2 Part Number  => IMEI
        // 3 MEM
        // 4 Stockage
        // 5 Indice Antutu
        // 6 Val A
        // 7 Val M
        // 8 Val S
        // 9 Total
        // 10 Pondération 
        // 11 Total Pondéré
        // 12 Catégorie
        // 13 Prix EC
        // 14 Identifiant EC
        // 15 Etat    // = statut
        // 16 IMEI
        // 17 OS
        // 18 Batterie
        $incsv2 = str_replace("\r","",$incsv);
        //$incsv2 = str_replace("\n","|",$incsv2);
        $explodeCsv = explode("\n", $incsv2);
        if (count($explodeCsv) == 19) {
            $marque   = $explodeCsv[0] ;
            $modele   = $explodeCsv[1] ;
            $ram      = $explodeCsv[3] ;
            $stockage = $explodeCsv[4] ;

            // pondératiion est sous la forme -50.00%
            $ponderationValue = $explodeCsv[10] ;
            $ponderationValue = str_replace('%', "", $ponderationValue);
            if (is_numeric($ponderationValue)) {
                $ponderationValue = (int) $ponderationValue;
                $ponderationValue = (string) $ponderationValue;
                $ponderationKey   = (string) $g_contexte_instance->getParamIniCls()->getParamName('smselectponderation',$ponderationValue);
            }

            $imei     = $explodeCsv[16] ;
            $idec     = $explodeCsv[14] ;

            $statutValue = $explodeCsv[15] ;
            if ($statutValue != "") {
                $statutKey = (string) $g_contexte_instance->getParamIniCls()->getParamName('smselectstatut',$statutValue);
            }

            $osf =  $explodeCsv[17] ;

            $batterieStatut = strtoupper($explodeCsv[18]) ;
            // if ($batterieStatut != "") {
            //     $statutKey = (string) $g_contexte_instance->getParamIniCls()->getParamName('smselectstatut',$statutValue);
            // }
        }else{
            $errmsg .= "<hr>le champ csv contient " .count($explodeCsv). " postes. Il devrait en contenir 4. Il y a peut être des virgules dans les valeurs :";
            $tempcsv = str_replace(",", '<span style="background-color: red;">,</span>', $incsv );
            $errmsg .= '<br><span style="color:black">' .$tempcsv. '</span><hr>';
        }
    }
}

// *****************************************************************
// **** Contrôle des valeurs entrées
// *****************************************************************

if ($marque != removeMultipleSpace($marque)) {
    $marqueMsg = 'contient des espaces en double';
}
if ($modele != removeMultipleSpace($modele)) {
    $modeleMsg = 'contient des espaces en double';
}

if (! ctype_digit($ram)) {
    $ramMsg = '<span style="color: red">uniquement des chiffres</span>';
    $errmsg .= "<br>Ram : uniquement des chiffres";
    $errInForm = true;
}
if (! ctype_digit($stockage)) {
    $stockageMsg = '<span style="color: red">uniquement des chiffres</span>';
    $errmsg   .= "<br>Stockage : uniquement  des chiffres";
    $errInForm = true;
}
if ($idec != removeMultipleSpace($idec)) {
    $idecMsg = 'contient des espaces en double';
}
if ($statutKey == 0) {
    $statutKeyMsg = '<span style="color: red">faire un choix</span>';
    $errmsg    .= "<br>Préciser le statut";
    $errInForm  = true;
}
if ($batterieStatut != "OK" && $batterieStatut != "KO" && $batterieStatut != "NC") {
    $batterieMsg = '<span style="color: red">faire un choix</span>';
    $errmsg    .= "<br>Préciser le statut de la batterie";
    $errInForm  = true;
}


$statutText       = getStatutText($statutKey);
$ponderationValue = getPonderationValue($ponderationKey);

//$calculCatPossible = true;
//if ($marque == "" || $modele == "" ||

//if (! $errInForm) {
    if ($marque != "" && $modele != "" && $ram != "" && $stockage != "") {
        $smObj = Smartphone::getInstance();
        $smObj->setMarque($marque);
        $smObj->setModele($modele);
        $smObj->setRam($ram);
        $smObj->setStockage($stockage);
        $smObj->setPonderationKey($ponderationKey);
        $smObj->setPonderationValue((int) $ponderationValue);
        $smObj->setIdEc($idec);
        $smObj->setStatutKey($statutKey);
        $smObj->setStatutText($statutText);
        $smObj->setImei($imei);
        $smObj->setOs($osf);
        $smObj->setBatterieStatut($batterieStatut);

        $evaluationSmObj = EvaluationSm::getInstance($smObj, $supressSpacesBool);
        $evaluationSmObj->evalSmartphone();

        $smRowFound  = $evaluationSmObj->getSmRowFound();
        if ($smRowFound) {
            $smRow = $evaluationSmObj->getSmRow();
            $indice      = $smRow['indice'];
            $os          = $smRow['os'];
            $url         = $smRow['url'];
            $origine     = $smRow['crtorigine'];
            $errMsg      = $evaluationSmObj->getErrMsg();
            $helpHtml   .= $errMsg;
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

        }
    }
//}
// enreg non trouvé
// recherche des enregs sur la marque
$dbInstance = DbManagement::getInstance();
$db = $dbInstance->openDb();
$tableName = $dbInstance->tableName('smartphones');
$debutModele = '';
if ($modele != '') {
    $debutModele = trim(strtok($modele.' ', ' '));
}
$sqlQuery  = "SELECT * FROM $tableName ";
//$sqlQuery .= " where marque = :marque and modele like :debutModele "; // and ram = :ram and stockage = :stockage ";
$sqlQuery .= " where marque = :marque "; // and ram = :ram and stockage = :stockage ";
$sqlQuery .= " ORDER BY modele, ram, stockage;";

$stmt = $db->prepare($sqlQuery);
$stmt->execute([
    'marque' =>formatKey($marque,true),
    //'debutModele' => '%'.$debutModele.'%',
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
        
        //}
    //}
//}
$cvt = 'cvtTextToCsv';
$htmlentities = 'cvtToHtmlentities';
$supressSpaces = ($supressSpacesBool ? "checked" : "");
$htmlpage  = getHtmlHead();

$htmlpage .= <<<"EOT"
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
  
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>

<link rel="stylesheet" href="style/stylesm.css" />
<link rel="stylesheet" href="style/stylesm.css" />
<script>
    $(document).ready( function () {
        // initialisation des champs de DATATABLE
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


                        if (columnName == 'Modele') {
                            let debutModele = document.getElementById('modele').value.split(" ")[0] // premier mot
                            column.search(debutModele, false, false).draw();
                            input.value = debutModele;
                        }
                        if (columnName == 'Ram') {
                            //column.search('^'+document.getElementById('ram').value+'$', true, false).draw();
                            input.value = document.getElementById('ram').value;
                            column.search(document.getElementById('ram').value, false, false).draw();
                        }
                        if (columnName == 'Stockage') {
                            //column.search('^'+document.getElementById('stockage').value+'$', true, false).draw();
                            input.value = document.getElementById('stockage').value;
                            column.search(document.getElementById('stockage').value, false, false).draw();
                        }
                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });

                    }
            });
            table_sm_modele_table.search("").draw();
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
        document.getElementById('idec').value = 'LITEST-9999';
        document.getElementById('statutKey').value="4";
        document.getElementById('imei').value = '123456789123456';
        document.getElementById('osf').value = 'android test';
        document.getElementById('batterieok').checked = true;
    }

    function clearValues() {
        document.getElementById('marque').value = '';
        document.getElementById('modele').value = '';
        document.getElementById('ram').value = '';
        document.getElementById('stockage').value = '';
        document.getElementById('ponderationKey').value="5";
        document.getElementById('idec').value = '';
        document.getElementById('statutKey').value="0";
        document.getElementById('imei').value = '';
        document.getElementById('osf').value = '';
        document.getElementById('batterieok').checked = false;
        document.getElementById('batterieko').checked = false;
        document.getElementById('batterienc').checked = false;
    }

    function searchKimovil() {
        //let url = 'https://www.kimovil.com/fr/ou-acheter-';
        let url = 'https://www.kimovil.com/fr/comparatif-smartphone/name.'
        url    += document.getElementById('marque').value.replace(/\s\s+/g, ' '); //.replace(' ','-');
        url    += ' ';
        url    += document.getElementById('modele').value.replace(/\s\s+/g, ' '); //.replace(' ','-');
        window.open(url, '_blank');
    }

    function copyToMarque(m, r, s) {
        document.getElementById('modele').value = m;
        document.getElementById('ram').value = r;
        document.getElementById('stockage').value = s;
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
                    html += '<td>' +sm['marque']  + '</td>';
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
                    html +=   '<button title="Utiliser ce modèle de smartphone"';
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
        dataArray['origine']   = '{$fct(basename(__FILE__))}';
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
     * categorie2 est le tableau avec le resultat du calcul catégorie
     */
    function setDuplicationModal(marque2, modele2, ram2, stockage2, indice2, categorie2) {
        const modalPrefix = 'chooseSm';
        let   tableDiv = document.getElementById( modalPrefix + '_tab');
        const marque   = document.getElementById('marque').value;
        const modele   = document.getElementById('modele').value;
        const ram      = document.getElementById('ram').value;
        const stockage = document.getElementById('stockage').value;

        // check si les valeurs sont identiques
        let colorMarque = "#000000";
        if (marque.toLowerCase() != marque2.toLowerCase()) {colorMarque = "#FF0000"}
        let colorModele = "#000000";
        if (modele.toLowerCase() != modele2.toLowerCase()) {colorModele = "#FF0000"}
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
        html += '<td>Modèle </td>';
        html += '<td id="'+modalPrefix+'_marque2"   >'+marque2+'</td>';
        html += '<td id="'+modalPrefix+'_modele2"   >'+modele2+'</td>';
        html += '<td id="'+modalPrefix+'_ram2"      >'+ram2+'</td>';
        html += '<td id="'+modalPrefix+'_stockage2" >'+stockage2+'</td>';
        html += '<td id="'+modalPrefix+'_indice2">'+indice2+'</td>';
        html += '<td style="background-color: #74992e">'+categorie2[4]+'</td></td>';
        html += '</tr>';
        html += '<tr>';
        html += '<td>Créer</td>';
        html += '<td class="marqueWidth">'+marque+'</td>';
        html += '<td class="modeleWidth">'+modele+'</td>';
        html += '<td class="ramWidth">'+ram+'</td>';
        html += '<td class="stockageWidth">'+stockage+'</td>';
        html += '<td>&nbsp;</td><td>&nbsp;</td>';
        html += '</tr>';
    
        html += '</tbody>';
        html += '</table>';
        html += '<br>';
        tableDiv.innerHTML = html;
        let   buttonDiv = document.getElementById(modalPrefix + '_button');
        if (marque.toLowerCase() != marque2.toLowerCase() || ram != ram2 || stockage != stockage2) {
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

    function displayHelp(adresse) {
        window.open(adresse, '_blank'); 
    }
</script>
EOT;
$htmlpage .= '</head>';
$htmlpage .= '<body class="body_flex">';
$htmlpage .= '<main>';
$htmlpage .= getHtmlHeader();
$htmlpage .= <<<"EOT"
<div id="div_00" style="display: flex;">
    <div id="div_01" style="border:1px solid;padding: 10px;width: 700px; background-color: #dddddd;">
        La recherche se fait sans tenir compte des majuscules/minuscules.
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOT;
$temp = $paramPhpArray['fichiers']['aide_sm_categorisation'];
$htmlpage .= <<<"EOT"
<img src="images/icones/aide.png" style="width:20px;" onclick="displayHelp('$temp')">
        <br>
        Les espaces/blancs en début et fin de critère sont supprimés.
        <button onclick="setDemoValues()">set demo values</button>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <button onclick="clearValues()">effacer</button>

        <br>
        <form id="form_search" action="exsearchsmartphone.php"  method="post">
            <div style="border:1px solid;padding: 10px;width: 600px;" class="input">
            <label class="shortLabel" for="marque">Marque</label>
            <input type="text" id="marque" name="marque" class="inputForm" value="{$htmlentities($marque)}">&nbsp;$marqueMsg<br>
            <label class="shortLabel" for="modele">Modèle</label>
            <input type="text" id="modele" name="modele" class="inputForm" value="{$htmlentities($modele)}">&nbsp;$modeleMsg
            <button id ="btnKimovil" type="button" onclick="searchKimovil()">Kimovil</button><br>
            <label class="shortLabel" for="ram">Ram Go</label>
            <input type="number" min="0" step="1" id="ram" name="ram" class="inputForm" value="{$htmlentities($ram)}">&nbsp;$ramMsg<br>
            <label class="shortLabel" for="stockage">Stockage Go</label>
            <input type="number" min="0" step="1" id="stockage" name="stockage" class="inputForm" value="{$htmlentities($stockage)}">&nbsp;$stockageMsg<br>

            <label class="shortLabel" for="ponderationKey">Pondération</label>
            <select name="ponderationKey" id="ponderationKey" class="selectForm">
                {$fct(getPonderationSelect($ponderationKey))}
            </select>&nbsp;$ponderationMsg<br>

            <label class="shortLabel" for="idec">Identifiant EC</label>
            <input type="text" id="idec" name="idec" class="inputForm" value="{$htmlentities($idec)}">&nbsp;$idecMsg<br>

            <label class="shortLabel" for="statutKey">Statut</label>
            <select name="statutKey" id="statutKey" class="selectForm">
                {$fct(getStatutSelect($statutKey))}
            </select>&nbsp;$statutKeyMsg<br>
            <label class="shortLabel" for="imei">IMEI</label>
            <input type="number" id="imei" name="imei" class="inputForm" value="{$htmlentities($imei)}">&nbsp;$imeiMsg<br>

            <label class="shortLabel" for="osf">OS</label>
            <input type="text" id="osf" name="osf" class="inputForm" value="{$htmlentities($osf)}">&nbsp;$osfMsg<br>

            <!-- Batteries -->
            <label class="shortLabel" for="div_batterie">Batterie</label>
            <div id="div_batterie" class="inputForm" style="display: inline-block;">
                OK
                <input type="radio" id="batterieok" name="batterie" style="width:30px" value="OK" {$fct(checkedIfEqual($batterieStatut, 'OK'))}>

                &nbsp;&nbsp;&nbsp;&nbsp;ko
                <input type="radio" id="batterieko" name="batterie" style="width:30px" value="KO" {$fct(checkedIfEqual($batterieStatut, 'KO'))}>
            </div>
            &nbsp;$batterieMsg<br>
            </div><!-- div container du form -->
EOT;
if ($g_contexte_instance->isConnected()) {
    $htmlpage .= <<<"EOT"
            <div style="border:1px solid;padding: 10px;width: 600px;" class="input">
                <b>Pour MARC V.  </b>
                Utilisé si le champ "Marque" n'est pas renseigné.<br>
                (copier la colonne C2:C21)
                <label class="shortLabel" for="incsv">csv</label>
                <textarea id="incsv" name="incsv" cols="60" value="{$htmlentities($incsv)}"></textarea><br>
            </div>
EOT;
}
$htmlpage .= <<<"EOT"
            <div style="border:1px solid;padding: 10px;width: 600px;" class="input">
                <label for="supressSpaces" class="longLabel">Supprimer les espaces en trop des textes :</label>
                <input type="checkbox" id="supressSpaces" name="supressSpaces" $supressSpaces />
            </div>
            <br>

            <input type="submit" value="Catégoriser/Chercher">
        </form>
    </div> <!-- div_01 -->
EOT;
if ($helpHtml != "") {
$htmlpage .= <<<"EOT"
    <div id="div_help" style="border:1px solid;padding: 10px;width: 800px; background-color: #eeeeee;">
        $helpHtml<hr>
        Votre smartphone existe peut-être dans la base avec une orthographe légèrement différente.<br>
        Pour vous aider, <b>2 extractions</b> vous sont présentées ci-dessous :<br>
        <br>
        <b>1) Ceux de la <u>marque</u> recherchée, préfiltré sur le 1er mot du <u>modèle</u> que vous avez indiqué, sa <u>ram</u>
         et son <u>stockage</u>.</b><br>
         Si vous trouvez votre bonheur, vous pouvez :<br>
         - utiliser le résultat du calcul de la catégorie<br>
         - nous aider à améliorer la base en y ajoutant votre découverte :<br>
         &nbsp;- cliquez sur le bouton "<u><b>ok</b></u>" de la ligne (aucun danger)<br>
         &nbsp;- une fenêtre s'affichera, suivez ses instructions"<br>
         <br>
        <b>2) la liste des marques contenues dans la base</b><br>
        Vérifier qu'il n'y a pas une erreur de votre part dans la saisie de la marque<br>
        <br>
        La colonne "URL" permet d'afficher la page kimovil du smartphone. Le visuel peut aider à valider le choix.
        <hr>
        <b>Vous ne trouvez pas votre smartphone dans notre base</b>, cherchez le sur le site <a href="https://www.kimovil.com/fr/comparatif-smartphone" target="_blank">kimovil</a><br>
        puis ajoutez le à la base de données en cliquant sur ==><button onclick="displayAddInDb()">ajouter</button>
    </div> <!-- div_help -->
EOT;
}
$htmlpage .= <<<"EOT"
</div> <!-- div_00 -->
<hr>
EOT;
// if ($errmsg != '') {
//     $htmlpage .= '<span style="color:red;">'.$errmsg.'</span><br>';
// }

//=========================================================
//=== smartphone trouvé
//=========================================================
$resultCsv = "";
if ($smRowFound) {
    if ($idec == "" || $statutKey == 0 || $imei == "" || $osf == "" || $batterieMsg != "") {
        $resultCsv .= '<span style="color:red">un des champs IdEc, Statut, IMEI, OS ou Batterie n\'est pas renseigné<br>';
        $resultCsv .= 'le csv ci-dessous n\'est pas peut-être pas assez complet<br></span>';
    }
    $resultCsv .= <<<"EOT"
csv pour copie dans GSheet (modèle de Marc Vaneeckhoutte)<br>
<textarea style="width:100%">
""\t
{$cvt("".$idec)}\t
{$cvt("Smartphone")}\t
{$cvt($evaluationSmObj->getCategoriePondereAlpha()."")}\t
{$cvt("".$statutText)}\t
{$cvt("".$smRow['marque'])}\t
{$cvt("".$smRow['modele'])}\t
{$cvt("".$batterieStatut)}\t
""\t
""\t
""\t
{$cvt("".$imei)}\t
$indice\t
{$cvt("".$osf)}\t
$stockage\t
$ram\t
</textarea>
<hr>
EOT;
$resultCsv = str_replace("\n", '', $resultCsv);
$resultCsv = str_replace("\r", '', $resultCsv);
}

// smartphone trouvé : affichage du calcul
if($smRowFound) {
    $htmlpage .= <<<"EOT"
<div id="div_02" style="border:1px solid;padding: 10px;width: 700px; background-color: #aaaaaa;">
<h2 style="text-align: center;">Résultat</h2>
$resultCsv
<p  style="text-align: center;">Les critères utilisés pour la recherche sont en rouge quand ils sont différents de ceux que vous avez saisis<p>.

<table style=" margin: 0 auto;">
<thead>
<tr><th>&nbsp;</th><th>Détail du calcul</th><th>&nbsp;</th></tr>
</thead>
<tbody>
<tr><td>Ram</td>      <td style="text-align: right;">{$htmlentities("".$smRow['ram'])}</td>     <td style="text-align: right;">{$evaluationSmObj->getNoteRam()}</td></tr>
<tr><td>Stockage</td> <td style="text-align: right;">{$htmlentities("".$smRow['stockage'])}</td><td style="text-align: right;">{$evaluationSmObj->getNoteStockage()}</td></tr>
<tr><td>Indice</td>   <td style="text-align: right;">{$htmlentities("".$smRow['indice'])}</td>  <td style="text-align: right;">{$evaluationSmObj->getNoteIndice()}</td></tr>
<tr><td>Total</td>    <td style="text-align: right;">&nbsp;</td>                                <td style="text-align: right;">{$evaluationSmObj->getNoteTotale()}</td></tr>
<tr><td><b>Catégorie</b></td><td>&nbsp;</td><td style="text-align: right;"><b>{$evaluationSmObj->getCategorieApha()}</b></td></tr>

<tr><td>Pondération</td>   <td style="text-align: right;">&nbsp;</td>  <td style="text-align: right;">{$evaluationSmObj->getPonderation()}</td></tr>
<tr><td>Total pond.</td>    <td style="text-align: right;">&nbsp;</td><td style="text-align: right;">{$evaluationSmObj->getNotePondere()}</td></tr>
<tr><td><b>Catégorie Pond</b></td><td>&nbsp;</td><td style="text-align: right;"><b>{$evaluationSmObj->getCategoriePondereAlpha()}</b></td></tr>

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

$htmlpage .= getPlagesAsTable();
$htmlpage .= '</div>';
}else{
    //=========================================================
    //=== smartphone NON trouvé
    //=========================================================
    //if (! $errInForm) {
        // la recherche a échouée affichage des modèles 
        $marqueGrey = setSpaceGrey($marque);
        $htmlpage .= '<hr><h3>Smartphones de la marque <span style="text-decoration: underline;">'.$marque.'</span>';
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
            $htmlpage .= "<tr><th>Modele</th><th>Ram</th><th>Stockage</th><th>Indice</th><th>Catégorie</th><th>Web</th><th>Copier</th></tr>";
            $htmlpage .= '</thead>';
            $htmlpage .= '<tfoot>';
            $htmlpage .= '<tr>';
            $htmlpage .= '<th><input class="modeleWidth"></th>';
            $htmlpage .= '<th><input class="ramWidth"></th>';
            $htmlpage .= '<th><input class="stockageWidth"></th>';
            $htmlpage .= '<th><input class="indiceWidth"></th>';
            $htmlpage .= '<th><input class="indiceWidth"></th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '<th>&nbsp;</th>';
            $htmlpage .= '</tr>';
            $htmlpage .= '</tfoot>';
            $htmlpage .= '<tbody>';
            $evalSmTemp  = EvaluationSm::getInstance();
            foreach($rowsForMarqueLikeModel as $m) {
                $note      = $evalSmTemp->calculCategorie($m['ram'], $m['stockage'], $m['indice'], 0, 'GB' );
                $htmlpage .= '<tr>';
                $htmlpage .= '<td class="marque">'.htmlentities($m['modele']);
                $htmlpage .= '<img src="images/icones/ok01.webp" alt="ok" width="15" title="copier dans modèle, Ram & Stockage"';
                $htmlpage .= 'onclick="copyToMarque(\'' .$m['modele']. '\', \'' .$m['ram']. '\', \'' .$m['stockage'] .'\')">';
                $htmlpage .= '</td>';
                $htmlpage .= '<td style="text-align: right;">'.$m['ram']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['stockage']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['indice']."</td>";
                $htmlpage .= '<td style="text-align: right; text-align:center">'.$note['categorieApha']."</td>";
                $htmlpage .= '<td>'.getUrlInchor($m['url']).'</td>'; 
                $htmlpage .= '<td>';
                if ($ram == $m['ram'] && $m['stockage'] == $stockage) {
                    $htmlpage .=  makeSetDuplicationModalButton($m, $note);
                }
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
        if (false) {
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
            $evalSmTemp  = EvaluationSm::getInstance();
            foreach($modelesForMarqueRamStk as $m) {
                $note      = $evalSmTemp->calculCategorie($m['ram'], $m['stockage'], $m['indice'] );
                $htmlpage .= '<tr>';
                $htmlpage .= "<td>".htmlentities($m['marque'])."</td>";
                $htmlpage .= '<td class="marque">'.htmlentities($m['modele']).'</td>';
                $htmlpage .= '<td style="text-align: right;">'.$m['ram']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['stockage']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$m['indice']."</td>";
                $htmlpage .= '<td style="text-align: right;">'.$note['categorieApha']."</td>";
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
      
    //}

}
// div duplication d'un smartphone
$htmlpage .= <<<"EOT"

<div id="chooseSm_div" class="modal">
    <!-- Modal content -->
    <div  class="modal-content">
        <span id = "chooseSm_close" class="close">&times;</span>
        <hr>
        Vous avez cherché un smartphone inconnu dans la base et vous en avez sélectionné un pour remplacer votre sélection.<br>
        Vous pouvez simplement utiliser l'indice et la catégorie de celui que vous avez trouvé<br>
        <br>
        Mais si votre smartphone est un homonime de celui que vous avez sélectionne (car certains smartphone ont plusieurs nom de modèle),<br>
        <br>
        <b>Merci de compléter notre base de données</b> en créant une copie avec le nom de modèle de celui que vous avez en main<br>
        Cela facilitera le travail de vos collègues et les traitements de masse automatisés (excel)<br>
        &nbsp;&nbsp; (la copie n'est possible que si marque, ram et stockage sont identiques)<br>
        <br>
        <div id="chooseSm_tab">
        </div>
        <div id="chooseSm_button">
            Ajouter ce nouveau smartphone dans la base<br>
            votre nom : <input type="text" id="username"><br>
            <button onclick="copyInDb()">ajouter</button><br>
        </div>
        <span id="chooseSm_msg"></span>
    </div>
</div>

<!-- modal pour afficher un message -->
<div id="msg_div"  style="display: block;
        z-index: 99999;
        position: absolute;
        top: 0px;
        height: 100%;
        width: 100%;
        background-color: rgba(4,0,0,0.4);
        display:none;">
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
        <div>
            <span id = "addindb_close" class="close">&times;</span>
            <b>Ajout d'un smartphone à notre base</b><br>
            <br>
            Vous avez un smartphone que vous n'avez pas trouvé dans notre base.<br>
            Vous l'avez cherché et trouvé sur le site Kimovil.<br>
            Vous pouvez ajouter cette découverte dans notre base grâce au formulaire ci dessous<br>
            <br>
            Le smartphone sera indiqué "à controler" afin que le gestionnaire de la base le vérifie<br>
            <div style="display:flex">
                <div id="addindb_saisie" style="  margin-left: auto; margin-right: auto;">
                </div>
                <div style="  margin-left: auto; margin-right: auto;">
                <img src="images/smartphones/kimovil.webp" style="height:400px">
                </div>
            <div>
            <div id="addindb_msg" style="color:red">
            </div>

        <div>
    </div>

</div>
</main>
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
    global $modele, $ram, $stockage;
    $retour = "";
    if ($modele != "" && $ram != "" && $stockage !="") {
        $retour = '<button  title="Utiliser ce modèle de smartphone" onclick="setDuplicationModal(' .'\'' 
            .$from['marque']. '\', \'' 
            .$from['modele']. '\', \'' 
            .$from['ram']. '\', \'' 
            .$from['stockage']. '\', \'' 
            .$from['indice']. '\', \'' 
            .'['.implode(',',$note).']' 
            .'\')">ok</button>';
    }
    return $retour;
}

function getUrlInchor($url) {
    $retour = "";
    if ($url != '') {
        $retour = '<a href="'.$url.'" target="_blank">voir';
    }
    return $retour;
}

function checkedIfEqual($var, $val, $cassensitive = false) {
    $retour = "";
    if ( $cassensitive ) {
        if ($var == $val) {
            $retour = "checked";
        }
    }else{
        if (strtolower($var) == strtolower($val)) {
            $retour = "checked";
        }
    }
    return $retour;
}

