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
	$.ajax({ 
		url: "/bb/ajax.php",
		success: function(data) { $('#bb_topics').html(data); }
	});
}

function helpHide(user_id) {
	$("#help_text").slideUp("slow");
	css_add(document.getElementById("hide_help_btn"), 'hidden');
	css_remove(document.getElementById("show_help_btn"), 'hidden');
	ajax_set('users', 'help', user_id, 0);
	return false;
}

function helpShow(user_id) {
	$("#help_text").slideDown("slow");
	css_add(document.getElementById("show_help_btn"), 'hidden');
	css_remove(document.getElementById("hide_help_btn"), 'hidden');
	ajax_set('users', 'help', user_id, 1);
	return false;
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