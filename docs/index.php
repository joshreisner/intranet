<?	include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE documents SET isActive = 0, deletedOn = GETDATE(), deletedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_drop();
}

drawTop();

?>
<table class="left" cellspacing="1">
    <? if ($isAdmin) {
    	$colspan = 4;
	    echo drawHeaderRow("List", $colspan, "add", "add_edit.php");
    } else {
    	$colspan = 4;
	    echo drawHeaderRow("List", $colspan);
    }?>
	<tr>
		<th></th>
		<th align="left">Name, Description</th>
		<th align="right">Updated</th>
		<? if ($isAdmin) {?><th></th><? }?>
	</tr>
    <?
    $categories = db_query("SELECT 
    			c.id, 
    			c.description, 
    			(SELECT COUNT(*) FROM documents_to_categories d2c JOIN documents d ON d2c.documentID = d.id WHERE d2c.categoryID = c.id AND d.isActive = 1) documents 
    		FROM documents_categories c
    		ORDER BY c.precedence");
	while ($c = db_fetch($categories)) {
		if (!$c["documents"]) continue;
		?>
		<tr class="group">
			<td colspan="<?=$colspan?>"><?=$c["description"]?></td>
		</tr>
		<? $documents = db_query("SELECT 
							d.id, 
							d.name, 
							d.description,
							ISNULL(d.updatedOn, d.createdOn) updatedOn,
							i.icon, 
							i.description alt
						FROM documents d
						JOIN documents_to_categories d2c ON d.id = d2c.documentID
						JOIN documents_types i ON d.typeID = i.id
						WHERE d2c.categoryID = " . $c["id"] . "
						AND d.isActive = 1
						ORDER BY d.name;");
				while ($d = db_fetch($documents)) {?>
		<tr>
			<td width="16"><a href="info.php?id=<?=$d["id"]?>"><img src="<?=$locale?><?=$d["icon"]?>" width="16" height="16" border="0" alt="<?=$d["alt"]?>"></a></td>
			<td class="text2"><a href="info.php?id=<?=$d["id"]?>"><?=$d["name"]?></a></td>
			<td align="right"><?=format_date($d["updatedOn"])?></td>
			<?=deleteColumn("Delete document?", $d["id"]);?>
		</tr>
	<? }
} ?>
</table>
<? drawBottom(); ?>