
function lgvas_isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

var lgvas_globalId = 1;

function lgvas_onAdd(maxCnt) {
	// Fügt ganz hinten ein Eintrag dazu; dazu wird die "id" des neuen Eintrages übergeben
	//document.forms[#].elements["lgvas_para_anzahl" + id].style.visibility = 'hidden';
	if (lgvas_globalId <= maxCnt) {
		document.getElementById("lgvas_para_anzahl_root" + lgvas_globalId).style.display = '';
		document.getElementById("lgvas_para_anzahl_visible" + lgvas_globalId).value = '1';
		errMsgElm = document.getElementById("lgvas_para_anzahl" + lgvas_globalId + "_error");
		if (errMsgElm != null) {
			errMsgElm.style.display = 'none';
		}
		document.getElementById("lgvas_para_anzahl" + lgvas_globalId).focus();
		
		lgvas_globalId++;
	}
	if (lgvas_globalId > maxCnt) {
		document.getElementById("lgvas_para_anzahl_add").style.display = 'none';
	}
}

function lgvas_onRemove(id, maxCnt) {
	// Entferne den angegebenen Eintrag...
	// Dazu müssen aber alle nachfolgenden Einträge nachrutschen... somit ist der aktuelle entfernt ;)
	for (i=id; i<maxCnt; i++) {
		if (i < lgvas_globalId) {
			val = document.getElementById("lgvas_para_anzahl" + (i+1)).value;
			document.getElementById("lgvas_para_anzahl" + i).value = val;
		} else {
			document.getElementById("lgvas_para_anzahl" + i).value = "";
		}
	}
	// Entferne jetzt den letzten sichtbaren...
	lgvas_globalId--;
	document.getElementById("lgvas_para_anzahl_root" + lgvas_globalId).style.display = 'none';
	document.getElementById("lgvas_para_anzahl_visible" + lgvas_globalId).value = '';

	// und zeige auch wieder den "Hinzufügen" Button an, wenn wieder welche frei sind...
	if (lgvas_globalId == maxCnt) {
		document.getElementById("lgvas_para_anzahl_add").style.display = '';
	}
}

function lgvas_onAdd_onLoad(startId, maxCnt) {
	lgvas_globalId = startId;
	if (lgvas_globalId > maxCnt) {
		document.getElementById("lgvas_para_anzahl_add").style.display = 'none';
	}
}


// Payment scripts

function lgvas_payment_selection_onChange(newValue) {
	if (newValue === "bar") {
		document.getElementById("lgvas_payment_sepa_tr_desc").style.display = 'none';
		document.getElementById("lgvas_payment_sepa_tr_konto").style.display = 'none';
		document.getElementById("lgvas_payment_sepa_tr_iban").style.display = 'none';
		document.getElementById("lgvas_payment_sepa_tr_bic").style.display = 'none';
		document.getElementById("lgvas_payment_sepa_tr_cb").style.display = 'none';
	}
	if (newValue === "sepa") {
		document.getElementById("lgvas_payment_sepa_tr_desc").style.display = '';
		document.getElementById("lgvas_payment_sepa_tr_konto").style.display = '';
		document.getElementById("lgvas_payment_sepa_tr_iban").style.display = '';
		document.getElementById("lgvas_payment_sepa_tr_bic").style.display = '';
		document.getElementById("lgvas_payment_sepa_tr_cb").style.display = '';
	}
	console.info(newValue);
}