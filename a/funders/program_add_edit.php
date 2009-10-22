<?  
include("../include.php");

if ($posting) {
	if (url_id()) {
		db_query("UPDATE funders_programs SET programDesc = '" . $_POST["programDesc"] . "' WHERE programID = " . $_GET["id"]);
	} else {
		$_GET["id"] = db_query("INSERT INTO funders_programs ( programDesc ) VALUES ( '{$_POST["programDesc"]}' )");
	}
	url_change("program.php?id=" . $_GET["id"]);
}

drawTop();

if (url_id()) {
	$program = db_grab("SELECT programDesc FROM funders_programs WHERE programID = " . $_GET["id"]);
	$title = "Edit Program";
} else {
	$title = "Add New Program";
}
?>
<table class="left" cellspacing="1">
	<form method="post" action="<?=$_josh["request"]["path_query"]?>">
	<?=drawHeaderRow($title, 2);?>
	<tr>
		<td class="left">Name</td>
		<td><?=draw_form_text("programDesc", @$program)?></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?=draw_form_submit("save changes")?></td>
	</tr>
	</form>
</table>
<? drawBottom();?>