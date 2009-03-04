<?
include("../include.php");

if (!$isAdmin) url_change("tags.php");

if ($_POST) {
	$t = db_grab("SELECT MAX(precedence) precedence FROM intranet_tags WHERE typeID = " . $_GET["id"]);
	$t["precedence"]++;
	db_query("INSERT INTO intranet_tags ( 
					typeID,
					generation,
					precedence,
					tag,
					isActive 
				) VALUES ( 
					{$_GET["id"]},
					1,
					{$t["precedence"]},
					'{$_POST["tag"]}',
					1
				)");
	url_change();
}

if (isset($_GET["deactivateTagType"])) {
	db_query("UPDATE intranet_tags_types SET isActive = 0 WHERE id = " . $_GET["deactivateTagType"]);
	url_query_drop("deactivateTagType");
} elseif (isset($_GET["deactivateTag"])) {
	db_query("UPDATE intranet_tags SET isActive = 0 WHERE id = " . $_GET["deactivateTag"]);
	url_query_drop("deactivateTag");
} elseif (isset($_GET["alphabetize"])) {
	$tags = db_query("SELECT tag FROM intranet_tags WHERE typeID = " . $_GET["id"]);
	$values = array();
	while ($t = db_fetch($tags)) $values[] = $t["tag"];
	sort($values);
	$counter = 1;
	foreach ($values as $value) {
		db_query("UPDATE intranet_tags SET precedence = {$counter} WHERE typeID = {$_GET["id"]} AND tag = '{$value}'");
		$counter++;
	}
	url_query_drop("alphabetize");
} elseif (isset($_GET["moveTagUp"])) {
	//code not written yet
	$tag = db_grab("SELECT typeID, precedence FROM intranet_tags WHERE id = " . $_GET["moveTagUp"]);
} elseif (isset($_GET["moveTagDown"])) {
	//code not written yet
	$tag = db_grab("SELECT typeID, precedence FROM intranet_tags WHERE id = " . $_GET["moveTagDown"]);
}

drawTop();

?>
<script language="javascript">
	<!--
	function validate(form) {
		if (!form.tag.value.length) return false;
		return true;
	}
	
	function deactivateTagType(id) {
		if (confirm("Are you sure you want to deactivate this tag type?")) location.href = "tag.php?id=<?=$_GET["id"]?>&deactivateTagType=" + id;
	}
	
	function deactivateTag(id) {
		if (confirm("Are you sure you want to deactivate this tag?")) location.href = "tag.php?id=<?=$_GET["id"]?>&deactivateTag=" + id;
	}
	
	function generateExcel() {
		var elname = '';
		var oneFound = false;
		var tags = new Array();
		for (var i = 0; i < document.taglist.elements.length; i++) {
			elname = document.taglist.elements[i].name.split("_");
			if (elname[0] == "tag") {
				if (document.taglist.elements[i].checked) {
					oneFound = true;
					tags[tags.length] = elname[1];
				}
			}
		}
		if (oneFound) {
			location.href='export.php?id=' + tags.join("|");
		} else {
			alert("Please select one or more tag values from which to generate your Excel document!");
		}
	}
	//-->
</script>

<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE">
	<?
	$t = db_grab("SELECT name FROM intranet_tags_types WHERE id = " . $_GET["id"]);
	echo drawHeaderRow("<a href='tags.php' class='white'>Tags</a> &gt; " . $t["name"], 3, "alphabetize", "tag.php?alphabetize=true&id=" . $_GET["id"])
	?>
	<form name="taglist">
	<tr bgcolor="#F6F6F6" class="small">
		<td width="80%">Tag Name</td>
		<td width="20%" align="right"># Contacts</td>
		<td width="16"></td>
	</tr>
		<?
		$values = db_query("SELECT
							t.id, 
							t.tag, 
							(SELECT count(*) FROM intranet_objects o 
								INNER JOIN intranet_instances i ON o.instanceCurrentID = i.id
								INNER JOIN intranet_instances_to_tags i2t ON i.id = i2t.instanceID
								WHERE o.typeID = 22 AND i2t.tagID = t.id) contactcount
						FROM intranet_tags t 
						WHERE t.typeID = {$_GET["id"]} AND t.isActive = 1
						ORDER BY t.precedence");
		while ($v = db_fetch($values)) {?>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td width="80%"><a href="value.php?id=<?=$v["id"]?>"><?=$v["tag"]?></a></td>
		<td align="right"><?=number_format($v["contactcount"])?></td>
		<td width="16"><a href="javascript:deactivateTag(<?=$v["id"]?>);"><img src="/images/icons/delete.gif" width="16" height="16" border="0"></a></td>
	</tr>
	<?}?>
	</form>
</table>
<br>
<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE">
	<tr>
		<td class="bold" bgcolor="#F6F6F6" colspan="2">Add New Tag</td>
	</tr>
	<form method="post" action="<?=$request["path_query"]?>" onsubmit="return validate(this);">
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6">Name:</td>
		<td><?=draw_form_text("tag")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center" bgcolor="#F6F6F6"><?=draw_form_submit("Add Tag")?></td>
	</tr>
	</form>
</table>
<? drawBottom();?>