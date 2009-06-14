<?
include("include.php");

if ($posting) {
	//update topic.  don't update the thread_date, or send any emails
	$id = db_save("bb_topics");
	if (getOption("channels")) db_checkboxes("channels", "bb_topics_to_channels", "topic_id", "channel_id", $_GET["id"]);
	bbDrawRss();
	url_change("topic.php?id=" . $_GET["id"]);
}

drawTop();

$t = db_grab("SELECT title, type_id, description, is_admin, created_user FROM bb_topics WHERE id = " . $_GET["id"]);

$form = new intranet_form;
if ($module_admin) {
	$form->addUser("created_user",  "Posted By" , $t["created_user"], false, "ddeedd");
	$form->addCheckbox("is_admin",  "Admin Post?", $t["is_admin"], "(check if yes)", "ddeedd");
}
$form->addRow("itext",  "Subject" , "title", $t["title"], "", true);
if (getOption("bb_types")) $form->addRow("select",  "Category" , "type_id", "SELECT id, title FROM bb_topics_types", $t["type_id"]);
if (getOption("channels")) $form->addCheckboxes("channels", "Networks", "channels", "bb_topics_to_channels", "topic_id", "channel_id", $_GET["id"]);
$form->addRow("textarea", "Message" , "description", $t["description"], "", true);
$form->addRow("submit"  , "edit topic");
$form->draw("Edit Bulletin Board Topic");

drawBottom();
?>