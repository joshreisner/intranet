<?php include("include.php");
drawTop();
?>

<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("Assign " . db_grab("SELECT shortName name FROM departments WHERE departmentID = " . $departmentID) . " Tickets", 5);
	echo drawTicketHeader();

	//unassigned tickets
	$result = db_query("select
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.ticketID = t.id) as ticketfollowups,
				t.created_user,
				t.updated_date,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.created_date,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = 3 AND a.user_id = t.created_user) AS is_adminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN users			u ON u.user_id	= t.created_user
			LEFT JOIN intranet_images	m ON u.imageID	= m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND (t.ownerID IS NULL OR t.ownerID = 0) AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Unassigned Tickets</td>
		</tr>
		<? while ($r = db_fetch($result)) echo drawTicketRow($r);
	}
	
	//your tickets
	$result = db_query("SELECT
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.id = t.id) as ticketfollowups,
				t.created_user,
				t.updated_date,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.created_date,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM users_to_modules a where a.module_id = 3 and a.user_id = t.created_user) as is_adminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN users   u ON u.user_id    = t.created_user
			LEFT  JOIN intranet_images  m ON u.imageID   = m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND t.ownerID = " . $_SESSION["user_id"] . " AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Your Tickets</td>
		</tr>
		<? while ($r = db_fetch($result)) echo drawTicketRow($r);
	}
	
	//other tickets
	$result = db_query("select
				t.title,
				t.statusID,
				(SELECT COUNT(*) FROM helpdesk_tickets_followups f where f.id = t.id) as ticketfollowups,
				t.created_user,
				t.updated_date,
				t.departmentID,
				t.id,
				t.ownerID,
				t.priorityID,
				t.created_date,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last,
				(SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = 3 AND a.user_id = t.created_user) AS is_adminIT,
				u.imageID,
				m.width,
				m.height
			FROM helpdesk_tickets t
			JOIN users   u ON u.user_id    = t.created_user
			LEFT  JOIN intranet_images  m ON u.imageID   = m.imageID
			WHERE (t.statusID <> 9 OR t.statusID IS NULL) AND t.ownerID <> 0 AND t.ownerID <> " . $_SESSION["user_id"] . " AND t.departmentID = $departmentID
			ORDER BY t.priorityID");
	if (db_found($result)) {?>
		<tr class="group">
			<td colspan="5">Other People's Tickets</td>
		</tr>
		<?
		while ($r = db_fetch($result)) echo drawTicketRow($r);
	}?>
</table>
<? drawBottom(); ?>