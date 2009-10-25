<?
include("../../include.php");

if ($posting) {
	$id = db_save("openings");
	url_change("position.php?id=" . $id);
}

echo drawTop();

$r = db_grab("SELECT 
				j.title,
				j.description,
				j.is_internship,
				j.corporationID,
				j.officeID
			FROM openings j
			WHERE j.id = " . $_GET["id"]);

$form = new intranet_form;
$form->addRow("itext",  "Title" , "title", $r["title"], "", true);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, title from organizations ORDER BY title", $r["corporationID"], true);
$form->addRow("select", "Location" , "officeID", "SELECT id, name FROM offices ORDER BY precedence", $r["officeID"], true);
$form->addRow("checkbox", "Internship?" , "is_internship", $r["is_internship"]);
$form->addRow("textarea", "Description" , "description", $r["description"], "", true);
$form->addRow("submit"  , "update position");
$form->draw("<a href='positions.php' class='white'>Open Positions</a> &gt; Update Position");

echo drawBottom();?>