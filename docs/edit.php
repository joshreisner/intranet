<?php
include('../include.php');

if ($posting) {
	error_debug('user is posting', __file__, __line__);
	if ($uploading) list($_POST['content'], $_POST['type_id']) = file_get_uploaded('content', 'docs_types');
	langTranslatePost('title,description');
	$id = db_save('docs');
	//debug();
	db_checkboxes('categories', 'docs_to_categories', 'documentID', 'categoryID', $id);
	if (getOption('channels')) db_checkboxes('channels', 'docs_to_channels', 'doc_id', 'channel_id', $id);
	url_change('info.php?id=' . $id);
}

if (url_id()) {
	$d = db_grab('SELECT title, description FROM docs WHERE id = ' . $_GET['id']);
	$pageAction = getString('edit');
} else {
	$pageAction = getString('add_new');
}

echo drawTop();

//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query('SELECT description, extension FROM docs_types ORDER BY description');
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t['extension'] . '")';
	$doctypes[] = ' - ' . $t['description'] . ' (.' . $t['extension'] . ')';
}
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
				alert('Only these filetypes are supported by this system:\n\n <?=implode('\\n', $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.');
				return false;
			}
		}
		return true;
	}
	//-->
</script>
<?
$f = new form('docs', @$_GET['id'], $page['title']);
$f->set_title_prefix($page['breadcrumbs']);
$f->set_field(array('name'=>'title' . langExt(), 'label'=>getString('title'), 'type'=>'text'));
$f->set_field(array('name'=>'description' . langExt(), 'label'=>getString('description'), 'type'=>'textarea', 'class'=>'tinymce'));
$f->set_field(array('name'=>'content', 'label'=>getString('file'), 'type'=>'file', 'additional'=>getString('upload_max') . file_get_max()));
if (getOption('languages')) $f->set_field(array('name'=>'language_id', 'type'=>'select', 'sql'=>'SELECT id, title FROM languages ORDER BY title', 'label'=>getString('language'), 'required'=>true));
if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>getString('networks'), 'options_table'=>'channels', 'option_title'=>'title' . langExt(), 'linking_table'=>'docs_to_channels', 'object_id'=>'doc_id', 'option_id'=>'channel_id'));
$f->set_field(array('name'=>'categories', 'label'=>getString('categories'), 'type'=>'checkboxes', 'options_table'=>'docs_categories', 'option_title'=>'title' . langExt(), 'linking_table'=>'docs_to_categories', 'object_id'=>'documentID', 'option_id'=>'categoryID'));
langUnsetFields($f, 'title,description');
langTranslateCheckbox($f, url_id());
echo $f->draw(); 

echo drawBottom();
?>