<?  include("../../include.php");

if (url_action("delete")) {
	db_delete('board_members');
	url_drop();
} elseif ($posting) {
	$id = db_save('board_members');
	url_change();
}

echo drawTop();
echo drawTableStart();
if ($page['is_admin']) {
	$colspan = 2;
	echo drawHeaderRow("Board Members", $colspan, "new", "#bottom");
} else {
	$colspan = 3;
	echo drawHeaderRow("Board Members", $colspan);
}
$result = db_query("SELECT
				m.id,
				m.firstname,
				m.lastname,
				m.board_position,
				o.description organization
			FROM board_members m
			JOIN organizations o ON m.organization_id = o.id
			WHERE m.is_active = 1
			ORDER BY o.description, m.lastname, m.firstname");
if (db_found($result)) {?>
	<tr>
		<th align="left" width="60%">Name</th>
		<th align="left" width="40%">Position on Board</th>
		 <? if ($page['is_admin']) echo "<th width='16'></th>"; ?>
	</tr>
	<?
	$lastCorporation = "";
	while ($r = db_fetch($result)) {
		if ($r["organization"] != $lastCorporation) {
			$lastCorporation = $r["organization"];
			echo "<tr class='group'><td colspan='" . $colspan . "'>" . $lastCorporation . "</td></tr>";
		}?>
	    <tr>
	        <td><a href="member.php?id=<?=$r["id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
	        <td><nobr><?=$r["board_position"]?></nobr></td>
			<?=drawDeleteColumn("Are you sure you want to delete this board member?", $r["id"])?>
	    </tr>
	<? }
} else {
	echo drawEmptyResult("No board members added yet", $colspan);
}
echo drawTableEnd();
?>

<a name="bottom"></a>

<? if ($page['is_admin']) {
	$form = new intranet_form;
	$form->addRow("itext",  "First Name" , "firstname", "", "", true, 255);
	$form->addRow("itext",  "Last Name" , "lastname", "", "", true, 255);
	$form->addRow("select", "Organization", "organization_id", "SELECT id, title from organizations ORDER BY title", "", true);
	$form->addRow("itext",  "Position on Board" , "board_position", "", "", false, 255);
	$form->addRow("itext",  "Employment" , "employment", "", "", false, 255);
	$form->addRow("textarea", "Bio" , "bio", "", "", false);
	$form->addRow("submit"  , "add board member");
	$form->draw("Add a Board Member");
}
echo drawBottom();
?>