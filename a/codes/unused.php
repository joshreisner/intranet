<? include("../../include.php");

drawTop();
?>
<table class="left">
	<?=drawHeaderRow("Long Distance Codes", 1)?>
	<?
	$codes = db_query("SELECT
	l.code
FROM ldcodes l
WHERE (SELECT COUNT(*) FROM users u WHERE u.is_active = 1 AND u.officeID = 1 AND u.longdistancecode = l.code) = 0
ORDER BY NEWID()");
	while ($c = db_fetch($codes)) {?>
	<tr>
		<td><?=sprintf("%04s", $c["code"]);?></td>
	</tr>
	<? }?>
</table>

<? drawBottom();?>