<?php
include("../../include.php");

if ($posting) {
	$id = db_save("wiki_topics_types");
    url_change();
}

echo drawTop();

?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("Types", 2);
	$tags = db_query("SELECT 
		t.id, 
		t.description,
		(SELECT COUNT(*) FROM wiki_topics w WHERE w.type_id = t.id) topics
		FROM wiki_topics_types t 
		WHERE t.is_active = 1
		ORDER BY t.description");
	if (db_found($tags)) {?>
	<tr>
		<th align="left">Type</th>
		<th align="right">#</th>
	</tr>
	<? while ($t = db_fetch($tags)) {?>
	<tr>
		<td><? if ($t["topics"]) {?><a href="type.php?id=<?=$t["id"]?>"><? }?><?=$t["description"]?><? if ($t["topics"]) {?></a><? }?></td>
		<td align="right"><?=$t["topics"]?></td>
	</tr>
	<? } 
	} else {
		echo drawEmptyResult("No types have been entered yet.", 2);
	}?>
</table>
<? if ($page['is_admin']) {
	$form = new intranet_form;
	if ($page['is_admin']) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addRow("itext",  "Tag" , "description", "", "", true, 255);
	$form->addRow("submit"  , "add tag");
	$form->draw("Add a New Type");
}

echo drawBottom(); ?>