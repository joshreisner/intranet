function validate_users_requests(form) {
	var errors = new Array();
	if (form_text_empty(form.firstname)) errors[errors.length] = 'The first name field is empty';
	if (form_text_empty(form.lastname)) errors[errors.length] = 'The last name field is empty';
	if (form_text_empty(form.email)) errors[errors.length] = 'The email field is empty';
	if (!form.organization_id.value.length) errors[errors.length] = 'Organization is not selected';
	if (!form.legal.checked) errors[errors.length] = 'You must agree to the terms of use';
	return form_errors(errors);
}

function refreshBB() {
	new Ajax.Request('/bb/ajax.php', {
		onSuccess: function(transport) {
			document.getElementById('bb_topics').innerHTML = transport.responseText;
		}
	});	
}

function showHelp(id, value) {
	ajax_set('users', 'help', id, value);
}

function set_users_help(value) {
	//value has already been set to this
	if (value == 0) {
		new Effect.BlindUp("helptext");
		document.getElementById("showhelp").innerHTML = 'Show Help';
		document.getElementById("showhelp").href = "javascript:ajax_set('users','help','session',1);";
	} else if (value == 1) {
		new Effect.BlindDown("helptext");
		document.getElementById("showhelp").innerHTML = 'Hide Help';
		document.getElementById("showhelp").href = "javascript:ajax_set('users','help','session',0);";
	} else {
		alert(value);
	}
}

function toggleCheckbox(which) {
	document.getElementById(which).checked = !document.getElementById(which).checked;
}

function toggleCheckboxes(chkd) {
	for (var i = 0; i < document.frmUserList.elements.length; i++) {
		if (document.frmUserList.elements[i].name.indexOf("chk") != -1) document.frmUserList.elements[i].checked = chkd;
	}
}

function triggerEmailTo() {
	var oneFound = false;
	var arrEmails = new Array();
	var arrRecord = new Array();
	for (var i = 0; i < document.frmUserList.elements.length; i++) {
		if (document.frmUserList.elements[i].name.indexOf("chk") != -1) {
			if (document.frmUserList.elements[i].checked) {
				arrRecord = document.frmUserList.elements[i].name.split("_");
				if (arrRecord[2].length > 0) {
					oneFound = true;
					arrEmails[arrEmails.length] = arrRecord[2];
				}
			}
		}
	}
	
	if (oneFound) {
		location.href="mailto:" + arrEmails.join(";");
	} else {
		alert("No checkboxes are selected that correspond to email addresses!");
	}
}
				
function doSearch(form) {
    if (!form.q.value.length) return false;
    return true;
}