<?
include("../include.php");

if ($_POST) {
	$theuser_id = ($module_admin) ? $_POST["created_user"] : $_SESSION["user_id"];
	$_POST["description"] = format_html($_POST["description"]);
	db_query("UPDATE openings SET
			title			= '{$_POST["title"]}',	
			description		= '{$_POST["description"]}',	
			corporationID	= {$_POST["corporationID"]},
			officeID		= {$_POST["officeID"]},
			updated_date		= GETDATE(),
			updated_user		= {$theuser_id}
			WHERE id = " . $_GET["id"]);
	url_change("position.php?id=" . $_GET["id"]);
}

drawTop();

$r = db_grab("SELECT 
				j.id,
				j.title,
				j.description,
				j.corporationID,
				j.officeID
			FROM openings j
			WHERE j.id = " . $_GET["id"]);

$form = new intranet_form;
if ($module_admin) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, "EEDDCC");
$form->addRow("itext",  "Title" , "title", $r["title"], "", true);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", $r["corporationID"], true);
$form->addRow("select", "Location" , "officeID", "SELECT id, name FROM offices ORDER BY precedence", $r["officeID"], true);
$form->addRow("textarea", "Description" , "description", $r["description"], "", true);
$form->addRow("submit"  , "update position");
$form->draw("<a href='positions.php' class='white'>Open Positions</a> &gt; Update Position");

drawBottom();?>