<?php
include("../../include.php");

if ($posting) {
	db_enter("links", "text url precedence");
	url_change();
} elseif (url_action("delete")) {
	db_query("UPDATE links SET is_active = 0, deleted_date = NOW(), deleted_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_drop("id, action");
}

drawTop();

echo drawTableStart();
echo drawHeaderRow(false, 5, "new", "#bottom");?>
<tr>
	<th style="text-align:left;">Link</th>
	<th style="text-align:left;">Address</th>
	<th style="width:16px;"></th>
	<th style="width:16px;"></th>
	<th style="width:16px;"></th>
</tr>
<?
$links = db_query("SELECT id, text, url FROM links WHERE is_active = 1 ORDER BY precedence");
if ($max = db_found($links)) {
	$counter = 1;
	while ($l = db_fetch($links)) {?>
		<tr>
			<td><?=$l["text"]?></td>
			<td><a href="<?=$l["url"]?>"><?=$l["url"]?></a></td>
			<td><? if ($counter != 1) echo draw_img($locale . "images/icons/moveup.gif")?></td>
			<td><? if ($counter != $max) echo draw_img($locale . "images/icons/movedown.gif")?></td>
			<?=deleteColumn("are you sure?", $l["id"]);?>
		</tr>
	<? 
	$counter++;
	}
} else {
	echo drawEmptyResult("No links entered in the system yet!");
}
echo drawTableEnd();

echo '<a name="bottom"></a>';
$form = new intranet_form;
$form->addRow("hidden", "", "precedence", ($max + 1));
$form->addRow("itext",  "Link" , "text", "", "", true);
$form->addRow("itext",  "Address" , "url", "http://", "", true, 255);
$form->addRow("submit"  , "add new link");
$form->draw("Add a New Link");

drawBottom(); 
?>

drawBottom();?>