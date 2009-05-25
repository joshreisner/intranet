<?php include("include.php");

//delete type
if (isset($_GET["deleteType"])) {
	db_query("DELETE FROM helpdesk_tickets_types WHERE id = " . $_GET["id"]);
	url_change("types.php");
}

drawTop();	

$where1 = (isset($_GET["id"])) ? "= " . $_GET["id"] : "IS NULL";

$tickets = db_query("select
			t.title,
			t.statusID,
			t.type_id,
			(SELECT COUNT(*) FROM helpdesk_tickets_followups f WHERE f.ticketID = t.id) as ticketfollowups,
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
		INNER JOIN users   u ON u.id    = t.created_user
		WHERE t.type_id $where1 $where
		ORDER BY t.created_date DESC");

echo drawTicketFilter();
?>

<table class="left" cellspacing="1">
	<? 
	if (isset($_GET["id"])) {
		$type	= db_grab("SELECT description name FROM helpdesk_tickets_types WHERE id = " . $_GET["id"]);
		if (db_found($tickets)) {
			echo drawHeaderRow("<a class='white' href='types.php'>Types</a> &gt; " . $type . " (" . db_found($tickets) . ")", 5, "edit name", "type_add_edit.php?id=" . $_GET["id"]);
			echo drawTicketHeader();
			while ($r = db_fetch($tickets)) echo drawTicketRow($r, "type");
		} else {
			if ($filtered) {
				echo drawHeaderRow("<a class='white' href='types.php'>Types</a> &gt; " . $type . " (" . db_found($tickets) . ")", 5);
				echo drawEmptyResult("No tickets have this type / month / year.", 5);
			} else {
				echo drawHeaderRow("<a class='white' href='types.php'>Types</a> &gt; " . $type . " (" . db_found($tickets) . ")", 5, "edit name", "type_add_edit.php?id=" . $_GET["id"], "delete", $request["path_query"] . "&deleteType=true");
				echo drawEmptyResult("No tickets are tagged as this type.  You can delete the type above.", 5);
			}
		}
	} else {
		echo drawHeaderRow("<a class='white' href='types.php'>Types</a> &gt; No Type Set" . " (" . db_found($tickets) . ")", 5);
		if (db_found($tickets)) {
			echo drawTicketHeader();
			while ($r = db_fetch($tickets)) echo drawTicketRow($r, "type");
		} else {
			if ($filtered) {
				echo drawEmptyResult("No tickets are untyped in this month / year.", 5);
			} else {
				echo drawEmptyResult("No tickets are untyped!  Excellent!", 5);
			}
		}
	}
	?>
</table>
<? drawBottom(); ?>