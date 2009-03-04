<?  include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE board_members SET 
				deletedOn = GETDATE(),
				deletedBy = {$_SESSION["user_id"]},
				isActive = 0
			WHERE id = " . $_GET["id"]);
	url_drop();
} elseif ($posting) {
	db_query("INSERT INTO board_members (
		firstname,
		lastname,
		bio,
		positionOnBoard,
		employment,
		corporationID,
		createdOn,
		createdBy,
		isActive
	) VALUES (
		'" . $_POST["firstname"] . "',
		'" . $_POST["lastname"] . "',
		'" . $_POST["bio"] . "',
		'" . $_POST["positionOnBoard"] . "',
		'" . $_POST["employment"] . "',
		" . $_POST["corporationID"] . ",
		GETDATE(),
		" . $_SESSION["user_id"] . ",
		1
	)");
	url_change();
}

drawTop();
?>

<table class="left" cellspacing="1">
	<? if ($isAdmin) {
		$colspan = 3;
		echo drawHeaderRow("Board Members", $colspan, "new", "#bottom");
	} else {
		$colspan = 3;
		echo drawHeaderRow("Board Members", $colspan);
	}?>
	<tr>
		<th align="left" width="60%">Name</th>
		<th align="left" width="40%">Position on Board</th>
		 <? if ($isAdmin) echo "<th width='16'></th>"; ?>
	</tr>
	<?
	$result = db_query("SELECT
					m.id,
					m.firstname,
					m.lastname,
					m.positionOnBoard,
					o.description organization
					FROM board_members m
					JOIN organizations o ON m.corporationID = o.id
					WHERE m.isActive = 1
					ORDER BY o.description, m.lastname, m.firstname");
	$lastCorporation = "";
	while ($r = db_fetch($result)) {
		if ($r["organization"] != $lastCorporation) {
			$lastCorporation = $r["organization"];
			echo "<tr class='group'><td colspan='" . $colspan . "'>" . $lastCorporation . "</td></tr>";
		}
	 ?>
	    <tr>
	        <td><a href="member.php?id=<?=$r["id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
	        <td><nobr><?=$r["positionOnBoard"]?></nobr></td>
			<?=deleteColumn("Are you sure you want to delete this board member?", $r["id"])?>
	    </tr>
	<? }?>
</table>

<a name="bottom"></a><br>

<? if ($isAdmin) {
	$form = new intranet_form;
	$form->addRow("itext",  "First Name" , "firstname", "", "", true, 255);
	$form->addRow("itext",  "Last Name" , "lastname", "", "", true, 255);
	$form->addRow("select", "Organization", "corporationID", "SELECT id, description FROM organizations ORDER BY description", "", true);
	$form->addRow("itext",  "Position on Board" , "positionOnBoard", "", "", false, 255);
	$form->addRow("itext",  "Employment" , "employment", "", "", false, 255);
	$form->addRow("textarea", "Bio" , "bio", "", "", false);
	$form->addRow("submit"  , "add board member");
	$form->draw("Add a Board Member");
}
drawBottom(); ?>