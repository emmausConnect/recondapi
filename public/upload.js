var ddup = {
  // v 2022/05/06 16h42
  // (A) ON PAGE LOAD
  hzone: null, // HTML upload zone
  hstat: null, // HTML upload status

  init: function () {
    // (A1) GET HTML ELEMENTS
    ddup.hzone = document.getElementById("upzone");
    ddup.hstat = document.getElementById("upstat");
    ddup.hform2 = document.getElementById("uploadButton");

    //document.getElementById("excelresult").style.display = "none";

    // (A2) DRAG-DROP SUPPORTED
    if (window.File && window.FileReader && window.FileList && window.Blob) {
      // HIGHLIGHT DROPZONE ON FILE HOVER
      ddup.hzone.addEventListener("dragenter", function (e) {
        e.preventDefault();
        e.stopPropagation();
        ddup.hzone.classList.add('highlight');
      });
      ddup.hzone.addEventListener("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        ddup.hzone.classList.remove('highlight');
      });

      // DROP TO UPLOAD FILE
      ddup.hzone.addEventListener("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
      });
      ddup.hzone.addEventListener("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        ddup.hzone.classList.remove('highlight');
        ddup.queue(e.dataTransfer.files);
      });
    }
    // (A3) DRAG-DROP UPLOAD NOT SUPPORTED
    else {
      ddup.hzone.style.display = "none";
    }

    ddup.hform2.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var a = document.getElementById('upload_file');
      var f = a.files[0];
      ddup.queue(a.files);
    });
  },

  // (B) UPLOAD QUEUE + HANDLER
  // NOTE: AJAX IS ASYNCHRONOUS
  // A QUEUE IS REQUIRED TO STOP SERVER FLOOD
  upqueue: [], // upload queue
  uplock: false, // currently uploading a file
  queue: function (files) {
    // FILE LIST INTO QUEUE
    for (let f of files) {
      // OPTIONAL - SHOW UPLOAD STATUS
      ddup.hstat.innerHTML += `<div>"${f.name}" - Ajouté à la file d'attente</div>`;
      // ADD TO QUEUE
      ddup.upqueue.push(f);
    }
    // GO!
    ddup.go();
  },

  // (C) AJAX UPLOAD
  go: function () {
    if (!ddup.uplock && ddup.upqueue.length != 0) {

      // (C1) QUEUE STATUS UPDATE
      ddup.uplock = true;

      // (C2) PLUCK OUT FIRST FILE IN QUEUE
      let thisfile = ddup.upqueue[0];
      ddup.upqueue.shift();

      // OPTIONAL - SHOW UPLOAD STATUS
      ddup.hstat.innerHTML += `<div>"${thisfile.name}" - Traitement en cours</div>`;

      // (C3) UPLOAD DATA

      // (C4) AJAX REQUEST
      //ddup.hstat.innerHTML = "";
      trtexcelfile(thisfile);

      document.getElementById("excelenvoyer").style.display = "none";
      //setTimeout(startProgress(id), 2000); // pour laisser le temps au PHP de démarrer
    }
  },
  getId: function () {
    var id = Date.now() + '_' + Math.random().toString().substr(2, 8);
    return id;
  }
};

function getFormData(thisfile) {
  let data = new FormData();
  var id = ddup.getId();
  data.append('id', id);
  data.append('upfile', thisfile);
  switch (varglobal['type']) {
    case 'pc' :
      // colonnage pour PC
      data.append("ligneentete", document.getElementById("ligneentete").value);
      data.append("colnumlot", document.getElementById("colnumlot").value);
      data.append("colidentifiantunique", document.getElementById("colidentifiantunique").value);
      data.append("coltypemateriel", document.getElementById("coltypemateriel").value);
      data.append("colconstructeur", document.getElementById("colconstructeur").value);
      data.append("colpcmodel", document.getElementById("colpcmodel").value);
      data.append("colnumserie", document.getElementById("colnumserie").value);
      data.append("colcpu", document.getElementById("colcpu").value);
      data.append("coltailledisk", document.getElementById("coltailledisk").value);
      data.append("coltypedisk", document.getElementById("coltypedisk").value);
      data.append("coltailledisk2", document.getElementById("coltailledisk2").value);
      data.append("coltypedisk2", document.getElementById("coltypedisk2").value);
      data.append("coltailleram", document.getElementById("coltailleram").value);
      data.append("coldvd", document.getElementById("coldvd").value);
      data.append("colwebcam", document.getElementById("colwebcam").value);
      data.append("colecran", document.getElementById("colecran").value);
      data.append("colremarque", document.getElementById("colremarque").value);
      data.append("colgradeesthetique", document.getElementById("colgradeesthetique").value);
      data.append("colcategorie", document.getElementById("colcategorie").value);
      data.append("colerreur", document.getElementById("colerreur").value);
      break;
    case 'sm' :
      // colonnage smartphone
        data.append("ligneentete", document.getElementById("ligneentete").value);
        data.append("colnumlot", document.getElementById("colnumlot").value);
        data.append("colidentifiantunique", document.getElementById("colidentifiantunique").value);
        data.append("coltypemateriel", document.getElementById("coltypemateriel").value);
        data.append("colconstructeur", document.getElementById("colconstructeur").value);
        data.append("colmodel", document.getElementById("colmodel").value);
        data.append("colimei", document.getElementById("colimei").value);
        data.append("colcpu", document.getElementById("colcpu").value);
        data.append("colos", document.getElementById("colos").value);
        data.append("coltaillestockage", document.getElementById("coltaillestockage").value);
        data.append("coltailleram", document.getElementById("coltailleram").value);
        data.append("colbatterie", document.getElementById("colbatterie").value);
        data.append("colecran", document.getElementById("colecran").value);
        data.append("colecranresolution", document.getElementById("colecranresolution").value);
        data.append("colchargeur", document.getElementById("colchargeur").value);
        data.append("coloperateur", document.getElementById("coloperateur").value);
        data.append("colstatut", document.getElementById("colstatut").value);
        data.append("colremarque", document.getElementById("colremarque").value);
        data.append("colcouleur", document.getElementById("colcouleur").value);
        data.append("colgradeesthetique", document.getElementById("colgradeesthetique").value);
        data.append("colcategorie", document.getElementById("colcategorie").value);
        data.append("colerreur", document.getElementById("colerreur").value);
        break;
  //===========
  } // switch
  if (document.getElementById("coldebug")) {
    data.append("coldebug", document.getElementById("coldebug").value);
  }

  //data.append("recalculcategorie", document.getElementById("recalculcategorie").checked);
  if (document.getElementById("recalculcategorieyes").checked) {
    data.append("recalculcategorie", "yes");
  }else{
    data.append("recalculcategorie", "no");
  }
  if (document.getElementById("unitepardefautyes").checked) {
    data.append("unitepardefaut", "yes");
  }else{
    data.append("unitepardefaut", "no");
  }
  if (document.getElementById("typediskpardefautyes").checked) {
    data.append("typediskpardefaut", "yes");
  }else{
    data.append("typediskpardefaut", "no");
  }
  return data;
}

function trtexcelfile(thisfile) {
  document.getElementById("excelattente").style.display = "block";
  data = getFormData(thisfile)
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "extrtexcel"+varglobal['type']+".php?upload=1"); // varglobal['type'] : PC ou SM
  xhr.onerror = function(){
    console.log("error")
    ddup.hstat.innerHTML += '<span style="color: red;">Erreur lors du traitement.<br>Code retour :' + xhr.status + '</span>';
  }

  xhr.onload = function ($file) {
    var reponseJson;
    try {
      reponseJson = JSON.parse(xhr.responseText);
    } catch (error) {
      ddup.hstat.innerHTML += "UNE ERREUR A EU LIEU, MERCI DE PREVENIR EMMAUS-CONNECT :<br>";
      ddup.hstat.innerHTML += xhr.responseText;
    }
    if (document.getElementById('log')) {
      document.getElementById('log').value = reponseJson['log'];
    }

    if (reponseJson['status'] == 'OK') {
      let msg = '<div><a href="' + reponseJson['url'] + '" title="' + reponseJson['duree'] + 's" class="ec-btn" style="background-color:#029126">Charger le résultat</a></div>';
      //ddup.hstat.innerHTML += msg;
      document.getElementById("downloadlink").innerHTML= msg;
      if (reponseJson['url2'] !="") {
        msg = '<div><a href="' + reponseJson['url2'] + '" title="' + reponseJson['duree'] + 's" class="ec-btn" style="background-color:#029126">Charger le résultat BOLC</a></div>';
        //ddup.hstat.innerHTML += msg;
        document.getElementById("downloadlink2").innerHTML= msg;
      }
    } else {
      let msg = '<span style="color: red;">' + reponseJson['errmsg'] + '</span>';
      ddup.hstat.innerHTML += msg;
      document.getElementById("downloadlink").innerHTML= msg;
    }
    document.getElementById("excelattente").style.display = "none";
    document.getElementById("exceldisplayresult").style.display = "block";
  }

  xhr.onerror = function (evt) {
    // OPTIONAL - SHOW UPLOAD STATUS
    ddup.hstat.innerHTML += `<div>"${thisfile.name}" - AJAX ERROR</div>`;
    // NEXT BETTER PLAYER!
    ddup.uplock = false;
    ddup.go();
  };
  xhr.send(data);
}





function addLog(message) {
  var r = document.getElementById('results');
  r.innerHTML += message + '<br>';
  r.scrollTop = r.scrollHeight;
}

window.addEventListener("DOMContentLoaded", ddup.init);