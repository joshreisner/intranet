<?php
include("../include.php");

//download
if (url_action("delete")) {
	db_query("UPDATE policy_documents SET isActive = 0, deletedOn = GETDATE(), deletedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_drop("id, action");
} elseif (url_id()) {
	$d = db_grab("SELECT d.name, t.extension, d.content FROM policy_documents d JOIN documents_types t ON d.typeID = t.id WHERE d.id = " . $_GET["id"]);
	//db_query("INSERT INTO documents_views ( documentID, userID, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");
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
	if ($isAdmin) {
		echo drawheaderRow("", 4, "add", "edit/");
	} else {
		echo drawheaderRow("", 3);
	}	

	$documents = db_query("SELECT d.id, d.name, t.icon, ISNULL(d.updatedOn, d.createdOn) updatedOn FROM policy_documents d JOIN documents_types t ON d.typeID = t.id WHERE d.isActive = 1 AND d.categoryID = " . $_GET["category"]);
	if (db_found($documents)) {?>
	<tr>
		<th width="16"></th>
		<th>Name</th>
		<th class="r">Updated</th>
		<? if ($isAdmin) {?><th width="16"></th><? }?>
	</tr>
	<? while ($d = db_fetch($documents)) {
		$link = "./?id=" . $d["id"];
	?>
	<tr>
		<td width="16"><?=draw_img($locale . $d["icon"], $link)?></td>
		<td><a href="<?=$link?>"><?=$d["name"]?></a></td>
		<td class="r"><?=format_date($d["updatedOn"])?></td>
		<?=deleteColumn("Delete document?", $d["id"]);?>
	</tr>
		<? }
	} else {
		echo drawEmptyResult("No documents added to this category yet!");
	}
	?>
</table>
<?=drawBottom();?>