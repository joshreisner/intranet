<? include("../include.php");

drawtop();?>

<table class="left">
	<?=drawHeaderRow("Long Distance Codes", 2)?>
	<?
	$staff = db_query("SELECT user_id, firstname, lastname, longDistanceCode FROM users WHERE is_active = 1 and officeID = 1 ORDER BY lastname, firstname");
	while ($s = db_fetch($staff)) {?>
	<tr>
		<td><a href="/staff/view.php?id=<?=$s["user_id"]?>"><?=$s["lastname"]?>, <?=$s["firstname"]?></a></td>
		<td><?=sprintf("%04s", $s["longDistanceCode"]);?></td>
	</tr>
	<?}?>
</table>
<? drawBottom();?>