<?php include("include.php");

url_query_require("offices.php");

echo drawTop();

$result = db_query("SELECT
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
			WHERE u.officeID = {$_GET["id"]} $where
			ORDER BY t.created_date DESC");
echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("<a href='offices.php' class='white'>Offices</a> &gt; " . db_grab("SELECT name FROM offices WHERE id = " . $_GET["id"]) . " (" . db_found($result) . ")", 5);
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
<?=drawBottom(); ?>