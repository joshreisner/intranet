<?php
include('../../include.php');
echo drawTop();
db_switch('seedco_wp');

if (url_id()) {
	//job page
	$r = db_grab('SELECT 
			p.id,
			p.post_title "title",
			p.post_status "status",
			p.post_content "description",
			p.post_modified "updated_date",
			(SELECT m.meta_value FROM wp_postmeta m WHERE m.post_id = p.id AND m.meta_key = "job-location") "location"
		FROM wp_posts p
		WHERE p.id = ' . $_GET['id']);
	?>
	<table class="left" cellspacing="1">
		<?=drawHeaderRow("View Position", 2)?>
		<tr>
			<td class="left">Location</td>
			<td><?=$r['location']?></td>
		</tr>
		<tr>
			<td class="left">Position</td>
			<td class="text">
				<h1><?=$r['title']?></h1><br/>
				<?=$r['description']?>
			</td>
		</tr>
		<tr>
			<td class="left">Updated</td>
			<td><?=format_date($r['updated_date'])?></td>
		</tr>
	</table>
	<?php
} else {
	//jobs list
?>
	<table class="left" cellspacing="1">
		<?=drawHeaderRow('Open Positions', 2)?>
		<tr>
			<th width="80%">Title</th>
			<th class="r" width="20%"><nobr>Last Update</nobr></th>
		</tr>
		<?php
		$result = db_query('SELECT 
			p.id,
			p.post_title "title",
			p.post_status "status",
			p.post_content "content",
			p.post_modified "updated_date",
			(SELECT m.meta_value FROM wp_postmeta m WHERE m.post_id = p.id AND m.meta_key = "job-location") "location"
		FROM wp_posts p
		WHERE post_type = "careers" AND (post_status = "publish" or post_status = "draft")
		ORDER BY 6');
		
		$lastLocation = "";
		while ($r = db_fetch($result)) {
		if ($r['location'] != $lastLocation) {
			$lastLocation = $r['location'];
			echo '<tr class="group"><td colspan="2">' . $lastLocation . '</td></tr>';
			}?>
			<tr>
				<td><a href="./?id=<?=$r['id']?>"><?=$r['title']?></a></td>
				<td class="r"><?=format_date($r['updated_date'])?></td>
			</tr>
			<? }?>
	</table>
<?php
}
db_switch();
echo drawBottom();
?>