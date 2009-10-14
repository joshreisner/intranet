<?
include("include.php");

if ($posting) {
	//update topic.  don't update the thread_date, or send any emails
	format_post_bits('is_admin');
	langTranslatePost('title');
	langTranslatePost('description');
	$id = db_save('bb_topics');
	if (getOption('channels')) db_checkboxes('channels', 'bb_topics_to_channels', 'topic_id', 'channel_id', $id);
	bbDrawRss();
	url_change("topic.php?id=" . $_GET["id"]);
}

drawTop();

echo drawTopicForm();

drawBottom();
?>