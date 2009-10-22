<?
include("../../include.php");

if (!empty($_POST)) {
	db_query("UPDATE board_members SET
			firstname		= '{$_POST["firstname"]}',	
			lastname		= '{$_POST["lastname"]}',	
			bio				= '{$_POST["bio"]}',	
			organization_id	= {$_POST["organization_id"]},
			board_position	= '{$_POST["board_position"]}',	
			employment		= '{$_POST["employment"]}',	
			updated_date	= GETDATE(),
			updated_user	= {$_SESSION["user_id"]}
			WHERE id		= " . $_GET["id"]);
	url_change("member.php?id=" . $_GET["id"]);
}

drawTop();

$r = db_grab("SELECT 
				firstname,
				lastname,
				bio,
				board_position,
				employment,
				organization_id
			FROM board_members
			WHERE id = " . $_GET["id"]);
	
$form = new intranet_form;
$form->addRow("itext",  "First Name" , "firstname", $r["firstname"], "", true, 255);
$form->addRow("itext",  "Last Name" , "lastname", $r["lastname"], "", false, 255);
$form->addRow("select", "Organization" , "organization_id", "SELECT id, title from organizations ORDER BY title", $r["organization_id"]);
$form->addRow("itext",  "Position on Board" , "board_position", $r["board_position"], "", false, 255);
$form->addRow("itext",  "Employment" , "employment", $r["employment"], "", false, 255);
$form->addRow("textarea", "Bio" , "bio", $r["bio"], "", false);
$form->addRow("submit"  , "update board member");
$form->draw("<a href='index.php' class='white'>Board Members</a> &gt; Update Member");

drawBottom();?>