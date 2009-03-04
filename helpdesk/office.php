<?php include("include.php");

url_query_require("offices.php");

drawTop();


$result = db_query("SELECT
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.ticketID = t.id) as ticketfollowups,
				t.createdBy,
				t.updatedOn,
				t.id,
				t.ownerID,
				t.priorityID,
				t.createdOn,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM administrators a WHERE a.moduleID = 3 AND a.userID = t.createdby) isAdminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN intranet_users u ON u.userID = t.createdBy
			LEFT JOIN intranet_images m ON u.imageID = m.imageID
			WHERE u.officeID = {$_GET["id"]} $where
			ORDER BY t.createdOn DESC");
echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("<a href='offices.php' class='white'>Offices</a> &gt; " . db_grab("SELECT name FROM intranet_offices WHERE id = " . $_GET["id"]) . " (" . db_found($result) . ")", 5);
	if (db_found($result)) {
		echo drawTicketHeader();
		while ($r = db_fetch($result)) echo drawTicketRow($r);
	} else {
		if ($filtered) {
			echo drawEmptyResult("No tickets for this office / month / year.", 5);
		} else {
			echo drawEmptyResult("No tickets have been posted from this office.", 5);
		}
	}?>
</table>
<? drawBottom(); ?>