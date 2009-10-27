<?php
include('../include.php');

echo drawTop();

$clips = db_query('SELECT
	c.id,
	c.title,
	c.url,
	c.publication,
	c.pub_date,
	c.description,
	t.title type
	FROM press_clips c
	JOIN press_clips_types t ON c.type_id = t.id
	WHERE c.is_active = 1 AND ' . db_datediff('c.pub_date') . ' < 7
	ORDER BY t.precedence, c.pub_date');

$return = '';
$lastType = '';
while ($c = db_fetch($clips)) {
	if ($lastType != $c['type']) {
		$return .= '<div style="font-size:18px;margin-top:24px;">' . $c['type'] . ':</div>';
		$lastType = $c['type'];
	}
	$return .= 
		draw_link($c['url'], $c['title'], false, array('style'=>'font-size:14px;')) . '<br>' . 
		$c['publication'] . '<br>' . 
		format_date($c['pub_date'], ' ', 'M d, Y', false) . 
		$c['description'];
	
}
echo draw_div_class('press_export', $return);

echo '<textarea class="press_export">' . htmlentities($return, ENT_QUOTES, 'UTF-8') . '</textarea>';

echo drawBottom();
?>