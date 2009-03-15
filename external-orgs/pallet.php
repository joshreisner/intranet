<?php
$orgs = db_query("SELECT id, description FROM external_orgs_types ORDER BY description", 4);
$types = array();
while ($o = db_fetch($orgs)) {
	$types[] = '<a href="' . $m["url"] . '/?type=' . $o["id"] . '">' . $o["description"] . '</a>';
}
	?>
	<tr>
		<td width="50%"><?=@$types[0]?></td>
		<td width="50%"><?=@$types[1]?></td>
	</tr>
	<tr>
		<td width="50%"><?=@$types[2]?></td>
		<td width="50%"><?=@$types[3]?></td>
	</tr>
