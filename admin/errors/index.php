<? include("../include.php");

drawTop();
echo drawTableStart();
$errors = db_query("SELECT 
		e.name,
		e.created_date,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		e.instances
	FROM errors e
	JOIN users u ON e.created_date = u.user_id
	ORDER BY e.created_date DESC");
while ($e = db_fetch($errors)) {?>
	<tr>
		<td><?=$e["name"]?></td>
		<td><?=$e["first"]?> <?=$e["last"]?></td>
		<td clas="r"><?=$e["created_date"]?></td>
	</tr>
<? }
echo drawTableEnd();
drawBottom();?>