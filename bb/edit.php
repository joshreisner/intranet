<?
include("../include.php");

if ($posting) {
	format_post_bits("is_admin");
	$_POST["description"] = format_html($_POST["description"]);
	db_enter("bb_topics", "title description is_admin");
	//db_query("UPDATE bb_topics SET threadDate = GETDATE() WHERE id = " . $_GET["id"]); don't do this
	syndicateBulletinBoard();
	url_change("topic.php?id=" . $_GET["id"]);
}

drawTop();


$t = db_grab("SELECT title, description, is_admin, created_user FROM bb_topics WHERE id = " . $_GET["id"]);

$form = new intranet_form;
if ($is_admin) {
	$form->addUser("created_user",  "Posted By" , $t["created_user"], false, "ddeedd");
	$form->addCheckbox("is_admin",  "Admin Post?", $t["is_admin"], "(check if yes)", "ddeedd");
}
$form->addRow("itext",  "Subject" , "title", $t["title"], "", true);
$form->addRow("textarea", "Message" , "description", $t["description"], "", true);
$form->addRow("submit"  , "edit topic");
$form->draw("Edit Bulletin Board Topic");

drawBottom();
?>