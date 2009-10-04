<?
include('../include.php');

if ($posting) {
	//debug();
	error_debug('user is posting', __file__, __line__);
	if ($uploading) list($_POST['content'], $_POST['type_id']) = file_get_uploaded('userfile', 'docs_types');
	$id = db_save('docs');
	debug();
	db_checkboxes('docs', 'docs_to_categories', 'documentID', 'categoryID', $id);
	if (getOption('channels')) db_checkboxes('channels', 'docs_to_channels', 'doc_id', 'channel_id', $id);
	exit;
	url_change('/docs/info.php?id=' . $id);
}

if (url_id()) {
	$d = db_grab('SELECT title, description FROM docs WHERE id = ' . $_GET['id']);
	$pageAction = 'Edit Document';
} else {
	$pageAction = 'Add Document';
}

drawTop();


//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query('SELECT description, extension FROM docs_types ORDER BY description');
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t['extension'] . '")';
	$doctypes[] = ' - ' . $t['description'] . ' (.' . $t['extension'] . ')';
}
echo drawMessage('The maximum size you can upload here is ' . file_get_max() . '.');
?>
<script language='javascript'>
	<!--
	function validate(form) {
		tinyMCE.triggerSave();
		if (!form.title.value.length) {
			alert('Please enter a title for this document.');
			return false;
		}
		if (!form.description.value.length) {
			alert('Please enter a description for this document.');
			return false;
		}
		oneFound = false;
		for (var i = 0; i < form.elements.length; i++) {
			var checkParts = form.elements[i].name.split('_');
			if ((checkParts[0] == 'chk') && (form.elements[i].checked)) oneFound = true;
		}
		if (!oneFound) {
			alert('Please select a category.');
			return false;
		}
		if (!form.userfile.value.length) {
			<? if (!isset($_GET['id'])) {?>
			alert('Please select a file to upload.');
			return false;
			<? }?>
		} else {
			var arrFile   = form.userfile.value.split('.');
			var extension = arrFile[arrFile.length - 1].toLowerCase();
			if (<?=implode(' && ', $extensions)?>) {
				alert('Only these filetypes are supported by this system:\n\n <?=implode('\\n', $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filetitle.');
				return false;
			}
		}
		return true;
	}
	//-->
</script>
<?
$form = new intranet_form;
$form->addRow('itext',  'Title', 'title', @$d['title'], '', true, 65);
$form->addRow('textarea', 'Description', 'description', @$d['description']);
$form->addCheckboxes('docs', 'Categories', 'docs_categories', 'docs_to_categories', 'documentID', 'categoryID', @$_GET['id']);
if (getOption('channels')) $form->addCheckboxes('channels', 'Networks', 'channels', 'docs_to_channels', 'doc_id', 'channel_id', $_GET['id']);
$form->addRow('file', 'Document', 'userfile');
$form->addRow('submit',   'Save Changes');
$form->draw($pageAction);


drawBottom();
?>