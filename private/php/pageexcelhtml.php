<?php
declare(strict_types=1);
/****************************************************************************
 * HTML page Upload Excel
**************************************************************************** */
function getUploadHtmlHead() {
	$retour = getHtmlHead();
	$retour .= <<<EOT
	<link rel="stylesheet" href="upload.css"/>
    <script src="upload.js"></script>
    </head>
EOT;
	return $retour;
}

/** =========== PAGE de Upload ================== */
function getHtmlExcel() {
	GLOBAL $debug;
	$menuInitial = "/?".$_SERVER['QUERY_STRING'];
	$isConnected = false;
	$extention   = "";
	if(array_key_exists('emmaususerconnected',$_SESSION) && $_SESSION['emmaususerconnected'] == 'Y') {
		$isConnected = true;
		$extention   = "_C";  // pour qualifier les actions associées aux choix des vesions excel
	}
	$retour = "";
	$styleProgress = "block";
	$artdebug      = "block";
	if ($debug !=1 ) { 
		$styleProgress = "none";
		$artdebug      = "none";
	}
	//$menuInitial = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']."/?".$_SERVER['QUERY_STRING'];
	$retour = getHtmlHeader();
	//echo getHtmlHeader();

	$retour .= <<<"EOT"
	<script>
	/** **************************************************************************
	 *  container global
	 */
	varglobal = [];
	varglobal['templates'] = {};

	/** ***************************************************************************
	 * body onload : charge les nom de templates
	 */
	async function execInitPage() {
		document.getElementById("waitsavetemplate").style.display = 'none';
		document.getElementById("divexcel").hidden = true;
		await updateVersionOption();
		document.getElementById("divexcel").hidden = false;
	}
	
	async function updateVersionOption() {
		await getConfigColFromSrv();
		fillVersionOption();
	}

	/**
	 * crée les tag option de la boite de sélection de version
	 */
	function fillVersionOption() {
		let select    = document.getElementById('choixversion');
		let configCol = varglobal['templates'];
		let c= Object.keys(configCol);
		let opt = document.createElement('option');
		opt.value = '';
		opt.innerHTML = '-- choisissez un modèle';
		select.appendChild(opt);
		c.sort();
		c.forEach(versionName => {
			let opt = document.createElement('option');
			opt.value = versionName;
			opt.innerHTML = versionName + " : " + configCol[versionName]['templatedesc'] + ' (' +configCol[versionName]['updatedby'] +')';
			select.appendChild(opt);
		})
	}

	function removeOptions() {
		let selectElement    = document.getElementById('choixversion');
		var i, L = selectElement.options.length - 1;
		for(i = L; i >= 0; i--) {
		   selectElement.remove(i);
		}
	 }

	/** ****************************************************************************
	 * affiche l'écran en fonction du choix de version fait par l'utilisateur.
	 * 
	 * @param {string} version
	 */
	async function choixVersion(version) {
		const contexte = "$extention"; // "_C" si connecté
		document.getElementById("excelchoixversion").style.display = 'none';
		switch (version) {
			case "*BOLC" :
				//openMsg();
				setColumn(version);
				//closeMsg()
				document.getElementById("versionchoisienum").innerHTML= version ;
				if (contexte == "") {
					excelRecalculOpen();
				}else{
					document.getElementById("formexcel").style.display = 'block';
				}
				break;
			case "choisir" :
				document.getElementById("versionchoisienum").innerHTML=" libre ";
				setColumn('*BOLC');
				document.getElementById("formexcel").style.display = 'block';
				break;
			case "jeudetest" :
				setColumn("jeudetest");
				document.getElementById("versionchoisienum").innerHTML=" jeux de test ";
				document.getElementById("formexcel").style.display = 'block';
				break;				
		}
	}

	function openMsg() {
        document.getElementById("popupForm").style.display = "block";
      }
      function closeMsg() {
        document.getElementById("popupForm").style.display = "none";
      }

	function checkColInputErreur() {
		var retour ="";
		var className = document.getElementsByClassName("colinputcollection");
		for(var index=0; index < className.length; index++){
			if (className[index].checkValidity() == false) {
			   console.log(className[index].innerHTML);
				retour = "KO";
			}
		}
		return retour;
	}

	function displayErrMsg1(msg) {
		document.getElementById('majtemplateresponse').innerHTML = msg;
		document.getElementById('majtemplateresponse').classList.remove('msgsuccesstext');
		document.getElementById('majtemplateresponse').classList.add('msgerrtext');
	}

	function displaySuccessMsg1(msg) {
		document.getElementById('majtemplateresponse').innerHTML = msg;
		document.getElementById('majtemplateresponse').classList.remove('msgerrtext');
		document.getElementById('majtemplateresponse').classList.add('msgsuccesstext');
	}


	/**
	 * affiche la div demandant s'il faut recalculer les catégories déjà renseignées dans le fichier
	 */
	function excelRecalculOpen() {
		document.getElementById("excelrecalculerreur").style.color = "";
		document.getElementById("excelrecalcul").style.display = 'block';
	}

	/**
	 * gère le click sur l'item select de version
	 * 
	 * @param {object} elem html item qui a été cliqué
	 */
	function applyVersion(elem) {
		let e=elem;
		setColumn(elem.value);
	}

	/**
	 * assigne le numéro de la ligne d'en-tête et le colonnage dans le formulaire
	 * 
	 * @param {string} version nom du template à appliquer
	 */ 
	function setColumn(version = "*BOLC") {
		document.getElementById('choixversion').value=version;
		let config1 = getConfigCol(version);
		let config = config1['data'];
		if (config != "") {
			document.getElementById("ligneentete").value          =  config["ligneentete"];
			document.getElementById("colnumlot").value            =  config["colnumlot"];
			document.getElementById("colidentifiantunique").value =  config["colidentifiantunique"];
			document.getElementById("coltypemateriel").value      =  config["coltypemateriel"];
			document.getElementById("colconstructeur").value      =  config["colconstructeur"];
			document.getElementById("colpcmodel").value           =  config["colpcmodel"];
			document.getElementById("colnumserie").value          =  config["colnumserie"];
			document.getElementById("colcpu").value               =  config["colcpu"];
			document.getElementById("coltypedisk").value          =  config["coltypedisk"];
			document.getElementById("coltailledisk").value        =  config["coltailledisk"];
			document.getElementById("coltypedisk2").value         =  config["coltypedisk2"];
			document.getElementById("coltailledisk2").value       =  config["coltailledisk2"];
			document.getElementById("coltailleram").value         =  config["coltailleram"];
			document.getElementById("coldvd").value               =  config["coldvd"];
			document.getElementById("colwebcam").value            =  config["colwebcam"];
			document.getElementById("colecran").value             =  config["colecran"];
			document.getElementById("colremarque").value          =  config["colremarque"]
			document.getElementById("colgradeesthetique").value   =  config["colgradeesthetique"]
			document.getElementById("colcategorie").value         =  config["colcategorie"];
			document.getElementById("colerreur").value            =  config["colerreur"];
			document.getElementById("saveastemplatename").value   =  config1["templatename"];
			document.getElementById("saveastemplatedesc").value   =  config1["templatedesc"];
		}
	}
	//*************************************************************************************
	// gestion des CONTINUER et RETOUR ====================================================
	//*************************************************************************************

	/**
	 * gère le click sur le bouton "continuer"
	 * 
	 * @param {string} origine  id de la div contenant le bouton
	 */
	function continuer(origine) {
		document.getElementById(origine).style.display = 'none';
		switch(origine) {
			case "excelintro":
				document.getElementById("excelchoixversion").style.display = 'block';
				break;
			case "formexcel":
				var inputError = checkColInputErreur();
				if (inputError != "KO") {
					displaySuccessMsg1("");
					excelRecalculOpen();
				}else{
					displayErrMsg1('Veuillez corriger les erreurs');
					document.getElementById(origine).style.display   = 'block';
				}
				break;
			case "excelrecalcul":
				document.getElementById("excelenvoyer").style.display = 'block';
				break;
		}
	}

	/**
	 * gère le click sur le bouton "retour"
	 * 
	 * @param {string} origine  id de la div contenant le bouton
	 */
	function retour(origine) {
		const contexte = "$extention"; // "_C" si connecté
		document.getElementById(origine).style.display = 'none';
		switch(origine) {
			case "formexcel":
				document.getElementById("excelchoixversion").style.display = 'block';
				break;
			case "excelrecalcul":
				if (contexte === "") {
					document.getElementById("excelchoixversion").style.display = 'block';
				}else{
					document.getElementById("formexcel").style.display = 'block';
				}
				break;
			case "excelenvoyer":
				document.getElementById("excelchoixversion").style.display = 'block';
				break;
			case "exceldisplayresult" :
				document.getElementById("excelchoixversion").style.display = 'block';
				break;
		}
	}

	/**
	 * traite le clique sur le bouton "continuer" de la div "voulez vous écraser le précédent calcul"
	 * 
	 * vérifie que le choix a été fait et ne pass aà la suite que si c'est OK
	 */
	function excelRecalculCheck() {
		const bYes = document.getElementById("recalculcategorieyes");
		const bNo  = document.getElementById("recalculcategorieno");
		if (!bYes.checked && !bNo.checked) {
			document.getElementById("excelrecalculerreur").style.color = "red";
		}else{
			continuer("excelrecalcul");
		}
	}

	//*************************************************************************************
	//  Gestion des templates
	//*************************************************************************************
	/**
	 * retourne le json avec les colonne d'un template
	 * 
	 * @param {string} version nom du template
	 */
	function getConfigCol(version) {
		let retour = {};
		const c= varglobal['templates'];
		if ( c[version] !== undefined) {
			retour = varglobal['templates'][version];
		}
		return retour;
	}


	/**
	 * gestion du click sur le bouton "sauvegarder le modèle"
	 */
	async function saveAsTemplate() {
		document.getElementById('majtemplateresponse').innerHTML = '';
		document.getElementById('savetemplate').style.display = 'none';
		document.getElementById("waitsavetemplate").style.display = 'block';
		let response = await sendTemplateUpdate('update');
		removeOptions();
		await updateVersionOption();
		document.getElementById('choixversion').value=document.getElementById("saveastemplatename").value;
		document.getElementById("waitsavetemplate").style.display = 'none';
		document.getElementById('savetemplate').style.display = 'block';
	}

	/**
	 * gestion du click sur le bouton "supprimer le modèle"
	 */
	async function deleteTemplate() {
		document.getElementById('majtemplateresponse').innerHTML = '';
		document.getElementById('savetemplate').style.display = 'none';
		document.getElementById("waitsavetemplate").style.display = 'block';
		let response = await sendTemplateUpdate('delete');
		removeOptions();
		await updateVersionOption();
		document.getElementById("waitsavetemplate").style.display = 'none';
		document.getElementById('savetemplate').style.display = 'block';
	}

	/**
	 * construit le JSON du template à mettre à jour et l'envoie
	 */
	async function sendTemplateUpdate(todo) {
		let formData = buildJsonsTemplateFromForm();
		Object.keys(formData).forEach(function(key) {
			formData[key] = formData[key].toUpperCase();
		  })
		let dataJson= {};
		dataJson["operation"]     = todo;
		dataJson["templatename"]  = document.getElementById("saveastemplatename").value;
		dataJson["templatedesc"]  = document.getElementById("saveastemplatedesc").value;
		dataJson["data"] = formData;
		let response = await sendTemplatePost(dataJson);
		return response;
	}

	/**
	 * envoie du JSON du template vers le serveur
	 */
	async function sendTemplatePost(dataJson) {
		let response = await fetch('/extemplatesupdate.php', {
			method: 'POST',
			headers: {
			  'Content-Type': 'application/json;charset=utf-8'
			},
			body: JSON.stringify(dataJson)
		  });

		  let responseJson = await response.json();
		  //let responseJson = JSON.parse(result);
		  if (responseJson['status'] !== 'OK') {
			  displayErrMsg1(responseJson['msg'])
		  }else{
			  displaySuccessMsg1(responseJson['msg'])
		  }
	}

	/**
	 * envoie du JSON du template vers le serveur
	 */
	async function sendTemplatePostOLD(dataJson) {
		let xhr = new XMLHttpRequest();
		xhr.open("POST", "/extemplatesupdate.php?a=b");
		
		xhr.setRequestHeader("Accept", "application/json", true);
		xhr.setRequestHeader('Content-Type', 'application/json');
		
		xhr.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				let responseJson = JSON.parse(xhr.responseText);
				if (responseJson['status'] !== 'OK') {
					displayErrMsg1(responseJson['msg'])
				}else{
					displaySuccessMsg1(responseJson['msg'])
				}
			}
		};
		
		let data = JSON.stringify(dataJson)
		
		xhr.send(data);
	}

	/**
	 * lit les templates sur le serveur et les stocke dans varglobal['templates']
	 * 
	 * {
	 *   "operation": "update",
	 *   "templatename": "xt",
	 *   "templatedesc": "descrip",
	 *   "data": {
	 *     "ligneentete": "4",
	 *     "colnumlot": "xt",
	 *     "colidentifiantunique": "B",
	 *     "coltypemateriel": "C",
	 *     "colconstructeur": "D",
	 *     "colpcmodel": "E",
	 *     "colnumserie": "F",
	 *     "colcpu": "G",
	 *     "coltypedisk": "H",
	 *     "coltailledisk": "I",
	 *     "coltypedisk2": "J",
	 *     "coltailledisk2": "K",
	 *     "coltailleram": "L",
	 *     "coldvd": "M",
	 *     "colwebcam": "N",
	 *     "colecran": "O",
	 *     "colremarque"
	 *     "colgradeesthetique"
	 *     "colcategorie": "P",
	 *     "colerreur": "S"
	 *   }
	 * }
	 */
	async function getConfigColFromSrv() {
		const responseJson = await getTextFile("/extemplatesget.php");
		const t1 = JSON.parse(responseJson['data'])
		const t2 = t1['data']; // tableau nnom => 
		varglobal['templates'] = t2;
	}

	/**
	 * retour un Promise pour lire un fichier texte sur le serveur
	 * 	 * 
	 * retour : json
	 *   status : OK / KO
	 *   errmsg : text
	 *   data   : la réponse
	 * 
	 * @ url url du fichier à lire
	 */
	function getTextFile(url) {
		return new Promise((resolve) => {
			let req = new XMLHttpRequest();
			req.open('GET', url);
			let repJson = {"status":"" , "errmsg":"" , "data":""};
			req.onload = function() {
				if (req.status == 200) {
					let reptext = req.response;
					repJson = {"status":"OK" , "errmsg":"" , "data":reptext};
					resolve(repJson);
				} else {
					repJson = {"status":"KO" , "errmsg":"File not Found" , "data":""};
					resolve(repJson);
				}
			};
			req.onerror = function() {
				repJson = {"status":"KO" , "errmsg":"Erreur de serveur" , "data":""};
				resolve(repJson);
			}
			req.send();
		});
	}

	/** *****************************************************************************************
	 * construit le JSON contenant les valeurs du templates à partir du formulaire
	 */
	function buildJsonsTemplateFromForm() {
		var retour = {};
		retour["ligneentete"]          = document.getElementById("ligneentete").value;
		retour["colnumlot"]            = document.getElementById("colnumlot").value;
		retour["colidentifiantunique"] = document.getElementById("colidentifiantunique").value;
		retour["coltypemateriel"]      = document.getElementById("coltypemateriel").value;
		retour["colconstructeur"]      = document.getElementById("colconstructeur").value;
		retour["colpcmodel"]           = document.getElementById("colpcmodel").value;
		retour["colnumserie"]          = document.getElementById("colnumserie").value;
		retour["colcpu"]               = document.getElementById("colcpu").value;
		retour["coltypedisk"]          = document.getElementById("coltypedisk").value;
		retour["coltailledisk"]        = document.getElementById("coltailledisk").value;
		retour["coltypedisk2"]         = document.getElementById("coltypedisk2").value;
		retour["coltailledisk2"]       = document.getElementById("coltailledisk2").value;
		retour["coltailleram"]         = document.getElementById("coltailleram").value;
		retour["coldvd"]               = document.getElementById("coldvd").value;
		retour["colwebcam"]            = document.getElementById("colwebcam").value;
		retour["colecran"]             = document.getElementById("colecran").value;
		retour["colremarque"]          = document.getElementById("colremarque").value;
		retour["colgradeesthetique"]   = document.getElementById("colgradeesthetique").value;
		retour["colcategorie"]         = document.getElementById("colcategorie").value;
		retour["colerreur"]            = document.getElementById("colerreur").value;
		return retour
	}


	</script>

	<article id="divexcel" >
	<h2>Traitement d'un Excel</h2>

	<div id="excelsaisie">
		<!-- CHOIX DE LA VERSION -->
		<div id="excelchoixversion" class="div-saisie">
			<div class="menuoption" style="padding:0px 0px 0px 0px;">
				<h3>Introduction
				<img src="images/icones/aide.png" onclick="parent.open('exdisplayvideoaidecolonne.htm')" style="position: relative;
				left: 50px;top: 0px; " height=20px></h3>
				<p>Ce site permet de calculer la catégorie des PC listés dans un Excel.<br>
				<span style="color:red">La feuille à analyser doit être la première du fichier.<br>
				Il se peut que certaines formules fassent échouer le traitement</span></p>

				<h3>Choix du modèle</h3>
				Cliquez sur l'image du modèle d'Excel que vous voulez traiter :<br><br>
				<img src="images\xls_v2.jpg" alt="version 2" width="820px" class="img-border overborder" onclick="choixVersion('*BOLC')"><br>
				<br>
EOT;

	if($isConnected) {
		$retour .= <<<'EOT'
				comme vous vous êtes identifié, vous avez aussi les options ci-dessous<br>
				<br>
				<a class="ec-btn menuoption"  alt="tout choisir" onclick="choixVersion('choisir')">Je veux choisir mes colonnes</a><br>
				<!--
				<br>
				<a class="ec-btn menuoption"  alt="jeu de test"  onclick="choixVersion('jeudetest')">Jeu de test (pour les développeurs)</a><br>
				-->
EOT;
	}

	$retour .= <<<"EOT"
				<div class="div-menu-bas">
					<a class="ec-btn ec-nav" href="$menuInitial"  style="float: left;" target="_self" alt="retour au menu initial">Retour</a><br>
				</div>
			</div>
		</div>
EOT;
$retour .= <<<'EOT'
		<!-- CHOIX DES COLONNES -->
		<div id="formexcel" class="div-saisie" style="display:none">
			<p>Vous avez choisi la version <span id="versionchoisienum"></span>.<br>
			Vous pouvez choisir un colonnage mémorisé ou indiquer les colonnes de votre excel</p>

			<p>A la fin du traitement, un lien vous permettra de charger le résultat.</p>

			<label for="choixversion">Indiquez le modèle d'excel que vous utilisez</label>
			<select name="choixversion" id="choixversion" onchange="applyVersion(this)">
			</select> 
			<hr>
			<form id="fileChoice">

				<p><label for="ligneentete">N° ligne en-tête de colonne *</label>
				  <input id="ligneentete" name="ligneentete" type='number' class="colinputcollection" size="4"  min="1" max="1000" required ></p>
	
				<p><label for="colnumlot">Numéro Lot</label>
				  <input id="colnumlot" name="colnumlot"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colidentifiantunique">Identifiant unique</label>
				  <input id="colidentifiantunique" name="colidentifiantunique"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="coltypemateriel">Type matériel</label>
				  <input id="coltypemateriel" name="coltypemateriel"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colconstructeur">Constructeur</label>
				  <input id="colconstructeur" name="colconstructeur"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colpcmodel">Colonne modèle du PC</label>
				  <input id="colpcmodel" name="colpcmodel" type='text' class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colnumserie">N° Serie</label>
				  <input id="colnumserie" name="colnumserie"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colcpu">Colonne cpu *</label>
				  <input id="colcpu" name="colcpu" type='text' class="colinputcollection" size="2" required pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="coltypedisk">Colonne type disque 1 *</label>
				  <input id="coltypedisk" name="coltypedisk" type='text' class="colinputcollection" size="2" required pattern="[a-zA-Z]{1,2}" maxlength="2"></p>
				<p><label for="coltailledisk">Colonne taille disque 1 *</label>
				  <input id="coltailledisk" name="coltailledisk" type='text' class="colinputcollection" size="2" required pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="coltypedisk2">Colonne type disque 2</label>
				  <input id="coltypedisk2" name="coltypedisk2" type='text' class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>
				<p><label for="coltailledisk2">Colonne taille disque 2</label>
				  <input id="coltailledisk2" name="coltailledisk2" type='text' class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="coltailleram">Colonne taille RAM *</label>
				  <input id="coltailleram" name="coltailleram" type='text' class="colinputcollection" size="2" required pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="coldvd">DVD</label>
				  <input id="coldvd" name="coldvd"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colwebcam">Webcam</label>
				  <input id="colwebcam" name="colwebcam"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colecran">ECRAN</label>
				  <input id="colecran" name="colecran"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colremarque">Remarque</label>
				<input id="colremarque" name="colremarque"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colgradeesthetique">Grade esth.</label>
				 <input id="colgradeesthetique" name="colgradeesthetique"type="text" class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"></p>

				<p><label for="colcategorie">Colonne catégorie PC *</label>
				  <input id="colcategorie" name="colcategorie" type='text' class="colinputcollection" size="2" required pattern="[a-zA-Z]{1,2}" maxlength="2"> (contiendra la catégorie ou un message d'erreur)</p>

				<p><label for="colerreur">Colonne erreur</label>
				  <input id="colerreur" name="colerreur" type='text' class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2"> (si à blanc, les erreurs seront placées dans la colonne catégorie)</p>
EOT;
	if ($isConnected) {
		$retour .= <<< EOT
				<p><label for="coldebug">Colonne debug</label>
					<!-- <input id="coldebug" name="coldebug" type="checkbox" value="Y" checked="no"/> -->
					<input id="coldebug" name="coldebug" type='text' class="colinputcollection" size="2" pattern="[a-zA-Z]{1,2}" maxlength="2">

					si renseignée, met le détail du calcul dans l'Excel (réservé à @emmaus-connect)
				</p>
EOT;
	}

	$retour .= <<<"EOT"
				<div id="savetemplate">
					<div id="savetemplate" style="
						border-top-style: solid;
						border-top-width: 1px;
						padding: 5px 0px 0px 0px;
						margin: 5px 0px 0px 0px;
					">
						<a class="ec-btn" onclick="saveAsTemplate()">Sauvegarder sous</a>&nbsp;<a class="ec-btn" onclick="deleteTemplate()">supprimer</a><br><br>
						&nbsp;&nbsp;nom : <input id="saveastemplatename" name="saveastemplatename" type='text' class="colinputcollection" size='30'>
						&nbsp;&nbsp;description : <input id="saveastemplatedesc" name="saveastemplatedesc" type='text' class="colinputcollection" size='50'>
						<br>
						<span id="majtemplateresponse"></span>
					</div>
	EOT;
		$retour .= <<<"EOT"
					<div class="div-menu-bas">
						<a class="ec-btn ec-nav" onclick="retour('formexcel')" style="float: left;">Retour</a>
						<a class="ec-btn ec-nav" onclick="continuer('formexcel')" style="float: right;">Continuer</a><br><p>
					</div>
				</div>
				<div id="waitsavetemplate" style="margin: auto;width: 60%;text-align:center;"><img src="images/icones/wait28.gif"></div>
			</div>

		<!-- RECALCUL -->
		<div id="excelrecalcul" class="div-saisie" style="display:none">
			<div class="menuoption" style="padding:0px 0px 0px 0px;">
				<h3>Options avancées</h3>
				<h4>Recalcul des catégories</h4>
				<p>
					Votre Excel contient peut-être des lignes avec des catégories de PC déjà renseignées,<br><span id="excelrecalculerreur"> indiquez si vous voulez les recalculer</span>.<br>
					<p>
					<input type="radio" id="recalculcategorieyes" name="recalculcategorie" value="yes" onclick("excelrecalculclick();")>
					<label for="recalculcategorieyes">Ecraser les catégories de l'excel par les nouvelles valeurs</label><br>
					<input type="radio" id="recalculcategorieno" name="recalculcategorie" value="no"  onclick("excelrecalculclick();")>
					<label for="recalculcategorieno">Ne pas changer les catégories dejà présentes dans l'Excel</label><br>
					</p>
				</p>
EOT;
				if ($isConnected) {
					$retour .= '<div>';
					
				}else {
					$retour .= '<div style="display:none">';
				}

		$retour .= <<< EOT

						<h4>Unité Go par défaut</h4>

						<p>
							Votre Excel contient peut-être des tailles de disque ou RAM sans unité,<br><span id="excelunitepardefauterreur"> indiquez si vous voulez les forcer à Go</span>.<br>
							<div class="radioLeft">
							<p>
							<input type="radio" id="unitepardefautyes" name="unitepardefaut" value="yes">
							<label for="unitepardefautyes">Forcer à Go</label><br>
							<input type="radio" id="unitepardefautno" name="unitepardefaut" value="no">
							<label for="unitepardefautno">Ne pas forcer</label><br>
							</p>
							</div>
						</p>
						<h4>Type HDD par défaut</h4>
						<p>
							Votre Excel contient peut-être des disques sans type indiqué,<br><span id="exceltypediskpardefauterreur"> indiquez si vous voulez les forcer à HDD</span>.<br>
							<p>
							<div class="radioLeft">
							<input type="radio" id="typediskpardefautyes" name="typediskpardefaut" value="yes">
							<label for="typediskpardefautyes">Forcer à HDD</label><br>
							<input type="radio" id="typediskpardefautno" name="typediskpardefaut" value="no">
							<label for="typediskpardefautno">Ne pas forcer</label><br>
							</p>
							</div>
						</p>
					</div>
				</div>
				<div class="div-menu-bas">
					<a class="ec-btn ec-nav" onclick="retour('excelrecalcul')" style="float: left;">Retour</a>
					<a class="ec-btn ec-nav" onclick="excelRecalculCheck()" style="float: right;">Continuer</a><br><p>
				</div>
			</div>
		</div>

		<!-- ENVOI DU FICHIER -->
		<div id="excelenvoyer" class="div-saisie" style="display:none">
			<h3>Envoi du fichier</h3>
			</form>
			<div id="upzone" style="width:800px; height:150px">
			  Glissez le fichier à traiter ici
			</div>
			<br>
			<div class="div-menu-bas">
				<a class="ec-btn ec-nav" onclick="retour('excelenvoyer')" style="float: left;">Retour</a><br>
			</div>
		</div>

		<!-- ATTENTE -->
		<div id="excelattente" class="div-saisie" style="display:none">
			<h3>Avancement du traitement</h3>
			<div style="margin: auto;width: 60%;text-align:center"><img src="images/icones/wait28.gif"></div>
			
			<label id="percentage" for="file">Traitement</label> 
			<progress id='progressor' value="0" max="100" style="width:600px"></progress>
			<!-- Log msg avancement --> 
			<div id="upstat"></div>

			<div style="display:$styleProgress">
				<p>Progress</p>
				<div id="results" style="border:1px solid #000; padding:10px; width:800px; height:250px; overflow:auto; background:#eee;"></div>
			</div>
		</div>
		
		<!-- affichage du résultat -->
		<div id="exceldisplayresult" class="div-saisie" style="display:none">
			<h3>Résultat</h3>
			<div id="downloadlink" style="margin: auto;width: 60%;"></div>
			<br>
			<div id="downloadlink2" style="margin: auto;width: 60%;"></div>
			<br>
			<div style=" color: #e55314;">
				Il se peut que les formules de la feuille ne fonctionnent pas<br>
				Parfois cela se résout en forçant le recalcul :<br>
				* Excel : F9<br>
				* Libre office = Ctrl+Maj+F9<br>
			</div>
			<br />
			<div class="div-menu-bas">
				<a class="ec-btn ec-nav" href="/" style="float: left;" >Accueil</a>
				<a class="ec-btn ec-nav" onclick="retour('exceldisplayresult')" style="float: right;" >Traiter un autre Excel</a>
			</div>
		</div>
	</div>
	</article>
EOT;
	$retour .= <<<"EOT"
	<article id="artdebug" style="border-style:inset; width:850px; display:$artdebug">
	<h2>Débug</h2>
	<div id="divlog">
	<pre>
	<textarea id="log" rows="200" cols="100">
	<pre>
	<pre>
	</textarea>
	</pre>
	</div>
	</article>
    <div class="loginPopup">
      <div class="formPopup" id="popupForm">
        <div class="formContainer">
          traitement en cours


        </div>
      </div>
    </div>
EOT;
	$retour .= getFooter();
	$retour .= '</main>';
	return $retour;
}