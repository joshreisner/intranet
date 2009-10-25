<?php
include("../include.php");

echo drawTop();

db_query("DELETE FROM users_to_channels");
$users = db_query("SELECT id, organization_id FROM users");
while ($u = db_fetch($users)) {
	if ($u["organization_id"] == 3) {
		db_query("INSERT INTO users_to_channels ( user_id, channel_id ) VALUES ( {$u["id"]}, 1 )");
		echo "coalition<br>";
	} else {
		db_query("INSERT INTO users_to_channels ( user_id, channel_id ) VALUES ( {$u["id"]}, 6 )");	
		echo "immigration<br>";
	}
}

echo drawBottom();?>