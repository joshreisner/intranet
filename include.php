<?php
//session
session_start();
if (!isset($_SESSION["user_id"])) $_SESSION["user_id"] = false;
if (!isset($pageIsPublic)) $pageIsPublic = false;
	
//joshlib & localize
$locale = "/_" . str_replace("www.", "", $_SERVER["HTTP_HOST"]) . "/";
$_josh["config"] = $locale . "config.php";

@extract(includeLibrary()) or die("Can't locate library! " . $_SERVER["DOCUMENT_ROOT"]);
//debug();

//apply security
if (!$pageIsPublic) {
	if (!$_SESSION["user_id"] && !login(@$_COOKIE["last_login"], "", true)) url_change("/?goto=" . urlencode($_josh["request"]["path_query"]));

	//determine location & scenario
	$page		= getPage();
	$location	= $_josh["request"]["folder"];
	$uploading	= (isset($_FILES["userfile"]["tmp_name"]) && !empty($_FILES["userfile"]["tmp_name"])) ? true : false;

	//get modules info
	$result = db_query("SELECT 
			m.id,
			p.url,
			m.name,
			m.pallet,
			m.isPublic,
			u.is_closed,
			u.is_admin
		FROM modules m
		JOIN pages p ON p.id = m.homePageID
		LEFT JOIN users_to_modules u ON u.module_id = m.id
		WHERE m.is_active = 1 AND u.user_id = {$_SESSION["user_id"]}
		ORDER BY m.precedence");

	$modules	= array();
	$areas		= array();

	while ($r = db_fetch($result)) {
		$modules[$r["id"]] = array(
			"id"		=> $r["id"],
			"name"		=> $r["name"],
			"url"		=> $r["url"],
			"isPublic"	=> $r["isPublic"],
			"pallet"	=> $r["pallet"],
			"is_closed"	=> $r["is_closed"],
			"is_admin"	=> $r["is_admin"]
		);
		if (($r["name"] == "Admin") && $r["is_admin"]) $_SESSION["is_admin"] = true;
		if (!$r["pallet"]) $areas[$r["name"]] = $r["id"];
	}
	ksort($areas);
	
	//indicate admin privileges for the current module
	$is_admin = (isset($modules[$page["module_id"]])) ? $modules[$page["module_id"]]["is_admin"] : false;
	
	//check to see if user needs update
	//todo make this a preference
	if (($_SESSION["update_days"] > 90 || empty($_SESSION["updated_date"])) && ($_josh["request"]["path"] != "/staff/add_edit.php")) {
		error_debug("user needs address update");
		url_change("/staff/add_edit.php?id=" . $_SESSION["user_id"]);
	} elseif ($_SESSION["password"] && ($_josh["request"]["path"] != "/login/password_update.php") && ($_josh["request"]["path"] != "/staff/add_edit.php")) {
		error_debug("user needs password update");
		url_change("/login/password_update.php");
	}		

	//special pages that don't belong to a module still need info
	if (!isset($page["module_id"])) $page["module_id"] = 0;
	if (!isset($modules[$page["module_id"]])) {
		error_debug("unspecified module");
		$modules[$page["module_id"]]["pallet"]	= false;
		$modules[$page["module_id"]]["isPublic"]	= false;
		$modules[$page["module_id"]]["pallet"]	= false;
		$modules[$page["module_id"]]["name"]		= "Intranet";
		$modules[$page["module_id"]]["is_admin"]	= false;
	}

	//get helpdesk pallet info
	$helpdeskOptions = db_table("SELECT 
			d.departmentID id, 
			d.shortName name, 
			(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.departmentID = d.departmentID AND t.statusID <> 9) num_open
		FROM departments d
		WHERE isHelpdesk = 1
		ORDER BY d.shortName");
	$helpdeskStatus = db_grab("SELECT message FROM it_system_status");

	//handle side menu pref updates
	if (isset($_GET["module"])) {
		$closed = db_grab("SELECT is_closed FROM users_to_modules WHERE module_id = {$_GET["module"]} AND user_id = " . $_SESSION["user_id"]);
		if ($closed != "") {
			db_query("UPDATE users_to_modules SET is_closed = " . abs($closed - 1) . " WHERE module_id = {$_GET["module"]} AND user_id = " . $_SESSION["user_id"]);
		} else {
			db_query("INSERT INTO users_to_modules ( user_id, module_id, is_closed ) VALUES ( {$_SESSION["user_id"]}, {$_GET["module"]}, 1 )");
		}
		url_query_drop("module");
	} elseif (url_action("help")) {
		$_SESSION["help"] = abs($_SESSION["help"] - 1);
		db_query("UPDATE users SET help = {$_SESSION["help"]} WHERE user_id = " . $_SESSION["user_id"]);
		url_query_drop("action");
	}
}

//done!
error_debug("done processing include!");
	
	
//custom functions - miscellaneous
	function email_invite($id, $email, $name) {
		global $_SESSION;
		$email = format_email($email);
		$message = '<tr><td class="text">
			Welcome ' . $name . '!  You can
			<a href="' . url_base() . '/login/password_reset.php?id=' . $id . '">log in to the Intranet now</a>.  
			The system will prompt you to pick a password and update your contact information.
			<br><br>
			If you run into problems, please ask <a href="mailto:' . $_SESSION["email"] . '">' . $_SESSION["full_name"] . '</a> for help.
			</td></tr>';
		email_user($email, "Intranet Login Information", $message);
	}

	function email_user($address, $title, $content, $colspan=1) {
		global $_josh;
		
		$message = drawEmailHeader() . 
			drawTableStart() . 
			drawHeaderRow($title, $colspan) . 
			$content . 
			drawTableEnd() . 
			drawEmailFooter();
	
		$headers	 = "MIME-Version: 1.0\r\n";
		$headers	.= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers	.= "From: " . $_josh["email_default"] . "\r\n";
		if (!mail($address, $title, $message, $headers)) error_handle("Couldn't Send Email", "The message to " . $address . " was rejected by the mailserver for some reason", true);
	}
	
	function error_email($msg="Undefined error message") {
		global $_SESSION, $_josh;
		//if (isset($_josh["email_default"]) && ($_SESSION["user_id"] != 1)) {
		if (isset($_josh["email_default"]) && isset($_josh["email_admin"])) {
			if ($_SESSION["user_id"]) {
				if ($_josh["email_admin"] == $_SESSION["email"]) return;
				$msg = str_replace("<!--user-->", "<a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $_SESSION["user_id"] . "'>" . $_SESSION["full_name"] . "</a>", $msg);
			} else {
				$msg = str_replace("<!--user-->", "<i>User ID not set yet</i>", $msg);
			}
			email($_josh["email_admin"], $msg, "Error: " . $_josh["request"]["host"], $_josh["email_default"]);
		}
	}
	
    function login($username, $password, $skippass=false) {
    	global $_SESSION;
    	//need id, fullname, email departmentid, ishelpdesk, homepage, update_days, updated_on, first
		if ($skippass) {
			$where = "";
			error_debug("<b>login</b> running without password");
        } else {
			$where = " AND " . db_pwdcompare($password, "u.password") . " = 1";
			error_debug("<b>login</b> running with password");
        }

 		if ($user = db_grab("SELECT 
			u.user_id id,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname,
			u.email,
			" . db_pwdcompare("", "u.password") . " password,
			p.url homepage,
			u.departmentID,
			d.isHelpdesk,
			u.help,
			u.updated_date,
			" . db_datediff("u.updated_date", "GETDATE()") . " update_days
		FROM users u
		LEFT JOIN departments d ON u.departmentID = d.departmentID
		LEFT JOIN pages p				ON u.homePageID = p.id
		WHERE u.email = '$username' AND u.is_active = 1" . $where)) {
			//login was good
			db_query("UPDATE users SET lastlogin = GETDATE() WHERE user_id = " . $user["id"]);
			$_SESSION["user_id"]		= $user["id"];
			$_SESSION["email"]			= $user["email"];
			$_SESSION["homepage"]		= ($user["homepage"]) ? $user["homepage"] : "/bb/";
			$_SESSION["departmentID"]	= $user["departmentID"];
			$_SESSION["isHelpdesk"]		= $user["isHelpdesk"];
			$_SESSION["help"]			= $user["help"];
			$_SESSION["update_days"]	= $user["update_days"];
			$_SESSION["updated_date"]	= $user["updated_date"];
			$_SESSION["password"]		= $user["password"];
			$_SESSION["full_name"]		= $user["firstname"] . " " . $user["lastname"];
			$_SESSION["is_admin"]		= false;
			
			cookie("last_login", $user["email"]);
			cookie("last_email", $user["email"]);
			
			return true;
		}		
		return false;
	}
	
	function getPage() {
		global $_josh;
		if ($return = db_grab("SELECT p.id, p.name, p.helpText, p.is_admin, p.isSecure, m.id module_id, m.name module FROM pages p LEFT JOIN modules m ON p.module_id = m.id WHERE p.url = '{$_josh["request"]["path"]}'")) {
			return $return;
		} else {
			error_debug("creating page");
			db_query("INSERT INTO pages ( url, name ) VALUES ( '{$_josh["request"]["path"]}', 'Untitled Page' )");
			return getPage();
		}
	}
	
	function getString($key) {
		global $_josh, $strings, $locale;
		if (!isset($strings)) include($_josh["root"] . $locale . "strings.php");
		return $strings[$key];
	}
	
//post functions
	function getDocTypeID($filename) {
		$array = explode(".", $filename);
		return db_grab("SELECT id FROM docs_types WHERE extension = '" . array_pop($array) . "'");
	}

	function updateInstanceWords($id, $text) {
		global $ignored_words;
		$words = array_diff(split("[^[:alpha:]]+", strtolower(strip_tags($text))), $ignored_words);
		if (count($words)) {
			$text = implode("|", $words);
			db_query("index_intranet_instance $id, '$text'");
		}
	}

//delete functions
	function deleteLink($prompt=false, $id=false, $action="delete", $index="id") {
		global $_GET;
		if (!$id && isset($_GET["id"])) $id = $_GET[$index];
		$prompt = ($prompt) ? "'" . str_replace("'", '"', $prompt) . "'" : "false";
		return "javascript:url_prompt('" . url_query_add(array("action"=>$action, $index=>$id), false) . "', " . $prompt . ");";
	}
	
	function deleteColumn($prompt=false, $id=false, $action="delete", $adminOnly=true) {
		global $is_admin, $locale;
		if ($adminOnly && !$is_admin) return false;
		if (!$id) return '<td width="16">&nbsp;</td>';
		return '<td width="16">' . draw_img($locale . "images/icons/delete.gif", deleteLink($prompt, $id, $action)) . '</td>';
	}

	function drawCheckboxText($chkname, $description) {
		return '<span class="clickme" onclick="javascript:toggleCheckbox(\'' . $chkname . '\');">' . $description . '</span>';
	}

//rss functions (syndication)
	function drawSyndicateLink($name) {
		global $locale;
		return '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . $locale . 'syndicate/' . $name . '.xml">';
	}
	
	function syndicateBulletinBoard() {
		global $_josh, $locale;
		
		$items = array();
		
		$topics = db_query("SELECT 
				t.id,
				t.title,
				t.description,
				t.is_admin,
				t.threadDate,
				(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topicID AND f.is_active = 1) replies,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				u.email
			FROM bb_topics t
			JOIN users u ON u.user_id = t.created_user
			WHERE t.is_active = 1 
			ORDER BY t.threadDate DESC", 15);
		
		while ($t = db_fetch($topics)) {
			if ($t["is_admin"]) $t["title"] = "ADMIN: " . $t["title"];
			if ($t["replies"] == 1) {
				$t["title"] .= " (" . $t["replies"] . " comment)";
			} elseif ($t["replies"] > 1) {
				$t["title"] .= " (" . $t["replies"] . " comments)";
			}
			$items[] = array(
				"title" => $t["title"],
				"description" => $t["description"],
				"link" => url_base() . "/bb/topic.php?id=" . $t["id"],
				"date" => $t["threadDate"],
				"author" => $t["email"] . " (" . $t["firstname"] . " " . $t["lastname"] . ")"
			);
		}

		file_rss("Bulletin Board: Last 15 Topics", "http://" . $_josh["request"]["host"] . "/bb/", $items, $locale . "syndicate/bb.xml");
	}
	

//form class
	class intranet_form {
		var $rows, $js;
		
		function addUser($name="user_id", $desc="User", $default=0, $nullable=false, $admin=false) {
			global $rows, $location;
			$rows .= '<tr>
				<td class="left">' . $desc . '</td>
				<td';
			if ($admin) $rows .= ' class="admin ' . $location . '-hilite"';
			$rows .= '>' . drawSelectUser($name, $default, $nullable) . '</td>
			</tr>';
		}
		
		function addCheckbox($name="", $desc="", $default=0, $additionalText="(check if yes)", $admin=false) {
			global $rows, $location;
			$rows .= '<tr>
				<td class="left">' . $desc . '</td>
				<td';
			if ($admin) $rows .= ' class="admin ' . $location . '-hilite"';
			$rows .= '><table class="nospacing">
						<tr>
							<td>' . draw_form_checkbox($name, $default) . '</td>
							<td>' . drawCheckboxText($name, $additionalText) . '</td>
						</tr>
					</table>
				</td>
			</tr>';
		}
		
		function addCheckboxes($name, $desc, $table, $linking_table=false, $table_col=false, $link_col=false, $id=false, $admin=false) {
			global $rows;
			$rows .= '<tr';
			if ($admin) $rows .= ' class="admin"';
			
			//special exception for modules table
			$description = ($table == "modules") ? "name" : "description";
			
			$rows .= '>
				<td class="left">' . $desc . '</td>
				<td>';
				if ($id) {
					$result = db_query("SELECT 
							t.id, 
							t.$description description, 
							(SELECT COUNT(*) FROM $linking_table l WHERE l.$table_col = $id AND l.$link_col = t.id) checked
						FROM $table t
						WHERE t.is_active = 1
						ORDER BY t.$description");
				} else {
					$result = db_query("SELECT id, $description description, 0 checked FROM $table WHERE is_active = 1 ORDER BY $description");
				}
				if ($total = db_found($result)) {
					$counter = 0;
					$max = ceil($total / 3);
					$rows .= '<table class="nospacing" width="100%"><tr>';
					while ($r = db_fetch($result)) {
						if ($counter == 0) $rows .= '<td width="33%" style="vertical-align:top;"><table class="nospacing">';
						$chkname = "chk_" . $name . "_" . $r["id"];
						$rows .= '
							<tr>
							<td>' . draw_form_checkbox($chkname, $r["checked"]) . '</td>
							<td>' . drawCheckboxText($chkname, $r["description"]) . '</td>
							</tr>';
						if ($counter == ($max - 1)) {
							$rows .= '</table></td>';
							$counter = 0;
						} else {
							$counter++;
						}
					}
					if ($counter != 0) $rows .= '</table></td>';
					$rows .= '</tr></table>';
				}
				$rows .= '
				</td>
			</tr>';
		}
		
		function addSelect($name="", $desc="", $sql="", $default=0, $nullable=false, $bgcolor=false) {
			global $rows;
			$rows .= '
			<tr>
				<td>' . $desc . '</td>
				<td>' . draw_form_select($name, $sql, $default, $nullable) . '</td>
			</tr>';
		}
		
		function addJavascript($conditions, $message) {
			global $js;
			$js .= "
				if (" . $conditions . ") errors[errors.length] = '" . addslashes($message) . "';
			";
		}
		
		function addRaw($row) {
			global $rows;
			$rows .= $row;
		}
		
		function addGroup($text="") {
			global $rows;
			$rows .= '
				<tr class="group">
					<td colspan="2">' . $text . '</td>
				</tr>';
		}
			
		function addRow($type, $title, $name="", $value="", $default="", $required=false, $maxlength=50, $onchange=false) {
			global $rows, $js, $months, $month, $today, $year, $_josh;
			$textlength = ($maxlength > 50) ? 50 : $maxlength;
			$value = trim($value);
			if ($type == "raw") {
				$rows .= $title;
			} else {
				$rows .= '<tr>';
				if (($type != "button") && ($type != "submit") && ($type != "hidden") && ($type != "raw")) $rows .= '<td class="left">' . $title . '</td>';
				
				if ($type == "text") { //output text, no form element
					$rows .= '<td>' . $value . '</td>';
				} elseif ($type == "date") {
					$rows .= '<td>' . draw_form_date($name, $value, false, false, $required) . '</td>';
				} elseif ($type == "datetime") {
					$rows .= '<td>' . draw_form_date($name, $value, true) . '</td>';
				} elseif ($type == "checkbox") {
					$rows .= '<td>' . draw_form_checkbox($name, $value) . '</td>';
				} elseif ($type == "itext") {
					$rows .= '<td>' . draw_form_text($name, $value, false, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "phone") {
					$rows .= '<td>' . draw_form_text($name, $value, 14, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "extension") {
					$rows .= '<td>' . draw_form_text($name, $value, 4, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "password") {
					$rows .= '<td>' . draw_form_password($name, $value, $textlength, $maxlength) . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "select") {
					$rows .= '<td>';
					$rows .= draw_form_select($name, $value, $default, $required, false, $onchange);
					$rows .= '</td>';
				} elseif ($type == "user") {
					$result = db_query("SELECT 
											user_id, 
											ISNULL(nickname, firstname) first,
											lastname last 
										FROM users
										WHERE is_active = 1
										ORDER by lastname");
					while ($r = db_fetch($result)) {
						$options[$r["user_id"]] = $r["first"] . ", " . $r["last"];
					}
					$rows .= '<td>';
					$rows .= draw_form_select($name, $options, $default, $required, false, $onchange);
					$rows .= '</td>';
				} elseif ($type == "department") {
					$rows .= '<td><select name="' . $name . '">';
					$result = db_query("SELECT 
											departmentID,
											departmentName,
											quoteLevel
										FROM departments
										WHERE is_active = 1
										ORDER by precedence");
					while ($r = db_fetch($result)) {
						$rows .= '<option value="' . $r["departmentID"] . '"';
						if ($r["departmentID"] == $default) $rows .= ' selected';
						$rows .= '>';
						if ($r["quoteLevel"] == 2) {
							$rows .= "&nbsp;&#183;&nbsp;";
						} elseif ($r["quoteLevel"] == 3) {
							$rows .= "&nbsp;&nbsp;&nbsp;-&nbsp;";
						}
						$rows .= $r["departmentName"] . '</option>';
					}
					$rows .= '</select></td>';
				} elseif ($type == "userpic") {
					$rows .= '<td>' . drawName($name, $value, $default, true, " ") . '</td>';
				} elseif ($type == "textarea") {
					$rows .= '<td>' . draw_form_textarea($name, $value) . '</td>';
					$js .= " tinyMCE.triggerSave();" .  $_josh["newline"];
					if ($required) $js .= "if (!form." . $name . ".value.length || (form." . $name . ".value == '<p>&nbsp;</p>')) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "textarea-plain") {
					$rows .= '<td>' . draw_form_textarea($name, $value, "noMceEditor") . '</td>';
					if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . $_josh["newline"];
				} elseif ($type == "hidden") {
					$rows .= draw_form_hidden($name, $value);
				} elseif ($type == "submit") {
					$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_submit($title, "button") . '</td>';
				} elseif ($type == "button") {
					$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_button($title, $value, "button") . '</td>';
				} elseif ($type == "file") {
					$rows .= '<td>' . draw_form_file($name, "file", $onchange) . '</td>';
				}
				$rows .= '</tr>' . $_josh["newline"];
			}
		}	
		
		function draw($pageTitle) {
			global $rows, $_josh, $js, $location;
			if ($js) {
			?>
			<script language="javascript">
			<!--
				function validate(form) {
					var errors = new Array();
					<?=$js;?>
					return showErrors(errors);
				}
			//-->
			</script>
			<? }?>
			<a name="bottom"></a>
			<table class="left" cellspacing="1">
				<tr>
					<td class="head <?=$location?>" colspan="2"><?=$pageTitle?></td>
				</tr>
				<form method="post" action="<?=$_josh["request"]["path_query"]?>" enctype="multipart/form-data" onsubmit="javascript:return validate(this);">
				<?=$rows;?>
				</form>
			</table>
			<?
		}
	}

	function htmlwrap($str, $len=60) {
		$words = explode(" ", strip_tags($str));
		foreach ($words as $word) {
		  if (strlen($word) > $len) {
			  $parts = explode($word, $str);
			  if (count($parts) == 3) $str = $parts[0] . $word . $parts[1] . substr($word, 0, $len-3) . "..." . $parts[2];
		  }
		}
		return $str;
	}

	function db_enter($table, $fields, $index="id") {
		global $_POST, $_GET, $language, $_SESSION;
		
		$fields = explode(" ", $fields);
		foreach ($fields as $field) {
			if ($field == "password") { //binary password
				if (url_id()) {
					$query1[] = $field . " = PWDENCRYPT('" . $_POST[$field] . "')";
				} else {
					$query1[] = $field;
					$query2[] = $field . " = PWDENCRYPT('" . $_POST[$field] . "')";
				}
			} elseif (substr($field, 0, 1) == "#") { //numeric
				$field = substr($field, 1);
				if (empty($_POST[$field])) $_POST[$field] = "NULL";
				if (url_id()) {
					$query1[] = $field . " = " . $_POST[$field];
				} else {
					$query1[] = $field;
					$query2[] = $_POST[$field];
				}
			} elseif (substr($field, 0, 1) == "*") { //date
				$field = substr($field, 1);
				if (isset($_POST["no" . $field])) {
					if (url_id()) {
						$query1[] = $field . " = NULL";
					} else {
						$query1[] = $field;
						$query2[] = "NULL";
					}
				} else {
					if (url_id()) {
						$query1[] = $field . " = " . format_post_date($field);
					} else {
						$query1[] = $field;
						$query2[] = format_post_date($field);
					}
				}
			} elseif (substr($field, 0, 1) == "@") { //file
				$field = substr($field, 1);
				if (isset($_POST[$field])) { //file posting is optional, from a php point of view
					if (url_id()) {
						$query1[] = $field . " = " . format_binary($_POST[$field]);
					} else {
						$query1[] = $field;
						$query2[] = format_binary($_POST[$field]);
					}
				}
			} elseif (substr($field, 0, 1) == "|") { //html
				$field = substr($field, 1);
				if (isset($_POST[$field])) {
					if (url_id()) {
						$query1[] = $field . " = " . format_html($_POST[$field]);
					} else {
						$query1[] = $field;
						$query2[] = "'" . format_html($_POST[$field]) . "'";
					}
				}
			} else { //text
				$_POST[$field] = trim($_POST[$field]);
				$_POST[$field] = (empty($_POST[$field])) ? "NULL" : "'" . $_POST[$field] . "'";
				if (url_id()) {
					$query1[] = $field . " = " . $_POST[$field];
				} else {
					$query1[] = $field;
					$query2[] = $_POST[$field];
				}
			}
		}
		if (url_id()) {
			$query1[] = "updated_date = GETDATE()";
			if (isset($_POST["updated_user"])) {
				$query1[] = "updated_user = " . $_POST["updated_user"];
			} else {
				$query1[] = "updated_user = " . $_SESSION["user_id"];
			}
			db_query("UPDATE " . $table . " SET " . implode(", ", $query1) . " WHERE " . $index . " = " . $_GET["id"]);
			return $_GET["id"];
		} else {
			$query1[] = "created_date";
			$query2[] = "GETDATE()";
			$query1[] = "created_user";
			$query2[] = (isset($_POST["created_user"])) ? $_POST["created_user"] : $_SESSION["user_id"];
			$query1[] = "is_active";
			$query2[] = 1;
			$r = db_query("INSERT INTO " . $table . " ( " . implode(", ", $query1) . " ) VALUES ( " . implode(", ", $query2) . ")");
			return $r;
		}
	}
		
	function verifyImage($user_id) {
		global $_josh, $locale;
		if (!is_file($_josh["root"] . $locale . "staff/" . $user_id . ".jpg") || !is_file($_josh["root"] . $locale . "staff/" . $user_id . "-thumbnail.jpg")) {
			if ($image = db_grab("SELECT image FROM users WHERE user_id = " . $user_id)) {
				file_put($locale . "staff/" . $imageID . ".jpg", $image);
				file_image_resize($locale . "staff/" . $imageID . ".jpg", $locale . "staff/" . $imageID . "-thumbnail.jpg", 45);
			}
		}
	}

//custom functions - draw functions

	function drawTableStart() {
		return '<table cellspacing="1" class="left">';
	}
	
	function drawTableEnd() {
		return '</table>';
	}
	
	function drawEmptyResult($text="None found.", $colspan=1) {
		return '<tr><td class="empty" colspan="' . $colspan . '">' . $text . '</td></tr>';
	}
	
	function drawServerMessage($str, $align="left") {
		if (empty($str) || !format_html_text($str)) return false;
		$message  = '<table class="message">';
		$message .= '<tr><td class="yellow" align="' . $align . '">' . $str . '</td>';
		$message .= '</tr></table>';
		return $message;
	}
							
	function drawNavigation() {
		global $_SESSION, $is_admin, $page, $location;
		if (!$page["module_id"]) return false;
		$pages	= array();
		$admin	= ($is_admin) ? "" : "AND is_admin = 0";
		$result	= db_query("SELECT name, url FROM pages WHERE module_id = {$page["module_id"]} {$admin} AND isInstancePage = 0 ORDER BY precedence");
		while ($r = db_fetch($result)) {
			if ($r["url"] != "/helpdesk/") $pages[$r["url"]] = $r["name"];
		}
		$location = (($location == "bb") || ($location == "cal") || ($location == "docs") || ($location == "staff") || ($location == "helpdesk") || ($location == "contacts")) ? $location : "areas";
		return drawNavigationRow($pages, $location);
	}
	
	function drawNavigationRow($pages, $module="areas", $pq=false) {
		global $_josh;
		$count = count($pages);
		if ($count < 2) return false;
		$return = '<table class="navigation ' . $module . '" cellspacing="1">
			<tr class="' . $module . '-hilite">';
		$cellwidth = round(100 / $count, 2);
		$match = ($pq) ? $_josh["request"]["path_query"] : $_josh["request"]["path"];
		//echo $match;  don't put url_base in match, if you can help it
		foreach ($pages as $url=>$name) {
			if ($match == $url) {
				$cell = ' class="selected">' . $name . '';
			} else {
				$cell = '><a href="' . $url . '">' . $name . '</a>';
			}
			$return .= '<td width="' . $cellwidth . '%"' . $cell . '</td>';
		}
		return $return . '</tr>
			</table>';
	}
		
	function drawHeaderRow($name=false, $colspan=1, $link1text=false, $link1link=false, $link2text=false, $link2link=false) {
		global $_josh, $location, $modules, $page;
		error_debug("drawing header row");
		if (!$name) $name = $page["name"];
		//urls are absolute because it could be used in an email
		$header ='<tr>
				<td class="head ' . $location . '" colspan="' . $colspan . '">
					<div class="head-left">
					';
		if ($location != "login") {
			$header .='<a  href="http://' . $_josh["request"]["host"] . '/' . $_josh["request"]["folder"] . '/">' . $modules[$page["module_id"]]["name"] . '</a>';
		}
		if ($name) {
			$header .=' &gt; ';
			if ($_josh["request"]["subfolder"]) $header .= '<a href="http://' . $_josh["request"]["host"] . '/' . $_josh["request"]["folder"] . '/' . $_josh["request"]["subfolder"] . '/">' . format_text_human($_josh["request"]["subfolder"]) . '</a> &gt; ';
			$header .= $name;
		}
		$header .= "</div>";
		if ($link2link && $link2text) $header .= '<a class="right" href="' . $link2link . '">' . $link2text . '</a>';
		if ($link1link && $link1text) $header .= '<a class="right" href="' . $link1link . '">' . $link1text . '</a>';
		$header .='</td></tr>';
		return $header;
	}

	function drawName($user_id, $name, $date=false, $withtime=false, $separator="<br>") {
		global $locale;
		$base = url_base();
		$date = ($date) ? format_date_time($date, "", $separator) : false;
		$img  = draw_img($locale . "staff/" . $user_id . "-thumbnail.jpg", $base . "/staff/view.php?id=" . $user_id);		
		verifyImage($user_id);
		return '
		<table cellpadding="0" cellspacing="0" border="0" width="144">
			<tr valign="top" style="background-color:transparent;">
				<td width="46" height="37" align="center">' . $img . '</td>
				<td><a href="' . $base . '/staff/view.php?id=' . $user_id . '">' . format_string($name, 20) . '</a><br>' . $date . '</td>
			</tr>
		</table>';
	}
	
//custom functions - form functions

	function drawSelectUser($name, $selectedID=false, $nullable=false, $length=0, $lname1st=false, $jumpy=false, $text="", $class=false) { 
		$result = db_query("SELECT u.user_id, ISNULL(u.nickname, u.firstname) first, u.lastname last FROM users u WHERE u.is_active = 1 ORDER by last, first");
		if ($jumpy) $jumpy = "location.href='/staff/view.php?id=' + this.value";
		$array = array();
		while ($r = db_fetch($result)) {
			$array[$r["user_id"]] = ($lname1st) ? $r["last"] . ", " . $r["first"] : $r["first"] . " " . $r["last"];
		}
		return draw_form_select($name, $array, $selectedID, !$nullable, $class, $jumpy);
	}
	
	function drawThreadTop($title, $content, $user_id, $fullname, $date, $editurl=false) {
		global $_josh;
		$return  = '<tr>
				<td height="150" class="left">' . 
				drawName($user_id, $fullname, $date, true) . 
				'</td>
				<td class="text"><h1>' . $title . '</h1>';
		if ($editurl) {
			$return .= '<a class="right button floating" href="' . $editurl . '">edit this</a>';
		}
		$return .= '' . 
					str_replace('href="../', 'href="http://' . $_josh["request"]["host"] . '/', $content) . '
				</td>
			</tr>';
		return $return;	
	}
	
	function drawThreadComment($content, $user_id, $fullname, $date, $is_admin=false) {
		global $location;
		$return  = '<tr><td class="left">';
		$return .= drawName($user_id, $fullname, $date, true) . '</td>';
		$return .= '<td class="right text ';
		if ($is_admin) $return .= $location . "-hilite";
		$return .= '" height="80">' . $content . '</td></tr>';
		return $return;
	}
	
	function drawThreadCommentForm($showAdmin=false) {
		global $is_admin, $_josh, $_SESSION;
		$return = '
			<a name="bottom"></a>
			<form method="post" action="' . $_josh["request"]["path_query"] . '" onsubmit="javascript:return validate(this);">
			<tr valign="top">
				<td class="left">' . drawName($_SESSION["user_id"], $_SESSION["full_name"], $_SESSION["imageID"], $_SESSION["width"], $_SESSION["height"], false, true) . '</td>
				<td>' . draw_form_textarea("message", "", "mceEditor thread");
		if ($showAdmin && $is_admin) {
			$return .= '
				<table class="nospacing">
					<tr>
						<td width="16">' . draw_form_checkbox("is_admin") . '</td>
						<td>' . drawCheckboxText("is_admin", "This followup is admin-only (invisible to most users)") . '</td>
					</tr>
				</table>';
		}
		$return .= '
				</td>
			</tr>
			<tr>
				<td class="bottom" colspan="2">' . draw_form_submit("Update Conversation") . '</td>
			</tr>
			</form>';
		return $return;
	}

	function drawEmailHeader() {
		global $_josh, $locale;
		return '<html><head> 
		<style type="text/css">
		' . file_get("/styles/screen.css") . '
		</style>
		</head>
		<body class="email">';
	}
	
	function drawEmailFooter() {
		global $_josh;
		return '<div class="emailfooter">This message was generated by the <a href="http://' . $_josh["request"]["host"] . '/">Intranet</a>.</div>
		</body></html>';
	}
	
	function drawTop() {
		global $_GET, $_SESSION, $_josh, $page, $is_admin, $locale, $location;
		error_debug("starting top");
		$title = $page["module"] . " > " . $page["name"];
	?><html>
		<head>
			<title><?=$title?></title>
			<link rel="stylesheet" type="text/css" media="screen" href="/styles/screen.css" />
			<link rel="stylesheet" type="text/css" media="print" href="/styles/print.css" />
			<!--[if IE]><link rel="stylesheet" type="text/css" media="screen" href="/styles/ie.css" /><![endif]--> 
			<?
			echo draw_javascript_src("/javascript.js");
			echo draw_javascript_src($locale . "tinymce/jscripts/tiny_mce/tiny_mce.js");
			echo draw_javascript_src();
			echo draw_javascript("form_tinymce_init('/styles/tinymce.css');");
			?>
		</head>
		<body>
			<div id="container">
				<div id="banner"><?=draw_img($locale . "images/banner.png", $_SESSION["homepage"])?></div>
				<div id="left">
					<div id="help">
					<a class="button left" href="<?=$_SESSION["homepage"]?>">Home</a>
					<a class="button right" href="<?=url_query_add(array("action"=>"help"), false)?>">Show Help</a>
				<? if ($_SESSION["help"]) {
					if ($_SESSION["is_admin"]) {?>
						<a class="button right" href="/admin/pages/?id=<?=$page["id"]?>&returnTo=<?=urlencode($_josh["request"]["path_query"])?>">Edit Page Info</a>
					<? }?>
					<div class="text">
					<?
					echo ($page["helpText"]) ? $page["helpText"] : "No help is available for this page.";
					?>
					</div>
				<? }?>
					</div>
		<? 
		if ($location == "helpdesk") echo drawNavigationHelpdesk();
		echo drawNavigation();
		$_josh["drawn"]["top"] = true;
		error_debug("finished drawing top");
	}
			
	function drawBottom() {
		global $_SESSION, $_GET, $_josh, $modules, $areas, $locale, $helpdeskOptions, $helpdeskStatus;
		?>
				</div>
				<div id="right">
					<div id="tools">
						<a class="right button" href="/index.php?action=logout">Log Out</a>
						Hello <b><a href="/staff/view.php?id=<?=$_SESSION["user_id"]?>"><?=$_SESSION["full_name"]?></b></a>.

						<form name="search" method="get" action="/staff/search.php" onSubmit="javascript:return doSearch(this);">
			            <input type="text" name="q" value="Search Staff" onfocus="javascript:form_field_default(this, true, 'Search Staff');" onblur="javascript:form_field_default(this, false, 'Search Staff');">
						</form>
						
						<table class="links">
							<? if ($_SESSION["is_admin"]) {?><tr><td colspan="2" style="padding:6px 6px 0px 0px;"><a class="right button" href="/admin/links/">Edit Links</a></td></tr><? } ?>
		<?
		$side = "left";
		$links = db_query("SELECT url, text FROM links WHERE is_active = 1 ORDER BY precedence");
		while ($l = db_fetch($links)) {
			if ($side == "left") echo "<tr>";
			echo '<td width="50%"><a href="' . $l["url"] . '">' . $l["text"] . '</a></td>';
			if ($side == "right") echo "</tr>";
			$side = ($side == "left") ? "right" : "left";
		}
		?>
						</table>
					</div>
		<? 
			            
		foreach ($modules as $m) {
			if ($m["pallet"]) { ?>
			<table class="right" cellspacing="1">
				<tr>
					<td colspan="2" class="head <?=str_replace("/", "", $m["url"])?>">
						<div class="head-left"><a href="<?=$m["url"]?>"><?=$m["name"]?></a></div>
						<?=draw_img($m["url"] . "arrow-" . format_boolean($m["is_closed"], "up|down") . ".gif", url_query_add(array("module"=>$m["id"]), false))?>
					</td>
				</tr>
				<? if (!$m["is_closed"]) include($_josh["root"] . $m["pallet"]);?>
			</table>
			<? }
		}?>
				</div>
				<div id="footer">page rendered in <?=format_time_exec()?></div>
			</div>
		</body>
	</html>
		<? db_close();
	}

//include joshlib it's the convention to put this at the bottom
function includeLibrary() {
	global $_SERVER, $_josh;
	$possibilities = array(
		"D:\Sites\joshlib\\", //seedco-web-srv
		"/home/hcfacc/www/joshlib/", //icd 2
		"/Users/josh/Sites/joshlib/",  //dora mac mini
		"/Users/joshreisner/Sites/joshlib/", //macbook
		"/home/joshreisner/www/joshlib/joshlib/" //icdsoft
	);
	if ($_SERVER["HTTP_HOST"] == "dev-intranet.seedco.org") array_unshift($possibilities, "D:\Sites\joshlib-dev\\");
	foreach ($possibilities as $p) {
		if (@include($p . "index.php")) {
			$_josh["joshlib"] = $p;
			//echo $p . "<br>";
			return $_josh;
		}
	}
	return false;
}
?>