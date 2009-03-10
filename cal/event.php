<?
include("include.php");

$e = db_grab("SELECT 
		e.title, 
		e.description, 
		e.startDate, 
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.created_user,
		e.created_date,
		t.color,
		t.description type,
		MONTH(e.startDate) month, 
		YEAR(e.startDate) year
	FROM cal_events e
	JOIN users u ON e.created_user = u.user_id
	JOIN cal_events_types t ON e.typeID = t.id
	WHERE e.id = " . $_GET["id"]);
	
if (url_action("delete")) {
	db_query("DELETE FROM cal_events WHERE id = " . $_GET["id"]);
	url_change("/cal/?month="  . $e["month"] . "&year=" . $e["year"]);
}

drawTop();
echo drawNavigationCal($e["month"], $e["year"], true)
?>
<table class="left" cellspacing="1">
	<?
	if ($is_admin) {
		echo drawHeaderRow("Event Details", 2, "edit", "event_edit.php?id=" . $_GET["id"], "delete", url_query_add(array("action"=>"delete"), false));
	} elseif ($_SESSION["user_id"] == $e["created_user"]) {
		echo drawHeaderRow("Event Details", 2, "edit", "event_edit.php?id=" . $_GET["id"], "delete", url_query_add(array("action"=>"delete"), false));
	} else {
		echo drawHeaderRow("Event Details", 2);
	}?>
	<tr>
		<td class="left">Title</td>
		<td class="right" bgcolor="#ffffff"><b><?=$e["title"]?></b></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><span class="block" style="background-color:<?=$e["color"]?>;"><?=$e["type"]?></span></td>
	</tr>
	<tr>
		<td class="left">Start Date</td>
		<td><?=format_date_time($e["startDate"])?></td>
	</tr>
	<tr valign="top">
		<td class="left" height="200">Description</td>
		<td class="text"><?=$e["description"]?></td>
	</tr>
	<tr valign="top">
		<td class="left">Created</td>
		<td><?=drawName($e["created_user"], $e["first"] . " " . $e["last"], $e["created_date"], true);?></td>
	</tr>
</table>
<? drawBottom();?>