<?php
include("../include.php");
url_query_require();

if (url_action("delete")) {
	db_delete("press_clips");
	url_change("/press-clips/");
}

echo drawTop();
echo drawTableStart();
echo drawHeaderRow("Recent Clips", 2, "Edit", "edit/?id=" . $_GET["id"], "Delete", drawDeleteLink());

$r = db_grab("SELECT c.title, c.url, c.pub_date, c.publication, c.type_id, c.description, t.title type FROM press_clips c JOIN press_clips_types t ON c.type_id = t.id WHERE c.id = " . $_GET["id"]);
?>
	<tr>
		<td class="left">Title</td>
		<td class="title"><?=$r["title"]?></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><?=draw_link("/press-clips/categories.php?id=" . $r["type_id"], $r["type"])?></td>
	</tr>
	<tr>
		<td class="left">Published</td>
		<td><?=$r["publication"]?>, <?=format_date($r["pub_date"])?></td>
	</tr>
	<tr>
		<td class="left">URL</td>
		<td><?=draw_link($r["url"], format_string($r["url"], 70), true)?></td>
	</tr>
	<tr>
		<td class="left">Description</td>
		<td class="text"><?=$r["description"]?></td>
	</tr>
</table>
<?=drawBottom();?>