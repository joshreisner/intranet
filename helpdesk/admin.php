<?php include("include.php");
url_query_require("admins.php");
drawTop();

echo drawTicketFilter();
?>

<table class="left" cellspacing="1">
	<?
	$result = db_query("SELECT
						t.title,
						t.statusID,
						(SELECT COUNT(*) FROM helpdesk_tickets_followups f WHERE f.ticketID = t.id) as ticketfollowups,
						t.created_user,
						t.updated_date,
						t.id,
						t.ownerID,
						t.priorityID,
						t.created_date,
						ISNULL(u.nickname, u.firstname) first,
						u.lastname last
					FROM helpdesk_tickets t
					JOIN users  u ON u.user_id    = t.created_user
					WHERE t.ownerID = {$_GET["id"]} $where
					ORDER BY t.created_date DESC");
	$admin = db_grab("SELECT ISNULL(u.nickname, u.firstname) first FROM users u WHERE u.user_id = " . $_GET["id"]);
	echo drawHeaderRow("<a href='admins.php' class='white'>Admins</a> &gt; " . $admin["first"] . " (" . db_found($result) . ")", 5);
	
	if (db_found($result)) {
		echo drawTicketHeader();
		while ($r = db_fetch($result)) echo drawTicketRow($r);
	} else {
		if ($filtered) {
			echo drawEmptyResult("No tickets were assigned to this admin in this month / year", 5);
		} else {
			echo drawEmptyResult("No tickets were assigned to this admin.", 5);
		}
	}
	?>
</table>
<? drawBottom(); ?>