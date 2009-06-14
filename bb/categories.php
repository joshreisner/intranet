<?php
include("include.php");

drawTop();
echo drawTableStart();
echo drawHeaderRow("Categories", 2);?>
	<tr>
		<th>Category</th>
		<th class="r">Topics</th>
	</tr>
<?
$result = db_query("SELECT y.id, y.title, (SELECT COUNT(*) FROM bb_topics t WHERE t.type_id = y.id AND t.is_active = 1) count FROM bb_topics_types y ORDER BY y.title");
while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="category.php?id=<?=$r["id"]?>"><?=$r["title"]?></a></td>
		<td class="r"><?=$r["count"]?></td>
	</tr>
<? }
if ($r = db_grab("SELECT COUNT(*) FROM bb_topics WHERE type_id IS NULL AND is_active = 1")) {?>
	<tr class="total">
		<td class="l"><a href="category.php">Uncategorized</a></td>
		<td class="r"><?=$r?></td>
	</tr>
<? }
echo drawTableEnd();
drawBottom();
?>