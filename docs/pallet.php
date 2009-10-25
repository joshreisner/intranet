<?
$result = db_query('SELECT
		d.id, d.title, 
		ISNULL(d.updated_date, d.created_date) updated_date,
		i.icon, i.description, (SELECT COUNT(*) FROM docs_views v WHERE v.documentID = d.id) downloads
		FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	' . getChannelsWhere('docs', 'd', 'doc_id') . '
	ORDER BY downloads DESC', 4);
if (db_found($result)) {
	while ($r = db_fetch($result)) {
		$return .= '
	<tr>
		<td width="16"><a href="/' . $m["folder"] . '/download.php?id=' . $r["id"] . '"><img src="' . $r["icon"] . '" width="16" height="16" border="0" alt="' . $r["description"] . '"></a></td>
		<td width="99%">
			<div style="float:right;"><nobr>' . format_date($r["updated_date"], "", "M d") . '</nobr></div>
			<a href="/' . $m["folder"] . '/download.php?id=' . $r["id"] . '">' . format_string($r["title"], 30) . '</a>
		</td>
	</tr>';
	}
} else {
	$return .= drawEmptyResult("No documents added yet.", 2);
}
