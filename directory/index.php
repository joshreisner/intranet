<? include("../include.php");
drawTop();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Organizations", 4, "new", "organization_add_edit.php");?>
	<tr>
		<th align="left">Organization</th>
		<th align="left" style="width:120px">City, State</th>
		<th align="right" style="width:100px">Last Update</th>
	</tr>
	<?
	$result = db_query("SELECT 
							o.id,
							o.name, 
							o.phone,
							z.city, 
							z.state, 
							o.zip,
							o.lastUpdatedOn
						FROM web_organizations o
						INNER JOIN zip_codes z ON o.zip = z.zip
						ORDER BY name");
	while ($r = db_fetch($result)) {?>
		<tr>
			<td><a href="organization_view.php?id=<?=$r["id"]?>"><?=$r["name"]?></a></td>
			<td><?=$r["city"]?>, <?=$r["state"]?></td>
			<td align="right"><?=format_date($r["lastUpdatedOn"])?></td>
		</tr>
	<? }?>
</table>
<? drawBottom();?>