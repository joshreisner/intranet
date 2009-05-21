<?php
include("../include.php");

if ($posting) {
	$id = db_save("wiki_topics");
	db_checkboxes("tags", "wiki_topics_to_tags", "topicID", "tagID", $id);
    url_change();
}

drawTop();

?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("Main Page", 4);
	$topics = db_query("SELECT 
		w.id,
		w.title,
		w.description,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		w.created_date
	FROM wiki_topics w
	JOIN wiki_topics_types t ON w.typeID = t.id
	JOIN users u ON w.created_user = u.user_id
	WHERE w.is_active = 1
	ORDER BY w.created_date DESC");
	if (db_found($topics)) {?>
	<tr>
		<th width="16"></th>
		<th align="left">Title</th>
		<th align="left" width="100">Created By</th>
		<th align="right" width="80">Created On</th>
	</tr>
	<?php
	while ($t = db_fetch($topics)) {?>
	<tr height="36">
		<td></td>
		<td><a href="topic.php?id=<?=$t["id"]?>"><?=$t["title"]?></a></td>
		<td><?=$t["first"]?> <?=$t["last"]?></td>
		<td align="right"><?=format_date($t["created_date"])?></td>
	</tr>
	<? }
	} else {
		echo drawEmptyResult("No Wiki Topics have been entered into the system yet.<br>Perhaps you would like to <a href='#bottom'>add one</a>?", 4);
	}?>
</table>

<a name="bottom"></a>

<? if ($module_admin) {
	$form = new intranet_form;
	if ($module_admin) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addRow("itext",  "Title" , "title", "", "", true, 255);
	$form->addRow("select", "Type" , "typeID", "SELECT id, description FROM wiki_topics_types");
	$form->addCheckboxes("tags", "Tags", "wiki_tags", "wiki_topics_to_tags");
	$form->addRow("textarea", "Description" , "description", "", "", true);
	$form->addRow("submit"  , "post wiki topic");
	$form->draw("Add a Wiki Topic");
}

drawBottom(); ?>