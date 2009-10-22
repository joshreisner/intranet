<?
include("../../include.php");
drawTop();

$service = db_grab("SELECT name FROM web_services WHERE id = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("<a href='/departments/earnbenefits/services.php' class='white'>Services</a> &gt; " . $service, 1, "new", "organization_add_edit.php");?>
	<tr>
		<th align="left">Service</th>
	</tr>
	<?
	$result = db_query("SELECT o.id, o.name
						FROM web_organizations_2_services o2s 
						INNER JOIN web_organizations o ON o2s.organizationID = o.id
						WHERE o2s.serviceID = {$_GET["id"]} ORDER BY o.name");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="organization.php?id=<?=$r["id"]?>"><?=$r["name"]?></a></td>
	</tr>
	<? }?>
</table>

<? drawBottom();?>