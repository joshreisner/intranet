<?php
include("../include.php");

echo drawTop();
echo drawTableStart();
echo drawHeaderRow("Recent Clips");
$result = db_query("SELECT c.title, c.pub_date, c.publication, ISNULL(c.created_date, c.updated_date) updated FROM press_clips c ORDER BY updated DESC", 20);
if (db_found($result)) {?>
	<tr>
		<th>Title</th>
		<th></th>
		<th></th>
	</tr>
	<?
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><?=$r["title"]?></td>
		<td></td>
		<td></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("There are no recent clips.");
}

include("edit/index.php");

echo drawTableEnd();
echo drawBottom();
?>