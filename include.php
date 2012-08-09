<?php
//start session, set defaults
session_start();
if (!isset($_SESSION['user_id']))		$_SESSION['user_id']		= false;
if (!isset($_SESSION['channel_id']))	$_SESSION['channel_id']		= false;
if (!isset($_SESSION['language_id']))	$_SESSION['language_id']	= 1;

//joshlib
extract(joshlib());

$_josh['tinymce_mode'] = 'simple';

//debug();

//include options file if it exists
include_once(DIRECTORY_ROOT . '/strings.php');
@include_once(DIRECTORY_ROOT . DIRECTORY_WRITE . '/strings.php');
@include_once(DIRECTORY_ROOT . DIRECTORY_WRITE . '/options.php');

//set language code
if (getOption('languages')) {
	if (!isset($_SESSION['language']))		$_SESSION['language'] = db_grab('SELECT code FROM languages WHERE id = ' . $_SESSION['language_id']);
	
	//language overwrites eg dates
	if ($_SESSION['language'] == 'es') {
		setlocale(LC_TIME, 'es_ES.utf8');
		$_josh['date']['strings'] = array('Ayer', 'Hoy', 'Mañana');
		$_josh['days']		= array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
		$_josh['months']	= array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
		$_josh['mos']		= array('ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jun', 'ago', 'sep', 'oct', 'nov', 'dic');
	} elseif ($_SESSION['language'] == 'fr') {
		setlocale(LC_TIME, 'fr_FR.utf8');
		$_josh['date']['strings'] = array('Hier', 'Aujourd\'hui', 'Demain');
		$_josh['days']		= array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
		$_josh['months']	= array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
		$_josh['mos']		= array('jan', 'fév', 'mar', 'avr', 'mai', 'jui', 'jul', 'aoû', 'sep', 'oct', 'nov', 'déc');
	} elseif ($_SESSION['language'] == 'ru') {
		setlocale(LC_TIME, 'ru_RU.utf8');
		$_josh['date']['strings'] = array('Вчера', 'Сегодня', 'Завтра');
		$_josh['days']		= array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');
		$_josh['months']	= array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
		$_josh['mos']		= array('янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');
	}
	
	if (isset($_GET['language_id'])) {
		$_SESSION['language_id'] = $_GET['language_id'];
		$_SESSION['language'] = db_grab('SELECT code FROM languages WHERE id = ' . $_GET['language_id']);
		if (user()) db_query('UPDATE users SET language_id = ' . $_GET['language_id'] . ' WHERE id = ' . user());
		url_drop('language_id');
	}
} else {
	$_SESSION['language'] = 'en';
}

//debug();

//apply security
if (!isset($pageIsPublic) || !$pageIsPublic) {
	//page is not public
	if (!user()) {
		error_debug('user_id session not set', __file__, __line__);
		if (!login(@$_COOKIE['last_login'], '', true)) {
			error_debug('could not log in with ' . @$_COOKIE['last_login'] . ', redirecting', __file__, __line__);
			url_change('/?goto=' . urlencode($_josh['request']['path_query']));
		}
	} 
	
	//determine location & scenario
	error_debug('user is logged in, determining location & scenario', __file__, __line__);

	$user = db_grab('SELECT id, help FROM users WHERE id = ' . $_SESSION['user_id']);

	//get modules info
	error_debug('getting modules', __file__, __line__);
	$modules = db_table('SELECT 
			m.id,
			m.title' . langExt() . ' title,
			m.folder,
			m.color,
			m.hilite,
			(SELECT COUNT(*) FROM users_to_modules_closed u WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND u.module_id = m.id) is_closed,
			(SELECT COUNT(*) FROM users_to_modules u WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND u.is_admin = 1 AND u.module_id = m.id) is_admin
		FROM modules m
		WHERE m.is_active = 1
		ORDER BY m.precedence');
		
	//get sub-list of modulettes
	$modulettes = db_table('SELECT m.id, m.title' . langExt() . ' title, m.folder, m.is_public, (SELECT COUNT(*) FROM users_to_modulettes u2m WHERE m.id = u2m.modulette_id AND u2m.user_id = ' . $_SESSION['user_id'] . ') is_admin FROM modulettes m WHERE m.is_active = 1 ORDER BY m.title' . langExt());

	//get page
	$page = array('id'=>false, 'title'=>'Untitled Page', 'module_id'=>false, 'modulette_id'=>false, 'color'=>'666', 'hilite'=>'eee', 'helptext'=>'<p>No description has been written yet for this page.</a>');
	if ($request['folder']) {
		//in a folder, look for module
		foreach ($modules as $m) {
			//override module admin privileges if user is site admin
			if ($_SESSION['is_admin']) $m['is_admin'] = true;		
		
			//start breadcrumbs and title, set module_id, is_admin
			if ($request['folder'] == $m['folder']) {
				$page['breadcrumbs'] = draw_link('/' . $request['folder'] . '/', $m['title'], false, 'breadcrumb') . ' &gt; ';
				$page['module_id'] = $m['id'];
				$page['is_admin'] = $m['is_admin'];
				$page['color'] = $m['color'];
				$page['hilite'] = $m['hilite'];
			}
			
			//get helpdesk info, because we should here
			if ($m['folder'] == 'helpdesk') {
				//helpdesk is activated
				error_debug('getting helpdesk pallet info', __file__, __line__);
				$helpdeskOptions = db_table('SELECT 
						d.departmentID id, 
						d.shortName name, 
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.departmentID = d.departmentID AND t.statusID <> 9) num_open
					FROM departments d
					WHERE isHelpdesk = 1
					ORDER BY d.shortName');
				$helpdeskStatus = db_grab('SELECT message FROM it_system_status');
			}			
		}
		
		if (($request['folder'] == 'a') && $request['subfolder']) {
			foreach ($modulettes as $m) {
				//override modulette admin privileges if user is site admin
				if ($_SESSION['is_admin']) $m['is_admin'] = true;		

				if ($request['subfolder'] == $m['folder']) {
					$page['breadcrumbs'] .= draw_link(url_base() . '/' . $request['folder'] . '/' . $request['subfolder'] . '/', $m['title']) . ' &gt; ';
					$page['modulette_id'] = $m['id'];
					$page['is_admin'] = $m['is_admin']; //overriding module privilege with that of modulette
				}
			}
		}
		
		//get actual page from database now, just need the title
		if ($page['modulette_id'] && $page['module_id']) {
			$m = db_grab('SELECT id, title' . langExt() . ' title, description' . langExt() . ' description FROM pages WHERE url = "' . $request['page'] . '" AND module_id = ' . $page['module_id'] . ' AND modulette_id = ' . $page['modulette_id']);
		} elseif ($page['module_id']) {
			$m = db_grab('SELECT id, title' . langExt() . ' title, description' . langExt() . ' description FROM pages WHERE url = "' . $request['page'] . '" AND module_id = ' . $page['module_id']);
		} else {
			$m = db_grab('SELECT id, title' . langExt() . ' title, description' . langExt() . ' description FROM pages WHERE url = "' . $request['page'] . '" AND module_id IS NULL');
			//error_handle('Something is wrong!', 'Page is not set for ' . $request['url']);
		}
		
		if ($m) {
			$page['id'] = $m['id'];
			$page['title'] = $m['title'];
			$page['helptext'] = $m['description'];
		} else {
			$page['title'] = 'Untitled Page';
		}
	}
		
	//check to see if user needs update ~ todo make this a site preference
	error_debug('checking if user needs update', __file__, __line__);
	if ($_SESSION['password']) {
		error_debug('user needs password update', __file__, __line__);
	 	if ($_josh['request']['path'] != '/login/password_update.php') url_change('/login/password_update.php');
	} elseif (($_SESSION['update_days'] > 90) || empty($_SESSION['updated_date'])) {
		error_debug('user needs address update', __file__, __line__);
		if ($_josh['request']['path'] != '/staff/add_edit.php') url_change('/staff/add_edit.php?id=' . $_SESSION['user_id']);
	}		

	//handle side menu pref updates
	error_debug('handle side menu pref updates', __file__, __line__);
	if (isset($_GET['module'])) {
		//todo ajax
		if (db_grab('SELECT COUNT(*) FROM users_to_modules_closed WHERE module_id = ' . $_GET['module'] . ' AND user_id = ' . $_SESSION['user_id'])) {
			db_query('DELETE FROM users_to_modules_closed WHERE module_id = ' . $_GET['module'] . ' AND user_id = ' . $_SESSION['user_id']);
		} else {
			db_query('INSERT INTO users_to_modules_closed ( module_id, user_id ) VALUES ( ' . $_GET['module'] . ', ' . $_SESSION['user_id'] . ' )');
		}
		url_query_drop('module');
	} elseif(isset($_GET['channel_id'])) {
		$_SESSION['channel_id'] = (empty($_GET['channel_id'])) ? false : $_GET['channel_id'];
		url_drop('channel_id');
	}
}

//obsolete functions
error_debug('include obsolete.php', __file__, __line__);
include(DIRECTORY_ROOT . '/obsolete.php');

//done!
error_debug('done processing include!', __file__, __line__);
	
//draw functions
function drawColumnDelete($id) {
	return draw_img('/images/icons/delete.png', 'javascript:confirmDelete(' . $id . ');');
}

function drawEmail($message) {
	//parse out a regular screen and make it look good in email
	
	$module = db_grab('SELECT color, hilite FROM modules WHERE folder = "' . url_folder() . '"');
	
	$message = str_replace('<div class="message">', '<div style="
		border:1px solid #ccc;
		background-color:#ffffee;
		padding:16px;
		line-height:18px;
		margin:0px 0px 14px 0px;
		font-size:13px;
		width:auto;
		">', $message);
		
	$message = str_replace('<div class="display thread">', '<div style="
		border:1px solid #ccc;
		border-bottom:0;
		margin-bottom:15px; position:relative;
		">', $message);
		
	$message = str_replace('<div class="title">', '<div style="
		border-bottom:1px solid #ccc; padding-left:4px; line-height:24px; color:#fff; background-image:url(' . url_base() . '/images/gradient.png); background-repeat:repeat-x;
		background-color:#' . $module['color'] . ';
		">', $message);
	
	$message = str_replace('<div class="row', '<div style="
		background-color:#eee; overflow:auto; border-bottom:1px solid #ccc;
		" class="', $message);
	
	$message = str_replace('<div class="label">', '<div style="
		padding:5px; float:left; width:135px;
		">', $message);
	
	$message = str_replace('<div class="content">', '<div style="
		background-color:#fff; float:right; width:420px; padding:10px; border-left:1px solid #e6e6e6; min-height:90px;
		">', $message);
	
	$message = str_replace('<h1>', '<h1 style="
		font-size:18px; 
		line-height:22px;
		font-weight:normal;
		margin:0px;
		">', $message);
	
	$message = str_replace('<a href="/', '<a style="
		color:#0000ff;
		cursor:pointer;
		outline:none;
		" href="' . url_base() . '/', $message);
	
	$message = str_replace('<a class="breadcrumb" href="/', '<a style="
		color:#fff;
		" href="' . url_base() . '/', $message);
	
	$message = str_replace('<a class="image" href="/', '<a style="
		float:left;
		margin-right:2px;
		border:0;
		" href="' . url_base() . '/', $message);
	
	$message = str_replace(' src="/', ' style="
		" src="' . url_base() . '/', $message);
	
	$message = str_replace(NEWLINE, '', $message);
	$message = str_replace("\t", '', $message);
	
	return '<html>
		<body style="font-family:Verdana, sans-serif; color:#444; font-size:12px; text-align:center;">
			<div style="margin:16px auto; width:594px; text-align:left;">
				' . $message . '
				<div style="color:#aaa;font-size:11px;text-align:right;">This message was generated by the ' . draw_link(url_base() . '/', getString("app_name"), false, array('style'=>'color:#888')) . '.</div>
			</div>
		</body>
	</html>';
}

function drawHeader($options=false, $title=false) {
	//get the page for the header
	global $_josh, $page, $modules, $modulettes;
	$return = draw_div_class('left', $page['breadcrumbs'] . (($title) ? $title : $page['title']));
	if ($options) foreach ($options as $url=>$name) $return .= draw_link($url, $name, false, array('class'=>'right'));
	return $return;
}

function drawMessage($str) {
	if (empty($str) || !format_html_text($str)) return false;
	return draw_div_class('message', $str);
}

function drawName($user_id, $name, $date=false, $withtime=false, $separator='<br/>', $updated=false) {
	return draw_div_class('name', 
		draw_img(file_dynamic('users', 'image_small', $user_id, 'jpg', $updated), '/staff/view.php?id=' . $user_id) . 
		draw_link('/staff/view.php?id=' . $user_id, format_string($name, 20)) . 
		BR . 
		(($date) ? format_date_time($date, '', ' ' . $separator) : '')
	);
}

function drawNavigation($pages=false, $match='path') {
	global $_josh, $page;
	
	if (!$pages) {
		if (!$page['module_id']) return false; //not in module
		$pages		= array();
		$admin		= ($page['is_admin']) ? ' ' : ' AND is_admin = 0 ';
		$modulette	= (empty($page['modulette_id'])) ? ' AND p.modulette_id IS NULL ' : ' AND p.modulette_id = ' . $page['modulette_id'];
		$result	= db_query('SELECT p.id, p.title' . langExt() . ' title, p.url, m.folder, m2.folder modulette FROM pages p JOIN modules m ON p.module_id = m.id LEFT JOIN modulettes m2 ON p.modulette_id = m2.id WHERE m.id = ' . $page['module_id'] . $modulette . $admin . ' AND p.is_hidden = 0 ORDER BY p.precedence');
		while ($r = db_fetch($result)) {
			//don't do navigation for helpdesk.  it needs to do it, since a message could go above -- todo fix this
			if ($r['url'] != '/helpdesk/') $pages['/' . $r['folder'] . '/' . (empty($r['modulette']) ? '' : $r['modulette'] . '/') . $r['url']] = $r['title'];
		}
	}

	$count = count($pages);
	if ($count < 2) return false;
	$cellwidth = round(100 / $count, 2);
	$cells = array();
	foreach ($pages as $url=>$name) {
		$cell = ($url == $_josh['request'][$match]) ? ' class="selected">' . $name : '><a href="' . $url . '">' . $name . '</a>';
		$cells[] = '<td width="' . $cellwidth . '%"' . $cell . '</td>';
	}
	return '<table class="navigation" cellspacing="1"><tr>' . implode('', $cells) . '</tr></table>';
}

function drawPanel($str) {
	if (empty($str) || !format_html_text($str)) return false;
	return draw_div_class('panel', $str);
}

function drawSelectUser($name, $selectedID=false, $nullable=false, $length=0, $lname1st=false, $jumpy=false, $text='', $class=false) { 
	global $_SESSION;
	if (getOption('channels') && $_SESSION['channel_id']) {
		$result = db_query('SELECT u.id, ISNULL(u.nickname, u.firstname) first, u.lastname last FROM users u JOIN users_to_channels u2c ON u.id = u2c.user_id WHERE u.is_active = 1 AND u2c.channel_id = ' . $_SESSION['channel_id'] . ' ORDER by last, first');
	} else {
		$result = db_query('SELECT u.id, ISNULL(u.nickname, u.firstname) first, u.lastname last FROM users u WHERE u.is_active = 1 ORDER by last, first');
	}
	if ($jumpy) $jumpy = 'location.href=\'/staff/view.php?id=\' + this.value';
	$array = array();
	while ($r = db_fetch($result)) {
		$array[$r['id']] = ($lname1st) ? $r['last'] . ', ' . $r['first'] : $r['first'] . ' ' . $r['last'];
	}
	return draw_form_select($name, $array, $selectedID, !$nullable, $class, $jumpy, '', 36);
}

function drawSimpleTop($title=false) {
	//leave title empty for emails
	$return = url_header_utf8() . draw_doctype() . '
		<head>' . 
			draw_meta_utf8() . 
			draw_favicon(DIRECTORY_WRITE . '/favicon.png') .
			draw_css(file_get('/styles/simple.css'));
	if ($title) {
		$return .= draw_container('title', $title);
		$return .= draw_javascript_src();
		$return .= draw_javascript_src('/javascript.js');
	}
	$return .= '</head>
		<body class="s">';
	return $return;
}

function drawSimpleBottom($email=false) {
	//if it's an email, send true
	$return = '';
	if ($email) $return .= draw_div_class('email_footer', getString('email_footer') . draw_link(url_base(), getString('app_name')));
	$return .= '</body></html>';
}

function drawStaffList($where, $errmsg=false, $options=false, $listtitle=false, $searchterms=false) {
	global $page, $_josh;
	
	if (!$errmsg) $errmsg = getString('results_empty');
	
	//only show delete for admins on pages that aren't the chagnes page
	$showDelete = ($page['is_admin'] && ($page['id'] != 35));
		
	$t = new table('staff', drawHeader($options, $listtitle));
	$t->set_column('pic', 'c', '&nbsp;', 50);
	$t->set_column('name', 'l', getString('name') . ((getOption('staff_showoffice') ? ' / ' . getString('location') : '')));
	$t->set_column('title', 'l', getString('staff_title') . ' / ' . ((getOption('staff_showdept') ? getString('department') : getString('organization'))), 222);
	$t->set_column('phone', 'l', getString('telephone'));
	if ($showDelete) $t->set_column('del', 'c', '&nbsp;', 16);
	
	$result = db_table('SELECT DISTINCT
							u.id, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone,
							c.title' . langExt() . ' organization,
							u.organization_id,
							o.name office, 
							o.isMain,
							u.title, 
							d.departmentName department
						FROM users u
						LEFT JOIN users_to_channels u2c ON u.id = u2c.user_id
						LEFT JOIN departments d	ON d.departmentID = u.departmentID 
						LEFT JOIN organizations c ON u.organization_id = c.id
						LEFT JOIN offices o ON o.id = u.officeID
						' . getChannelsWhere('users', 'u', 'user_id') . ' AND ' . $where . '
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)');
	
	foreach ($result as &$r) {
		$link = '/staff/view.php?id=' . $r['id'];
		$r['pic'] = draw_img(file_dynamic('users', 'image_small', $r['id'], 'jpg'), $link);
		$r['name'] = draw_link($link, $r['lastname'] . ', ' . $r['firstname']);
		if (getOption('staff_showoffice')) $r['name'] .= '<br/>' . $r['office'];
		if (getOption('staff_showdept')) {
			$r['title'] .= '<br/>' . $r['department'];
		} else {
			$r['title'] .= '<br/>' . draw_link('organizations.php?id=' . $r['organization_id'], format_string($r['organization']));
		}
		if ($showDelete) $r['del'] = drawColumnDelete($r['id']);
	}
	
	return $t->draw($result, $errmsg);
}

function drawSyndicateLink($name) {
	return draw_rss_link(DIRECTORY_WRITE . '/rss/' . $name . '.xml');
}

function drawTableEnd() {
	//not obsolete!  calendar and other nonstandard tables
	return '</table>';
}

function drawTableStart() {
	//not obsolete!  calendar and other nonstandard tables
	return '<table cellspacing="1" class="left">';
}

function drawThreadComment($content, $user_id, $fullname, $date, $admin=false, $updated=false) {
	$return  = '<tr><td class="left">';
	$return .= drawName($user_id, $fullname, $date, true, ' ', $updated) . '</td>';
	$return .= '<td class="right text ';
	if ($admin) $return .= ' hilite';
	$return .= '" height="80"><div class="text">' . $content . '</div></td></tr>';
	return $return;
}

function drawThreadCommentForm($showAdmin=false) {
	global $page, $_josh, $_SESSION;
	$name = ($_josh['request']['folder'] == 'bb') ? 'description' : 'message';
	
	$return = '<a name="bottom"></a><form ';
	if ($_josh['db']['language'] == 'mysql') $return .= 'accept-charset="utf-8" ';
	$return .= 'method="post" action="' . $_josh['request']['path_query'] . '" onsubmit="javascript:return validate(this);"><tr valign="top">
			<td class="left">' . drawName($_SESSION['user_id'], $_SESSION['full_name'], false, true, false) . '</td>
			<td>' . draw_form_textarea($name . langExt(), '', 'mceEditor thread');
	if ($showAdmin && $page['is_admin']) {
		$return .= '
			<table class="nospacing">
				<tr>
					<td width="16">' . draw_form_checkbox('is_admin') . '</td>
					<td>' . drawCheckboxText('is_admin', 'This followup is admin-only (invisible to most users)') . '</td>
				</tr>
			</table>';
	}
	$return .= '
			</td>
		</tr>
		<tr>
			<td class="bottom" colspan="2">' . draw_form_submit(getString('add_followup')) . '</td>
		</tr>
		</form>';
	return $return;
}

function drawThreadTop($title, $content, $user_id, $fullname, $date, $editurl=false) {
	global $_josh;
	$return  = '<tr>
			<td height="150" class="left">' . 
			drawName($user_id, $fullname, $date, true, false) . 
			'</td>
			<td class="text"><div class="text top"><h1>' . $title . '</h1>';
	if ($editurl) {
		$return .= '<a class="right button floating" href="' . $editurl . '">edit this</a>';
	}
	$return .= str_replace('href="../', 'href="http://' . $_josh['request']['host'] . '/', $content) . '
			</div></td>
		</tr>';
	return $return;	
}

function drawTop($headcontent=false) {
	global $_josh, $page, $user;

	ob_start('browser_output');
	error_debug('starting top', __file__, __line__);
	if ($_josh['db']['language'] == 'mysql') url_header_utf8();
	
	if (empty($page['helptext'])) $page['helptext'] = getString('help_empty');
	
	$return = draw_doctype() . 
		draw_container('head',
			(($_josh['db']['language'] == 'mysql') ? draw_meta_utf8() : '') .
			draw_container('title', $page['title']) .
			draw_favicon(DIRECTORY_WRITE . '/favicon.png') .
			draw_css_src('/css/global.css',	'screen') .
			draw_css_src('/css/print.css',	'print') .
			draw_css_src('/css/ie.css',		'ie') .
			draw_css_src(DIRECTORY_WRITE . '/screen.css', 'screen') .
			lib_get('jquery') .
			draw_javascript_src() .
			draw_javascript_src('/js/global.js') .
			draw_css('
				#left table.left td.head, #left div.display div.title { background-color:#' . $page['color'] . '; }
				#left table.table th.table_title, #left form fieldset legend span, #left table.navigation { background-color:#' . $page['color'] . '; }
				#left table.navigation tr, #left form fieldset div.admin, #left table td.hilite { background-color:#' . $page['hilite'] . '; }
			') . 
			draw_javascript('
			function confirmDelete(id) {
				if (confirm("' . getString('are_you_sure') . '")) {
					var newloc = "' . url_query_add(array('action'=>'delete', 'delete_id'=>'replaceme'), false) . '";
					location.href = newloc.replace("replaceme", id);
				}
			}
			function changeDept(id, user_id) {
				location.href="' . $_josh['request']['path_query'] . '&newDeptID=" + id + "&contactID=" + user_id;
			}
			') . 
			//draw_firebug() .
			drawSyndicateLink('bb') . 
			$headcontent
		);
	$return .= draw_body_open() . '
		<div id="container">
			' . draw_div('banner', draw_img(DIRECTORY_WRITE . '/banner' . langExt() . '.png', $_SESSION['homepage'])) . '
			<div id="left">
				<div id="help">
					<div id="help_buttons">' .
					draw_link($_SESSION['homepage'], getString('home'), false, 'left') . 
					draw_link('javascript:helpShow(' . user() . ')', getString('help_show'), false, array('class'=>'right' . ($user['help'] ? ' hidden' : false), 'id'=>'show_help_btn')) . 
					draw_link('javascript:helpHide(' . user() . ')', getString('help_hide'), false, array('class'=>'right' . (!$user['help'] ? ' hidden' : false), 'id'=>'hide_help_btn'));
					
	if ($_SESSION['is_admin']) {
		if ($page['id']) {
			$return .= draw_link('/a/admin/page.php?id=' . $page['id'], getString('page_edit_info'), false, 'right');
		} else {
			$return .= draw_link('/a/admin/page.php?module_id=' . $page['module_id'] . '&modulette_id=' . $page['modulette_id'] . '&url=' . urlencode($_josh['request']['page']), 'Create Page Here', false, 'right');
		}
	}
	$return .= '</div><div id="help_text"';
	if (!$user['help']) $return .= ' class="hidden"';
	$return .= '>' . $page['helptext'] . '</div></div>';

	if ($_josh['request']['folder'] == 'helpdesk') $return .= drawNavigationHelpdesk();
	$return .= drawNavigation();
	$_josh['drawn']['top'] = true;
	error_debug('finished drawing top', __file__, __line__);
	return $return;
}

//it's convention to put this right below drawTop()
function drawBottom() {
	global $_SESSION, $_GET, $_josh, $modules, $helpdeskOptions, $helpdeskStatus, $modulettes, $page;
	$return = '
			</div>
			<div id="right">
				<div id="tools">
					<a class="right button" href="/index.php?action=logout">' . getString('log_out') . '</a>
					' . getString('hello') . ' <a href="/staff/view.php?id=' . $_SESSION['user_id'] . '"><b>' . $_SESSION['full_name'] . '</b></a>';
	//search
	$return .= '<form name="search" accept-charset="utf-8" method="get" action="/staff/search.php" onsubmit="javascript:return doSearch(this);">
			<input type="text" name="q" placeholder="' . getString('staff_search') . '"/>
		</form>';

	//channel or language selectors
	if (getOption('channels')) $return .= draw_form_select('channel_id', 'SELECT id, title' . langExt() . ' title FROM channels WHERE is_active = 1 ORDER BY precedence', $_SESSION['channel_id'], false, 'channels', 'url_query_set(\'channel_id\', this.value)', getString('networks_view_all'));
	if (getOption('languages')) $return .= draw_form_select('language_id', 'SELECT id, title FROM languages ORDER BY title', $_SESSION['language_id'], true, 'languages', 'url_query_set(\'language_id\', this.value)');

	//links
	$links = db_table('SELECT title' . langExt() . ' title, url FROM links WHERE is_active = 1 ORDER BY precedence');
	foreach ($links as &$l) $l = draw_link($l['url'], $l['title'], true);
	$return .= draw_div('#links', draw_container('h3', getString('links')) . (admin() ? draw_link('/a/admin/links.php', getString('edit'), false, array('class'=>'right button')) : false) . draw_list($links));
	
	$return .= '</div>';
	
	foreach ($modules as $m) {
		$return .= '
		<table class="right ' . $m['folder'] . '" cellspacing="1">
			<tr>
				<td colspan="2" class="head" style="background-color:#' . $m['color'] . ';">
					<a href="/' . $m['folder'] . '/" class="left">' . $m['title'] . '</a>
					' . draw_img('/images/arrows-new/' . format_boolean($m['is_closed'], 'up|down') . '.png', url_query_add(array('module'=>$m['id']), false)) . '
				</td>
			</tr>';
			if (!$m['is_closed']) include(DIRECTORY_ROOT . DIRECTORY_SEPARATOR . $m['folder'] . DIRECTORY_SEPARATOR . 'pallet.php');
		$return .= '</table>';
	}
	$return .= '</div>
	<div id="footer">';
	
	//if (admin()) $return .= 'page rendered in ' . format_time_exec() . '<br/>';
	$return .= getString('copyright') . '<br/>';
	if (getOption('legal')) $return .= draw_link('/login/legal.php', getString('legal_title'));
	
	$return .= '</div></div>
		<div id="subfooter"></div>
	</body>
</html>';

	//record pageview
	if ($page['id'] && user()) db_query('INSERT INTO pages_views ( page_id, user_id, timestamp ) VALUES ( ' . $page['id'] . ', ' . user('NULL') . ', GETDATE() )');
	
	return $return;
}

//email functions
function emailAdmins($message, $subject) {
	return emailUser(db_array('SELECT email FROM users WHERE is_admin = 1 AND is_active = 1'), $subject, $message);
}

function emailInvite($id) {
	$user = db_grab("SELECT u.nickname, u.email, u.firstname, l.code language FROM users u JOIN languages l ON u.language_id = l.id WHERE u.id = " . $id);
	$name = (!$user["nickname"]) ? $user["firstname"] : $user["nickname"];
	$message = getString('email_invite_message', $user['language']);
	$message = str_replace('%LINK%', url_base() . '/login/password_reset.php?id=' . $id, $message);
	$message = str_replace('%NAME%', $name, $message);
	emailUser($user['email'], getString('email_invite_subject', $user['language']), $message);
}

function emailPassword($user_id) {
	$user = db_grab('SELECT u.email, l.code language FROM users u JOIN languages l ON u.language_id = l.id WHERE u.is_active = 1 AND u.id = ' . $user_id);
	$message = getString('email_password_message', $user['language']);
	$message = str_replace('%LINK%', url_base() . '/login/password_reset.php?id=' . $user_id, $message);
	emailUser($user['email'], getString('email_password_subject', $user['language']), $message);
}

function emailUser($to, $subject, $message) {
	$to = (is_array($to)) ? array_unique($to) : array($to);
	
	//dev limiting
	if ($limit = getOption('email_limit')) $to = array_intersect($limit, $to);

	//do a little maintenance
	if (!$tocount = count($to)) return false;
	for ($i = 0; $i < $tocount; $i++) {
		if (empty($to[$i])) {
			unset($to[$i]);
		} elseif ((url_tld() == 'site') && !in_array($to[$i], array('josh@joshreisner.com', 'josh.reisner@gmail.com', 'josh@bureaublank.com', 'jreisner@seedco.org'))) {
			unset($to[$i]);
		}
	}
	if (!$tocount = count($to)) return false;
	
	//repeat subject and basic formatting
	$message = '<div style="font-family:Verdana;font-size:11px;line-height:17px;"><h1 style="font-weight:normal;font-size:20px;margin:0px 0px 10px 0px">' . $subject . '</h1>' . $message . '</div>';
	
	//send
	$result = email($to, $message, $subject);
		
	//keep a record
	foreach ($to as $t) {
		db_query('INSERT INTO emails ( address, subject, message, created_date, created_user, was_sent ) VALUES (
			\'' . $t . '\',
			\'' . format_quotes($subject) . '\',
			\'' . format_quotes($message) . '\',
			GETDATE(),
			' . user('NULL') . ',
			' . format_boolean($result, '1|0') . '
			)');
	}		
	return $result;
}

function formAddChannels($form, $table, $column) {
	if (getOption('channels')) $form->set_field(array('name'=>'channels', 'option_title'=>'title' . langExt(), 'type'=>'checkboxes', 'label'=>getString('channels_label'), 'options_table'=>'channels', 'linking_table'=>$table . '_to_channels', 'object_id'=>$column, 'option_id'=>'channel_id', 'default'=>'all'));
}

function getChannelsWhere($table, $short, $column) {
	if (getOption('channels') && $_SESSION['channel_id']) return ' JOIN ' . $table . '_to_channels t2c ON ' . $short . '.id = t2c.' . $column . ' WHERE ' . $short . '.is_active = 1 AND t2c.channel_id = ' . $_SESSION['channel_id'] . ' ';
	return ' WHERE ' . $short . '.is_active = 1 ';
}

function getOption($key) {
	global $options;

	//already set, either by options.php or previous run
	if (isset($options[$key])) return $options[$key];

	//default options.  override these in your config file by specifying $options variables
	$defaults['channels']				= false;
	$defaults['languages']				= false;
	$defaults['legal']					= false;
	$defaults['requests']				= true;

	$defaults['bb_notifyfollowup']		= false;
	$defaults['bb_notifypost']			= false;
	$defaults['bb_types']				= false;
	$defaults['bb_threaded']			= false;
	
	$defaults['cal_showholidays']		= true;

	$defaults['email_limit']			= false;
	
	$defaults['staff_alertnew']			= false;
	$defaults['staff_alertdelete']		= false;
	$defaults['staff_allowshared']		= false;
	$defaults['staff_showdept']			= true;
	$defaults['staff_showoffice']		= true;
	$defaults['staff_showrank']			= true;
	$defaults['staff_showhome']			= true;
	$defaults['staff_showemergency']	= true;
	$defaults['staff_ldcode']			= true;
	
	//don't run through this again for this key
	$options[$key] = $defaults[$key];
	
	return $defaults[$key];
}

function getString($key, $language=false) {
	global $strings;

	//default is user language, but can be overridden, such as in emails
	if (!$language) $language = $_SESSION['language'];
	
	//success
	if (isset($strings[$key][$language])) return $strings[$key][$language];

	//is set for english, suggest translation
	if (isset($strings[$key]['en'])) error_handle('string not set', 'The string ' . $key . ' is not set for language ' . $language . '.  Suggested translation:<p style="font-style:italic;">' . language_translate($strings[$key]['en'], 'en', $language)) . '</p>';

	error_handle('string not defined', 'The string ' . $key . ' is not defined yet in English.');
	
}

function langExt($code=false) {
	//return field name appendage
	if (!$code) $code = $_SESSION['language'];
	if ($code == 'en') return '';
	return '_' . $code;
}

function langExtT($code=false) {
	//intended to be transliteration.  being used in press-clips
	if (!$code) $code = $_SESSION['language'];
	if ($code == 'ru') return '_ru';
	return '';
}

function langTranslatePost($keys) {
	//set incoming POST values for languages
	if (!getOption('languages')) return false;
	global $_POST;
	
	//make sure do translations checkbox is checked
	if (!isset($_POST['translations_do'])) return false;

	//list of fields to translate
	$keys = array_separated($keys);

	//get list of languages to translate to
	$languages = db_array('SELECT code FROM languages WHERE id <> ' . $_SESSION['language_id']);
	
	foreach ($keys as $key) {
		foreach ($languages as $language) {
			$_POST[$key . langExt($language)] = language_translate($_POST[$key . langExt()], $_SESSION['language'], $language);
		}
	}
}

function langTranslateCheckbox($form, $show=true) {
	if (!getOption('languages')) return false;
	$form->set_field(array('name'=>'translations_do', 'type'=>(($show) ? 'checkbox' : 'hidden'), 'label'=>getString('translations_do'), 'value'=>0));
}

function langUnsetFields($form, $names) {
	//unset fields for other languages
	//todo - take multiple names
	//if (!getOption('languages')) return false;
	$names = array_separated($names);
	foreach ($names as $name) {
		$languages = db_array('SELECT code FROM languages WHERE id <> ' . $_SESSION['language_id']);
		foreach ($languages as &$l) $l = $name . langExt($l);
		$form->unset_fields(implode(',', $languages));
	}
}

function login($username, $password, $skippass=false) {
	global $_SESSION;
	//need id, fullname, email departmentid, ishelpdesk, homepage, update_days, updated_on, first
	if ($skippass) {
		$where = '';
		error_debug('<b>login</b> running without password', __file__, __line__);
    } else {
		$where = ' AND ' . db_pwdcompare($password, 'u.password') . ' = 1';
		error_debug('<b>login</b> running with password', __file__, __line__);
    }

	if ($user = db_grab('SELECT 
		u.id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname,
		u.email,
		' . db_pwdcompare('', 'u.password') . ' password,
		u.departmentID,
		d.isHelpdesk,
		u.help,
		u.is_admin,
		u.updated_date,
		u.language_id,
		l.code language,
		' . db_datediff('u.updated_date', 'GETDATE()') . ' update_days
	FROM users u
	LEFT JOIN languages l ON u.language_id = l.id
	LEFT JOIN departments d ON u.departmentID = d.departmentID
	WHERE u.email = \'' . $username . '\' AND u.is_active = 1' . $where)) {
		//login was good
		db_query('UPDATE users SET lastlogin = GETDATE() WHERE id = ' . $user['id']);
		$_SESSION['user_id']		= $user['id'];
		$_SESSION['is_admin']		= $user['is_admin'];
		$_SESSION['email']			= $user['email'];
		$_SESSION['homepage']		= '/bb/';
		$_SESSION['departmentID']	= $user['departmentID'];
		$_SESSION['isHelpdesk']		= $user['isHelpdesk'];
		$_SESSION['update_days']	= $user['update_days'];
		$_SESSION['updated_date']	= $user['updated_date'];
		$_SESSION['password']		= $user['password'];
		$_SESSION['language_id']	= $user['language_id'];
		$_SESSION['language']		= $user['language'];
		$_SESSION['full_name']		= $user['firstname'] . ' ' . $user['lastname'];
		$_SESSION['isLoggedIn']		= true;
		
		cookie('last_login', $user['email']);
		cookie('last_email', $user['email']);
		return true;
	}
	$_SESSION['user_id']		= false;
	return false;
}

function updateInstanceWords($id, $text) {
	global $ignored_words;
	$words = array_diff(split('[^[:alpha:]]+', strtolower(strip_tags($text))), $ignored_words);
	if (count($words)) {
		$text = implode('|', $words);
		db_query('index_intranet_instance ' . $id, '\'' . $text . '\'');
	}
}

//it's convention to always put this at the bottom
function joshlib() {
	//look for joshlib at joshlib/index.php, ../joshlib/index.php, all the way down
	global $_josh;
	$count = substr_count($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'], '/');
	for ($i = 0; $i < $count; $i++) if (@include(str_repeat('../', $i) . 'joshlib/index.php')) return $_josh;
	die('Could not find Joshlib.');
}

class display {
	//draw a left/right table thing like a bulletin board topic thread or the staff view page
	
	var $class		= false;
	var $controls	= array();
	var $rows		= array();
	var $title		= false;
	
	function __construct($title=false, $rows=false, $controls=false, $class=false) {
		if ($title)		$this->title = $title;
		if ($rows)		$this->rows = $rows;
		if ($controls)	$this->controls = $controls;
		if ($class)		$this->class = $class;
	}
	
	function row($label, $content) {
		$this->rows[$label] = $content;
	}
	
	function draw($bottom=false) {
		global $page;
		$count = count($this->rows);
		if (!$this->title) $this->title = $page['breadcrumbs'] . $page['title'];
		$return = draw_div_class('title', format_string($this->title, 40) . draw_nav($this->controls));
		$counter = 1;
		foreach ($this->rows as $label=>$content) {
			$class = 'row';
			if ($counter == 1) $class .= ' first';
			if ($counter == $count) $class .= ' last';
			$return .= draw_div_class($class, draw_div_class('label', $label) . draw_div_class('content', $content));
			$counter++;
		}
		if ($bottom) $return .= draw_div_class('bottom', $bottom);
		if (!empty($this->class)) $this->class = ' ' . $this->class;
		return draw_div_class('display' . $this->class, $return);
	}
}

?>