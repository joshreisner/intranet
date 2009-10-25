<?
include("../include.php");

if (url_action("delete")) {
	db_delete('docs');
	url_change("/docs/");
}

$d = db_grab("SELECT 
		d.title,
		d.description,
		d.content,
		i.icon,
		i.description fileType
	FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	WHERE d.id = " . $_GET["id"]);

echo drawTop();
?>

<table class="left" cellspacing="1">
    <?
    if ($page['is_admin']) {
    	echo drawHeaderRow("Document Info", 2, "edit","edit.php?id=" . $_GET["id"], "delete", drawDeleteLink("Delete document?"));
    } else {
    	echo drawHeaderRow("Document Info", 2);
    }
    ?>
	<tr>
		<td class="left">Title</td>
		<td><h1><a href="download.php?id=<?=$_GET["id"]?>"><?=$d["title"]?></h1></a></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><table class="nospacing"><tr>
			<td><?=draw_img($d["icon"])?></td>
			<td><?=$d["fileType"]?> (<?=format_size(strlen($d["content"]))?>)</td>
			</tr></table>
		</td>
	</tr>
	<tr>
		<td class="left">Categories</td>
		<td>
			<? $categories = db_query("SELECT
				c.title
			FROM docs_to_categories d2c
			JOIN docs_categories c ON d2c.categoryID = c.id
			WHERE d2c.documentID = " . $_GET["id"]);
				while ($c = db_fetch($categories)) {?>
				 &#183; <?=$c["title"]?></a><br>
			<? }?>
		</td>
	</tr>
	<? if (getOption('channels')) {?>
	<tr>
		<td class="left">Networks</td>
		<td>
			<? $channels = db_query("SELECT
				c.title_en
			FROM docs_to_channels d2c
			JOIN channels c ON d2c.channel_id = c.id
			WHERE d2c.doc_id = " . $_GET["id"]);
				while ($c = db_fetch($channels)) {?>
				 &#183; <?=$c["title_en"]?></a><br>
			<? }?>
		</td>
	</tr>
	<? }?>
	<tr height="120">
		<td class="left">Description</td>
		<td class="text"><?=nl2br($d["description"])?></td>
	</tr>
</table>
<?
$views = db_query("SELECT 
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.id,
			v.viewedOn
			FROM docs_views v
			JOIN users u ON v.userID = u.id
			WHERE v.documentID = " . $_GET["id"] . "
			ORDER BY v.viewedOn DESC", 5);
if (db_found($views)) {?>
<table class="left" cellspacing="1">
    <tr>
		<td class="head docs" colspan="2">Recent Views</td>
	</tr>
	<tr class="left">
		<th align="left">Title</th>
		<th align="right">Date</th>
	</tr>
	<? while($v = db_fetch($views)) {?>
	<tr>
		<td width="70%"><a href="/staff/view.php?id=<?=$v["id"]?>"><?=$v["first"]?> <?=$v["last"]?></a></td>
		<td width="30%" align="right"><?=format_date_time($v["viewedOn"], " ")?></td>
	</tr>
	<? }?>
</table>
<? 
}
echo drawBottom();?>