<?
include("include.php");

//$module_admin = false; //debugging

if ($posting) {
	//make checkboxes into bits
	$_POST["email"]				= strtolower($_POST["email"]);
	$_POST["phone"]				= format_phone($_POST["phone"]);
	$_POST["homePhone"]			= format_phone(@$_POST["homePhone"]);
	$_POST["homeCell"]			= format_phone(@$_POST["homeCell"]);
	$_POST["emerCont1Phone"]	= format_phone(@$_POST["emerCont1Phone"]);
	$_POST["emerCont1Cell"]		= format_phone(@$_POST["emerCont1Cell"]);
	$_POST["emerCont2Phone"]	= format_phone(@$_POST["emerCont2Phone"]);
	$_POST["emerCont2Cell"]		= format_phone(@$_POST["emerCont2Cell"]);
	
	format_post_nulls("corporationID,departmentID,officeID,rankID");
		
	if ($module_admin) {
		$email_address = $_POST["email"]; //db_enter is going to mess it up; i should fix that!
		$id = db_enter("users", "firstname nickname lastname title email rankID *startDate *endDate #corporationID #departmentID #officeID phone bio homeAddress1 homeAddress2 homeCity homeStateID homeZIP homePhone homeCell homeEmail emerCont1Name emerCont1Relationship emerCont1Phone emerCont1Cell emerCont1Email emerCont2Name emerCont2Relationship emerCont2Phone emerCont2Cell emerCont2Email", "user_id");
		
		//if new user, reset password, delete request, and send invite
		if (!isset($_GET["id"])) {
			//optional new staff alert message
			if (getOption("staff_alertnew")) emailAdmins("<a href='" . url_base() . "/staff/view.php?id=" . $id . "'>" . $_POST["firstname"] . " " . $_POST["lastname"] . "</a> was just added to the Seedco Intranet.", "Intranet: New Staff Added");

			//reset pass and delete request
			db_query("UPDATE users SET password = PWDENCRYPT('') WHERE user_id = " . $id);
			if (isset($_GET["requestID"])) db_query("DELETE FROM users_requests WHERE id = " . $_GET["requestID"]);

			//send invitation
			$name = str_replace("'", "", ($_POST["nickname"] == "NULL") ? $_POST["firstname"] : $_POST["nickname"]);
			email_invite($id, $email_address, $name);
		}
		
		//update permissions
		db_checkboxes("permissions", "users_to_modules", "user_id", "module_id", $id);

		//handle is_admin
		if ($_SESSION["is_admin"]) {
			format_post_bits("is_admin");
			db_query("UPDATE users SET is_admin = {$_POST["is_admin"]} WHERE user_id = " . $id);
		}
		
		//check long distance code
		if (($locale == "/_seedco/") && ($_POST["officeID"] == "1")) {
			if (!db_grab("SELECT longdistancecode FROM users WHERE user_id = " . $id)) {
				$code = db_grab("SELECT code FROM ldcodes WHERE code NOT IN ( SELECT longdistancecode FROM users WHERE is_active = 1 AND longdistancecode IS NOT NULL)");
				db_query("UPDATE users SET longDistanceCode = {$code} WHERE user_id = " . $id);
			}
		}
		
	} else {
		$id = db_enter("users", "firstname nickname lastname email title #corporationID departmentID officeID phone bio homeAddress1 homeAddress2 homeCity homeStateID homeZIP homePhone homeCell homeEmail emerCont1Name emerCont1Relationship emerCont1Phone emerCont1Cell emerCont1Email emerCont2Name emerCont2Relationship emerCont2Phone emerCont2Cell emerCont2Email", "user_id");
	}
	
	if ($id == $_SESSION["user_id"]) {
		$user = db_grab("SELECT u.updated_date, " . db_datediff("u.updated_date", "GETDATE()") . " update_days FROM users u WHERE u.user_id = " . $_SESSION["user_id"]);
		$_SESSION["updated_date"]	 = $user["updated_date"];
		$_SESSION["update_days"] = $user["update_days"];
	}

	if ($uploading) { 
		//upload new staff image, probably should insert this in above update statement
		//also need to ensure they're uploading a JPG
		file_image_resize($_FILES["userfile"]["tmp_name"], $locale . "staff/" . $id . ",jpg", 270);
		file_image_resize($_FILES["userfile"]["tmp_name"], $locale . "staff/" . $id . "-thumbnail,jpg", 40);
		unlink($_FILES["userfile"]["tmp_name"]);
		$image	= format_binary(file_get($locale . "staff/" . $id . ",jpg"));

		//add image to user	
		db_query("UPDATE users SET image = $image WHERE user_id = " . $id);
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
		u.is_admin,
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
		u.created_date,
		u.updated_date,
		u.startDate,
		u.endDate
		FROM users u
		WHERE u.user_id = " . $_GET["id"]);
		
	if (($_GET["id"] == $_SESSION["user_id"]) && ($_SESSION["update_days"] > 90)) {
		echo drawMessage(getString("staff_update"));
	} elseif (empty($_SESSION["updated_date"])) {
		echo drawMessage(getString("staff_firsttime"));
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
		u.is_admin,
		u.departmentID,
		u.created_date,
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
if (getOption("staff_showdept")) $form->addRow("department", "Department", "departmentID", "", @$r["departmentID"]);
if (getOption("staff_showoffice")) $form->addRow("select", "Location", "officeID", "SELECT id, name from intranet_offices order by name", @$r["officeID"], true);

$form->addRow("phone",  "Phone", "phone", @format_phone($r["phone"]), "", true, 14);
$form->addRow("textarea-plain", "Bio", "bio", @$r["bio"]);

if ($module_admin) { //some fields are admin-only (we don't want people editing the staff page on the website)
	$form->addGroup("Administrative Information [public, but not editable by staff]");
	if (getOption("staff_showrank")) $form->addRow("select", "Rank", "rankID", "SELECT id, description from intranet_ranks", @$r["rankID"], true);
	$form->addRow("date", "Start Date", "startDate", @$r["startDate"], "", false);
	$form->addRow("date", "End Date", "endDate", @$r["endDate"], "", false);
	if ($_SESSION["is_admin"]) $form->addCheckbox("is_admin", "Site Admin", @$r["is_admin"]);
	if (!@$r["is_admin"]) $form->addCheckboxes("permissions", "Permissions", "modules", "users_to_modules", "user_id", "module_id", @$_GET["id"]);
	$form->addRow("file", "Image", "userfile");
}

if (getOption("staff_showhome")) {
	$form->addGroup("Home Contact Information [private]");
	$form->addRow("itext", "Address 1", "homeAddress1", @$r["homeAddress1"], "", false);
	$form->addRow("itext", "Address 2", "homeAddress2", @$r["homeAddress2"], "", false);
	$form->addRow("itext", "City", "homeCity", @$r["homeCity"], "", false);
	$form->addRow("select", "State", "homeStateID", "SELECT stateID, stateName from intranet_us_states order by stateName", @$r["homeStateID"], false);
	$form->addRow("itext", "ZIP", "homeZIP", @$r["homeZIP"], "", false, 5);
	$form->addRow("itext", "Home Phone", "homePhone", @format_phone($r["homePhone"]), "", false, 14);
	$form->addRow("itext", "Cell Phone", "homeCell", @format_phone($r["homeCell"]), "", false, 14);
	$form->addRow("itext", "Personal Email", "homeEmail", @$r["homeEmail"], "", false);
}

if (getOption("staff_showemergency")) {
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
}

$form->addRow("submit",   "Save Changes");
if (isset($_GET["id"])) {
	$form->draw("Edit Staff Info");
} else {
	$form->draw("Add New Staff Member");
}
drawBottom();?>