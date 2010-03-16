<?  include("../../include.php");

if (url_action("delete")) {
	db_query("DELETE FROM queries WHERE id = " . $_GET["id"]);
	url_drop();
}

echo drawTop();
?>	
<table class="left" cellspacing="1">
	<? if ($page['is_admin']) {
		echo drawHeaderRow("Reports", 6, "new", "query_edit.php");
	} else {
		echo drawHeaderRow("Reports", 5);
	}?>
	<tr>
		<th align="left" width="16"></th>
		<th align="left">Report Name</th>
		<th width="60">DLs</th>
		<th width="80">C/R</th>
		<th align="right">Updated</th>
		<? if ($page['is_admin']) {?><th width="16"></th><? }?>
	</tr>
	<? 
	if ($_josh["db"]["language"] == "mssql") {
		$result = db_query("SELECT 
				q.id,
				q.name,
				q.description,
				ISNULL(q.updated_date, q.created_date) updated_date,
				(SELECT count(*) FROM queries_executions e WHERE e.queryID = q.id) downloads,
				(SELECT TOP 1 num_columns FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC) num_columns,
				(SELECT TOP 1 num_rows    FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC) num_rows
			FROM queries q
			WHERE q.is_active = 1
			ORDER BY ISNULL(q.updated_date, q.created_date) DESC");
	} elseif ($_josh["db"]["language"] == "mysql") {
		$result = db_query("SELECT 
				q.id,
				q.name,
				q.description,
				ISNULL(q.updated_date, q.created_date) updated_date,
				(SELECT count(*) FROM queries_executions e WHERE e.queryID = q.id) downloads,
				(SELECT num_columns FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC LIMIT 1) num_columns,
				(SELECT num_rows    FROM queries_executions e WHERE e.queryID = q.id ORDER BY e.executedOn DESC LIMIT 1) num_rows
			FROM queries q
			WHERE q.is_active = 1
			ORDER BY ISNULL(q.updated_date, q.created_date) DESC");
	}
	while ($r = db_fetch($result)) {?>
	<tr height="46">
		<td><a href="download.php?id=<?=$r["id"]?>"><img src="<?=DIRECTORY_WRITE?>/images/doctypes/xls.png" width="16" height="16" border="0"></a></td>
		<td><a href="download.php?id=<?=$r["id"]?>"><b><?=$r["name"]?></b></a><? if($page['is_admin']){?>&nbsp;&nbsp;/&nbsp;<a href="query_edit.php?id=<?=$r["id"]?>">edit</a><?}?><br><?=$r["description"]?></td>
		<td align="center"><?=number_format($r["downloads"])?></td>
		<td align="center"><nobr><?=number_format($r["num_columns"])?> / <?=number_format($r["num_rows"])?></nobr></td>
		<td align="right"><nobr><?=format_date($r["updated_date"])?></nobr></td>
		<?=drawDeleteColumn("Delete this database query?", $r["id"])?>
	</tr>
	<? }?>
</table>
<?=drawBottom() ?>