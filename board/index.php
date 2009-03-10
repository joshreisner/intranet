<?  include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE board_members SET 
				deleted_date = GETDATE(),
				deleted_user = {$_SESSION["user_id"]},
				is_active = 0
			WHERE id = " . $_GET["id"]);
	url_drop();
} elseif ($posting) {
	db_query("INSERT INTO board_members (
		firstname,
		lastname,
		bio,
		board_position,
		employment,
		organization_id,
		created_date,
		created_user,
		is_active
	) VALUES (
		'" . $_POST["firstname"] . "',
		'" . $_POST["lastname"] . "',
		'" . $_POST["bio"] . "',
		'" . $_POST["board_position"] . "',
		'" . $_POST["employment"] . "',
		" . $_POST["organization_id"] . ",
		GETDATE(),
		" . $_SESSION["user_id"] . ",
		1
	)");
	url_change();
}

drawTop();
echo drawTableStart();
if ($is_admin) {
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
		 <? if ($is_admin) echo "<th width='16'></th>"; ?>
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
			<?=deleteColumn("Are you sure you want to delete this board member?", $r["id"])?>
	    </tr>
	<? }
} else {
	echo drawEmptyResult("No board members added yet", $colspan);
}
echo drawTableEnd();
?>

<a name="bottom"></a>

<? if ($is_admin) {
	$form = new intranet_form;
	$form->addRow("itext",  "First Name" , "firstname", "", "", true, 255);
	$form->addRow("itext",  "Last Name" , "lastname", "", "", true, 255);
	$form->addRow("select", "Organization", "organization_id", "SELECT id, description FROM organizations ORDER BY description", "", true);
	$form->addRow("itext",  "Position on Board" , "board_position", "", "", false, 255);
	$form->addRow("itext",  "Employment" , "employment", "", "", false, 255);
	$form->addRow("textarea", "Bio" , "bio", "", "", false);
	$form->addRow("submit"  , "add board member");
	$form->draw("Add a Board Member");
}
drawBottom(); ?>