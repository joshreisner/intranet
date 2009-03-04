<?
include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE documents SET isActive = 0, deletedOn = GETDATE(), deletedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["id"]);
	url_change("/docs/");
}

$d = db_grab("SELECT 
		d.name,
		d.description,
		d.content,
		i.icon,
		i.description fileType
	FROM documents d
	JOIN intranet_doctypes i ON d.typeID = i.id
	WHERE d.id = " . $_GET["id"]);

drawTop();

?>

<table class="left" cellspacing="1">
    <?
    if ($isAdmin) {
    	echo drawHeaderRow("Document Info", 2, "edit","add_edit.php?id=" . $_GET["id"], "delete", deleteLink("Delete document?"));
    } else {
    	echo drawHeaderRow("Document Info", 2);
    }
    ?>
	<tr>
		<td class="left">Name</td>
		<td><h1><a href="download.php?id=<?=$_GET["id"]?>"><?=$d["name"]?></h1></a></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><table class="nospacing"><tr>
			<td><?=draw_img($locale . $d["icon"])?></td>
			<td><?=$d["fileType"]?> (<?=format_size(strlen($d["content"]))?>)</td>
			</tr></table>
		</td>
	</tr>
	<tr>
		<td class="left">Categories</td>
		<td>
			<? $categories = db_query("SELECT
				c.description
			FROM documents_to_categories d2c
			JOIN documents_categories c ON d2c.categoryID = c.id
			WHERE d2c.documentID = " . $_GET["id"]);
				while ($c = db_fetch($categories)) {?>
				 &#183; <?=$c["description"]?></a><br>
			<? }?>
		</td>
	</tr>
	<tr height="120">
		<td class="left">Description</td>
		<td class="text"><?=nl2br($d["description"])?></td>
	</tr>
</table>
<?
$views = db_query("SELECT 
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.userID,
			v.viewedOn
			FROM documents_views v
			JOIN intranet_users u ON v.userID = u.userID
			WHERE v.documentID = " . $_GET["id"] . "
			ORDER BY v.viewedOn DESC", 5);
if (db_found($views)) {?>
<table class="left" cellspacing="1">
    <tr>
		<td class="head docs" colspan="2">Recent Views</td>
	</tr>
	<tr class="left">
		<th align="left">Name</th>
		<th align="right">Date</th>
	</tr>
	<? while($v = db_fetch($views)) {?>
	<tr>
		<td width="70%"><a href="/staff/view.php?id=<?=$v["userID"]?>"><?=$v["first"]?> <?=$v["last"]?></a></td>
		<td width="30%" align="right"><?=format_date_time($v["viewedOn"], " ")?></td>
	</tr>
	<? }?>
</table>
<? 
}
drawBottom();?>