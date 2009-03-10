<?	include("../include.php");

if ($posting) {
	error_debug("handling bb post");
	format_post_bits("is_admin");
	$id = db_enter("bb_topics", "title |description is_admin");
	db_query("UPDATE bb_topics SET threadDate = GETDATE() WHERE id = " . $id);
	
	if ($_POST["is_admin"] == "'1'") { //send admin email
		//get topic 
		$r = db_grab("SELECT 
				t.title,
				t.description,
				u.user_id,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				t.created_date
				FROM bb_topics t
				JOIN users u ON t.created_user = u.user_id
				WHERE t.id = " . $id);
		
		//construct email
		$message  = drawEmailHeader();
		$message .= drawServerMessage("<b>Note</b>: This is an Administration/Human Resources topic from the <a href='http://" . $server . "/bulletin_board/'>Intranet Bulletin Board</a>.  For more information, please contact the <a href='mailto:hrpayroll@seedco.org'>Human Resources Department</a>.");
		$message .= '<table class="center">';
		$message .= drawHeaderRow("Email", 2);
		$message .= drawThreadTop($r["title"], $r["description"], $r["user_id"], $r["firstname"] . " " . $r["lastname"], $r["created_date"]);
		$message .= '</table>' . drawEmailFooter();
		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: " . $_josh["email_default"] . "\r\n";
		
		//get addresses & send
		$users = db_query("SELECT email FROM users WHERE is_active = 1");
		while ($u = db_fetch($users)) {
			mail($u["email"], $r["title"], $message, $headers);
		}
	}
	syndicateBulletinBoard();
	url_change();
}

drawTop();
echo draw_autorefresh(5);
echo drawSyndicateLink("bb");
echo drawTableStart();
echo drawHeaderRow("", 4, "new", "#bottom");
error_debug("get bb topix");

$topics = db_query("SELECT 
		t.id,
		t.title,
		t.is_admin,
		t.threadDate,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topicID AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
	FROM bb_topics t
	JOIN users u ON u.user_id = t.created_user
	WHERE t.is_active = 1 
	ORDER BY t.threadDate DESC", 15);
if (db_found($topics)) {?>
	<tr>
		<th width="320">Topic</th>
		<th width="120">Starter</th>
		<th class="c">Replies</th>
		<th class="r">Last Post</th>
	</tr>
	<?
	while ($r = db_fetch($topics)) {
		$r["lastname"] = htmlentities($r["lastname"]); //see http://work.joshreisner.com/request/?id=477
		if ($r["is_admin"]) $r["replies"] = "-";?>
		<tr class="thread"<? if ($r["is_admin"]) {?> style="background-color:#fffce0;"<? }?>
			onclick		= "location.href='topic.php?id=<?=$r["id"]?>';"
			onmouseover	= "javascript:aOver('id<?=$r["id"]?>')"
			onmouseout	= "javascript:aOut('id<?=$r["id"]?>')">
			<td class="input"><a href="topic.php?id=<?=$r["id"]?>" id="id<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><?=$r["firstname"]?> <?=$r["lastname"]?></td>
			<td align="center"><?=$r["replies"]?></td>
			<td align="right"><?=format_date($r["threadDate"])?></td>
		</tr>
	<? }
} else {
	echo drawEmptyResult("No topics have been added yet.  Why not <a href='#bottom'>be the first</a>?", 4);
}

?>
</table>
<a name="bottom"></a>
<?
$form = new intranet_form;
if ($is_admin) {
	$form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addCheckbox("is_admin",  "Admin Post?", 0, "(check if yes)", true);
}
$form->addRow("itext",  "Subject" , "title", "", "", true);
$form->addRow("textarea", "Message" , "description", "", "", true);
$form->addRow("submit"  , "add new topic");
$form->draw("Contribute a New Topic");

drawBottom(); 
?>