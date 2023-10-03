<?php
declare(strict_types=1);

$path_private_php = $g_contexte_instance->getPath('private/php');
require_once $path_private_php .'/pageheaderhtml.php';

$path_private_class = $g_contexte_instance->getPath('private/class');
require_once $path_private_class .'/db/dbmanagement.class.php';

require_once 'utilsm.php';


// $incsv         = "";
$supressSpaces = "off";
$title         = trim(getPostValue('title','Samsung Galaxy S3'));
$modele        = trim(getPostValue('modele','Galaxy S III'));
$ram           = trim(getPostValue('ram','1'));
$stockage      = trim(getPostValue('stockage','16'));
$incsv         = trim(getPostValue('incsv',"Samsung Galaxy S3, Galaxy S III,1,16"));
if (count($_POST) != 0) {
    $supressSpaces = getPostValue('supressSpaces',"off");
}
$supressSpacesBool = ($supressSpaces == "on"?True : False);
$errmsg = "";
$errInForm = false;
$logmsg = "";
$titleMsg         = "";
$modeleMsg        = "";
$ramMsg           = "";
$stockageMsg      = "";
$indice = 0;
$os     = "";
$url    = "";
//$imei = "";
$smRow = [];
$smRowFound = False;
$rowsForTitle = [];
$titleForMarque = [];



if ($title == "") {
    if ($incsv != null) {
        $explodeCsv = explode(",", $incsv);
        if (count($explodeCsv) == 4) {
            $title    = $explodeCsv[0] ;
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

if ($title != removeMultipleSpace($title)) {
    $titleMsg = 'contient des espaces en double';
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
    if (! $errInForm && $title != null && $title != "") {
        $dbInstance = DbManagement::getInstance();
        $db = $dbInstance->openDb();
        $tableName = $dbInstance->tableName('smartphones');
        $sqlQuery = "SELECT * from $tableName 
            where title=:title and modele=:modele and ram=:ram and stockage=:stockage;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'title'    =>formatKey($title,$supressSpacesBool),
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
            $origine     = $smRow['origine'];
            $note        = calculCategorie($ram, $stockage, $indice );
        }else{
            $errmsg .= "Il n'y a aucun modèle dans la base avec les critères spécifiés<br>. Pensez à cocher la case 'Supprimer les espaces en trop'";
        }
    }else{
        $errmsg .= "<br>Renseignez ou corrigez tous les champs";
    }
    $colorErrTitle = "";
    $colorErrModele = "";
    $colorErrRam = "";
    $colorErrStockage = "";
    if($smRowFound) {

        if ($title != $smRow['title']) {
            $colorErrTitle = "red";
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
        // enreg nonb trouvé
        // recherche des enregs sur title
        $sqlQuery = "SELECT * FROM $tableName where title = :title;";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'title' =>formatKey($title,true)
            ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) != 0) {
            foreach($rows as $row) {
                array_push($rowsForTitle, $row);
            }
        }

        $fisrtSpace = strpos($title," "); // positions start at 0, FALSE if the string is not found
        $marque = $title;
        if ($fisrtSpace != false) {
            $marque = substr($title, 0, $fisrtSpace);
        }
        $sqlQuery = "SELECT DISTINCT title FROM $tableName where SUBSTRING_INDEX(title,' ',1) =:marque ORDER BY title; ";

        $stmt = $db->prepare($sqlQuery);
        $stmt->execute([
            'marque' =>formatKey($marque,$supressSpacesBool)
            ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) != 0) {
            foreach($rows as $row) {
                array_push($titleForMarque, $row['title']);
            }
        }
    }
}
$cvt = 'cvtTextToCsv';
$htmlentities = 'cvtToHtmlentities';
$supressSpaces = ($supressSpacesBool ? "checked" : "");
$htmlpage  = getHtmlHead();
$htmlpage .= <<<"EOT"
<style>
.shortLabel {
    display: inline-block;
    width: 50px;
    text-align: right;
}
.longLabel {
    display: inline-block;
    width: 300px;
    text-align: right;
}
input {
    padding: 5px 10px;
}

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 20px;
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

#customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #ddd;
    padding: 2px;
}

tr:nth-child(even){
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

th {
    padding-top: 2px;
    padding-bottom: 2px;
    text-align: left;
    background-color: #04AA6D;
    color: white;
}
</style>

<script>
function displayDetailTitle(bouton, title) {
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Typical action to be performed when the document is ready:
            let elem = document.querySelector('[data-title="td_'+ title   +'"]');
            let list = JSON.parse(xhttp.responseText);
            let data = list['data'];
            let html = '<table>';
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
                html += '<td><a href="' +sm['url']+ '">' +sm['url']+ '</a></td>';
                html += '<td>';
                html +=   '<button onclick="setDuplicationModal(' +'\'' +title+ '\', \'' +sm['modele']+ '\', \'' +sm['ram']+ '\', \'' +sm['stockage']+ '\', \'' +sm['indice']+ '\', \'' +sm['categorie'] +'\')">ok</button>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody><table>';
            elem.innerHTML = html;
        }
    }
    xhttp.open("GET", "exgetsmartphoneslist.php?title="+title, true);
    xhttp.send();
}

function addInDb() {
    let userName = document.getElementById('username').value;
    if (userName == "" ) {
        document.getElementById('chooseSm_msg').innerText = "merci de saisir votre nom";
        return;
    }


    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let reponse = JSON.parse(xhttp.responseText);
            if (reponse['status'] == '0') {
                displayMsg('Erreur : <br>'+reponse['msg']);
            }else{
                closeModal('chooseSm')
                displayMsg('smartphone ajouté');
            }
        }
    }

    xhttp.open("POST", "exaddindb.php", true);
    let data = new FormData();
    data.append('title', document.getElementById('title').value);
    data.append('modele', document.getElementById('modele').value);
    data.append('ram', document.getElementById('ram').value);
    data.append('stockage', document.getElementById('stockage').value);
    data.append('title2', document.getElementById('title2').innerText);
    data.append('modele2', document.getElementById('modele2').innerText);
    data.append('ram2', document.getElementById('ram2').innerText);
    data.append('stockage2', document.getElementById('stockage2').innerText);
    data.append('username', document.getElementById('username').innerText);

    xhttp.send(data);
}

function setDuplicationModal(title2, modele2, ram2, stockage2, indice2, categorie2) {
    const modalPrefix = 'chooseSm';
    let tableDiv   = document.getElementById(modalPrefix + '_tab');
    const title    = document.getElementById('title').value;
    const modele   = document.getElementById('modele').value;
    const ram      = document.getElementById('ram').value;
    const stockage = document.getElementById('stockage').value;


    let html = '<table>';
    html += '<thead>';
    html += '<tr><th>&nbsp;</th><th>Titre</th><th>Modèle</th><th>Ram</th><th>Stockage</th><th>Indice</th><th>Catégorie</th></tr>';
    html += '</thead>';
    html += '<tbody>';
    html += '<tr><td>cherché</td><td>'+title+'</td><td>'+modele+'</td><td>'+ram+'</td><td>'+stockage+'</td><td>&nbsp;</td><td>&nbsp;</td></td>';
    html += '<tr><td>choisi </td><td id="title2">'+title2+'</td><td id="modele2">'+modele2+'</td>';
    html += '<td id="ram2">'+ram2+'</td><td id="stockage2">'+stockage2+'</td>';
    html += '<td>'+indice2+'</td>';
    html += '<td style="background-color: #74992e">'+categorie2[4]+'</td></td>';
 
    html += '</tbody>';
    html += '</table>';
    tableDiv.innerHTML = html;
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
<div style="border:1px solid;padding: 10px;width: 700px; background-color: #eeeeee;">
La recherche se fait sans tenir compte des majuscules/minuscules.
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<a href="https://docs.google.com/document/d/1yQG_MQC-HUv2MqzjF6HPVwcgJ1D30UhvhWEiTF0Z6TQ" target="_blank">aide</a>
<br>
Les espaces/blancs en début et fin de critère sont supprimés.
<br>
<form action="exsearchsmartphone.php"  method="post">
<div style="border:1px solid;padding: 10px;width: 600px;" class="input">
<label class="shortLabel" for="title">titre</label>
<input type="text" id="title" name="title" value="{$htmlentities($title)}">&nbsp;$titleMsg<br>
<label class="shortLabel" for="modele">modèle</label>
<input type="text" id="modele" name="modele" value="{$htmlentities($modele)}">&nbsp;$modeleMsg<br>
<label class="shortLabel" for="ram">ram</label>
<input type="text" id="ram" name="ram" value="{$htmlentities($ram)}">&nbsp;$ramMsg<br>
<label class="shortLabel" for="stockage">stockage</label>
<input type="text" id="stockage" name="stockage" value="{$htmlentities($stockage)}">&nbsp;$stockageMsg<br>
</div>
<br>
<div style="border:1px solid;padding: 10px;width: 600px;" class="input">
Utilisé si le champ "titre" n'est pas renseigné.<br>
<label class="shortLabel" for="incsv">csv</label>
<input type="text" id="incsv" name="incsv" size="60" value="{$htmlentities($incsv)}"><br>
(séparateur = virgule. Ne marche pas si les textes contiennent une virgule
</div>
<br>
<label for="supressSpaces" class="longLabel">Supprimer les espaces en trop des textes :</label>
<input type="checkbox" id="supressSpaces" name="supressSpaces" $supressSpaces /><br>
<br>
<input type="submit" value="Submit">
</form>
</div>
<hr>
<span style="color:red;">$errmsg</span><br>

EOT;
if($smRowFound) {
    $htmlpage .= <<<"EOT"
Les critères utilisés pour la recherche sont en rouge s'ils sont différents de ceux que vous avez saisis.
<table>
<thead>
<tr><th>&nbsp;</th><th>valeur</th><th>&nbsp;</th></tr>
</thead>
<tbody>
<tr><td>&nbsp;</td>   <td><b>Critères utilisés</b></td><td>&nbsp;</td></tr>
<tr><td>titre</td><td style="color:$colorErrTitle;">{$htmlentities("".$smRow['title'])}</td><td>&nbsp;</td></tr>
<tr><td>modele</td><td style="color:$colorErrModele;">{$htmlentities("".$smRow['modele'])}</td><td>&nbsp;</td></tr>
<tr><td>ram</td><td style="color:$colorErrRam;">{$htmlentities("".$smRow['ram'])}</td><td>&nbsp;</td></tr>
<tr><td>stockage</td><td style="color:$colorErrStockage;">{$htmlentities("".$smRow['stockage'])}</td><td>&nbsp;</td></tr>

<tr><td>&nbsp;</td>   <td><b>Valeurs trouvées dans la base</b></td><td>&nbsp;</td></tr>
<tr><td>indice</td><td>{$htmlentities("".$indice)}</td><td>&nbsp;</td></tr>
<tr><td>OS</td><td>{$htmlentities($os)}</td><td>&nbsp;</td></tr>
<tr><td>URL</td><td><a href="$url"  target="_blank">{$htmlentities($url)}</a></td><td>&nbsp;</td></tr>
<tr><td>origine</td><td>{$htmlentities("".$smRow['origine'])}</td><td>&nbsp;</td></tr>
<tr><td>crt by</td><td>{$htmlentities("".$smRow['crtby'])}</td><td>&nbsp;</td></tr>
<tr><td>crt date</td><td>{$htmlentities("".$smRow['crtdate'])}</td><td>&nbsp;</td></tr>

<tr><td>&nbsp;</td>   <td><b>Résultat du calcul</b></td>     <td>&nbsp;</td></tr>
<tr><td>Ram</td>      <td style="text-align: right;">{$htmlentities("".$smRow['ram'])}</td>     <td style="text-align: right;">{$note[0]}</td></tr>
<tr><td>Stockage</td> <td style="text-align: right;">{$htmlentities("".$smRow['stockage'])}</td><td style="text-align: right;">{$note[1]}</td></tr>
<tr><td>Indice</td>   <td style="text-align: right;">{$htmlentities("".$smRow['indice'])}</td>  <td style="text-align: right;">{$note[2]}</td></tr>
<tr><td>Total</td>    <td style="text-align: right;">&nbsp;</td>                                <td style="text-align: right;">{$note[3]}</td></tr>
<tr><td>Catégorie</td><td>&nbsp;</td>                                                           <td style="text-align: right;">{$note[4]}</td></tr>
</tbody></table>
csv :{$cvt($title)},{$cvt($modele)},$ram,$stockage,$indice,$note[4],{$cvt($os)},{$cvt($url)}><br>
<hr>
EOT;
}else{
    if (! $errInForm) {
        // la recherche a échouée
        // affichage des modèles
        //$htmlpage .= "<hr>.<br>";
        $titleGrey = setSpaceGrey($title);
        $htmlpage .= "<hr><h3>Recherche des smartphones uniquement sur le critère '$title' (espaces multiples supprimés) :</h3>";
        if (count($rowsForTitle) == 0) {
            $htmlpage .= "... il n'y a aucun smartphone dans la base sur ce seul critère";
        }else{
            $htmlpage .= "<h4>pour $title, la base contient ces smartphones</h4>";
            $htmlpage .= "<table>";
            $htmlpage .= "<tr><th>titre</th><th>modele</th><th>ram</th><th>stockage</th><th>indice</th><th>url</th></tr>";

            foreach($rowsForTitle as $m) {
                $htmlpage .= "<tr>";
                $htmlpage .= "<td>".htmlentities($m['title'])."</td><td>".htmlentities($m['modele'])."</td><td>".$m['ram']."</td>";
                $htmlpage .= "<td>".$m['stockage']."</td><td>".$m['indice']."</td>";
                $htmlpage .= '<td><a href="'.$m['url'].'" target="_blank">'.$m['url'].'</td>'; 
                $htmlpage .= '</tr>';
            }
            $htmlpage .= "</table>";
        }

        $htmlpage .= "<hr><h3>Afin d'aider à la recherche, voici la liste de 'titre' commençant par '$title' ( normaleent la marque = premier mot du critère)</h3>";

        if (count($titleForMarque) == 0) {
            $htmlpage .= "<br><b>aucun enregistrement trouvé dans la base pour la marque $marque</b>";
        }else{
            $htmlpage .= "<table>";
            $htmlpage .= "<thead>";
            $htmlpage .= "<tr><th>Titre</th><th>Sm</th></tr>";
            $htmlpage .= "</thead>";
            $htmlpage .= "<tbody>";
            foreach($titleForMarque as $m) {
                $htmlpage .= "<tr>";
                $htmlpage .= "<td>".htmlentities($m)."</td>";
                $htmlpage .= '<td data-title="td_'.htmlentities($m).'"><button onclick="displayDetailTitle(this, \''.htmlentities($m).'\')">Détail</button> </td>';
                $htmlpage .= "</tr>";
            }
            $htmlpage .= "</tbody></table>";
        }
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
            <li>compléter la base en y ajoutant celui que vous avez recherché complété des info de celui que vous avez trouvé</li>
        </ul>
        <div id="chooseSm_tab">
        </div>
        Voulez-vous ajouter ce nouveau smartphone dans la base<br>
        votre nom : <input type="text" id="username"><br>
        <button onclick="addInDb()">ajouter</button><br>
        <span id="chooseSm_msg"></span>
    </div>
</div>

<div id="msg_div" class="modal">
    <!-- Modal content -->
    <div  class="modal-content">
        <span id = "msg_close" class="close">&times;</span>
        <div id="msg_text">
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