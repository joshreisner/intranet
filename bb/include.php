<?php
include('../include.php');

function bbDrawTable($limit=false, $where=false, $title=false) {
	//only show add new on main page
	$options = (!$limit) ? false : array('#bottom'=>getString('add_new'));
	
	$t = new table('bb_topics', drawHeader($options, $title));
	$t->set_column('topic', 'l', getString('topic'));
	$t->set_column('starter', 'l', getString('starter'), 120);
	$t->set_column('replies', 'c', getString('replies'), 30);
	$t->set_column('last_post', 'r', getString('last_post'), 100);
	
	$result = bbGetTopics($where, $limit);
	
	foreach ($result as &$r) {
		array_argument($r, 'thread');
		if ($r['is_admin']) array_argument($r, 'admin');
		$r['link'] = 'topic.php?id=' . $r['id'];
		if (empty($r['topic'])) $r['topic'] = '<i>no topic entered</i>';
		$r['topic'] = draw_link($r['link'], $r['topic']);
		$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
		$r['last_post'] = format_date($r['last_post']);
	}
	
	return $t->draw($result, getString('topics_empty'));
}

function bbDrawTopic($id, $email=false) {
	global $_josh, $page;
	
	if (!$r = db_grab('SELECT 
		t.title' . langExt() . ' title,
		t.description' . langExt() . ' description,
		t.created_date,
		t.is_admin,
		t.type_id,
		y.title' . langExt() . ' type,
		u.id created_user,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname,
		' . db_updated('u') . '
	FROM bb_topics t
	JOIN users u ON t.created_user = u.id
	LEFT JOIN bb_topics_types y ON t.type_id = y.id
	WHERE t.id = ' . $id)) return false;

	$return = '';
	
	if ($r['is_admin'] == 1) $return .= drawMessage(getString('topic_admin'));
	
	$options = (($page['is_admin'] || (user() == $r['created_user'])) && !$email) ? array('edit.php?id=' . $id=>getString('edit'), 'javascript:checkDelete();'=>getString('delete')) : false;
	
	//display topic thread
	$d = new display($page['breadcrumbs'] . $r['title'], false, $options, 'thread');
	
	//if categories
	if (getOption('bb_types') && $r['type']) {
		$r['description'] .= draw_div_class('light', getString('category') . ': ' . draw_link('category.php?id=' . $r['type_id'], $r['type']));
	}
	
	//channels
	if (getOption('channels') && ($channels = db_array('SELECT c.title' . langExt() . ' title FROM channels c JOIN bb_topics_to_channels t2c ON c.id = t2c.channel_id WHERE t2c.topic_id = ' . $id . ' ORDER BY title' . langExt()))) {
		$r['description'] .= draw_div_class('light', 'Networks: ' . implode(', ', $channels));
	}
	
	$d->row(drawName($r['created_user'], $r['firstname'] . ' ' . $r['lastname'], $r['created_date'], true, BR, $r['updated']), '<h1>' . $r['title'] . '</h1>' . $r['description']);
	
	//append followups
	if ($r['is_admin']) {
		$return .= $d->draw();
	} else {
		$followups = db_table('SELECT
					f.description' . langExt() . ' description,
					ISNULL(u.nickname, u.firstname) firstname,
					u.lastname,
					f.created_date,
					f.created_user,
					' . db_updated('u') . '
				FROM bb_followups f
				JOIN users u ON u.id = f.created_user
				WHERE f.is_active = 1 AND f.topic_id = ' . $id . '
				ORDER BY f.created_date');
		foreach ($followups as $f) $d->row(drawName($f['created_user'], $f['firstname'] . ' ' . $f['lastname'], $f['created_date'], true, BR, $f['updated']), $f['description']);
		$return .= $d->draw();
	
		if (!$email) {
			//add a followup form
			$f = new form('bb_followups', false, getString('add_followup'));
			$f->unset_fields('topic_id');
			langUnsetFields($f, 'description');
			$return .= $f->draw(false, false);
		}
	}

	return $return;
}

function drawTopicForm() {
	global $page;
	$f = new form('bb_topics', @$_GET['id'], getString('topic_new'));
	if ($page['is_admin']) {
		$f->set_field(array('name'=>'created_user', 'class'=>'admin', 'type'=>'select', 'sql'=>'SELECT id, CONCAT_WS(", ", lastname, firstname) FROM users WHERE is_active = 1 ORDER BY lastname, firstname', 'default'=>user(), 'required'=>true, 'label'=>getString('posted_by')));
	}
	if ($page['is_admin'] && !getOption('bb_notifypost')) {
		$f->set_field(array('name'=>'is_admin', 'class'=>'admin', 'type'=>'checkbox', 'label'=>getString('is_admin')));
	} else {
		$f->unset_fields('is_admin');
	}
	$f->set_field(array('name'=>'title' . langExt(), 'type'=>'text', 'label'=>getString('title')));
	if (getOption('bb_types')) $f->set_field(array('name'=>'type_id', 'type'=>'select', 'sql'=>'SELECT id, title' . langExt() . ' title FROM bb_topics_types', 'label'=>getString('category')));
	formAddChannels($f, 'bb_topics', 'topic_id');
	$f->set_field(array('name'=>'description' . langExt(), 'type'=>'textarea', 'label'=>getString('description'), 'class'=>'tinymce'));
	$f->set_order('created_user,is_admin,title' . langExt() . ',type_id,channels,description' . langExt());
	$f->unset_fields('thread_date,type_id,replies');
	langUnsetFields($f, 'title,description');
	langTranslateCheckbox($f, url_id());
	return $f->draw(false, false);
}

function bbDrawRss() {
	global $_josh;
	
	$items = array();
	
	$topics = db_query('SELECT 
			t.id,
			t.title,
			t.description,
			t.is_admin,
			t.thread_date,
			t.replies,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname,
			u.email
		FROM bb_topics t
		JOIN users u ON u.id = t.created_user
		WHERE t.is_active = 1 
		ORDER BY t.thread_date DESC', 15);
	
	while ($t = db_fetch($topics)) {
		if ($t['is_admin']) $t['title'] = 'ADMIN: ' . $t['title'];
		if ($t['replies'] == 1) {
			$t['title'] .= ' (' . $t['replies'] . ' comment)';
		} elseif ($t['replies'] > 1) {
			$t['title'] .= ' (' . $t['replies'] . ' comments)';
		}
		$items[] = array(
			'title' => $t['title'],
			'description' => $t['description'],
			'link' => url_base() . '/bb/topic.php?id=' . $t['id'],
			'date' => $t['thread_date'],
			'author' => $t['email'] . ' (' . $t['firstname'] . ' ' . $t['lastname'] . ')'
		);
	}

	file_rss('Bulletin Board: Last 15 Topics', url_base() . '/bb/', $items, 'bb.xml');
}

function bbGetTopics($where=false, $limit=false) {
	return db_table('SELECT 
			t.id,
			t.title' . langExt() . ' topic,
			t.is_admin,
			t.thread_date last_post,
			t.replies,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname
		FROM bb_topics t
		JOIN users u ON u.id = t.created_user' . 
		getChannelsWhere('bb_topics', 't', 'topic_id') . $where .
		' ORDER BY t.thread_date DESC', $limit);
}

?>