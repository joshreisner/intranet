<?
include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE intranet_jobs SET 
				deletedOn = GETDATE(),
				deletedBy = {$_SESSION["user_id"]},
				isActive = 0
			WHERE id = " . $_GET["id"]);
	url_drop();
}


if ($posting) {
	$userID = ($isAdmin) ? $_POST["createdBy"] : $_SESSION["user_id"];
	format_post_html("description");
    db_query("INSERT INTO intranet_jobs (
    	title,
    	description,
		corporationID,
		officeID,
		createdBy,
		createdOn,
		isActive
	) VALUES (
		'" . $_POST["title"] . "',
		" . $_POST["description"] . ",
		" . $_POST["corporationID"] . ",
		" . $_POST["officeID"] . ",
		" . $userID . ",
		GETDATE(),
		1
    );");
    url_change();
}

drawTop();
?>
<table class="left" cellspacing="1">
	<? if ($isAdmin) {
		$colspan = 4;
		echo drawHeaderRow("Open Positions", $colspan, "new", "#bottom");
	} else {
		$colspan = 3;
		echo drawHeaderRow("Open Positions", $colspan);
	}?>
	<tr>
		<th align="left" width="50%">Title</th>
		<th align="left" width="30%">Location</th>
		<th align="right" width="20%"><nobr>Last Update</nobr></th>
		<? if ($isAdmin) {?><th></th><? }?>
	</tr>
	<?
	$result = db_query("SELECT 
							j.id,
							j.title,
							c.description corporationName,
							o.name office,
							ISNULL(j.updatedOn, j.createdOn) updatedOn
						FROM intranet_jobs j
						LEFT JOIN organizations c ON j.corporationID = c.id
						LEFT JOIN intranet_offices o ON j.officeID = o.id
						WHERE j.isActive = 1
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
			<td align="right"><?=format_date($r["updatedOn"])?></td>
			<?=deleteColumn("Delete this position?", $r["id"])?>
		</tr>
		<? }?>
</table>

<a name="bottom"></a>

<? if ($isAdmin) {
	$form = new intranet_form;
	if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addRow("itext",  "Title" , "title", "", "", true);
	$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", "", true);
	$form->addRow("select", "Location" , "officeID", "SELECT id, name FROM intranet_offices ORDER BY precedence", "", true);
	$form->addRow("textarea", "Description" , "description", "", "", true);
	$form->addRow("submit"  , "post open position");
	$form->draw("Add an Open Position");
}

drawBottom(); ?>