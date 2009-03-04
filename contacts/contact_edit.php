<?	include("../include.php");

if ($_POST) {
	//clean up values
	$_POST["objectID"]   = ($_POST["objectID"])   ?       $_POST["objectID"]         : "NULL";
	$_POST["varchar_01"] = ($_POST["varchar_01"]) ? "'" . $_POST["varchar_01"] . "'" : "NULL";
	$_POST["varchar_02"] = ($_POST["varchar_02"]) ? "'" . $_POST["varchar_02"] . "'" : "NULL";
	$_POST["varchar_03"] = ($_POST["varchar_03"]) ? "'" . $_POST["varchar_03"] . "'" : "NULL";
	$_POST["varchar_04"] = ($_POST["varchar_04"]) ? "'" . $_POST["varchar_04"] . "'" : "NULL";
	$_POST["varchar_05"] = ($_POST["varchar_05"]) ? "'" . $_POST["varchar_05"] . "'" : "NULL";
	$_POST["varchar_06"] = ($_POST["varchar_06"]) ? "'" . $_POST["varchar_06"] . "'" : "NULL";
	$_POST["varchar_07"] = ($_POST["varchar_07"]) ? "'" . $_POST["varchar_07"] . "'" : "NULL";
	$_POST["varchar_08"] = ($_POST["varchar_08"]) ? "'" . $_POST["varchar_08"] . "'" : "NULL";
	$_POST["varchar_09"] = ($_POST["varchar_09"]) ? "'" . $_POST["varchar_09"] . "'" : "NULL";
	$_POST["varchar_10"] = ($_POST["varchar_10"]) ? "'" . $_POST["varchar_10"] . "'" : "NULL";
	$_POST["varchar_11"] = ($_POST["varchar_11"]) ? "'" . $_POST["varchar_11"] . "'" : "NULL";
	$_POST["numeric_01"] = ($_POST["numeric_01"]) ?       $_POST["numeric_01"]       : "NULL";
	$_POST["text_01"]    = ($_POST["text_01"])    ? "'" . $_POST["text_01"]    . "'" : "NULL";
	
	//create new instance
	db_query("INSERT INTO intranet_instances (
					objectID,
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
					text_01,
					createdOn,
					createdBy
				) VALUES (
					" . $_POST["objectID"] . ",
					" . $_POST["varchar_01"] . ",
					" . $_POST["varchar_02"] . ",
					" . $_POST["varchar_03"] . ",
					" . $_POST["varchar_04"] . ",
					" . $_POST["varchar_05"] . ",
					" . $_POST["varchar_06"] . ",
					" . $_POST["varchar_07"] . ",
					" . $_POST["varchar_08"] . ",
					" . $_POST["varchar_09"] . ",
					" . $_POST["varchar_10"] . ",
					" . $_POST["varchar_11"] . ",
					" . $_POST["numeric_01"] . ",
					" . $_POST["text_01"] . ",
					GETDATE(),
					" . $_SESSION["user_id"] . "
				)");
	$instance = db_grab("SELECT MAX(id) id FROM intranet_instances");

	//handle tags
	reset($_POST);
	while (list($key, $value) = each($_POST)) {
		@list($control, $type, $id) = explode("_", $key);
		if ($control == "tag") {
			$tagID = ($type == "single") ? $value : $id;
			if ($tagID) db_query("INSERT INTO intranet_instances_to_tags ( instanceID, tagID ) VALUES ( {$instance}, {$tagID} )");
		}
	}
	
	//create or update object
	if (isset($_GET["id"])) {
		db_query("UPDATE intranet_objects SET instanceCurrentID = {$instance} WHERE id = " . $_GET["id"]);
	} else {
		db_query("INSERT INTO intranet_objects (
					typeID,
					instanceFirstID,
					instanceCurrentID,
					isActive
					) VALUES ( 
					22,
					{$instance},
					{$instance},
					1
					)");
		$_GET["id"] = db_grab("SELECT MAX(id) id FROM intranet_objects");
		db_query("UPDATE intranet_instances SET objectID = {$_GET["id"]} WHERE id = " . $instance);
	}
	
	//populate search indexes
	$text = $_POST["varchar_01"] . " " . $_POST["varchar_02"] . " " . $_POST["varchar_03"] . " " . $_POST["varchar_04"] . " " . $_POST["varchar_05"] . " " . $_POST["varchar_06"] . " " . $_POST["varchar_07"] . " " . $_POST["varchar_08"] . " " . $_POST["varchar_09"] . " " . $_POST["varchar_10"] . " " . $_POST["varchar_11"] . " " . $_POST["numeric_01"] . " " . $_POST["text_01"];
	updateInstanceWords($instance, $text);
	
	//redirect
	url_change("contact.php?id=" . $_GET["id"]);
}

drawTop();


if (isset($_GET["id"])) {
	$i = db_grab("SELECT
			i.id,
			(SELECT t1.id FROM intranet_tags t1 INNER JOIN intranet_instances_to_tags i2t1 ON t1.id = i2t1.tagID WHERE t1.isActive = 1 AND t1.typeID = 10 AND i2t1.instanceID = i.id) salutation,
			i.varchar_01 first,
			i.varchar_02 last,
			(SELECT t2.id FROM intranet_tags t2 INNER JOIN intranet_instances_to_tags i2t2 ON t2.id = i2t2.tagID WHERE t2.isActive = 1 AND t2.typeID = 11 AND i2t2.instanceID = i.id) suffix,
			i.varchar_03 nickname,
			i.varchar_04 org,
			i.varchar_05 title,
			i.varchar_06 address1,
			i.varchar_07 address2,
			i.numeric_01 zip,
			i.varchar_08 phone,
			i.varchar_09 fax,
			i.varchar_10 cell,
			i.varchar_11 email,
			z.city,
			z.state,
			i.text_01 notes
		FROM intranet_objects o
		INNER JOIN intranet_instances i ON i.id = o.instanceCurrentID
		LEFT  JOIN zip_codes z ON i.numeric_01 = z.zip
		WHERE o.id = " . $_GET["id"]);
}
?>
<script language="javascript">
	<!--
	function validate(form) {
		var errors = new Array();
		if (!form.tag_single_10.value) errors[errors.length] = "the Courtesy Title dropdown must be set";
		if (!form.varchar_01.value.length) errors[errors.length] = "First Name is empty";
		if (!form.varchar_02.value.length) errors[errors.length] = "Last Name is empty";
		if (!form.varchar_06.value.length) errors[errors.length] = "Address 1 is empty";
		if (!form.numeric_01.value.length) errors[errors.length] = "Postal Code is empty";
		<?
			$values = db_query("SELECT id FROM intranet_tags WHERE typeID = 15 AND isActive = 1");
			$checkboxes = array();
			while ($v = db_fetch($values)) $checkboxes[] = "!form.tag_multiple_" . $v["id"] . ".checked";
		?>
		return(showErrors(errors));
	}
	//-->
	
	<!-- if (<?=implode(" && ", $checkboxes);?>) errors[errors.length] = "Contact Department must be checked"; -->
</script>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("View Contact", 2);?>
	<form method="post" action="<?=$request["path_query"]?>" onsubmit="javascript:return validate(this);">
	<?=draw_form_hidden("objectID", @$_GET["id"])?>
	<tr>
		<td class="left">Courtesy Title</td>
		<td>
			<?=draw_form_select("tag_single_10", "SELECT id, tag FROM intranet_tags WHERE typeID = 10 AND isActive = 1 ORDER BY tag", @$i["salutation"])?>
		</td>
	</tr>
	<tr>
		<td class="left">Name</td>
		<td width="82%">
			<?=draw_form_text("varchar_01", @$i["first"], "", 255, "width:150px;")?>
			(<?=draw_form_text("varchar_03", @$i["nickname"], false, 255, "width:100px;")?>)
			<?=draw_form_text("varchar_02", @$i["last"], "", 255, "width:150px;")?>
		</td>
	</tr>
	<tr>
		<td class="left">Suffix</td>
		<td><?=draw_form_select("tag_single_11", "SELECT id, tag FROM intranet_tags WHERE typeID = 11 ORDER BY precedence", @$i["suffix"], false, false, true)?></td>
	</tr>
	<tr>
		<td class="left">Company</td>
		<td><?=draw_form_text("varchar_04", @$i["org"])?></td>
	</tr>
	<tr>
		<td class="left">Job Title</td>
		<td><?=draw_form_text("varchar_05", @$i["title"])?></td>
	</tr>
	<tr valign="top">
		<td class="left">Address 1</td>
		<td><?=draw_form_text("varchar_06", @$i["address1"], false, 255, "width:250px;")?> <i>eg 915 Broadway</i></td>
	</tr>
	<tr valign="top">
		<td class="left">Address 2</td>
		<td><?=draw_form_text("varchar_07", @$i["address2"], false, 255, "width:250px;")?> <i>eg 17th Floor</i></td>
	</tr>
	<tr valign="top">
		<td class="left">Postal Code</td>
		<td><?=draw_form_text("numeric_01", @$i["zip"], false, 5, "width:80px;")?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?=draw_form_text("varchar_08", @$i["phone"], false, 14, "width:120px;")?> <i>format (212) 473-0255</i></td>
	</tr>
	<tr>
		<td class="left">Fax</td>
		<td><?=draw_form_text("varchar_09", @$i["fax"], false, 14, "width:120px;")?></td>
	</tr>
	<tr>
		<td class="left">Cell</td>
		<td><?=draw_form_text("varchar_10", @$i["cell"], false, 14, "width:120px;")?></td>
	</tr>
	<tr>
		<td class="left">E-mail Address</td>
		<td><?=draw_form_text("varchar_11", @$i["email"], false, 255, "width:250px;")?></td>
	</tr>
	<tr valign="top">
		<td class="left">Notes</td>
		<td><?=draw_form_textarea("text_01", @$i["notes"])?></td>
	</tr>
	<tr class="group">
		<td colspan="2"><br>Tags</td>
	</tr>
	<?
	$tags = db_query("select 
					f.tagTypeID,
					f.name,
					f.fieldTypeID,
					f.isRequired
				from intranet_fields f
				join intranet_tags_types t on f.tagTypeID = t.id
				where f.objectTypeID = 22 and t.isactive = 1 
				order by f.precedence");
	while ($t = db_fetch($tags)) {?>
	<tr valign="top">
		<td bgcolor="#<?if($t["isRequired"]){?>FFDDDD<?}else{?>F6F6F6<?}?>" width="18%"><?=$t["name"]?></td>
		<td>
			<? if ($t["fieldTypeID"] == 4) {
				if (isset($_GET["id"])) $v = db_grab("SELECT i2t.tagID FROM intranet_instances_to_tags i2t JOIN intranet_tags t ON i2t.tagID = t.id WHERE i2t.instanceID = {$i["id"]} and t.typeID = {$t["tagTypeID"]} AND t.isActive = 1");
				echo draw_form_select("tag_single_" . $t["tagTypeID"], "SELECT id, tag FROM intranet_tags WHERE typeID = {$t["tagTypeID"]} AND isActive = 1 ORDER BY precedence", @$v["tagID"], false, false, !$t["isRequired"]);
			} elseif ($t["fieldTypeID"] == 5) {?>
				<table class="nospacing">
				<tr valign="top">
					<td width="40%"><table cellpadding="0" cellspacing="0" border="0">
				<?
				if (isset($_GET["id"])) {
				$values = db_query("SELECT 
										t.id, 
										t.tag, 
										(SELECT count(*) FROM intranet_instances_to_tags i2t WHERE i2t.tagID = t.id AND i2t.instanceID = {$i["id"]}) selected
									FROM intranet_tags t
									WHERE t.typeID = {$t["tagTypeID"]}
										AND t.isActive = 1
									ORDER by t.precedence");
				} else {
				$values = db_query("SELECT 
										t.id, 
										t.tag,
										0 selected
									FROM intranet_tags t
									WHERE t.typeID = {$t["tagTypeID"]}
										AND t.isActive = 1
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
		<td colspan="2" align="center" class="gray">
			<? if (isset($_GET["id"])) {
				echo draw_form_submit("  save changes  ");
			} else {
				echo draw_form_submit("  add contact  ");
			}?></td>
	</tr>
	</form>
</table>

<? drawBottom();?>