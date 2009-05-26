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
						u.id,
						(SELECT COUNT(*) FROM funders_awards a where a.staffID = u.id AND awardStatusID = 1) as active,
						(SELECT COUNT(*) FROM funders_awards a where a.staffID = u.id AND awardStatusID = 2) as proposals,
						(SELECT COUNT(*) FROM funders_awards a where a.staffID = u.id AND awardStatusID = 5) as strategies,
						u.is_active
					FROM users u
					WHERE (SELECT COUNT(*) FROM funders_awards a where a.staffID = u.id AND (awardStatusID = 1 OR awardStatusID = 2 OR awardStatusID = 5)) > 0
					ORDER BY last, first
					");
while ($r = db_fetch($result)) {?>
	<tr>
		<td><?=$r["first"]?> <?=$r["last"]?></td>
		<td align="right"><a href="staffawards.php?statusID=1&staffID=<?=$r["user_id"]?>"><?=$r["active"]?></a></td>
		<td align="right"><a href="staffawards.php?statusID=2&staffID=<?=$r["user_id"]?>"><?=$r["proposals"]?></a></td>
		<td align="right"><a href="staffawards.php?statusID=5&staffID=<?=$r["user_id"]?>"><?=$r["strategies"]?></a></td>
	</tr>
<? }?>
</table>

<? drawBottom();?>