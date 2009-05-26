<?php include("include.php");

url_query_require("users.php");

drawTop();


$result = db_query("select
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.ticketID = t.id) as ticketfollowups,
				t.created_user,
				t.updated_date,
				t.id,
				t.ownerID,
				t.priorityID,
				t.created_date,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = 3 AND a.user_id = t.created_user) is_adminIT
			FROM helpdesk_tickets t
			JOIN users u ON u.id = t.created_user
			WHERE t.created_user = {$_GET["id"]} $where
			ORDER BY t.created_date DESC");
echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?
	$u = db_grab("SELECT ISNULL(nickname, firstname) first, lastname last FROM users WHERE id = " . $_GET["id"]);
	echo drawHeaderRow("<a href='users.php' class='white'>Users</a> &gt; " . $u["first"] . " " . $u["last"] . " (" . db_found($result) . ")", 5);
	if (db_found($result)) {
		echo drawTicketHeader();
		while ($r = db_fetch($result)) echo drawTicketRow($r);
	} else {
		if ($filtered) {
			echo drawEmptyResult("No tickets for this user / month / year.", 5);
		} else {
			echo drawEmptyResult("This user hasn't posted any tickets.", 5);
		}
	}?>
</table>
<? drawBottom(); ?>