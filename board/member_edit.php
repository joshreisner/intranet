<?
include("../include.php");

if (!empty($_POST)) {
	db_query("UPDATE board_members SET
			firstname		= '{$_POST["firstname"]}',	
			lastname		= '{$_POST["lastname"]}',	
			bio				= '{$_POST["bio"]}',	
			corporationID	= {$_POST["corporationID"]},
			positionOnBoard	= '{$_POST["positionOnBoard"]}',	
			employment		= '{$_POST["employment"]}',	
			updatedOn		= GETDATE(),
			updatedBy		= {$_SESSION["user_id"]}
			WHERE id		= " . $_GET["id"]);
	url_change("member.php?id=" . $_GET["id"]);
}

drawTop();

$r = db_grab("SELECT 
				firstname,
				lastname,
				bio,
				positionOnBoard,
				employment,
				corporationID
			FROM board_members
			WHERE id = " . $_GET["id"]);
	
$form = new intranet_form;
$form->addRow("itext",  "First Name" , "firstname", $r["firstname"], "", true, 255);
$form->addRow("itext",  "Last Name" , "lastname", $r["lastname"], "", false, 255);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", $r["corporationID"]);
$form->addRow("itext",  "Position on Board" , "positionOnBoard", $r["positionOnBoard"], "", false, 255);
$form->addRow("itext",  "Employment" , "employment", $r["employment"], "", false, 255);
$form->addRow("textarea", "Bio" , "bio", $r["bio"], "", false);
$form->addRow("submit"  , "update board member");
$form->draw("<a href='index.php' class='white'>Board Members</a> &gt; Update Member");

drawBottom();?>