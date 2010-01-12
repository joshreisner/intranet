<?php
include("include.php");
echo drawTop();

//comings
echo drawStaffList('u.is_active = 1 AND ' . db_datediff('u.startdate') . ' < 60', getString('staff_new_empty'), array('add_edit.php'=>getString('add_new')), getString('staff_new'));

//goings
echo drawStaffList('u.is_active = 0 AND ' . db_datediff("u.endDate", "GETDATE()") . ' < 32', getString('staff_goings_empty'));

echo drawBottom();
?>