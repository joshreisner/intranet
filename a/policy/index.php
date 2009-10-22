<?php
include("../include.php");

//download
if (url_action("delete")) {
	db_query("UPDATE policy_docs SET is_active = 0, deleted_date = GETDATE(), deleted_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_drop("id, action");
} elseif (url_id()) {
	$d = db_grab("SELECT d.name, t.extension, d.content FROM policy_docs d JOIN docs_types t ON d.type_id = t.id WHERE d.id = " . $_GET["id"]);
	//db_query("INSERT INTO docs_views ( documentID, user_id, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");
	file_download($d["content"], $d["name"], $d["extension"]);
}

//get nav options
$options = array();
$categories = db_query("SELECT id, description FROM policy_categories ORDER BY description");
while ($c = db_fetch($categories)) {
	if (!isset($_GET["category"])) url_query_add(array("category"=>$c["id"]));
	$options[str_replace(url_base(), "", url_query_add(array("category"=>$c["id"]), false))] = $c["description"];
}

drawTop();
echo drawNavigationRow($options, "areas", true);
?>
<table class="left">
	<?
	if ($module_admin) {
		echo drawheaderRow("", 4, "add", "edit/");
	} else {
		echo drawheaderRow("", 3);
	}	

	$docs = db_query("SELECT d.id, d.name, t.icon, ISNULL(d.updated_date, d.created_date) updated_date FROM policy_docs d JOIN docs_types t ON d.type_id = t.id WHERE d.is_active = 1 AND d.categoryID = " . $_GET["category"] . " ORDER BY d.name");
	if (db_found($docs)) {?>
	<tr>
		<th width="16"></th>
		<th>Name</th>
		<th class="r">Updated</th>
		<? if ($module_admin) {?><th width="16"></th><? }?>
	</tr>
	<? while ($d = db_fetch($docs)) {
		$link = "./?id=" . $d["id"];
	?>
	<tr>
		<td width="16"><?=draw_img($_josh["write_folder"] . $d["icon"], $link)?></td>
		<td><a href="<?=$link?>"><?=$d["name"]?></a></td>
		<td class="r"><?=format_date($d["updated_date"])?></td>
		<?=drawDeleteColumn("Delete document?", $d["id"]);?>
	</tr>
		<? }
	} else {
		echo drawEmptyResult("No docs added to this category yet!");
	}
	?>
</table>
<?=drawBottom();?>