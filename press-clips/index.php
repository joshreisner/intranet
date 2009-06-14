<?php
include("../include.php");

echo drawTop();
echo drawTableStart();
echo drawHeaderRow("Recent Clips", 1, "Add", "#bottom");
$result = db_query("SELECT 
		c.id, 
		c.title, 
		c.pub_date, 
		t.title type, 
		c.publication, 
		ISNULL(c.created_date, c.updated_date) updated 
	FROM press_clips c 
	JOIN press_clips_types t ON c.type_id = t.id 
	WHERE c.is_active = 1
	ORDER BY t.title, pub_date DESC", 20);
if (db_found($result)) {?>
	<tr>
		<th>Title</th>
	</tr>
	<?
	$lastType = "";
	while ($r = db_fetch($result)) {
		if ($lastType != $r["type"]) {
			$lastType = $r["type"];
			?>
			<tr class="group">
				<td><?=$r["type"]?></td>
			</tr>
		<? }
	?>
	<tr class="thread">
		<td><?=draw_link("clip.php?id=" . $r["id"], format_string($r["title"], 80))?><br>
		<?=$r["publication"]?> <span class="light"><?=format_date($r["pub_date"])?></span>
		</td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("There are no recent clips.");
}
echo drawTableEnd();
include("edit/index.php");

echo drawBottom();
?>