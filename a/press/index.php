<?  include("../../include.php");

if (url_action("delete")) {
	db_query("UPDATE press_releases SET 
				deleted_date = GETDATE(),
				deleted_user = {$_SESSION["user_id"]},
				is_active = 0
			WHERE id = " . $_GET["id"]);
	url_drop();
} elseif ($posting) {
	$theuser_id = ($page['is_admin']) ? $_POST["created_user"] : $_SESSION["user_id"];
	db_query("INSERT INTO press_releases (
		headline,
		detail,
		location,
		releaseDate,
		text,
		corporationID,
		created_date,
		created_user,
		is_active
	) VALUES (
		'" . $_POST["headline"] . "',
		'" . $_POST["detail"] . "',
		'" . $_POST["location"] . "',
		" . format_post_date("releaseDate") . ",
		'" . format_html($_POST["text"]) . "',
		" . $_POST["corporationID"] . ",
		GETDATE(),
		" . $theuser_id . ",
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
		FROM press_releases
		WHERE id = " . $_GET["id"]);
	?>
	<table class="left" cellspacing="1">
		<? if ($page['is_admin']) {
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
		<? if ($page['is_admin']) {
			echo drawHeaderRow("Press Releases", 4, "new", "#bottom");
		} else {
			echo drawHeaderRow("Press Releases", 3);
		}?>
		<tr>
			<th align="left" width="62%">Headline</th>
			<th align="left" width="18%">Organization</th>
			<th align="right">Date</th>
			 <? if ($page['is_admin']) echo "<th></th>"; ?>
		</tr>
		<?
		$result = db_query("SELECT
						p.id,
						p.headline,
						p.releaseDate,
						c.description corporationName
						FROM press_releases p
						JOIN organizations c ON p.corporationID = c.id
						WHERE p.is_active = 1
						ORDER BY p.releaseDate DESC");
	
		while ($r = db_fetch($result)) { ?>
		    <tr height="40">
		        <td><a href="./?id=<?=$r["id"]?>"><?=$r["headline"]?></a></td>
		        <td><nobr><?=$r["corporationName"]?></nobr></td>
		        <td align="right"><nobr><?=format_date($r["releaseDate"], "n/a", "M d, Y", false)?></nobr></td>
				<?=drawDeleteColumn("Delete this press release?", $r["id"])?>
		    </tr>
		<? }?>
	</table>
	
	<a name="bottom"></a>
	
	<? if ($page['is_admin']) {
		$form = new intranet_form;
		if ($page['is_admin']) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, "EEDDCC");
		$form->addRow("itext",  "Headline" , "headline", "", "", true, 255);
		$form->addRow("itext",  "Detail" , "detail", "", "", false, 255);
		$form->addRow("itext",  "Location" , "location", "", "", true, 255);
		$form->addRow("select", "Organization" , "corporationID", "SELECT id, title from organizations ORDER BY title", "1", true);
		$form->addRow("date",  "Date" , "releaseDate", false, false, true);
		$form->addRow("textarea", "Text" , "text", "", "", true);
		$form->addRow("submit"  , "post press release");
		$form->draw("Add a Press Release");
	}
}
drawBottom(); ?>