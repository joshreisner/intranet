<?php
//session & env
session_start();
if (!isset($_SESSION["user_id"])) $_SESSION["user_id"] = false;
if (!isset($pageIsPublic)) $pageIsPublic = false;
	
//joshlib & localize
$_josh["write_folder"]	= "/_" . str_replace("www.", "", $_SERVER["HTTP_HOST"]);
$_josh["config"]		= $_josh["write_folder"] . "/config.php";
extract(joshlib());

//debug();

//apply security
if (!$pageIsPublic) {
	error_debug("page is not public");
	if (!$_SESSION["user_id"]) {
		error_debug("user_id session not set");
		if (!login(@$_COOKIE["last_login"], "", true)) {
			error_debug("couldn't log in with " . @$_COOKIE["last_login"] . ", redirecting");
			url_change("/?goto=" . urlencode($_josh["request"]["path_query"]));
		}
	} 
	
	//determine location & scenario
	error_debug("user is logged in, determining location & scenario");
	$page		= getPage();
	$location	= (($request["folder"] == "bb") || ($request["folder"] == "cal") || ($request["folder"] == "docs") || ($request["folder"] == "staff") || ($request["folder"] == "helpdesk") || ($request["folder"] == "contacts") || ($request["folder"] == "external-orgs") || ($request["folder"] == "press-clips")) ? $request["folder"] : "areas";

	//get modules info
	error_debug("getting modules");
	$result = db_query("SELECT 
			m.id,
			p.url,
			m.title,
			m.pallet,
			m.isPublic,
			(SELECT u.is_closed FROM users_to_modules u WHERE u.user_id = {$_SESSION["user_id"]} AND u.module_id = m.id) is_closed,
			(SELECT u.is_admin FROM users_to_modules u WHERE u.user_id = {$_SESSION["user_id"]} AND u.module_id = m.id) is_admin
		FROM modules m
		JOIN pages p ON p.id = m.homePageID
		WHERE m.is_active = 1
		ORDER BY m.precedence");

	$modules	= array();
	$areas		= array();

	while ($r = db_fetch($result)) {
		$modules[$r["id"]] = array(
			"id"		=> $r["id"],
			"title"		=> $r["title"],
			"url"		=> $r["url"],
			"isPublic"	=> $r["isPublic"],
			"pallet"	=> $r["pallet"],
			"is_closed"	=> $r["is_closed"],
			"is_admin"	=> $r["is_admin"]
		);
		if (!$r["pallet"]) $areas[$r["title"]] = $r["id"];
	}
	ksort($areas);
	
	//indicate admin privileges for the current module
	if (!$_SESSION["is_admin"]) {
		$module_admin = (isset($modules[$page["module_id"]])) ? $modules[$page["module_id"]]["is_admin"] : false;
	} else {
		$module_admin = true;
	}
	
	//check to see if user needs update ~ todo make this a preference
	error_debug("checking if user needs update");
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
		$modules[$page["module_id"]]["pallet"]		= false;
		$modules[$page["module_id"]]["isPublic"]	= false;
		$modules[$page["module_id"]]["pallet"]		= false;
		$modules[$page["module_id"]]["title"]		= getString("app_name");
		$modules[$page["module_id"]]["is_admin"]	= false;
	}

	//get helpdesk pallet info
	error_debug("getting helpdesk pallet info");
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
		if (db_grab("SELECT COUNT(*) FROM users_to_modules WHERE module_id = {$_GET["module"]} AND user_id = " . $_SESSION["user_id"])) {
			$closed = db_grab("SELECT is_closed FROM users_to_modules WHERE module_id = {$_GET["module"]} AND user_id = " . $_SESSION["user_id"]);
			db_query("UPDATE users_to_modules SET is_closed = " . abs($closed - 1) . " WHERE module_id = {$_GET["module"]} AND user_id = " . $_SESSION["user_id"]);
		} else {
			db_query("INSERT INTO users_to_modules ( user_id, module_id, is_closed ) VALUES ( {$_SESSION["user_id"]}, {$_GET["module"]}, 1 )");
		}
		url_query_drop("module");
	} elseif (url_action("help")) {
		$_SESSION["help"] = abs($_SESSION["help"] - 1);
		db_query("UPDATE users SET help = {$_SESSION["help"]} WHERE id = " . $_SESSION["user_id"]);
		url_query_drop("action");
	}
}

//done!
error_debug("done processing include!");
	
	
//custom functions & classes

function drawCheckboxText($chkname, $description) {
	return draw_container("span", $description, array("class"=>"clickme", "onclick"=>"javascript:toggleCheckbox('$chkname');"));
}

function drawDeleteColumn($prompt=false, $id=false, $action="delete", $adminOnly=true) {
	//if we're going to backend all the table stuff, then this should be incorporated somehow.  perhaps we will need to extend the class
	global $module_admin, $_josh;
	if ($adminOnly && !$module_admin) return false;
	if (!$id) return '<td width="16">&nbsp;</td>';
	return draw_tag("td", array("width"=>"16"), draw_img($_josh["write_folder"] . "/images/icons/delete.png", drawDeleteLink($prompt, $id, $action)));
}

function drawDeleteLink($prompt="Are you sure?", $id=false, $action="delete", $index="id") {
	global $_GET;
	if (!$id && isset($_GET[$index])) $id = $_GET[$index];
	$prompt = "'" . str_replace("'", '"', $prompt) . "'";
	return "javascript:url_prompt('" . url_query_add(array("action"=>$action, $index=>$id), false) . "', " . $prompt . ");";
}

function drawEmailFooter() {
	$string = getString("app_name");
	return '<div class="emailfooter">This message was generated by the <a href="' . url_base() . '/">' . $string . '</a>.</div></body></html>';
}

function drawEmailHeader() {
	return '<html><head>' . draw_css(file_get("/styles/screen.css")) . '</head><body class="email">';
}

function drawEmptyResult($text="None found.", $colspan=1) {
	//todo ~ obsolete.  tables should use joshlib's table class
	return draw_tag("tr", false, draw_tag("td", array("class"=>"empty", "colspan"=>$colspan), $text));
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
		$header .='<a  href="' . url_base() . '/' . $_josh["request"]["folder"] . '/">' . $modules[$page["module_id"]]["title"] . '</a>';
	}
	if ($name) {
		$header .=' &gt; ';
		if ($_josh["request"]["subfolder"]) $header .= '<a href="' . url_base() . '/' . $_josh["request"]["folder"] . '/' . $_josh["request"]["subfolder"] . '/">' . format_text_human($_josh["request"]["subfolder"]) . '</a> &gt; ';
		$header .= $name;
	}
	$header .= "</div>";
	if ($link2link && $link2text) $header .= '<a class="right" href="' . $link2link . '">' . $link2text . '</a>';
	if ($link1link && $link1text) $header .= '<a class="right" href="' . $link1link . '">' . $link1text . '</a>';
	$header .='</td></tr>';
	return $header;
}

function drawMessage($str, $align="left") {
	if (empty($str) || !format_html_text($str)) return false;
	return draw_container("div", $str, array("class"=>"message"));
}
						
function drawName($user_id, $name, $date=false, $withtime=false, $separator="<br>") {
	global $_josh;
	$base = url_base();
	$date = ($date) ? format_date_time($date, "", $separator) : false;
	$img  = draw_img($_josh["write_folder"] . "/staff/" . $user_id . "-small.jpg", $base . "/staff/view.php?id=" . $user_id);		
	verifyImage($user_id);
	return '
	<table cellpadding="0" cellspacing="0" border="0" width="144">
		<tr valign="top" style="background-color:transparent;">
			<td width="46" height="37" align="center">' . $img . '</td>
			<td><a href="' . $base . '/staff/view.php?id=' . $user_id . '">' . format_string($name, 20) . '</a><br>' . $date . '</td>
		</tr>
	</table>';
}

function drawNavigation() {
	global $_SESSION, $module_admin, $page, $location;
	if (!$page["module_id"]) return false;
	$pages	= array();
	$admin	= ($module_admin) ? "" : "AND is_admin = 0";
	$result	= db_query("SELECT name, url FROM pages WHERE module_id = {$page["module_id"]} {$admin} AND isInstancePage = 0 ORDER BY precedence");
	while ($r = db_fetch($result)) {
		//don't do navigation for helpdesk.  it needs to do it, since a message could go above
		if ($r["url"] != "/helpdesk/") $pages[$r["url"]] = $r["name"];
	}
	return drawNavigationRow($pages, $location);
}

function drawNavigationRow($pages, $module=false, $pq=false) {
	global $_josh, $location;
	if (!$module) $module = $location;
	$count = count($pages);
	if ($count < 2) return false;
	$return = '<table class="navigation ' . $module . '" cellspacing="1">
		<tr class="' . $module . '-hilite">';
	$cellwidth = round(100 / $count, 2);
	$match = ($pq) ? $_josh["request"]["path_query"] : $_josh["request"]["path"];
	//echo $match;  don't put url_base in match, if you can help it
	foreach ($pages as $url=>$name) {
		if (($url == $match) || ($url == url_base() . $match)) {
			$cell = ' class="selected">' . $name . '';
		} else {
			$cell = '><a href="' . $url . '">' . $name . '</a>';
		}
		$return .= '<td width="' . $cellwidth . '%"' . $cell . '</td>';
	}
	return $return . '</tr>
		</table>';
}
	
function drawSelectUser($name, $selectedID=false, $nullable=false, $length=0, $lname1st=false, $jumpy=false, $text="", $class=false) { 
	$result = db_query("SELECT u.id, ISNULL(u.nickname, u.firstname) first, u.lastname last FROM users u WHERE u.is_active = 1 ORDER by last, first");
	if ($jumpy) $jumpy = "location.href='/staff/view.php?id=' + this.value";
	$array = array();
	while ($r = db_fetch($result)) {
		$array[$r["id"]] = ($lname1st) ? $r["last"] . ", " . $r["first"] : $r["first"] . " " . $r["last"];
	}
	return draw_form_select($name, $array, $selectedID, !$nullable, $class, $jumpy);
}

function drawSyndicateLink($name) {
	global $_josh;
	return draw_rss_link($_josh["write_folder"] . '/syndicate/' . $name . '.xml');
}

function drawTableEnd() {
	//todo ~ obsolete.  tables should use joshlib's table class
	return '</table>';
}

function drawTableStart() {
	//todo ~ obsolete.  tables should use joshlib's table class
	return '<table cellspacing="1" class="left">';
}

function drawThreadComment($content, $user_id, $fullname, $date, $module_admin=false) {
	global $location;
	$return  = '<tr><td class="left">';
	$return .= drawName($user_id, $fullname, $date, true) . '</td>';
	$return .= '<td class="right text ';
	if ($module_admin) $return .= $location . "-hilite";
	$return .= '" height="80">' . $content . '</td></tr>';
	return $return;
}

function drawThreadCommentForm($showAdmin=false) {
	global $module_admin, $_josh, $_SESSION;
	$return = '
		<a name="bottom"></a>
		<form method="post" action="' . $_josh["request"]["path_query"] . '" onsubmit="javascript:return validate(this);">
		<tr valign="top">
			<td class="left">' . drawName($_SESSION["user_id"], $_SESSION["full_name"], false, true) . '</td>
			<td>' . draw_form_textarea("message", "", "mceEditor thread");
	if ($showAdmin && $module_admin) {
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

function drawTop() {
	global $_GET, $_SESSION, $_josh, $page, $module_admin, $location;
	error_debug("starting top");
	$title = $page["module"] . " > " . $page["name"];
?><html>
	<?
	echo draw_container("head",
		draw_container("title", $title) .
		'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . 
		draw_css_src("/styles/screen.css",	"screen") .
		draw_css_src("/styles/print.css",	"print") .
		draw_css_src("/styles/ie.css",		"ie") .
		draw_javascript_src($_josh["write_folder"] . "/tinymce/jscripts/tiny_mce/tiny_mce.js") .
		draw_javascript_src("/javascript.js") .
		draw_javascript_src() .
		draw_javascript("form_tinymce_init('/styles/tinymce.css');")
	);
	?>
	<body>
		<div id="container">
			<?=draw_div("banner", draw_img($_josh["write_folder"] . "/images/banner.png", $_SESSION["homepage"]))?>
			<div id="left">
				<div id="help">
				<a class="button left" href="<?=$_SESSION["homepage"]?>">Home</a>
				<a class="button right" href="<?=url_query_add(array("action"=>"help"), false)?>">Show Help</a>
			<? if ($_SESSION["help"]) {
				if ($_SESSION["is_admin"]) {?>
					<a class="button right" href="/admin/pages/?id=<?=$page["id"]?>">Edit Page Info</a>
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

//it's convention to put this right below drawTop()
function drawBottom() {
	global $_SESSION, $_GET, $_josh, $modules, $areas, $helpdeskOptions, $helpdeskStatus;
	?>
			</div>
			<div id="right">
				<div id="tools">
					<a class="right button" href="/index.php?action=logout">Log Out</a>
					Hello <a href="/staff/view.php?id=<?=$_SESSION["user_id"]?>"><b><?=$_SESSION["full_name"]?></b></a>.

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
		echo '<td width="50%"><a href="' . $l["url"] . '" target="new">' . $l["text"] . '</a></td>';
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
					<div class="head-left"><a href="<?=$m["url"]?>"><?=$m["title"]?></a></div>
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
		<div id="subfooter"></div>
	</body>
</html>
	<? db_close();
}

function emailAdmins($message, $subject, $colspan=1) {
	return emailUsers(db_array("SELECT email FROM users WHERE is_admin = 1 AND is_active = 1"), $subject, $message, $colspan);
}

function emailInvite($id, $email, $name) {
	global $_SESSION;
	$email = format_email($email);
	$message = '<tr><td class="text">
		Welcome ' . $name . '!  You can
		<a href="' . url_base() . '/login/password_reset.php?id=' . $id . '">log in to the ' . getString("app_name") . ' now</a>.  
		The system will prompt you to pick a password and update your contact information.
		<br><br>
		If you run into problems, please ask <a href="mailto:' . $_SESSION["email"] . '">' . $_SESSION["full_name"] . '</a> for help.
		</td></tr>';
	emailUser($email, getString("app_name") . " Login Information", $message);
}

function emailUser($address, $title, $content, $colspan=1, $message=false) {
	global $_josh, $_SESSION;

	//build message
	$message = drawEmailHeader() . 
		(($message) ? drawMessage($message) : "") . 
		drawTableStart() . 
		drawHeaderRow($title, $colspan) . 
		$content . 
		drawTableEnd() . 
		drawEmailFooter();
	
	//only send to me if developing
	if (($_josh["mode"] == "dev") && ($address != "josh@joshreisner.com")) return false;
	
	//send
	$result = email($address, $message, $title);
	
	//keep a record
	db_query("INSERT INTO emails ( address, subject, message, created_date, created_user, was_sent ) VALUES (
		'$address',
		'" . format_quotes($title) . "',
		'" . format_quotes($message) . "',
		GETDATE(),
		" . (($_SESSION["user_id"]) ? $_SESSION["user_id"] : "NULL") . ",
		" . format_boolean($result, "1|0") . "
		)");
	
	return $result;
}

function emailUsers($addresses, $title, $content, $colspan=1, $message=false) {
	if (!is_array($addresses)) $addresses = array($addresses);
	$addresses = array_unique($addresses);
	foreach($addresses as $a) emailUser($a, $title, $content, $colspan, $message);
	return true;
}

function getOption($key) {
	global $options;
	
	//default options.  override these in your config file by specifying $options variables
	$defaults["bb_notifyfollowup"]		= false;
	$defaults["bb_notifypost"]			= false;
	$defaults["bb_types"]				= false;
	
	$defaults["cal_showholidays"]		= true;
	
	$defaults["staff_alertnew"]			= false;
	$defaults["staff_alertdelete"]		= false;
	$defaults["staff_allowshared"]		= false;
	$defaults["staff_showdept"]			= true;
	$defaults["staff_showoffice"]		= true;
	$defaults["staff_showrank"]			= true;
	$defaults["staff_showhome"]			= true;
	$defaults["staff_showemergency"]	= true;
	
	if (!isset($options[$key])) return $defaults[$key];
	return $options[$key];
}

function getPage() {
	global $_josh;
	if ($return = db_grab("SELECT p.id, p.name, p.helpText, p.is_admin, p.isSecure, m.id module_id, m.title module FROM pages p LEFT JOIN modules m ON p.module_id = m.id WHERE p.url = '{$_josh["request"]["path"]}'")) {
		return $return;
	} else {
		error_debug("creating page");
		db_query("INSERT INTO pages ( url, name ) VALUES ( '{$_josh["request"]["path"]}', 'Untitled Page' )");
		return getPage();
	}
}

function getString($key) {
	global $strings;
	
	//default strings.  override these in your config file by specifying $strings variables
	$defaults["app_name"]			= 'Intranet';
	$defaults["app_welcome"]		= 'Welcome to the ' . $defaults["app_name"] . '.  If you don\'t have a login for this site or if you are having trouble, please use the links below:';
	$defaults["bb_admin"]			= 'This is an administrative announcement topic.';
	$defaults["staff_firsttime"]	= 'Welcome to the ' . $defaults["app_name"] . '!  Since this is your first time logging in, please make certain that your information here is correct, then click \'save changes\' at the bottom.';
	$defaults["staff_update"]		= 'Your personal info hasn\'t been updated in a while.  Please update this form and click Save at the bottom.  Your home and emergency contact information will remain private -- only senior staff will have access to it.';
	
	
	if (!isset($strings[$key])) return $defaults[$key];
	return $strings[$key];
}

class intranet_form {
	//todo ~ this is obsolete.  we should either use or extend the form class in joshlib
	var $rows, $js;
	
	function addUser($name="user_id", $desc="User", $default=false, $nullable=false, $admin=false) {
		global $rows, $location;
		$class = ($admin) ? "admin " . $location . "-hilite" : false;
		$rows .= draw_container("tr", 
			draw_container("td", $desc, array("class"=>"left")) . 
			draw_container("td", drawSelectUser($name, $default, $nullable), array("class"=>$class))
		);
	}
	
	function addCheckbox($name="", $desc="", $default=false, $additionalText="(check if yes)", $admin=false) {
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
		
		//special addition for permissions
		$where = ($name == "permissions") ? " AND l.is_admin = 1" : "";
		
		$rows .= '>
			<td class="left">' . $desc . '</td>
			<td>';
			if ($id) {
				$result = db_query("SELECT 
						t.id, 
						t.title,
						(SELECT COUNT(*) FROM $linking_table l WHERE l.$table_col = $id AND l.$link_col = t.id $where) checked
					FROM $table t
					WHERE t.is_active = 1
					ORDER BY t.title");
			} else {
				$result = db_query("SELECT id, title, 0 checked FROM $table WHERE is_active = 1 ORDER BY title");
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
						<td>' . drawCheckboxText($chkname, $r["title"]) . '</td>
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
		$rows .= draw_container("tr", 
			draw_container("td", $desc) . 
			draw_container("td", draw_form_select($name, $sql, $default, $nullable))
		);
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
		$rows .= draw_container("tr", draw_container("td", $text, array("colspan"=>2)), array("class"=>"group"));
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
										id, 
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
				if (!$required) $rows .= "<option></option>";
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
				$rows .= '<td>' . draw_form_textarea($name, $value, "mceEditor") . '</td>';
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
		echo draw_javascript("function validate(form) {
				var errors = new Array();
				" . $js . "
				return showErrors(errors);
			}");
		}?>
		<a name="bottom"></a>
		<table class="left" cellspacing="1">
			<tr>
				<td class="head <?=$location?>" colspan="2"><?=$pageTitle?></td>
			</tr>
			<form method="post" action="<?=$_josh["request"]["path_query"]?>" enctype="multipart/form-data" onsubmit="javascript:return validate(this);">
			<?
			if (isset($_josh["referrer"])) {
				echo draw_form_hidden("return_to", $_josh["referrer"]["url"]);
			}
			echo $rows;
			?>
			</form>
		</table>
		<?
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
		u.id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname,
		u.email,
		" . db_pwdcompare("", "u.password") . " password,
		u.departmentID,
		d.isHelpdesk,
		u.help,
		u.is_admin,
		u.updated_date,
		" . db_datediff("u.updated_date", "GETDATE()") . " update_days
	FROM users u
	LEFT JOIN departments d ON u.departmentID = d.departmentID
	WHERE u.email = '$username' AND u.is_active = 1" . $where)) {
		//login was good
		db_query("UPDATE users SET lastlogin = GETDATE() WHERE id = " . $user["id"]);
		$_SESSION["user_id"]		= $user["id"];
		$_SESSION["is_admin"]		= $user["is_admin"];
		$_SESSION["email"]			= $user["email"];
		$_SESSION["homepage"]		= "/bb/";
		$_SESSION["departmentID"]	= $user["departmentID"];
		$_SESSION["isHelpdesk"]		= $user["isHelpdesk"];
		$_SESSION["help"]			= $user["help"];
		$_SESSION["update_days"]	= $user["update_days"];
		$_SESSION["updated_date"]	= $user["updated_date"];
		$_SESSION["password"]		= $user["password"];
		$_SESSION["full_name"]		= $user["firstname"] . " " . $user["lastname"];
		
		cookie("last_login", $user["email"]);
		cookie("last_email", $user["email"]);
		
		return true;
	}
	$_SESSION["user_id"]		= false;
	return false;
}

function updateInstanceWords($id, $text) {
	global $ignored_words;
	$words = array_diff(split("[^[:alpha:]]+", strtolower(strip_tags($text))), $ignored_words);
	if (count($words)) {
		$text = implode("|", $words);
		db_query("index_intranet_instance $id, '$text'");
	}
}

function verifyImage($user_id) {
	//this doesn't appear to be very fast.
	global $_josh;
	$large	= $_josh["write_folder"] . "/staff/" . $user_id . "-large.jpg";
	$medium = $_josh["write_folder"] . "/staff/" . $user_id . "-medium.jpg";
	$small	= $_josh["write_folder"] . "/staff/" . $user_id . "-small.jpg";
	if (!file_is($large) || !file_is($medium) || !file_is($small)) {
		if ($image = db_grab("SELECT image FROM users WHERE id = " . $user_id)) {
			file_put($large, $image);
			file_put($medium, format_image_resize($image, 135));
			file_put($small, format_image_resize($image, 50));
		}
	}
}

//it's convention to always put this at the bottom
function joshlib() {
	global $_SERVER, $_josh, $strings, $options;
	$possibilities = array(
		"D:\Sites\joshlib\index.php", //seedco-web-srv
		"/home/hcfacc/www/joshlib/index.php", //icd 2
		"/Users/josh/Sites/joshlib/index.php",  //dora mac mini
		"/Users/joshreisner/Sites/joshlib/index.php", //macbook
		"/home/joshreisner/www/joshlib/joshlib/index.php" //icdsoft
	);
	foreach ($possibilities as $p) if (@include($p)) return $_josh;
	die("Can't locate library! " . $_SERVER["DOCUMENT_ROOT"]);
}
?>