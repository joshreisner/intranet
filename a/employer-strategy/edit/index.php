<?php
include("../../include.php");

if ($posting) {
	$id = db_save("employer_strategy_resources");
	url_change_post("../");
}

echo drawTop();

$f = new form("employer_strategy_resources", true, @$_GET["id"]);
$f->set_title(drawHeader());
$f->set_field(array("type"=>"checkboxes", "name"=>"tags", "options_table"=>"employer_strategy_resources_tags", "linking_table"=>"employer_strategy_resources_to_tags", "option_id"=>"tag_id", "object_id"=>"resource_id"));
echo $f->draw();

echo drawBottom();
?>