<?	include("../include.php");

if ($posting) {
	format_post_bits("isAdmin");
	$_POST["description"] = format_html($_POST["description"]);
	$id = db_enter("bb_topics", "title |description isAdmin");
	db_query("UPDATE bb_topics SET threadDate = GETDATE() WHERE id = " . $id);
	
	if ($_POST["isAdmin"] == "'1'") {
		//get topic 
		$r = db_grab("SELECT 
				t.title,
				t.description,
				u.userID,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				u.imageID,
				m.width,
				m.height,
				t.createdOn
				FROM bb_topics t
				JOIN users u ON t.createdBy = u.userID
				LEFT JOIN intranet_images m ON u.imageID = m.imageID
				WHERE t.id = " . $id);
		
		//construct email
		$message  = drawEmailHeader();
		$message .= drawServerMessage("<b>Note</b>: This is an Administration/Human Resources topic from the <a href='http://" . $server . "/bulletin_board/'>Intranet Bulletin Board</a>.  For more information, please contact the <a href='mailto:hrpayroll@seedco.org'>Human Resources Department</a>.");
		$message .= '<table width="100%" cellpadding="3" cellspacing="1" border="0">';
		$message .= drawHeaderRow("Email", 2);
		$message .= drawThreadTop($r["title"], $r["description"], $r["userID"], $r["firstname"] . " " . $r["lastname"], $r["imageID"], $r["width"], $r["height"], $r["createdOn"]);
		$message .= '</table>' . drawEmailFooter();
		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: " . $_josh["email_default"] . "\r\n";
		
		//get addresses & send
		$users = db_query("SELECT email FROM users WHERE isactive = 1");
		while ($u = db_fetch($users)) {
			mail($u["email"], $r["title"], $message, $headers);
		}
	}
	syndicateBulletinBoard();
	url_change();
}

drawTop();
drawSyndicateLink("bb");
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("All Topics", 4, "add new topic", "#bottom")?>
	<tr>
		<th align="left" width="320">Topic</td>
		<th align="left" width="120">Starter</td>
		<th>Replies</td>
		<th align="right">Last Post</td>
	</tr>
<?
//get bulletin board topics
$result = db_query("SELECT 
		t.id,
		t.title,
		t.isAdmin,
		t.threadDate,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topicID AND f.isActive = 1) replies,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
	FROM bb_topics t
	JOIN users u ON u.userID = t.createdBy
	WHERE t.isActive = 1 
	ORDER BY t.threadDate DESC");

while ($r = db_fetch($result)) {
	if ($r["isAdmin"]) $r["replies"] = "-";?>
	<tr class="thread"<? if ($r["isAdmin"]) {?> style="background-color:<?=$colors["yellow"]?>""<?}?>
			onclick		= "location.href='topic.php?id=<?=$r["id"]?>';"
			onmouseover	= "javascript:aOver('id<?=$r["id"]?>')"
			onmouseout	= "javascript:aOut('id<?=$r["id"]?>')">
		<td class="input"><a href="topic.php?id=<?=$r["id"]?>" id="id<?=$r["id"]?>"><?=$r["title"]?></a></td>
		<td><?=$r["firstname"]?> <?=$r["lastname"]?></td>
		<td align="center"><?=$r["replies"]?></td>
		<td align="right"><?=format_date($r["threadDate"])?></td>
	</tr>
	<? }?>
</table>
<a name="bottom"></a>
<?
$form = new intranet_form;
if ($isAdmin) {
	$form->addUser("createdBy",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addCheckbox("isAdmin",  "Admin Post?", 0, "(check if yes)", true);
}
$form->addRow("itext",  "Subject" , "title", "", "", true);
$form->addRow("textarea", "Message" , "description", "", "", true);
$form->addRow("submit"  , "add new topic");
$form->draw("Add a New Bulletin Board Topic");

drawBottom(); 
?>