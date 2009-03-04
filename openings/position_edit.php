<?
include("../include.php");

if ($_POST) {
	$theUserID = ($isAdmin) ? $_POST["createdBy"] : $_SESSION["user_id"];
	$_POST["description"] = format_html($_POST["description"]);
	db_query("UPDATE intranet_jobs SET
			title			= '{$_POST["title"]}',	
			description		= '{$_POST["description"]}',	
			corporationID	= {$_POST["corporationID"]},
			officeID		= {$_POST["officeID"]},
			updatedOn		= GETDATE(),
			updatedBy		= {$theUserID}
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
			FROM intranet_jobs j
			WHERE j.id = " . $_GET["id"]);

$form = new intranet_form;
if ($isAdmin) $form->addUser("createdBy",  "Posted By" , $_SESSION["user_id"], false, "EEDDCC");
$form->addRow("itext",  "Title" , "title", $r["title"], "", true);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, description FROM organizations ORDER BY description", $r["corporationID"], true);
$form->addRow("select", "Location" , "officeID", "SELECT id, name FROM intranet_offices ORDER BY precedence", $r["officeID"], true);
$form->addRow("textarea", "Description" , "description", $r["description"], "", true);
$form->addRow("submit"  , "update position");
$form->draw("<a href='positions.php' class='white'>Open Positions</a> &gt; Update Position");

drawBottom();?>