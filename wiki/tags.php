<?php
include("../include.php");

if ($posting) {
	$id = db_save("wiki_tags");
    url_change();
}

drawTop();

?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("Tags", 2);
	$tags = db_query("SELECT 
		t.id, 
		t.description,
		(SELECT COUNT(*) FROM wiki_topics_to_tags w2t WHERE w2t.tagID = t.id) topics
		FROM wiki_tags t 
		WHERE t.is_active = 1
		ORDER BY t.description");
	if (db_found($tags)) {?>
	<tr>
		<th>Tag</th>
		<th class="r">#</th>
	</tr>
	<? while ($t = db_fetch($tags)) {?>
	<tr>
		<td><? if ($t["topics"]) {?><a href="tag.php?id=<?=$t["id"]?>"><? }?><?=$t["description"]?><? if ($t["topics"]) {?></a><? }?></td>
		<td align="right"><?=$t["topics"]?></td>
	</tr>
	<? } 
	} else {
		echo drawEmptyResult("No tags have been entered yet.", 2);
	}?>
</table>
<? if ($module_admin) {
	$form = new intranet_form;
	if ($module_admin) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addRow("itext",  "Tag" , "description", "", "", true, 255);
	$form->addRow("submit"  , "add tag");
	$form->draw("Add a New Tag");
}

drawBottom(); ?>