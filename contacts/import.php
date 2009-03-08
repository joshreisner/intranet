<?	include("../include.php");

if ($_POST) {
	//clean up values
	$lines = explode("\r", $_POST["import"]);
	$counter = 0;
	foreach ($lines as $line) {
		$line = trim(str_replace("'", "''", $line));
		if ($_POST["type"] == "new") {
			list($salutation, $varchar_01, $varchar_02, $suffix, $varchar_04, $varchar_05, $varchar_06, $varchar_07, $city, $state, $numeric_01, $varchar_08, $varchar_09, $varchar_10, $varchar_11) = explode("\t", $line);
		} else {
			list($id, $salutation, $varchar_01, $varchar_02, $suffix, $varchar_04, $varchar_05, $varchar_06, $varchar_07, $city, $state, $numeric_01, $varchar_08, $varchar_09, $varchar_10, $varchar_11) = explode("\t", $line);
		}
		if (!$counter && ( //check that first row is header
			($salutation	!= "Courtesy Title") ||
			($varchar_01	!= "First Name") || 
			($varchar_02	!= "Last Name") || 
			($suffix		!= "Suffix") || 
			($varchar_04	!= "Company"))) {
			die("Header does not appear to be correct&mdash;nothing imported!");
		} elseif (!$counter) {
			$counter++;
			continue;
		}
		$varchar_01 = (!empty($varchar_01)) ? "'" . $varchar_01 . "'" : "NULL";
		$varchar_02 = (!empty($varchar_02)) ? "'" . $varchar_02 . "'" : "NULL";
		$varchar_03 = "NULL";
		$varchar_04 = (!empty($varchar_04)) ? "'" . $varchar_04 . "'" : "NULL";
		$varchar_05 = (!empty($varchar_05)) ? "'" . $varchar_05 . "'" : "NULL";
		$varchar_06 = (!empty($varchar_06)) ? "'" . $varchar_06 . "'" : "NULL";
		$varchar_07 = (!empty($varchar_07)) ? "'" . $varchar_07 . "'" : "NULL";
		$varchar_08 = (!empty($varchar_08)) ? "'" . $varchar_08 . "'" : "NULL";
		$varchar_09 = (!empty($varchar_09)) ? "'" . $varchar_09 . "'" : "NULL";
		$varchar_10 = (!empty($varchar_10)) ? "'" . $varchar_10 . "'" : "NULL";
		$varchar_11 = (!empty($varchar_11)) ? "'" . $varchar_11 . "'" : "NULL";
		$numeric_01 = (!empty($numeric_01)) ? substr($numeric_01, 0, 5) : "NULL";
		
		//create new instance
		die("INSERT INTO contacts_instances (
					varchar_01,
					varchar_02,
					varchar_03,
					varchar_04,
					varchar_05,
					varchar_06,
					varchar_07,
					varchar_08,
					varchar_09,
					varchar_10,
					varchar_11,
					numeric_01,
					created_date,
					created_user
				) VALUES (
					" . $varchar_01. ",
					" . $varchar_02. ",
					" . $varchar_03. ",
					" . $varchar_04. ",
					" . $varchar_05. ",
					" . $varchar_06. ",
					" . $varchar_07. ",
					" . $varchar_08. ",
					" . $varchar_09. ",
					" . $varchar_10. ",
					" . $varchar_11. ",
					" . $numeric_01. ",					
					GETDATE(),
					" . $_SESSION["user_id"] . "
				)");
		$instance = db_grab("SELECT MAX(id) id FROM contacts_instances");
	
		//handle tags
		reset($_POST);
		while (list($key, $value) = each($_POST)) {
			@list($control, $type, $id) = explode("_", $key);
			if ($control == "tag") {
				$tagID = ($type == "single") ? $value : $id;
				if ($tagID) db_query("INSERT INTO contacts_instances_to_tags ( instanceID, tagID ) VALUES ( {$instance["id"]}, {$tagID} )");
			}
		}
		
		//create or update object
		db_query("INSERT INTO contacts (
					typeID,
					instanceFirstID,
					instanceCurrentID,
					is_active
					) VALUES ( 
					22,
					{$instance["id"]},
					{$instance["id"]},
					1
					)");
		$_GET = db_grab("SELECT MAX(id) id FROM contacts");
		db_query("UPDATE contacts_instances SET objectID = {$_GET["id"]} WHERE id = " . $instance["id"]);
		
		//populate search indexes
		$text = $varchar_01 . " " . $varchar_02 . " " . $varchar_03 . " " . $varchar_04 . " " . $varchar_05 . " " . $varchar_06 . " " . $varchar_07 . " " . $varchar_08 . " " . $varchar_09 . " " . $varchar_10 . " " . $varchar_11 . " " . $numeric_01;
		updateInstanceWords($instance["id"], $text);
		
		$counter++;
	}
	
	
	//redirect
	url_change("import.php");
}

drawTop();


?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Import Contacts", 2);?>
	<form method="post" action="<?=$request["path_query"]?>">
	<tr>
		<td class="gray">Import</td>
		<td>
			<input type="radio" name="type" value="new" checked> New Contacts (must be in correct format)<br>
			<input type="radio" name="type" value="update" checked> Updated Contacts (must be in correct format with ID numbers)
		</td>
	</tr>
	<tr>
		<td class="gray">Excel Content</td>
		<td><textarea name="import" style="height:200px;" class="field"></textarea></td>
	</tr>
	<tr class="group">
		<td colspan="2">Tags</td>
	</tr>
	<?
	$tags = db_query("select 
					f.tagTypeID,
					f.name,
					f.fieldTypeID,
					f.isRequired
				from contacts_fields f
				join intranet_tags_types t on f.tagTypeID = t.id
				where f.objectTypeID = 22 and t.is_active = 1 
				order by f.precedence");
	while ($t = db_fetch($tags)) {?>
	<tr>
		<td bgcolor="#<?if($t["isRequired"]){?>FFDDDD<?}else{?>F6F6F6<?}?>" width="18%"><?=$t["name"]?></td>
		<td class="input" width="82%">
			<? if ($t["fieldTypeID"] == 4) {
				if (isset($_GET["id"])) $v = db_grab("SELECT i2t.tagID FROM contacts_instances_to_tags i2t JOIN intranet_tags t ON i2t.tagID = t.id WHERE i2t.instanceID = {$i["id"]} and t.typeID = {$t["tagTypeID"]} AND t.is_active = 1");
				echo form_select("tag_single_" . $t["tagTypeID"], "SELECT id, tag FROM intranet_tags WHERE typeID = {$t["tagTypeID"]} AND is_active = 1 ORDER BY precedence", @$v["tagID"], false, "field", false, !$t["isRequired"]);
			} elseif ($t["fieldTypeID"] == 5) {?>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr valign="top">
					<td width="40%"><table cellpadding="0" cellspacing="0" border="0">
				<?
				if (isset($_GET["id"])) {
				$values = db_query("SELECT 
										t.id, 
										t.tag, 
										(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.tagID = t.id AND i2t.instanceID = {$i["id"]}) selected
									FROM intranet_tags t
									WHERE t.typeID = {$t["tagTypeID"]}
										AND t.is_active = 1
									ORDER by t.precedence");
				} else {
				$values = db_query("SELECT 
										t.id, 
										t.tag,
										0 selected
									FROM intranet_tags t
									WHERE t.typeID = {$t["tagTypeID"]}
										AND t.is_active = 1
									ORDER by t.precedence");
				}
				$oneFound = false;
				while ($v = db_fetch($values)) {?>
					<tr>
						<td><input type="checkbox" name="tag_multiple_<?=$v["id"]?>"<? if ($v["selected"]) {?> checked<?}?>></td>
						<td class="input"><?=$v["tag"]?></td>
					</tr>
					<? if (isset($v["selected"]) && $v["selected"]) $oneFound = true;
				}?>
				</table>
					</td>
					<td class="input" width="60%"><font color="#D8282D"><? if ($t["isRequired"] && !$oneFound) {?>&nbsp;&#187; new required value!<?}?></td>
				</tr>
			</table>

			<?}?>
		</td>
	</tr>
	<?}?>
	<tr>
		<td colspan="2" align="center" bgcolor="#F6F6F6">
			<?=draw_form_submit("  run import  ");?>
		</td>
	</tr>
	</form>
</table>

<? drawBottom();?>