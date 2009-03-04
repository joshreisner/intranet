<?
include("../include.php");

if ($posting) {
	if ($uploading) {
		$type = getDocTypeID($_FILES["userfile"]["name"]);
		$content = format_binary(file_get($_FILES["userfile"]["tmp_name"]));
		@unlink($_FILES["userfile"]["tmp_name"]);
	}

	if (url_id()) {
		if ($uploading) {
			db_query("UPDATE documents SET 
				name = '{$_POST["name"]}',
				description = '{$_POST["description"]}',
				typeID = {$type},
				content = $content,
				updatedOn = GETDATE(),
				updatedBy = {$_SESSION["user_id"]}
				WHERE id = " . $_GET["id"]);
		} else {
			db_query("UPDATE documents SET 
				name = '{$_POST["name"]}',
				description = '{$_POST["description"]}',
				updatedOn = GETDATE(),
				updatedBy = {$_SESSION["user_id"]}
				WHERE id = " . $_GET["id"]);
		}
	} else {
		$_GET["id"] = db_query("INSERT into documents (
			name,
			description,
			typeID,
			content,
			createdOn,
			createdBy,
			isActive
		) VALUES (
			'" . $_POST["name"] . "',
			'" . $_POST["description"] . "',
			"  . $type . ",
			"  . $content . ",
			GETDATE(),
			"  . $_SESSION["user_id"] . ",
			1
		)");
	}

	db_checkboxes("doc", "documents_to_categories", "documentID", "categoryID", $_GET["id"]);
	url_change("/docs/info.php?id=" . $_GET["id"]);
}

if (url_id()) {
	$d = db_grab("SELECT name, description FROM documents WHERE id = " . $_GET["id"]);
	$pageAction = "Edit Document";
} else {
	$pageAction = "Add Document";
}

drawTop();


//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query("SELECT description, extension FROM intranet_doctypes ORDER BY description");
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
}
?>
<script language="javascript">
	<!--
	function validate(form) {
		tinyMCE.triggerSave();
		if (!form.name.value.length) {
			alert("Please enter a name for this document.");
			return false;
		}
		if (!form.description.value.length) {
			alert("Please enter a description for this document.");
			return false;
		}
		oneFound = false;
		for (var i = 0; i < form.elements.length; i++) {
			var checkParts = form.elements[i].name.split("_");
			if ((checkParts[0] == "chk") && (form.elements[i].checked)) oneFound = true;
		}
		if (!oneFound) {
			alert("Please select a category.");
			return false;
		}
		if (!form.userfile.value.length) {
			<? if (!isset($_GET["id"])) {?>
			alert("Please select a file to upload.");
			return false;
			<? }?>
		} else {
			var arrFile   = form.userfile.value.split(".");
			var extension = arrFile[arrFile.length - 1].toLowerCase();
			if (<?=implode(" && ", $extensions)?>) {
				alert("Only these filetypes are supported by this system:\n\n <?=implode("\\n", $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.");
				return false;
			}
		}
		return true;
	}
	//-->
</script>
<table class="left">
	<?=drawHeaderRow($pageAction, 2);?>
	<form enctype="multipart/form-data" action="<?=$_josh["request"]["path_query"]?>" method="post" onsubmit="javascript:return validate(this);">
	<tr>
		<td class="left">Name</td>
		<td><?=draw_form_text("name",  @$d["name"], "text")?></td>
	</tr>
	<tr>
		<td class="left">Description</td>
		<td><?=draw_form_textarea("description", @$d["description"])?></td>
	</tr>
	<tr>
		<td class="left">Category</td>
		<td>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="nospacing">
				<?
				if (url_id()) {
					$categories = db_query("SELECT c.id, c.description, (SELECT COUNT(*) FROM documents_to_categories d2c WHERE d2c.categoryID = c.id AND d2c.documentID = {$_GET["id"]}) checked FROM documents_categories c ORDER BY c.precedence");
				} else {
					$categories = db_query("SELECT id, description FROM documents_categories ORDER BY precedence");
				}
				while ($c = db_fetch($categories)) {?>
				<tr>
					<td width="16"><input type="checkbox" name="chk_doc_<?=$c["id"]?>"<? if (@$c["checked"]) {?> checked<? }?>></td>
					<td><?=$c["description"]?></td>
				</tr>
				<? }?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="left">File<? if (url_id()) {?> (optional)<? }?></td>
		<td><?=draw_form_file("userfile")?></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?=draw_form_submit($pageAction);?></td>
	</tr>
	</form>
</table>
<? drawBottom();?>