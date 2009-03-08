<?  include("../include.php");
	drawTop();
?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("", 1);
	foreach ($areas as $a) {
		if (!$modules[$a]["isPublic"] && !$modules[$a]["is_admin"]) continue;?>
	<tr>
		<td><a href="<?=$modules[$a]["url"]?>"><?=$modules[$a]["name"]?></a></td>
	</tr>
	<? }?>
</table>
<? drawBottom(); ?>