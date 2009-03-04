<?
include("../include.php");

if ($posting) {
	format_post_bits("isAdmin");
	$_POST["description"] = format_html($_POST["description"]);
	db_enter("bulletin_board_topics", "title description isAdmin");
	//db_query("UPDATE bulletin_board_topics SET threadDate = GETDATE() WHERE id = " . $_GET["id"]); don't do this
	syndicateBulletinBoard();
	url_change("topic.php?id=" . $_GET["id"]);
}

drawTop();


$t = db_grab("SELECT title, description, isAdmin, createdBy FROM bulletin_board_topics WHERE id = " . $_GET["id"]);

$form = new intranet_form;
if ($isAdmin) {
	$form->addUser("createdBy",  "Posted By" , $t["createdBy"], false, "ddeedd");
	$form->addCheckbox("isAdmin",  "Admin Post?", $t["isAdmin"], "(check if yes)", "ddeedd");
}
$form->addRow("itext",  "Subject" , "title", $t["title"], "", true);
$form->addRow("textarea", "Message" , "description", $t["description"], "", true);
$form->addRow("submit"  , "edit topic");
$form->draw("Edit Bulletin Board Topic");

drawBottom();
?>