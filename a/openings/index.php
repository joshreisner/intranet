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
			(SELECT m.meta_value FROM wp_postmeta m WHERE m.post_id = p.id AND m.meta_key = "job-location" LIMIT 1) "location"
		FROM wp_posts p
		WHERE p.id = ' . $_GET['id']);
	?>
	<table class="left" cellspacing="1">
		<?php echo drawHeaderRow("View Position", 2)?>
		<tr>
			<td class="left">Location</td>
			<td><?php echo $r['location']?></td>
		</tr>
		<tr>
			<td class="left">Position</td>
			<td class="text">
				<h1><?php echo $r['title']?></h1><br/>
				<?php echo nl2br($r['description'])?>
			</td>
		</tr>
		<tr>
			<td class="left">Updated</td>
			<td><?php echo format_date($r['updated_date'])?></td>
		</tr>
	</table>
	<?php
} else {
	//jobs list
?>
	<table class="left" cellspacing="1">
		<?php echo drawHeaderRow('Open Positions', 2)?>
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
			(SELECT m.meta_value FROM wp_postmeta m WHERE m.post_id = p.id AND m.meta_key = "job-location" LIMIT 1) "location"
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
				<td><a href="./?id=<?php echo $r['id']?>"><?php echo $r['title']?></a></td>
				<td class="r"><?php echo format_date($r['updated_date'])?></td>
			</tr>
			<?php }?>
	</table>
<?php
}
db_switch();
echo drawBottom();
?>