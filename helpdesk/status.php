<?php
include("include.php");

if ($posting) {
	format_post_html('message');
	db_query('DELETE FROM it_system_status');
	db_query('INSERT INTO it_system_status ( message, updated_date, updated_user ) VALUES (
		' . $_POST['message'] . ',
		GETDATE(),
		' . $_SESSION['user_id'] . '
	)');
	url_change('./');
}

echo drawTop();
echo lib_get('tinymce');

echo draw_javascript('
	function validate(form) {
		tinyMCE.triggerSave();
		//if (form.message.value.length) return true;
		//alert("please enter a status message");
		//return false;
	}
	form_tinymce_init("/styles/helpdesk-status.css", true);
');
?>
<table class="left" cellspacing="1">
	<form action="<?=$request["path_query"]?>" method="post" onsubmit="javascript:return validate(this);">
	<?=drawHeaderRow("Update Status Message");?>
	<tr>
		<td><?=draw_form_textarea("message", $helpdeskStatus, "tinymce", false);?></td>
	</tr>
	<tr>
		<td class="bottom"><?=draw_form_submit("update message");?></td>
	</tr>
	</form>
</table>
<?=drawBottom();?>