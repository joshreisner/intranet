<?
include("../../include.php");
drawTop();

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Services", 2, "add new organization", "organization_add_edit.php");?>
	<tr>
		<th align="left">Service</td>
		<th align="right">#</td>
	</tr>
	<?
	$result = db_query("SELECT s.id, s.name, (SELECT count(*) FROM web_organizations_2_services o2s WHERE o2s.serviceID = s.id) as countservices FROM web_services s ORDER by s.name");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="service.php?id=<?=$r["id"]?>"><?=$r["name"]?></a></td>
		<td align="right"><?=$r["countservices"]?></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>