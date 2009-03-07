<?
include("../include.php");
drawTop();

?>
<table class="left" cellspacing="1">
	<?=drawheaderRow("Most Popular", 3);?>
	<tr>
		<th width="16"></th>
		<th width="85%" align="left">Name</th>
		<th width="15%" align="right">Views</th>
	</tr>
	<?
	$result = db_query("SELECT 
			d.id,
			d.name,
			(SELECT COUNT(*) FROM documents_views v WHERE v.documentID = d.id) downloads,
			i.icon,
			i.description alt
		FROM documents d
		JOIN documents_types i ON d.typeID = i.id
		WHERE d.isActive = 1
		ORDER BY downloads DESC", 20);
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="info.php?id=<?=$r["id"]?>"><img src="<?=$locale?><?=$r["icon"]?>" width="16" height="16" border="0" alt="<?=$r["alt"]?>"></a></td>
		<td><a href="info.php?id=<?=$r["id"]?>"><?=$r["name"]?></a></td>
		<td align="right"><?=number_format($r["downloads"])?></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>