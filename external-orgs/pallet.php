<?php
$orgs = db_table('SELECT id, title' . langExt() . ' title FROM external_orgs_types ORDER BY title');
foreach ($orgs as &$o) $o = draw_link('/' . $m["folder"] . '/type.php?id=' . $o["id"], format_string($o["title"], 21));
$return .= draw_table_rows($orgs);
?>