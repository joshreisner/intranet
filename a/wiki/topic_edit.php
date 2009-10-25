<? include("../../include.php");

url_query_require();

if ($posting) {
	$id = db_save("wiki_topics");
	db_checkboxes("tags", "wiki_topics_to_tags", "topicID", "tagID", $id);
	url_change("topic.php?id=" . $id);
}

echo drawTop();



$r = db_grab("SELECT id, title, description, type_id FROM wiki_topics WHERE id = " . $_GET["id"]);

$form = new intranet_form;
if ($page['is_admin']) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
$form->addRow("itext",  "Title" , "title", $r["title"], "", true, 255);
$form->addRow("select", "Type" , "type_id", "SELECT id, description FROM wiki_topics_types", $r["type_id"], true);
$form->addCheckboxes("tags", "Tags", "wiki_tags", "wiki_topics_to_tags", "topicID", "tagID", $_GET["id"]);
$form->addRow("textarea", "Description" , "description", $r["description"], "", true);
$form->addRow("submit"  , "post wiki topic");
$form->draw("Add a Wiki Topic");

echo drawBottom();
?>