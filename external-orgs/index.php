<?php
include("../include.php");

//need a type
if (!isset($_GET["type"])) url_query_add(array("type"=>1));

drawTop();

//get nav options from db and draw
$options = array();
$types = db_query("SELECT id, description FROM external_orgs_types WHERE is_active = 1 ORDER BY description");
while ($t = db_fetch($types)) $options[url_query_add(array("type"=>$t["id"]), false)] = $t["description"];
echo drawNavigationRow($options, false, true);

//main table
echo drawTableStart();
if ($module_admin) {
	echo drawHeaderRow($page["name"], 1, "add new", "#bottom");
} else {
	echo drawHeaderRow();
}
$orgs = db_query("SELECT o.id, o.url, o.name, o.description FROM external_orgs o WHERE (SELECT COUNT(*) FRom external_orgs_to_types t WHERE t.org_id = o.id AND t.type_id = {$_GET["type"]}) > 0 ORDER BY name");
if (db_found($orgs)) {
	while ($o = db_fetch($orgs)) {?>
	<tr>
		<td class="text">
			<? if ($module_admin) {?>
			<a href="<?=deleteLink("delete this org?", $o["id"])?>" class="button-light right">del</a>
			<a href="edit/?id=<?=$o["id"]?>" class="button-light right">edit</a>
			<? }?>
			<a href="<?=$o["url"]?>"><?=$o["name"]?></a><br><?=$o["description"]?>
		</td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("There are no orgs listed for this type.");
}
echo drawTableEnd();

//add new
include("edit/index.php");
drawBottom();?>