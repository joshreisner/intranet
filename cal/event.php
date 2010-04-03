<?
include("include.php");

$e = db_grab('SELECT 
		e.title' . langExt() . ' title, 
		e.description' . langExt() . ' description, 
		e.start_date, 
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.created_user,
		e.created_date,
		t.color,
		t.description' . langExt() . ' type,
		MONTH(e.start_date) month, 
		YEAR(e.start_date) year
	FROM cal_events e
	JOIN users u ON e.created_user = u.id
	JOIN cal_events_types t ON e.type_id = t.id
	WHERE e.id = ' . $_GET["id"]);
	
if (url_action("delete")) {
	db_delete("cal_events");
	url_change("/cal/?month="  . $e["month"] . "&year=" . $e["year"]);
}

echo drawTop();
echo drawNavigationCal($e["month"], $e["year"], true)
?>
<table class="left" cellspacing="1">
	<?
	if ($page['is_admin']) {
		echo drawHeaderRow($page['title'], 2, getString('edit'), "edit.php?id=" . $_GET["id"], getString('delete'), drawDeleteLink());
	} elseif ($_SESSION["user_id"] == $e["created_user"]) {
		echo drawHeaderRow($page['title'], 2, getString('edit'), "edit.php?id=" . $_GET["id"], getString('delete'), drawDeleteLink());
	} else {
		echo drawHeaderRow($page['title'], 2);
	}?>
	<tr>
		<td class="left"><?=getString('title')?></td>
		<td class="right" bgcolor="#ffffff"><b><?=$e["title"]?></b></td>
	</tr>
	<tr>
		<td class="left"><?=getString('category')?></td>
		<td><span class="block" style="background-color:<?=$e["color"]?>;"><?=$e["type"]?></span></td>
	</tr>
	<tr>
		<td class="left"><?=getString('start_date')?></td>
		<td><?=format_date_time($e["start_date"])?></td>
	</tr>
	<tr valign="top">
		<td class="left" height="200"><?=getString('description')?></td>
		<td class="text"><?=$e["description"]?></td>
	</tr>
	<? if (getOption('channels')) {?>
	<tr>
		<td class="left"><?=getString('channels_label')?></td>
		<td>
			<? $channels = db_query('SELECT
				c.title' . langExt() . ' title
			FROM cal_events_to_channels e2c
			JOIN channels c ON e2c.channel_id = c.id
			WHERE e2c.event_id = ' . $_GET["id"]);
				while ($c = db_fetch($channels)) {?>
				 &#183; <?=$c["title"]?></a><br>
			<? }?>
		</td>
	</tr>
	<? }?>
	<tr valign="top">
		<td class="left"><?=getString('posted_by')?></td>
		<td><?=drawName($e["created_user"], $e["first"] . " " . $e["last"], $e["created_date"], true);?></td>
	</tr>
</table>
<?=drawBottom();?>