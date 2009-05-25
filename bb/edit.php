<?
include("../include.php");

if ($posting) {
	//update topic.  don't update the thread_date, or send any emails
	db_save("bb_topics");
	syndicateBulletinBoard();
	url_change("topic.php?id=" . $_GET["id"]);
}

drawTop();

$t = db_grab("SELECT title, description, is_admin, created_user FROM bb_topics WHERE id = " . $_GET["id"]);

$form = new intranet_form;
if ($module_admin) {
	$form->addUser("created_user",  "Posted By" , $t["created_user"], false, "ddeedd");
	$form->addCheckbox("is_admin",  "Admin Post?", $t["is_admin"], "(check if yes)", "ddeedd");
}
$form->addRow("itext",  "Subject" , "title", $t["title"], "", true);
$form->addRow("textarea", "Message" , "description", $t["description"], "", true);
$form->addRow("submit"  , "edit topic");
$form->draw("Edit Bulletin Board Topic");

drawBottom();
?>