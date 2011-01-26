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
		$r['topic'] = draw_link($r['link'], $r['topic']);
		$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
		$r['last_post'] = format_date($r['last_post']);
	}
	
	return $t->draw($result, getString('topics_empty'));
}

function bbDrawThreads($limit=false, $where=false) {
	$result = bbGetTopics($where, $limit);
	return 'Thread thing goes here' . BR . BR;
}

function drawTopicForm() {
	global $_GET, $page;
	$f = new form('bb_topics', @$_GET['id'], getString('topic_new'));
	if ($page['is_admin']) {
		$f->set_field(array('name'=>'created_user', 'class'=>'admin', 'type'=>'select', 'sql'=>'SELECT id, CONCAT_WS(", ", lastname, firstname) FROM users WHERE is_active = 1 ORDER BY lastname, firstname', 'default'=>$_SESSION['user_id'], 'required'=>true, 'label'=>getString('posted_by')));
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
	$f->unset_fields('thread_date');
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