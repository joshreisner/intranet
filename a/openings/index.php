<?
include("../include.php");

if (url_action("delete")) {
	db_delete("openings");
	url_drop();
} elseif ($posting) {
	//debug();
	if ($id = db_save("openings")) url_change("position.php?id=" . $id);
}

drawTop();
?>
<table class="left" cellspacing="1">
	<? if ($page['is_admin']) {
		$colspan = 4;
		echo drawHeaderRow("Open Positions", $colspan, "new", "#bottom");
	} else {
		$colspan = 3;
		echo drawHeaderRow("Open Positions", $colspan);
	}?>
	<tr>
		<th width="50%">Title</th>
		<th width="30%">Location</th>
		<th class="r" width="20%"><nobr>Last Update</nobr></th>
		<? if ($page['is_admin']) {?><th></th><? }?>
	</tr>
	<?
	$result = db_query("SELECT 
							j.id,
							j.title,
							c.description corporationName,
							o.name office,
							ISNULL(j.updated_date, j.created_date) updated_date
						FROM openings j
						LEFT JOIN organizations c ON j.corporationID = c.id
						LEFT JOIN offices o ON j.officeID = o.id
						WHERE j.is_active = 1
						ORDER BY c.description, j.title");
	$lastCorporation = "";
	while ($r = db_fetch($result)) {
	if ($r["corporationName"] != $lastCorporation) {
		$lastCorporation = $r["corporationName"];
		echo '<tr class="group"><td colspan="' . $colspan . '">' . $lastCorporation . '</td></tr>';
		}?>
		<tr>
			<td><a href="position.php?id=<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><?=$r["office"]?></td>
			<td class="r"><?=format_date($r["updated_date"])?></td>
			<?=drawDeleteColumn("Delete this position?", $r["id"])?>
		</tr>
		<? }?>
</table>

<a name="bottom"></a>

<? if ($page['is_admin']) {
	$form = new intranet_form;
	$form->addRow("itext",  "Title" , "title", "", "", true);
	$form->addRow("select", "Organization" , "corporationID", "SELECT id, title from organizations ORDER BY title", "", true);
	$form->addRow("select", "Location" , "officeID", "SELECT id, name FROM offices ORDER BY precedence", "", true);
	$form->addRow("checkbox", "Internship?" , "is_internship", $r["is_internship"]);
	$form->addRow("textarea", "Description" , "description", "", "", true);
	$form->addRow("submit"  , "post open position");
	$form->draw("Add an Open Position");
}

drawBottom(); ?>