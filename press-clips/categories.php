<?php
include("../include.php");
drawTop();
echo drawTableStart();

if (url_id()) {
	$title = db_grab("SELECT title FROM press_clips_types WHERE id = " . $_GET["id"]);
	echo drawHeaderRow("<a href='./'>Categories</a> &gt; " . $title, 3);

	$result = db_query("SELECT c.id, c.title, c.pub_date, t.title type, c.publication, ISNULL(c.created_date, c.updated_date) updated FROM press_clips c JOIN press_clips_types t ON c.type_id = t.id WHERE c.is_active = 1 AND c.type_id = " . $_GET["id"] . " ORDER BY updated DESC", 20);
	if (db_found($result)) {?>
		<tr>
			<th>Title</th>
			<th>Publication</th>
			<th class="r">Pub Date</th>
		</tr>
		<? while ($r = db_fetch($result)) {?>
		<tr>
			<td><?=draw_link("clip.php?id=" . $r["id"], $r["title"])?></td>
			<td><?=$r["publication"]?></td>
			<td class="r"><?=format_date($r["pub_date"])?></td>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("There are no clips tagged <i>$title</i>.");
	}
	
	
} else {
	echo drawHeaderRow("Categories", 2);?>
	<tr>
		<th>Category</th>
		<th class="r">Clips</th>
	</tr>
	<?
	$categories = db_query("SELECT t.id, t.title, (SELECT COUNT(*) FROM press_clips c WHERE c.type_id = t.id) num_clips FROM press_clips_types t ORDER BY t.precedence");
	while ($c = db_fetch($categories)) {
	?>
	<tr>
		<td><?=draw_link(url_query_add(array("id"=>$c["id"]), false), $c["title"])?></td>
		<td class="r"><?=$c["num_clips"]?></td>
	</tr>
	<?
	}
}
echo "</table>";
echo drawBottom();
?>