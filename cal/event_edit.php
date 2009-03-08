<?
include("include.php");

if ($posting) {
	$id = db_enter("cal_events", "title description *startDate typeID");
	url_change("./event.php?id=" . $_GET["id"]);
}


$e = db_grab("SELECT 
		e.title, 
		e.description, 
		e.startDate, 
		e.typeID,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.created_user,
		e.created_date,
		MONTH(e.startDate) month, 
		YEAR(e.startDate) year,
		u.imageID,
		i.width imgwidth,
		i.height imgheight
	FROM cal_events e
	JOIN users u ON e.created_user = u.user_id
	LEFT JOIN intranet_images i ON u.imageID = i.imageID
	WHERE e.id = " . $_GET["id"]);
	
drawTop();
echo drawNavigationCal($e["month"], $e["year"], true);


$form = new intranet_form;
if ($is_admin) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], $e["created_user"], true);
$form->addRow("itext",  "Title" , "title", $e["title"], "", true);
$form->addRow("select", "Type", "typeID", "SELECT id, description FROM cal_events_types ORDER BY description", $e["typeID"], true);
$form->addRow("datetime", "Date", "startDate", $e["startDate"]);
$form->addRow("textarea", "Notes" , "description", $e["description"], "", true);
$form->addRow("submit"  , "save changes");
$form->draw("Edit Event");

drawBottom();?>