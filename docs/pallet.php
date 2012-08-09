<?php
$result = db_query('SELECT
		d.id, 
		d.title' . langExt() . ' title, 
		l.title language,
		ISNULL(d.updated_date, d.created_date) updated_date,
		i.extension,
		i.description
		FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	LEFT JOIN languages l ON d.language_id = l.id
	' . getChannelsWhere('docs', 'd', 'doc_id') . '
	ORDER BY (SELECT COUNT(*) FROM docs_views v WHERE v.documentID = d.id) DESC', 4);
if (db_found($result)) {
	while ($r = db_fetch($result)) {
		$right = (getOption('languages')) ? $r['language'] : format_date($r['updated_date'], '', '%b %d');
		$return .= '
	<tr>
		<td width="16">' . file_icon($r['extension'], '/' . $m['folder'] . '/download.php') . '</td>
		<td width="99%">
			<div class="r" style="float:right; text-align:right; width:56px;">' . $right . '</div>
			<a href="/' . $m["folder"] . '/download.php?id=' . $r["id"] . '">' . format_string($r["title"], 30) . '</a>
		</td>
	</tr>';
	}
} else {
	$return .= drawEmptyResult(getString('documents_empty'), 2);
}
