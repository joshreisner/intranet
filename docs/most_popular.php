<?
include("../include.php");
drawTop();

echo drawTableStart();
echo drawheaderRow("Most Popular", 3);

$result = db_query("SELECT 
		d.id,
		d.title,
		(SELECT COUNT(*) FROM docs_views v WHERE v.documentID = d.id) downloads,
		i.icon,
		i.description alt
	FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	WHERE d.is_active = 1
	ORDER BY downloads DESC", 20);
if (db_found($result)) {?>
	<tr>
		<th width="16"></th>
		<th width="85%" align="left">title</th>
		<th width="15%" align="right">Views</th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="info.php?id=<?=$r["id"]?>"><img src="<?=$r["icon"]?>" width="16" height="16" border="0" alt="<?=$r["alt"]?>"></a></td>
		<td><a href="info.php?id=<?=$r["id"]?>"><?=$r["title"]?></a></td>
		<td align="right"><?=number_format($r["downloads"])?></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("No documents have been added yet", 3);
}
echo drawTableEnd();
drawBottom();?>