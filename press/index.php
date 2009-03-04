<?  include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE intranet_press_releases SET 
				deletedOn = GETDATE(),
				deletedBy = {$_SESSION["user_id"]},
				isActive = 0
			WHERE id = " . $_GET["id"]);
	url_drop();
} elseif ($posting) {
	$theUserID = ($isAdmin) ? $_POST["createdBy"] : $_SESSION["user_id"];
	db_query("INSERT INTO intranet_press_releases (
		headline,
		detail,
		location,
		releaseDate,
		text,
		corporationID,
		createdOn,
		createdBy,
		isActive
	) VALUES (
		'" . $_POST["headline"] . "',
		'" . $_POST["detail"] . "',
		'" . $_POST["location"] . "',
		" . format_post_date("releaseDate") . ",
		'" . format_html($_POST["text"]) . "',
		" . $_POST["corporationID"] . ",
		GETDATE(),
		" . $theUserID . ",
		1
	)");
	url_change();
}

drawTop();

if (url_id()) {
	$r = db_grab("SELECT
			headline,
			detail,
			location,
			text,
			releaseDate
		FROM intranet_press_releases
		WHERE id = " . $_GET["id"]);
	?>
	<table class="left" cellspacing="1">
		<? if ($isAdmin) {
			echo drawHeaderRow("Press Release", 1, "edit", "edit/?id=" . $_GET["id"]);
		} else {
			echo drawHeaderRow("Press Release", 1);
		}?>
		<tr>
			<td style="padding:20px;" class="text">
				<h1><?=$r["headline"]?></h1>
				<b><?=$r["detail"]?></b><br>
				<i><?=format_date($r["releaseDate"], "n/a", "M d, Y", false)?> ~ <?=$r["location"]?></i><br>
				<br>
				<?=$r["text"]?>
			</td>
		</tr>
	</table>
<? } else {?>
	
	<table class="left" cellspacing="1">
		<? if ($isAdmin) {
			echo drawHeaderRow("Press Releases", 4, "new", "#bottom");
		} else {
			echo drawHeaderRow("Press Releases", 3);
		}?>
		<tr>
			<th align="left" width="62%">Headline</th>
			<th align="left" width="18%">Organization</th>
			<th align="right">Date</th>
			 <? if ($isAdmin) echo "<th></th>"; ?>
		</tr>
		<?
		$result = db_query("SELECT
						p.id,
						p.headline,
						p.releaseDate,
						c.description corporationName
						FROM intranet_press_releases p
						JOIN organizations c ON p.corporationID = c.id
						WHERE p.isactive = 1
						ORDER BY p.releaseDate DESC");
	
		while ($r = db_fetch($result)) { ?>
		    <tr height="40">
		        <td><a href="./?id=<?=$r["id"]?>"><?=$r["headline"]?></a></td>
		        <td><nobr><?=$r["corporationName"]?></nobr></td>
		        <td align="right"><nobr><?=format_date($r["releaseDate"], "n/a", "M d, Y", false)?></nobr></td>
				<?=deleteColumn("Delete this press release?", $r["id"])?>
		    </tr>
		<? }?>
	</table>
	
	<a name="bottom"></a>
	
	<? if ($isAdmin) {
		$form = new intranet_form;
		if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $_SESSION["user_id"], false, "EEDDCC");
		$form->addRow("itext",  "Headline" , "headline", "", "", true, 255);
		$form->addRow("itext",  "Detail" , "detail", "", "", false, 255);
		$form->addRow("itext",  "Location" , "location", "", "", true, 255);
		$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", "1", true);
		$form->addRow("date",  "Date" , "releaseDate", false, false, true);
		$form->addRow("textarea", "Text" , "text", "", "", true);
		$form->addRow("submit"  , "post press release");
		$form->draw("Add a Press Release");
	}
}
drawBottom(); ?>