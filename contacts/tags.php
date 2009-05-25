<?
include("../include.php");

drawTop();

?>
<script language="javascript">
	<!--
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
			if (confirm("You didn't select any tag values.  Are you sure you want to generate an Excel with the whole database?  It will be a huge list!  It should take approximately two minutes to download.")) location.href='export.php';
			//alert("Please select one or more tag values from which to generate your Excel document!");
		}
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Tags", 3, "show deleted", "tags-deleted.php", "generate excel", "javascript:generateExcel();")?>
	<tr>
		<th width="16"></th>
		<th class="l">Tag Name</th>
		<th class="r"># Contacts</th>
	</tr>
	<form name="taglist">
	<?
	$types = db_query("SELECT
							t.id, 
							t.name
						FROM intranet_tags_types t WHERE t.is_active = 1
						ORDER BY t.name");
	while ($t = db_fetch($types)) {?>
	<tr class="group">
		<td colspan="3"><? if ($module_admin) {?><a href="tag.php?id=<?=$t["id"]?>"><?}?><b><?=$t["name"]?></b></a></td>
	</tr>
		<?
		$values = db_query("SELECT
							t.id, 
							t.tag, 
							(SELECT count(*) FROM contacts o 
								INNER JOIN contacts_instances i ON o.instanceCurrentID = i.id
								INNER JOIN contacts_instances_to_tags i2t ON i.id = i2t.instanceID
								WHERE o.type_id = 22 AND i2t.tagID = t.id AND o.is_active = 1) contactcount
						FROM intranet_tags t 
						WHERE t.type_id = {$t["id"]} AND t.is_active = 1
						ORDER BY t.precedence");
		while ($v = db_fetch($values)) {?>
	<tr valign="middle">
		<td><?=draw_form_checkbox("tag_" . $v["id"])?></td>
		<td><a href="value.php?id=<?=$v["id"]?>"><?=$v["tag"]?></a></td>
		<td align="right"><?=number_format($v["contactcount"])?></td>
	</tr>
		<? }
	}?>
	</form>
</table>
<? drawBottom();?>