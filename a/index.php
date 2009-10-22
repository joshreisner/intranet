<?  include("../include.php");
	drawTop();
?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("", 1);
	foreach ($modulettes as $m) {
		if (!$_SESSION['is_admin'] && !$m["is_public"] && !$m["is_admin"]) continue;?>
	<tr>
		<td><a href="<?=$m['folder']?>/"><?=$m['title']?></a></td>
	</tr>
	<? }?>
</table>
<? drawBottom(); ?>