<?php
include("include.php");

if ($posting) {
	db_query('DELETE FROM it_system_status');
	db_query('INSERT INTO it_system_status ( message, updated_date, updated_user ) VALUES ("' . format_html($_POST['helpdesk_status']) . '", GETDATE(),' . user() . ')');
	url_change('./');
}

echo drawTop();
echo lib_get('tinymce');

echo draw_javascript_src('/_intranet.seedco.site/lib/tinymce/tinymce_3_3_8/tiny_mce.js');
echo draw_javascript('form_tinymce_init("/css/tinymce-helpdesk-status.css", true);');
?>
<table class="left" cellspacing="1">
	<form action="<?=$request["path_query"]?>" method="post">
	<?=drawHeaderRow("Update Status Message");?>
	<tr>
		<td><?=draw_form_textarea("helpdesk_status", $helpdeskStatus, "tinymce", false);?></td>
	</tr>
	<tr>
		<td class="bottom"><?=draw_form_submit("update message");?></td>
	</tr>
	</form>
</table>
<?=drawBottom();?>