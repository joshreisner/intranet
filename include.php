<?php
//might need session for ajax in joshlib
session_start();
if (!isset($_SESSION['user_id']))		$_SESSION['user_id'] = false;
if (!isset($_SESSION['channel_id']))	$_SESSION['channel_id'] = false;
if (!isset($_SESSION['language_id']))	$_SESSION['language_id'] = 1;

//session & env
extract(joshlib());

//set language code
if (!isset($_SESSION['language']))		$_SESSION['language'] = db_grab('SELECT code FROM languages WHERE id = ' . $_SESSION['language_id']);

//include options file if it exists
@include($_josh['root'] . $_josh['write_folder'] . '/options.php');
@include($_josh['root'] . $_josh['write_folder'] . '/strings.php');

//debug();

//apply security
if (!isset($pageIsPublic) || !$pageIsPublic) {
	error_debug('page is not public', __file__, __line__);
	if (!$_SESSION['user_id']) {
		error_debug('user_id session not set', __file__, __line__);
		if (!login(@$_COOKIE['last_login'], '', true)) {
			error_debug('could not log in with ' . @$_COOKIE['last_login'] . ', redirecting', __file__, __line__);
			url_change('/?goto=' . urlencode($_josh['request']['path_query']));
		}
	} 
	
	//determine location & scenario
	error_debug('user is logged in, determining location & scenario', __file__, __line__);
	$page		= getPage();
	$location	= (($request['folder'] == 'bb') || ($request['folder'] == 'cal') || ($request['folder'] == 'docs') || ($request['folder'] == 'staff') || ($request['folder'] == 'helpdesk') || ($request['folder'] == 'contacts') || ($request['folder'] == 'external-orgs') || ($request['folder'] == 'press-clips')) ? $request['folder'] : 'areas';

	$user = db_grab('SELECT id, help FROM users WHERE id = ' . $_SESSION['user_id']);

	//get modules info
	error_debug('getting modules', __file__, __line__);
	$result = db_query('SELECT 
			m.id,
			p.url,
			m.title,
			m.pallet,
			m.isPublic,
			(SELECT u.is_closed FROM users_to_modules u WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND u.module_id = m.id) is_closed,
			(SELECT u.is_admin FROM users_to_modules u WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND u.module_id = m.id) is_admin
		FROM modules m
		JOIN pages p ON p.id = m.homePageID
		WHERE m.is_active = 1
		ORDER BY m.precedence');

	$modules	= array();
	$areas		= array();

	while ($r = db_fetch($result)) {
		$modules[$r['id']] = array(
			'id'		=> $r['id'],
			'title'		=> $r['title'],
			'url'		=> $r['url'],
			'isPublic'	=> $r['isPublic'],
			'pallet'	=> $r['pallet'],
			'is_closed'	=> $r['is_closed'],
			'is_admin'	=> $r['is_admin']
		);
		if (!$r['pallet']) $areas[$r['title']] = $r['id'];
	}
	ksort($areas);
	
	//indicate admin privileges for the current module
	if (!$_SESSION['is_admin']) {
		$module_admin = (isset($modules[$page['module_id']])) ? $modules[$page['module_id']]['is_admin'] : false;
	} else {
		$module_admin = true;
	}
	
	//check to see if user needs update ~ todo make this a preference
	error_debug('checking if user needs update', __file__, __line__);
	if (($_SESSION['update_days'] > 90 || empty($_SESSION['updated_date'])) && ($_josh['request']['path'] != '/staff/add_edit.php')) {
		error_debug('user needs address update', __file__, __line__);
		url_change('/staff/add_edit.php?id=' . $_SESSION['user_id']);
	} elseif ($_SESSION['password'] && ($_josh['request']['path'] != '/login/password_update.php') && ($_josh['request']['path'] != '/staff/add_edit.php')) {
		error_debug('user needs password update', __file__, __line__);
		url_change('/login/password_update.php');
	}		

	//special pages that don't belong to a module still need info
	if (!isset($page['module_id'])) $page['module_id'] = 0;
	if (!isset($modules[$page['module_id']])) {
		error_debug('unspecified module', __file__, __line__);
		$modules[$page['module_id']]['pallet']		= false;
		$modules[$page['module_id']]['isPublic']	= false;
		$modules[$page['module_id']]['pallet']		= false;
		$modules[$page['module_id']]['title']		= getString('app_name');
		$modules[$page['module_id']]['is_admin']	= false;
	}

	//get helpdesk pallet info
	error_debug('getting helpdesk pallet info', __file__, __line__);
	$helpdeskOptions = db_table('SELECT 
			d.departmentID id, 
			d.shortName name, 
			(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.departmentID = d.departmentID AND t.statusID <> 9) num_open
		FROM departments d
		WHERE isHelpdesk = 1
		ORDER BY d.shortName');
	$helpdeskStatus = db_grab('SELECT message FROM it_system_status');

	//handle side menu pref updates
	if (isset($_GET['module'])) {
		if (db_grab('SELECT COUNT(*) FROM users_to_modules WHERE module_id = ' . $_GET['module'] . ' AND user_id = ' . $_SESSION['user_id'])) {
			$closed = db_grab('SELECT is_closed FROM users_to_modules WHERE module_id = ' . $_GET['module'] . ' AND user_id = ' . $_SESSION['user_id']);
			db_query('UPDATE users_to_modules SET is_closed = ' . abs($closed - 1) . ' WHERE module_id = ' . $_GET['module'] . ' AND user_id = ' . $_SESSION['user_id']);
		} else {
			db_query('INSERT INTO users_to_modules ( user_id, module_id, is_closed ) VALUES ( ' . $_SESSION['user_id'] . ', ' . $_GET['module'] . ', 1 )');
		}
		url_query_drop('module');
	} elseif(isset($_GET['language_id'])) {
		$_SESSION['language_id'] = $_GET['language_id'];
		$_SESSION['language'] = db_grab('SELECT code FROM languages WHERE id = ' . $_GET['language_id']);
		//update users
		url_drop('language_id');
	} elseif(isset($_GET['channel_id'])) {
		$_SESSION['channel_id'] = (empty($_GET['channel_id'])) ? false : $_GET['channel_id'];
		//update users
		url_drop('channel_id');
	}
}

//done!
error_debug('done processing include!', __file__, __line__);
	

//obsolete functions
include($_josh['root'] . '/obsolete.php');


//draw functions
function drawEmailFooter() {
	$string = getString('app_name');
	return '<div class="emailfooter">This message was generated by the <a href="' . url_base() . '">' . $string . '</a>.</div></body></html>';
}

function drawEmailHeader() {
	return '<html><head>' . draw_css(file_get('/styles/screen.css')) . '</head><body class="email">';
}

function drawMessage($str, $align='left') {
	if (empty($str) || !format_html_text($str)) return false;
	return draw_container('div', $str, array('class'=>'message'));
}
						
function drawName($user_id, $name, $date=false, $withtime=false, $separator='<br>') {
	global $_josh;
	$base = url_base();
	$date = ($date) ? format_date_time($date, '', $separator) : false;
	$img  = draw_img($_josh['write_folder'] . '/dynamic/users-image_small-' . $user_id . '.jpg', $base . '/staff/view.php?id=' . $user_id);		
	return '
	<table cellpadding="0" cellspacing="0" border="0" width="144">
		<tr valign="top" style="background-color:transparent;">
			<td width="46" height="37" align="center">' . $img . '</td>
			<td><a href="' . $base . '/staff/view.php?id=' . $user_id . '">' . format_string($name, 20) . '</a><br>' . $date . '</td>
		</tr>
	</table>';
}

function drawNavigation() {
	global $_SESSION, $module_admin, $_josh, $page;
	if (!$page['module_id']) return false; //not in module
	$pages	= array();
	$admin	= ($module_admin) ? '' : 'AND is_admin = 0';
	$result	= db_query('SELECT name, url FROM pages WHERE module_id = ' . $page['module_id'] . ' ' . $admin . ' AND isInstancePage = 0 ORDER BY precedence');
	while ($r = db_fetch($result)) {
		//don't do navigation for helpdesk.  it needs to do it, since a message could go above
		if ($r['url'] != '/helpdesk/') $pages[$r['url']] = $r['name'];
	}

	$count = count($pages);
	if ($count < 2) return false;
	$return = '<table class="navigation" cellspacing="1">
		<tr>';
	$cellwidth = round(100 / $count, 2);

	foreach ($pages as $url=>$name) {
		if (($url == $_josh["request"]["path_query"]) || ($url == url_base() . $_josh["request"]["path_query"])) {
			$cell = ' class="selected">' . $name . '';
		} else {
			$cell = '><a href="' . $url . '">' . $name . '</a>';
		}
		$return .= '<td width="' . $cellwidth . '%"' . $cell . '</td>';
	}
	return $return . '</tr>
		</table>';
}

function drawHeader($options=false, $title=false) {
	//get the page for the header
	global $page;
	if (!$title) $title = $page['name'];
	$return = draw_link($page['url'], $page['module']) . ' &gt; ' . $title;	
	if ($options) foreach ($options as $url=>$name) $return .= draw_link($url, $name, false, array('class'=>'right'));
	return $return;
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
	return draw_form_select($name, $array, $selectedID, !$nullable, $class, $jumpy);
}

function drawSyndicateLink($name) {
	global $_josh;
	return draw_rss_link($_josh['write_folder'] . '/rss/' . $name . '.xml');
}

function drawThreadComment($content, $user_id, $fullname, $date, $module_admin=false) {
	global $location;
	$return  = '<tr><td class="left">';
	$return .= drawName($user_id, $fullname, $date, true) . '</td>';
	$return .= '<td class="right text ';
	if ($module_admin) $return .= $location . '-hilite';
	$return .= '" height="80"><div class="text">' . $content . '</div></td></tr>';
	return $return;
}

function drawThreadCommentForm($showAdmin=false) {
	global $module_admin, $_josh, $_SESSION;
	$return = '<a name="bottom"></a><form ';
	if ($_josh['db']['language'] == 'mysql') $return .= 'accept-charset="utf-8" ';
	$return .= 'method="post" action="' . $_josh['request']['path_query'] . '" onsubmit="javascript:return validate(this);"><tr valign="top">
			<td class="left">' . drawName($_SESSION['user_id'], $_SESSION['full_name'], false, true) . '</td>
			<td>' . draw_form_textarea('message', '', 'mceEditor thread');
	if ($showAdmin && $module_admin) {
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
			<td class="bottom" colspan="2">' . draw_form_submit('Update Conversation') . '</td>
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
			<td class="text"><div class="text top"><h1>' . $title . '</h1>';
	if ($editurl) {
		$return .= '<a class="right button floating" href="' . $editurl . '">edit this</a>';
	}
	$return .= str_replace('href="../', 'href="http://' . $_josh['request']['host'] . '/', $content) . '
			</div></td>
		</tr>';
	return $return;	
}

function drawTop() {
	global $_GET, $_SESSION, $_josh, $page, $module_admin, $location, $user;
	error_debug('starting top', __file__, __line__);
	$title = $page['module'] . ' > ' . $page['name'];
	if ($_josh['db']['language'] == 'mysql') url_header_utf8();
?><html>
	<?
	echo draw_container('head',
		(($_josh['db']['language'] == 'mysql') ? draw_meta_utf8() : '') .
		draw_container('title', $title) .
		draw_css_src('/styles/screen.css',	'screen') .
		draw_css_src('/styles/print.css',	'print') .
		draw_css_src('/styles/ie.css',		'ie') .
		draw_javascript_src('/javascript.js') .
		draw_javascript_lib()
	);
	$class = ($page['module']) ? $page['module'] : 'unassigned';
	?>
	<body>
		<div id="container">
			<?=draw_div('banner', draw_img($_josh['write_folder'] . '/banner.png', $_SESSION['homepage']))?>
			<div id="left" class="<?=$location?>">
				<div id="help">
				<a class="button left" href="<?=$_SESSION['homepage']?>">Home</a>
				<?=draw_link_ajax_set('users', 'help', 'session', abs($user['help'] - 1), (($user['help']) ? 'Hide' : 'Show') . ' Help', array('class'=>'button right', 'id'=>'showhelp'))?>
			<? if ($_SESSION['is_admin']) {?>
					<a class='button right' href='/admin/pages/?id=<?=$page['id']?>'>Edit Page Info</a>
			<? }?>
				<div id="helptext"<? if (!$user['help']) {?> style="display:none;"<? }?>>
					<?
					echo ($page['helpText']) ? $page['helpText'] : 'No help is available for this page.';
					?>
				</div>
				</div>
	<? 
	if ($location == 'helpdesk') echo drawNavigationHelpdesk();
	echo drawNavigation();
	$_josh['drawn']['top'] = true;
	error_debug('finished drawing top', __file__, __line__);
}

//it's convention to put this right below drawTop()
function drawBottom() {
	global $_SESSION, $_GET, $_josh, $modules, $areas, $helpdeskOptions, $helpdeskStatus;
	?>
			</div>
			<div id='right'>
				<div id='tools'>
					<a class='right button' href='/index.php?action=logout'>Log Out</a>
					Hello <a href='/staff/view.php?id=<?=$_SESSION['user_id']?>'><b><?=$_SESSION['full_name']?></b></a>.

					<form name='search' accept-charset='utf-8' method='get' action='/staff/search.php' onSubmit='javascript:return doSearch(this);'>
		            <input type='text' name='q' value='Search Staff' onfocus='javascript:form_field_default(this, true, 'Search Staff');' onblur='javascript:form_field_default(this, false, 'Search Staff');'>
					</form>
					<?
					if (getOption('channels')) echo draw_form_select('channel_id', 'SELECT id, title_en FROM channels ORDER BY title_en', $_SESSION['channel_id'], false, 'channels', 'url_query_set(\'channel_id\', this.value)', 'View All Networks');
					if (getOption('languages')) echo draw_form_select('language_id', 'SELECT id, title FROM languages ORDER BY title', $_SESSION['language_id'], true, 'languages', 'url_query_set(\'language_id\', this.value)');
					?>
					
					<table class="links">
						<? if ($_SESSION['is_admin']) {?><tr><td colspan="2" style="padding:6px 6px 0px 0px;"><a class="right button" href="/admin/links/">Edit Links</a></td></tr><? } ?>
	<?
	$side = 'left';
	$links = db_query('SELECT url, text FROM links WHERE is_active = 1 ORDER BY precedence');
	while ($l = db_fetch($links)) {
		if ($side == 'left') echo '<tr>';
		echo '<td width="50%"><a href="' . $l['url'] . '" target="new">' . $l['text'] . '</a></td>';
		if ($side == 'right') echo '</tr>';
		$side = ($side == 'left') ? 'right' : 'left';
	}
	?>
					</table>
				</div>
	<? 
		            
	foreach ($modules as $m) {
		if ($m['pallet']) { ?>
		<table class="right <?=str_replace('/', '', $m['url'])?>" cellspacing="1">
			<tr>
				<td colspan="2" class="head">
					<a href="<?=$m['url']?>"><?=$m['title']?></a>
					<?=draw_img($m['url'] . 'arrow-' . format_boolean($m['is_closed'], 'up|down') . '.gif', url_query_add(array('module'=>$m['id']), false))?>
				</td>
			</tr>
			<? if (!$m['is_closed']) include($_josh['root'] . $m['pallet']);?>
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


//email functions

function deleteColumn($id) {
	return '<a href="javascript:confirmDelete(' . $id . ');"><img src="/images/icons/delete.png" alt="delete" width="16" height="16" border="0"/></a>';
}

function emailAdmins($message, $subject, $colspan=1) {
	return emailUsers(db_array('SELECT email FROM users WHERE is_admin = 1 AND is_active = 1'), $subject, $message, $colspan);
}

function emailInvite($id, $email, $name) {
	global $_SESSION;
	$email = format_email($email);
	$message = '<tr><td class="text">
		Welcome ' . $name . '!  You can
		<a href="' . url_base() . '/login/password_reset.php?id=' . $id . '">log in to the ' . getString('app_name') . ' now</a>.  
		The system will prompt you to pick a password and update your contact information.
		<br><br>
		If you run into problems, please ask <a href="mailto:' . $_SESSION['email'] . '">' . $_SESSION['full_name'] . '</a> for help.
		</td></tr>';
	emailUser($email, getString('app_name') . ' Login Information', $message);
}

function emailUser($address, $title, $content, $colspan=1, $message=false) {
	global $_josh, $_SESSION;

	//build message
	$message = drawEmailHeader() . 
		(($message) ? drawMessage($message) : '') . 
		drawTableStart() . 
		drawHeaderRow($title, $colspan) .
		$content . 
		drawTableEnd() . 
		drawEmailFooter();
	
	//only send to me if developing
	if (($_josh['mode'] == 'dev') && ($address != 'josh@joshreisner.com')) return false;
	
	//send
	$result = email($address, $message, $title);
	
	//keep a record
	db_query('INSERT INTO emails ( address, subject, message, created_date, created_user, was_sent ) VALUES (
		' . $address . ',
		\'' . format_quotes($title) . '\',
		\'' . format_quotes($message) . '\',
		GETDATE(),
		' . (($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL') . ',
		' . format_boolean($result, '1|0') . '
		)');
	
	return $result;
}

function emailUsers($addresses, $title, $content, $colspan=1, $message=false) {
	if (!is_array($addresses)) $addresses = array($addresses);
	$addresses = array_unique($addresses);
	foreach($addresses as $a) emailUser($a, $title, $content, $colspan, $message);
	return true;
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

	$defaults['bb_notifyfollowup']		= false;
	$defaults['bb_notifypost']			= false;
	$defaults['bb_types']				= false;
	
	$defaults['cal_showholidays']		= true;
	
	$defaults['staff_alertnew']			= false;
	$defaults['staff_alertdelete']		= false;
	$defaults['staff_allowshared']		= false;
	$defaults['staff_showdept']			= true;
	$defaults['staff_showoffice']		= true;
	$defaults['staff_showrank']			= true;
	$defaults['staff_showhome']			= true;
	$defaults['staff_showemergency']	= true;
	
	//don't run through this again for this key
	$options[$key] = $defaults[$key];
	
	return $defaults[$key];
}

function getPage() {
	global $_josh;
	
	if ($return = db_grab('SELECT 
			p.id, 
			p.name, 
			p.helpText, 
			p.is_admin, 
			m.id module_id, 
			m.title module, 
			p2.url 
		FROM pages p 
		LEFT JOIN modules m ON p.module_id = m.id 
		LEFT JOIN pages p2 ON m.homePageID = p2.id
		WHERE p.url = \'' . $_josh['request']['path'] . '\'')) {
		return $return;
	} else {
		error_debug('creating page', __file__, __line__);
		db_query('INSERT INTO pages ( url, name ) VALUES ( \'' . $_josh['request']['path'] . '\', \'Untitled Page\' )');
		return getPage();
	}
}

function getPostTranslations($key) {
	if (!getOption('languages')) return false;
	global $_POST;
	$str = $_POST[$key . '_' . $_SESSION['language']];
	$result = db_query('SELECT code FROM languages WHERE id <> ' . $_SESSION['language_id']);
	while ($r = db_fetch($result)) {
		$_POST[$key . '_' . $r['code']] = language_translate($str, $src, $r['code']);
	}
}

function formFieldTranslate($handle, $name) {
	if (!getOption('languages')) return false;
	$languages = db_array('SELECT id, code FROM languages WHERE id <> ' . $_SESSION['language_id'], false, false, $name . '_');
	$handle->unset_fields(implode(',', $languages));
}

function formFieldTranslateCheckbox($handle) {
	if (!getOption('languages')) return false;
	$handle->set_field(array('name'=>'do_translations', 'type'=>'checkbox', 'value'=>1));
}

function getString($key) {
	global $strings;
	
	if (isset($strings[$key][$_SESSION['language']])) return $strings[$key][$_SESSION['language']];

	//default strings.  override these in your config file by specifying $strings variables
	$defaults['app_name']['en']			= 'Intranet';
	$defaults['app_welcome']['en']		= 'Welcome to the ' . $defaults['app_name'] . '.  If you don\'t have a login for this site or if you are having trouble, please use the links below:';
	$defaults['bb_admin']['en']			= 'This is an administrative announcement topic.';
	$defaults['staff_firsttime']['en']	= 'Welcome to the ' . $defaults['app_name'] . '!  Since this is your first time logging in, please make certain that your information here is correct, then click \'save changes\' at the bottom.';
	$defaults['staff_update']['en']		= 'Your personal info hasn\'t been updated in a while.  Please update this form and click Save at the bottom.  Your home and emergency contact information will remain private -- only senior staff will have access to it.';
	
	$defaults['topic']['en']			= 'Topic';
	$defaults['topic']['es']			= 'Tema';
	$defaults['topic']['fr']			= 'Sujet';
	$defaults['topic']['ru']			= 'Рубрики';
	
	$defaults['starter']['en']			= 'Started By';
	$defaults['starter']['es']			= 'Iniciado por';
	$defaults['starter']['fr']			= 'Commencé par';
	$defaults['starter']['ru']			= 'К работе';
	
	$defaults['replies']['en']			= 'Replies';
	$defaults['replies']['es']			= 'Respuestas';
	$defaults['replies']['fr']			= 'Réponses';
	$defaults['replies']['ru']			= 'Ответы';
	
	$defaults['last_post']['en']			= 'Last Post';
	$defaults['last_post']['es']			= 'Último';
	$defaults['last_post']['fr']			= 'Dernier';
	$defaults['last_post']['ru']			= 'Последнее сообщение';
	
	$defaults['no_topics']['en']			= 'No topics have been added yet.  Why not <a href="#bottom">be the first</a>?';
	$defaults['no_topics']['es']			= 'No hay temas se han añadido todavía. ¿Por qué no <a href="#bottom">no ser el primero</a>?';
	$defaults['no_topics']['fr']			= 'Aucun sujet n\'a encore été enregistré. Pourquoi <a href="#bottom">être pas le premier</a>?';
	$defaults['no_topics']['ru']			= 'Нет тем еще не было добавлено. Почему бы не <a href="#bottom">не быть первой</a>?';
	
	$defaults['category']['en']			= 'Category';
	$defaults['category']['es']			= 'Categoría';
	$defaults['category']['fr']			= 'Catégorie';
	$defaults['category']['ru']			= 'Категории';
	
	$defaults['new_topic']['en']			= 'Contribute a New Topic';
	$defaults['new_topic']['es']			= 'Contribuir con un nuevo tema';
	$defaults['new_topic']['fr']			= 'Contribuer par un nouveau thème';
	$defaults['new_topic']['ru']			= 'Добавить новую тему';

	$defaults['posted_by']['en']			= 'POsted By';
	$defaults['posted_by']['es']			= 'Publicado por';
	$defaults['posted_by']['fr']			= 'Signalé près';
	$defaults['posted_by']['ru']			= 'вывешено мимо';
	
	$defaults['is_admin']['en']			= 'Is Admin';
	$defaults['is_admin']['es']			= 'Es administrativo';
	$defaults['is_admin']['fr']			= 'Est administratif';
	$defaults['is_admin']['ru']			= 'административно';
	
	$defaults['title']['en']			= 'Title';
	$defaults['title']['es']			= 'Título';
	$defaults['title']['fr']			= 'Titre';
	$defaults['title']['ru']			= 'Название';
	
	$defaults['networks']['en']			= 'Networks';
	$defaults['networks']['es']			= 'Redes';
	$defaults['networks']['fr']			= 'Réseaux';
	$defaults['networks']['ru']			= 'Сети';

	$defaults['description']['en']		= 'Description';
	$defaults['description']['es']		= 'Descripción';
	$defaults['description']['fr']		= 'Description';
	$defaults['description']['ru']		= 'Описание';
	
	if (isset($defaults[$key][$_SESSION['language']])) return $defaults[$key][$_SESSION['language']];
	
	if (isset($defaults[$key]['en']) || isset($strings[$key]['en'])) {
		$str = (isset($strings[$key]['en'])) ? $strings[$key]['en'] : $defaults[$key]['en'];
		error_handle('string not set', 'The string ' . $key . ' is not set for language ' . $_SESSION['language'] . '.  Suggested translation:<p style="font-style:italic;">' . language_translate($str, 'en', $_SESSION['language'])) . '</p>';
	}

	error_handle('string not defined', 'The string ' . $key . ' is not defined yet.');
	
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
		' . db_datediff('u.updated_date', 'GETDATE()') . ' update_days
	FROM users u
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
	global $_SERVER, $_josh, $strings, $options;
	$possibilities = array(
		'D:\Sites\joshlib\index.php', //seedco-web-srv
		'/home/hcfacc/www/joshlib/index.php', //icd 2
		'/home/sites/www/joshlib/index.php', //icd 3
		'/home/joshreisner/www/joshlib/joshlib/index.php', //icd 4
		'/Users/joshreisner/Sites/joshlib/index.php' //dev
	);
	foreach ($possibilities as $p) if (@include($p)) return $_josh;
	die('Cannot locate library! ' . $_SERVER['DOCUMENT_ROOT']);
}
?>