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
						t.createdBy,
						t.updatedOn,
						t.id,
						t.ownerID,
						t.priorityID,
						t.createdOn,
						ISNULL(u.nickname, u.firstname) first,
						u.lastname last,
						u.imageID,
						m.width,
						m.height
					FROM helpdesk_tickets t
					JOIN users  u ON u.userID    = t.createdBy
					LEFT JOIN intranet_images m ON u.imageID   = m.imageID
					WHERE t.ownerID = {$_GET["id"]} $where
					ORDER BY t.createdOn DESC");
	$admin = db_grab("SELECT ISNULL(u.nickname, u.firstname) first FROM users u WHERE u.userID = " . $_GET["id"]);
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