<?  
include("../include.php");

drawTop();

?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Staff", 4)?>
	<tr>
		<th width="40%" align="left">Staff Name</th>
		<th width="20%" align="right"># active</th>
		<th width="20%" align="right"># proposals</th>
		<th width="20%" align="right"># strategies</th>
	</tr>
<?
$result = db_query("SELECT 
						ISNULL(u.nickname, u.firstname) first,
						u.lastname last,
						u.userID,
						(SELECT COUNT(*) FROM resources_awards a where a.staffID = u.userID AND awardStatusID = 1) as active,
						(SELECT COUNT(*) FROM resources_awards a where a.staffID = u.userID AND awardStatusID = 2) as proposals,
						(SELECT COUNT(*) FROM resources_awards a where a.staffID = u.userID AND awardStatusID = 5) as strategies,
						u.isActive
					FROM intranet_users u
					WHERE (SELECT COUNT(*) FROM resources_awards a where a.staffID = u.userID AND (awardStatusID = 1 OR awardStatusID = 2 OR awardStatusID = 5)) > 0
					ORDER BY last, first
					");
while ($r = db_fetch($result)) {?>
	<tr>
		<td><?=$r["first"]?> <?=$r["last"]?></td>
		<td align="right"><a href="staffawards.php?statusID=1&staffID=<?=$r["userID"]?>"><?=$r["active"]?></a></td>
		<td align="right"><a href="staffawards.php?statusID=2&staffID=<?=$r["userID"]?>"><?=$r["proposals"]?></a></td>
		<td align="right"><a href="staffawards.php?statusID=5&staffID=<?=$r["userID"]?>"><?=$r["strategies"]?></a></td>
	</tr>
<? }?>
</table>

<? drawBottom();?>