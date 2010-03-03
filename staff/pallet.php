<?php
$return .= '<tr><td colspan="2">' . drawSelectUser('staff', false, true, 0, true, true, "Jump to Staff Member") . '</td></tr>';

$pages = db_table('SELECT url, title' . langExt() . ' title FROM pages WHERE module_id = 6 AND is_active = 1 AND is_hidden <> 1 AND is_admin <> 1 ORDER by precedence');
foreach ($pages as &$p) $p = draw_link('/staff/' . $p['url'], $p['title']);
$return .= draw_table_rows($pages);