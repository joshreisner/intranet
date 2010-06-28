<?
include("../include.php");

//empty query is confusing
if (isset($_GET["q"]) && empty($_GET["q"])) url_change("/contacts/");

echo drawTop();

function formatArrayForText($array) {
	if (count($array) > 1) {
		$last = array_pop($array);
		return implode(", ", $array) . " and " . $last;
	} else {
		return $array[0];
	}
}
?>
<div id="panel">
	<form method="get" action="./" name="mainsearchform">
	Look for <input type="text" value="<?=@$_GET["q"]?>" name="q" class="field" size="34"></td>
	</form>
</div>
<?
if (isset($_GET["q"])) {
	//assemble where clause
	$searchTerms = explode(" ", $_GET["q"]);
	$counter = 0;
	$skips = array();
	$where = array();
	foreach ($searchTerms as $searchTerm) {
		$searchTerm = str_replace("'", "''", $searchTerm);
		if (in_array($searchTerm, $ignored_words)) {
			$skips[] = $searchTerm;
		} else {
			$terms[] = $searchTerm;
			$where[] = "w$counter.word = '$searchTerm'";
			$joins[] = "INNER JOIN contacts_instances_to_words i2w$counter ON i.id = i2w$counter.instanceID INNER JOIN words w$counter ON i2w$counter.wordID = w$counter.id";
			$counter++;
		}
	}
	if (count($skips)) {
		if (count($skips) == 1) {
			echo drawMessage("<b>Note:</b> The word {$skips[0]} was ignored in your search.");
		} else {
			echo drawMessage("<b>Note:</b> The words " . formatArrayForText($skips) . " were ignored in your search.");
		}
	}
	//$where[] = "o.is_active = 1";
	if (count($where)) {
		$where = implode(" AND ", $where);
		$joins = implode(" ", $joins);
		
		$needle = join('|',$searchTerms);
		
		$result = db_query("
						SELECT
							o.id,
							o.is_active,
							i.varchar_01 firstname,
							i.varchar_02 lastname,
							i.varchar_04 organization,
							i.varchar_08 phone,
							i.created_date last_updated,
							i.created_user user_id
						FROM contacts o
						INNER JOIN contacts_instances i ON i.ID = o.instanceCurrentID
						$joins
						WHERE $where
						ORDER BY 
								i.varchar_02, 
								i.varchar_01"); 
								?>
		<table class="left" cellspacing="1">
			<?
			if (db_found($result)) {
				echo drawHeaderRow("Contacts containing <i>" . formatArrayForText($terms) . "</i>", 4);?>
			<tr>
				<th>Name</th>
				<th>Company</th>
				<th>Phone</th>
			</tr>
			<? while ($c = db_fetch($result)) {
					$c["firstname"]  = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["firstname"]);
					$c["lastname"]  = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["lastname"]);
					$c["organization"] = preg_replace("/($needle)/i","<font style='background-color:#FFFFBB;padding:1px;'><b>\\0</b></font>", $c["organization"]);
					?>
				<tr <?if(!$c["is_active"]){?>class="deleted"<?}?>>
					<td><a href="contact.php?id=<?=$c["id"]?>"><?=$c["lastname"]?>, <?=$c["firstname"]?></a></td>
					<td><?=$c["organization"]?></td>
					<td><?=$c["phone"]?></td>
				</tr>
				<? } 
			} else {
				echo drawHeaderRow("Empty Result", 4);
				echo drawEmptyResult("No contact records contain <i>" . formatArrayForText($terms) . "</i>.");
			}
			?>
		<!--<tr>
			<td class="bottom" colspan="4">
				<?=draw_form_button("Add a new contact", "contact_edit.php")?>
			</td>
		</tr>-->
	</table>
	<? }
}?>
<script>
	<!--
	document.mainsearchform.q.focus();
	//-->
</script>
<?=drawBottom();?>