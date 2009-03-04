<?
include("include.php");

//$isAdmin = false; //debugging

if ($posting) {
	//make checkboxes into bits
	$_POST["email"]				= strtolower($_POST["email"]);
	$_POST["phone"]				= format_phone($_POST["phone"]);
	$_POST["homePhone"]			= format_phone($_POST["homePhone"]);
	$_POST["homeCell"]			= format_phone($_POST["homeCell"]);
	$_POST["emerCont1Phone"]	= format_phone($_POST["emerCont1Phone"]);
	$_POST["emerCont1Cell"]		= format_phone($_POST["emerCont1Cell"]);
	$_POST["emerCont2Phone"]	= format_phone($_POST["emerCont2Phone"]);
	$_POST["emerCont2Cell"]		= format_phone($_POST["emerCont2Cell"]);
	
	if ($isAdmin) {
		$email_address = $_POST["email"]; //db_enter is going to mess it up; i should fix that!
		$id = db_enter("intranet_users", "firstname nickname lastname title email rankID *startDate *endDate #corporationID #departmentID #officeID phone bio homeAddress1 homeAddress2 homeCity homeStateID homeZIP homePhone homeCell homeEmail emerCont1Name emerCont1Relationship emerCont1Phone emerCont1Cell emerCont1Email emerCont2Name emerCont2Relationship emerCont2Phone emerCont2Cell emerCont2Email", "userID");
		
		//if new user, reset password, delete request, and send invite
		if (!isset($_GET["id"])) {
			if ($locale == "/_seedco/") {
				email("jreisner@seedco.org,pchoi@seedco.org", 
					"<a href='http://intranet.seedco.org/staff/view.php?id=" . $id . "'>" . $_POST["firstname"] . " " . $_POST["lastname"] . "</a> was just added to the Seedco Intranet.", 
					"Intranet: New Staff Added");
			}
			db_query("UPDATE intranet_users SET password = PWDENCRYPT('') WHERE userID = " . $id);
			if (isset($_GET["requestID"])) db_query("DELETE FROM users_requests WHERE id = " . $_GET["requestID"]);
			//send invitation
			$name = str_replace("'", "", ($_POST["nickname"] == "NULL") ? $_POST["firstname"] : $_POST["nickname"]);
			email_invite($id, $email_address, $name);
		}
		//update permissions
		db_checkboxes("permissions", "administrators", "userID", "moduleID", $id);

		//check long distance code
		if (($locale == "/_seedco/") && ($_POST["officeID"] == "1")) {
			if (!db_grab("SELECT longdistancecode FROM intranet_users WHERE userID = " . $id)) {
				$code = db_grab("SELECT code FROM ldcodes WHERE code NOT IN ( SELECT longdistancecode FROM intranet_users WHERE isActive = 1 AND longdistancecode IS NOT NULL)");
				db_query("UPDATE intranet_users SET longDistanceCode = {$code} WHERE userID = " . $id);
			}
		}
	} else {
		$id = db_enter("intranet_users", "firstname nickname lastname email title #corporationID departmentID officeID phone bio homeAddress1 homeAddress2 homeCity homeStateID homeZIP homePhone homeCell homeEmail emerCont1Name emerCont1Relationship emerCont1Phone emerCont1Cell emerCont1Email emerCont2Name emerCont2Relationship emerCont2Phone emerCont2Cell emerCont2Email", "userID");
	}
	
	if ($uploading) { //upload new staff image
		//debug();
		//get file type id, size, read file
		$type	= getDocTypeID($_FILES["userfile"]["name"]);
		$size	= getimagesize($_FILES["userfile"]["tmp_name"]);
		$image	= format_binary(file_get($_FILES["userfile"]["tmp_name"]));
		unlink($_FILES["userfile"]["tmp_name"]);
		//die($image);
		//insert into images table
		$imageID = db_query("INSERT into intranet_images (
				image,
				width,
				height,
				docTypeID			
			) VALUES (
				" . $image . ", 
				" . $size[0] . ", 
				" . $size[1] . ",
				" . $type . "
			)");
	
		//add imageID to user	
		db_query("UPDATE intranet_users SET imageID = $imageID WHERE userID = " . $id);
	}

	url_change("view.php?id=" . $id);
}

drawTop();

if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
		u.firstname,
		u.nickname,
		u.lastname,
		u.title, 
		u.email,  
		u.bio, 
		u.phone, 
		u.rankID,
		u.lastlogin,
		u.officeID, 
		u.corporationID,
		u.departmentID,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		u.homeStateID,
		u.homeZIP,
		u.homePhone,
		u.homeCell,
		u.homeEmail,
		u.emerCont1Name,
		u.emerCont1Relationship,
		u.emerCont1Phone,
		u.emerCont1Cell,
		u.emerCont1Email,
		u.emerCont2Name,
		u.emerCont2Relationship,
		u.emerCont2Phone,
		u.emerCont2Cell,
		u.emerCont2Email,
		u.createdOn,
		u.updatedOn,
		u.startDate,
		u.endDate
		FROM intranet_users u
		WHERE u.userID = " . $_GET["id"]);
		
	if (($_GET["id"] == $_SESSION["user_id"]) && ($_SESSION["update_days"] > 90)) {
		echo drawServerMessage("Your personal info hasn't been updated in a while.  Please update this form and click Save at the bottom.  Your home and emergency contact information will remain private -- only senior staff (and their assistants) have access to it.");
	} elseif (empty($_SESSION["updatedOn"])) {
		echo drawServerMessage("Welcome to the Intranet!  Since this is your first time logging in, please make certain that the staff information here is correct, then click 'save changes' at the bottom.  (The emergency and home info is private and optional.)");
	}
} elseif (isset($_GET["requestID"])) {
	$r = db_grab("SELECT 
		u.firstname,
		u.nickname,
		u.lastname,
		u.title, 
		u.email,  
		u.bio, 
		u.phone, 
		u.officeID, 
		u.corporationID,
		u.departmentID,
		u.createdOn,
		GETDATE() startDate
		FROM users_requests u WHERE id = " . $_GET["requestID"]);
} else {
	$r["startDate"] = db_grab("SELECT GETDATE()");
}

//set default rank
if (!isset($r["rankID"])) $r["rankID"] = db_grab("SELECT id FROM intranet_ranks WHERE isDefault = 1");

$isRequired = (isset($_GET["id"]) && ($_GET["id"] == $_SESSION["user_id"]) && ($locale == "/_seedco/"));

$form = new intranet_form;
$form->addGroup("Public Information");
$form->addRow("itext",  "First Name", "firstname", @$r["firstname"], "", true, 50);
$form->addRow("itext",  "Nickname", "nickname", @$r["nickname"], "", false, 50);
$form->addRow("itext",  "Last Name", "lastname", @$r["lastname"], "", true, 50);
$form->addRow("itext",  "Email", "email", @$r["email"], "", true, 50);

$form->addRow("itext",  "Title", "title", @$r["title"], "", false, 100);
$form->addRow("select", "Organization", "corporationID", "SELECT id, description FROM organizations ORDER BY description", @$r["corporationID"], false);
$form->addRow("department", "Department", "departmentID", "", @$r["departmentID"]);
$form->addRow("select", "Location", "officeID", "SELECT id, name from intranet_offices order by name", @$r["officeID"], true);
$form->addRow("phone",  "Phone", "phone", @format_phone($r["phone"]), "", true, 14);
$form->addRow("textarea-plain", "Bio", "bio", @$r["bio"]);

if ($isAdmin) { //some fields are admin-only (we don't want people editing the staff page on the website)
	$form->addGroup("Administrative Information [public, but not editable by staff]");
	$form->addRow("select", "Rank", "rankID", "SELECT id, description from intranet_ranks", @$r["rankID"], true);
	$form->addRow("date", "Start Date", "startDate", @$r["startDate"], "", false);
	$form->addRow("date", "End Date", "endDate", @$r["endDate"], "", false);
	$form->addCheckboxes("permissions", "Permissions", "modules", "administrators", "userID", "moduleID", @$_GET["id"]);
	$form->addRow("file", "Image", "userfile");
}

$form->addGroup("Home Contact Information [private]");
$form->addRow("itext", "Address 1", "homeAddress1", @$r["homeAddress1"], "", false);
$form->addRow("itext", "Address 2", "homeAddress2", @$r["homeAddress2"], "", false);
$form->addRow("itext", "City", "homeCity", @$r["homeCity"], "", false);
$form->addRow("select", "State", "homeStateID", "SELECT stateID, stateName from intranet_us_states order by stateName", @$r["homeStateID"], false);
$form->addRow("itext", "ZIP", "homeZIP", @$r["homeZIP"], "", false, 5);
$form->addRow("itext", "Home Phone", "homePhone", @format_phone($r["homePhone"]), "", false, 14);
$form->addRow("itext", "Cell Phone", "homeCell", @format_phone($r["homeCell"]), "", false, 14);
$form->addRow("itext", "Personal Email", "homeEmail", @$r["homeEmail"], "", false);

$form->addGroup("First Emergency Contact [private]");
$form->addRow("itext", "Name", "emerCont1Name", @$r["emerCont1Name"], "", false);
$form->addRow("itext", "Relationship", "emerCont1Relationship", @$r["emerCont1Relationship"], "", false);
$form->addRow("itext", "Phone", "emerCont1Phone", @format_phone($r["emerCont1Phone"]), "", false, 14);
$form->addRow("itext", "Cell", "emerCont1Cell", @format_phone($r["emerCont1Cell"]), "", false, 14);
$form->addRow("itext", "Email", "emerCont1Email", @$r["emerCont1Email"], "", false);

$form->addGroup("Second Emergency Contact [private]");
$form->addRow("itext", "Name", "emerCont2Name", @$r["emerCont2Name"], "", false);
$form->addRow("itext", "Relationship", "emerCont2Relationship", @$r["emerCont2Relationship"], "", false);
$form->addRow("itext", "Phone", "emerCont2Phone", @format_phone($r["emerCont2Phone"]), "", false, 14);
$form->addRow("itext", "Cell", "emerCont2Cell", @format_phone($r["emerCont2Cell"]), "", false, 14);
$form->addRow("itext", "Email", "emerCont2Email", @$r["emerCont2Email"], "", false);

$form->addRow("submit",   "Save Changes");
if (isset($_GET["id"])) {
	$form->draw("Edit Staff Info");
} else {
	$form->draw("Add New Staff Member");
}
drawBottom();?>