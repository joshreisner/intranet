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
	if (!isset($_POST["is_admin"])) $_POST["is_admin"] = 0;
	if (!isset($_POST["notify_topics"])) $_POST["notify_topics"] = 0;

	if ($uploading) {
		if ($content = file_get_uploaded("userfile")) {
			$_POST["image_large"] = format_image_resize($content, 270);
			$_POST["image_medium"] = format_image_resize($content, 135);
			$_POST["image_small"] = format_image_resize($content, 50);
		}
	}

	if ($module_admin) {
		$id = db_save("users");
		//die("hi");
		
		//if new user, reset password, delete request, and send invite
		if (!isset($_GET["id"])) {
			//optional new staff alert message
			if (getOption("staff_alertnew")) emailAdmins(drawEmptyResult("<a href='" . url_base() . "/staff/view.php?id=" . $id . "'>" . $_POST["firstname"] . " " . $_POST["lastname"] . "</a> was just added to the Seedco Intranet."), "Intranet: New Staff Added");

			//reset pass and delete request
			db_query("UPDATE users SET password = PWDENCRYPT('') WHERE id = " . $id);
			if (isset($_GET["requestID"])) db_query("DELETE FROM users_requests WHERE id = " . $_GET["requestID"]);

			//send invitation
			$name = str_replace("'", "", ($_POST["nickname"] == "NULL") ? $_POST["firstname"] : $_POST["nickname"]);
			emailInvite($id, $_POST["email"], $name);
		}
		
		//update permissions
		db_query("UPDATE users_to_modules SET is_admin = 0 WHERE user_id = " . $id);
		foreach ($_POST as $key => $value) {
			@list($control, $field_name, $module_id) = explode("_", $key);
			if (($control == "chk") && ($field_name == "permissions")) {
				//set admin flag
				if (db_grab("SELECT COUNT(*) FROM users_to_modules WHERE module_id = $module_id AND user_id = " . $id)) {
					db_query("UPDATE users_to_modules SET is_admin = 1 WHERE module_id = $module_id AND user_id = " . $id);
				} else {
					db_query("INSERT INTO users_to_modules ( module_id, user_id, is_admin ) VALUES ( $module_id, $id, 1 )");
				}
			}
		}

		//channels
		if (getOption("channels")) db_checkboxes("channels", "users_to_channels", "user_id", "channel_id", $id);
		
		//check long distance code
		if (($_josh["write_folder"] == "/_intranet.seedco.org") && ($_POST["officeID"] == "1")) {
			if (!db_grab("SELECT longdistancecode FROM users WHERE id = " . $id)) {
				$code = db_grab("SELECT code FROM ldcodes WHERE code NOT IN ( SELECT longdistancecode FROM users WHERE is_active = 1 AND longdistancecode IS NOT NULL)");
				db_query("UPDATE users SET longDistanceCode = {$code} WHERE id = " . $id);
			}
		}
	} else {
		$id = db_save("users");
	}
	
	if ($id == $_SESSION["user_id"]) {
		$user = db_grab("SELECT u.updated_date, " . db_datediff("u.updated_date", "GETDATE()") . " update_days FROM users u WHERE u.id = " . $_SESSION["user_id"]);
		$_SESSION["updated_date"]	 = $user["updated_date"];
		$_SESSION["update_days"] = $user["update_days"];
	}

	//overwrite images
	file_dynamic('users', 'image_large', $id, 'jpg');
	file_dynamic('users', 'image_medium', $id, 'jpg');
	file_dynamic('users', 'image_small', $id, 'jpg');
	
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
		u.organization_id,
		u.departmentID,
		u.is_admin,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		u.notify_topics,
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
		WHERE u.id = " . $_GET["id"]);
		
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
		u.organization_id,
		u.departmentID,
		u.created_date,
		GETDATE() startDate
		FROM users_requests u 
		WHERE id = " . $_GET["requestID"]);
} else {
	$r["startDate"] = db_grab("SELECT GETDATE()");
}

//set default rank
if (!isset($r["rankID"])) $r["rankID"] = db_grab("SELECT id FROM intranet_ranks WHERE isDefault = 1");
if (!isset($r["notify_topics"])) $r["notify_topics"] = 1;

//this should be an $option
$isRequired = (isset($_GET["id"]) && ($_GET["id"] == $_SESSION["user_id"]) && ($_josh["write_folder"] == "/_intranet.seedco.org"));

$form = new intranet_form;
$form->addGroup("Public Information");
$form->addRow("itext",  "First Name", "firstname", @$r["firstname"], "", true, 50);
$form->addRow("itext",  "Nickname", "nickname", @$r["nickname"], "", false, 50);
$form->addRow("itext",  "Last Name", "lastname", @$r["lastname"], "", true, 50);
$form->addRow("itext",  "Email", "email", @$r["email"], "", true, 50);

$form->addRow("itext",  "Title", "title", @$r["title"], "", false, 100);
$form->addRow("select", "Organization", "organization_id", "SELECT id, title from organizations ORDER BY title", @$r["organization_id"], false);
if (getOption("staff_showdept")) $form->addRow("department", "Department", "departmentID", "", @$r["departmentID"]);
if (getOption("staff_showoffice")) $form->addRow("select", "Location", "officeID", "SELECT id, name from offices order by name", @$r["officeID"], true);

$form->addRow("phone",  "Phone", "phone", @format_phone($r["phone"]), "", true, 50);
$form->addRow("textarea", "Bio", "bio", @$r["bio"]);

if ($module_admin) { //some fields are admin-only (we don't want people editing the staff page on the website)
	$form->addGroup("Administrative Information");
	if (getOption("bb_notifypost")) $form->addCheckbox("notify_topics", "Notify Topics", @$r["notify_topics"]);
	if (getOption("channels")) $form->addCheckboxes("channels", "Networks", "channels", "users_to_channels", "user_id", "channel_id", $_GET["id"]);
	if (getOption("staff_showrank")) $form->addRow("select", "Rank", "rankID", "SELECT id, description from intranet_ranks", @$r["rankID"], true);
	$form->addRow("date", "Start Date", "startDate", @$r["startDate"], "", false);
	$form->addRow("date", "End Date", "endDate", @$r["endDate"], "", false);
	if ($_SESSION["is_admin"]) $form->addCheckbox("is_admin", "Site Admin", @$r["is_admin"]);
	if (!@$r["is_admin"]) {
		$form->addCheckboxes("permissions", "Permissions", "modules", "users_to_modules", "user_id", "module_id", @$_GET["id"]);
	}
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