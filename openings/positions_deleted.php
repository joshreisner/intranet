<?
include("../include.php");

drawTop();

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Open Positions", 3);?>
	<tr>
		<th align="left" width="50%">Title</th>
		<th align="left" width="40%">Area</th>
		<th align="right" width="10%"><nobr>Last Update</nobr></th>
	</tr>
	<?
	$offices = db_query("SELECT
							id,
							name
						FROM intranet_offices");
	while ($o = db_fetch($offices)) {
		$result = db_query("SELECT 
								j.id,
								j.title,
								d.departmentName,
								ISNULL(j.updatedOn, j.createdOn) updatedOn
							FROM intranet_jobs j
							LEFT JOIN departments d ON j.departmentID = d.departmentID
							WHERE j.isActive = 0
							ORDER BY j.title, departmentName");
		if (db_found($result)) {?>
			<tr class="group">
				<td colspan="3"><?=$o["name"]?></td>
			</tr>
			<? while ($r = db_fetch($result)) {?>
			<tr>
				<td><a href="position.php?id=<?=$r["id"]?>"><?=$r["title"]?></a></td>
				<td><?=$r["departmentName"]?></td>
				<td align="right"><?=format_date($r["updatedOn"])?></td>
			</tr>
			<? }
		}
	}?>
</table>
<? drawBottom();?>