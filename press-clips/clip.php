<?php
include("../include.php");
url_query_require();
echo drawTop();
echo drawTableStart();
echo drawHeaderRow("Recent Clips", 3, "Edit", "edit/?id=" . $_GET["id"]);

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
		<td class="left">Publication</td>
		<td><?=$r["publication"]?></td>
	</tr>
	<tr>
		<td class="left">Pub Date</td>
		<td><?=format_date($r["pub_date"])?></td>
	</tr>
	<tr>
		<td class="left">URL</td>
		<td><?=draw_link($r["url"])?></td>
	</tr>
	<tr>
		<td class="left">Description</td>
		<td class="text"><?=$r["description"]?></td>
	</tr>
</table>
<?=drawBottom();?>