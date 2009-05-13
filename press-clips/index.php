<?php
include("../include.php");

echo drawTop();
echo drawTableStart();
echo drawHeaderRow("Recent Clips", 3);
$result = db_query("SELECT c.id, c.title, c.pub_date, t.title type, c.publication, ISNULL(c.created_date, c.updated_date) updated FROM press_clips c JOIN press_clips_types t ON c.type_id = t.id ORDER BY updated DESC", 20);
if (db_found($result)) {?>
	<tr>
		<th>Title</th>
		<th>Publication</th>
		<th class="r">Pub Date</th>
	</tr>
	<?
	$lastType = "";
	while ($r = db_fetch($result)) {
		if ($lastType != $r["type"]) {
			$lastType = $r["type"];
			?>
			<tr class="group">
				<td colspan="3"><?=$r["type"]?></td>
			</tr>
		<? }
	?>
	<tr>
		<td><?=draw_link("clip.php?id=" . $r["id"], $r["title"])?></td>
		<td><?=$r["publication"]?></td>
		<td class="r"><?=format_date($r["pub_date"])?></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("There are no recent clips.");
}

include("edit/index.php");

echo drawTableEnd();
echo drawBottom();
?>