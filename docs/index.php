<?	include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE docs SET is_active = 0, deleted_date = GETDATE(), deleted_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_drop();
}

drawTop();

echo drawTableStart();
if ($module_admin) {
	$colspan = 4;
	echo drawHeaderRow("List", $colspan, "add", "add_edit.php");
} else {
	$colspan = 3;
    echo drawHeaderRow("List", $colspan);
}
$categories = db_query("SELECT 
			c.id, 
			c.title, 
			(SELECT COUNT(*) FROM docs_to_categories d2c JOIN docs d ON d2c.documentID = d.id WHERE d2c.categoryID = c.id AND d.is_active = 1) docs 
		FROM docs_categories c
		WHERE (SELECT COUNT(*) FROM docs_to_categories d2c JOIN docs d ON d2c.documentID = d.id WHERE d2c.categoryID = c.id AND d.is_active = 1) > 0
		ORDER BY c.precedence");
		
if (db_found($categories)) {?>
	<tr>
		<th></th>
		<th>Document</th>
		<th class="r">Updated</th>
		<? if ($module_admin) {?><th></th><? }?>
	</tr>
	<?
	while ($c = db_fetch($categories)) { ?>
		<tr class="group">
			<td colspan="<?=$colspan?>"><?=$c["title"]?></td>
		</tr>
		<? $docs = db_query("SELECT 
							d.id, 
							d.title, 
							d.description,
							ISNULL(d.updated_date, d.created_date) updated_date,
							i.icon, 
							i.description alt
						FROM docs d
						JOIN docs_to_categories d2c ON d.id = d2c.documentID
						JOIN docs_types i ON d.type_id = i.id
						WHERE d2c.categoryID = " . $c["id"] . "
						AND d.is_active = 1
						ORDER BY d.title;");
				while ($d = db_fetch($docs)) {?>
		<tr>
			<td width="16"><a href="info.php?id=<?=$d["id"]?>"><img src="<?=$_josh["write_folder"]?><?=$d["icon"]?>" width="16" height="16" border="0" alt="<?=$d["alt"]?>"></a></td>
			<td class="text2"><a href="info.php?id=<?=$d["id"]?>"><?=$d["title"]?></a></td>
			<td align="right"><?=format_date($d["updated_date"])?></td>
			<?=drawDeleteColumn("Delete document?", $d["id"]);?>
		</tr>
	<? }
	}
} else {
	echo drawEmptyResult("No documents have been added yet.", $colspan);
}
echo drawTableEnd();
drawBottom();
?>